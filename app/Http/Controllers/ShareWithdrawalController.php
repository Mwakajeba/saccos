<?php

namespace App\Http\Controllers;

use App\Models\ShareWithdrawal;
use App\Models\ShareAccount;
use App\Models\ShareProduct;
use App\Models\BankAccount;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ShareWithdrawalController extends Controller
{
    /**
     * Display a listing of share withdrawals
     */
    public function index()
    {
        return view('shares.withdrawals.index');
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getShareWithdrawalsData(Request $request)
    {
        if ($request->ajax()) {
            try {
                $withdrawals = ShareWithdrawal::with([
                    'shareAccount.customer',
                    'shareAccount.shareProduct',
                    'bankAccount',
                    'branch',
                    'company'
                ])->select('share_withdrawals.*');

                return DataTables::eloquent($withdrawals)
                ->addIndexColumn()
                ->addColumn('account_number', function ($withdrawal) {
                    return $withdrawal->shareAccount->account_number ?? 'N/A';
                })
                ->addColumn('customer_name', function ($withdrawal) {
                    return $withdrawal->shareAccount->customer->name ?? 'N/A';
                })
                ->addColumn('customer_number', function ($withdrawal) {
                    return $withdrawal->shareAccount->customer->customerNo ?? 'N/A';
                })
                ->addColumn('share_product_name', function ($withdrawal) {
                    return $withdrawal->shareAccount->shareProduct->share_name ?? 'N/A';
                })
                ->addColumn('withdrawal_amount_formatted', function ($withdrawal) {
                    return number_format($withdrawal->withdrawal_amount, 2);
                })
                ->addColumn('number_of_shares_formatted', function ($withdrawal) {
                    return number_format($withdrawal->number_of_shares, 4);
                })
                ->addColumn('withdrawal_fee_formatted', function ($withdrawal) {
                    return $withdrawal->withdrawal_fee ? number_format($withdrawal->withdrawal_fee, 2) : '0.00';
                })
                ->addColumn('total_amount_formatted', function ($withdrawal) {
                    return number_format($withdrawal->total_amount, 2);
                })
                ->addColumn('withdrawal_date_formatted', function ($withdrawal) {
                    return $withdrawal->withdrawal_date ? $withdrawal->withdrawal_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('bank_account_name', function ($withdrawal) {
                    return $withdrawal->bankAccount->name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($withdrawal) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'approved' => '<span class="badge bg-success">Approved</span>',
                        'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    ];
                    return $badges[$withdrawal->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('actions', function ($withdrawal) {
                    $actions = '';
                    $encodedId = Hashids::encode($withdrawal->id);

                    // View action
                    $actions .= '<a href="' . route('shares.withdrawals.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Edit action
                    $actions .= '<a href="' . route('shares.withdrawals.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';

                    // Delete action
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="Withdrawal #' . $withdrawal->id . '" title="Delete"><i class="bx bx-trash"></i></button>';

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
            } catch (\Exception $e) {
                Log::error('Share Withdrawals DataTable Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'draw' => $request->input('draw', 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to load share withdrawals data. Please refresh the page.'
                ], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show the form for creating a new share withdrawal
     */
    public function create()
    {
        // Get active share accounts with their relationships (only those that allow withdrawals)
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->whereHas('shareProduct', function($query) {
                $query->where('allow_share_withdrawals', true);
            })
            ->orderBy('account_number')
            ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::orderBy('name')->get();

        return view('shares.withdrawals.create', compact('shareAccounts', 'bankAccounts'));
    }

    /**
     * Store a newly created share withdrawal
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'share_account_id' => 'required|exists:share_accounts,id',
            'withdrawal_date' => 'required|date',
            'number_of_shares' => 'required|numeric|min:0.0001',
            'transaction_reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'cheque_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Get share account and product
            $shareAccount = ShareAccount::with('shareProduct')->findOrFail($request->share_account_id);
            $shareProduct = $shareAccount->shareProduct;
            
            // Check if withdrawals are allowed for this product
            if (!$shareProduct->allow_share_withdrawals) {
                throw new \Exception('Share withdrawals are not allowed for this share product.');
            }

            // Get chart accounts from share product
            $liabilityAccountId = $shareProduct->liability_account_id;
            $shareCapitalAccountId = $shareProduct->share_capital_account_id;
            
            if (!$liabilityAccountId) {
                throw new \Exception('Share product does not have a liability account configured. Please configure chart accounts in the share product.');
            }

            // Calculate withdrawal amount based on number of shares
            $nominalPrice = $shareProduct->nominal_price ?? 0;
            $withdrawalAmount = $request->number_of_shares * $nominalPrice;

            // Validate withdrawal amount against product constraints
            if ($shareProduct->minimum_withdrawal_amount && $withdrawalAmount < $shareProduct->minimum_withdrawal_amount) {
                $validator->errors()->add('number_of_shares', 'Withdrawal amount must be at least ' . number_format($shareProduct->minimum_withdrawal_amount, 2));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if ($shareProduct->maximum_withdrawal_amount && $withdrawalAmount > $shareProduct->maximum_withdrawal_amount) {
                $validator->errors()->add('number_of_shares', 'Withdrawal amount must not exceed ' . number_format($shareProduct->maximum_withdrawal_amount, 2));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Check if account has sufficient balance
            if ($shareAccount->share_balance < $request->number_of_shares) {
                $validator->errors()->add('number_of_shares', 'Insufficient share balance. Available: ' . number_format($shareAccount->share_balance, 4));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Check if partial withdrawal is allowed
            if (!$shareProduct->allow_partial_withdrawal && $request->number_of_shares != $shareAccount->share_balance) {
                $validator->errors()->add('number_of_shares', 'Partial withdrawal is not allowed. You must withdraw all shares (' . number_format($shareAccount->share_balance, 4) . ')');
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Calculate withdrawal fee if product has withdrawal fees
            $withdrawalFee = 0;
            if ($shareProduct->withdrawal_fee && $shareProduct->withdrawal_fee > 0) {
                if ($shareProduct->withdrawal_fee_type === 'fixed') {
                    $withdrawalFee = $shareProduct->withdrawal_fee;
                } elseif ($shareProduct->withdrawal_fee_type === 'percentage') {
                    $withdrawalFee = ($withdrawalAmount * $shareProduct->withdrawal_fee) / 100;
                }
            }

            // Net amount to be paid out (withdrawal amount minus fee)
            $totalAmount = $withdrawalAmount - $withdrawalFee;

            if ($totalAmount <= 0) {
                throw new \Exception('Total withdrawal amount after fees must be greater than 0.');
            }

            // Get bank account for GL transaction
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $user = auth()->user();

            // Create withdrawal
            $withdrawal = ShareWithdrawal::create([
                'share_account_id' => $request->share_account_id,
                'withdrawal_date' => $request->withdrawal_date,
                'withdrawal_amount' => $withdrawalAmount,
                'number_of_shares' => $request->number_of_shares,
                'withdrawal_fee' => $withdrawalFee,
                'total_amount' => $totalAmount,
                'transaction_reference' => $request->transaction_reference,
                'bank_account_id' => $request->bank_account_id,
                'liability_account_id' => $liabilityAccountId,
                'share_capital_account_id' => $shareCapitalAccountId,
                'cheque_number' => $request->cheque_number,
                'notes' => $request->notes,
                'status' => 'approved', // Auto-approve for now
                'branch_id' => $user->branch_id ?? null,
                'company_id' => $user->company_id ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Create GL Transactions
            $customerId = $shareAccount->customer_id;
            $description = "Share withdrawal for {$shareAccount->account_number} - " . ($request->notes ?: "Withdrawal #{$withdrawal->id}");

            // Credit: Bank Account (money paid out)
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $customerId,
                'amount' => $totalAmount,
                'nature' => 'credit',
                'transaction_id' => $withdrawal->id,
                'transaction_type' => 'share_withdrawal',
                'date' => $request->withdrawal_date,
                'description' => $description,
                'branch_id' => $user->branch_id ?? null,
                'user_id' => $user->id,
            ]);

            // Debit: Share Capital Account (if provided), otherwise Liability Account
            // Only debit one account to maintain double-entry balance
            $debitAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
            
            GlTransaction::create([
                'chart_account_id' => $debitAccountId,
                'customer_id' => $customerId,
                'amount' => $totalAmount,
                'nature' => 'debit',
                'transaction_id' => $withdrawal->id,
                'transaction_type' => 'share_withdrawal',
                'date' => $request->withdrawal_date,
                'description' => $description,
                'branch_id' => $user->branch_id ?? null,
                'user_id' => $user->id,
            ]);

            // Update share account balance (decrease)
            $shareAccount->share_balance -= $request->number_of_shares;
            $shareAccount->last_transaction_date = $request->withdrawal_date;
            $shareAccount->updated_by = $user->id;
            $shareAccount->save();

            DB::commit();

            return redirect()->route('shares.withdrawals.index')
                ->with('success', 'Share withdrawal created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Withdrawal Creation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Failed to create share withdrawal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified share withdrawal
     */
    public function show($id)
    {
        $withdrawal = ShareWithdrawal::with([
            'shareAccount.customer',
            'shareAccount.shareProduct',
            'bankAccount',
            'branch',
            'company',
            'createdBy',
            'updatedBy'
        ])->findOrFail(Hashids::decode($id)[0]);

        return view('shares.withdrawals.show', compact('withdrawal'));
    }

    /**
     * Show the form for editing the specified share withdrawal
     */
    public function edit($id)
    {
        $withdrawal = ShareWithdrawal::with(['shareAccount.shareProduct'])->findOrFail(Hashids::decode($id)[0]);
        
        // Get active share accounts (only those that allow withdrawals)
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->whereHas('shareProduct', function($query) {
                $query->where('allow_share_withdrawals', true);
            })
            ->orderBy('account_number')
            ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::orderBy('name')->get();

        return view('shares.withdrawals.edit', compact('withdrawal', 'shareAccounts', 'bankAccounts'));
    }

    /**
     * Update the specified share withdrawal
     */
    public function update(Request $request, $id)
    {
        $withdrawal = ShareWithdrawal::with(['shareAccount.shareProduct'])->findOrFail(Hashids::decode($id)[0]);

        $validator = Validator::make($request->all(), [
            'share_account_id' => 'required|exists:share_accounts,id',
            'withdrawal_date' => 'required|date',
            'number_of_shares' => 'required|numeric|min:0.0001',
            'transaction_reference' => 'nullable|string|max:255',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'cheque_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $shareAccount = ShareAccount::with('shareProduct')->findOrFail($request->share_account_id);
            $shareProduct = $shareAccount->shareProduct;
            
            // Get chart accounts from share product
            $liabilityAccountId = $shareProduct->liability_account_id;
            $shareCapitalAccountId = $shareProduct->share_capital_account_id;
            
            if (!$liabilityAccountId) {
                throw new \Exception('Share product does not have a liability account configured.');
            }

            // Calculate withdrawal amount
            $nominalPrice = $shareProduct->nominal_price ?? 0;
            $withdrawalAmount = $request->number_of_shares * $nominalPrice;

            // Calculate withdrawal fee
            $withdrawalFee = 0;
            if ($shareProduct->withdrawal_fee && $shareProduct->withdrawal_fee > 0) {
                if ($shareProduct->withdrawal_fee_type === 'fixed') {
                    $withdrawalFee = $shareProduct->withdrawal_fee;
                } elseif ($shareProduct->withdrawal_fee_type === 'percentage') {
                    $withdrawalFee = ($withdrawalAmount * $shareProduct->withdrawal_fee) / 100;
                }
            }

            $totalAmount = $withdrawalAmount - $withdrawalFee;

            // If changing account or shares, reverse old balance and apply new
            $oldShares = $withdrawal->number_of_shares;
            $newShares = $request->number_of_shares;

            // Delete old GL transactions
            GlTransaction::where('transaction_id', $withdrawal->id)
                ->where('transaction_type', 'share_withdrawal')
                ->delete();

            // Get bank account for GL transaction
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $user = auth()->user();

            // Update withdrawal
            $withdrawal->update([
                'share_account_id' => $request->share_account_id,
                'withdrawal_date' => $request->withdrawal_date,
                'withdrawal_amount' => $withdrawalAmount,
                'number_of_shares' => $newShares,
                'withdrawal_fee' => $withdrawalFee,
                'total_amount' => $totalAmount,
                'transaction_reference' => $request->transaction_reference,
                'bank_account_id' => $request->bank_account_id,
                'liability_account_id' => $liabilityAccountId,
                'share_capital_account_id' => $shareCapitalAccountId,
                'cheque_number' => $request->cheque_number,
                'notes' => $request->notes,
                'status' => $request->status,
                'updated_by' => $user->id,
            ]);

            // Update share account balance (only if approved)
            if ($request->status === 'approved') {
                // Reverse old shares (add back)
                $oldAccount = $withdrawal->shareAccount;
                if ($oldAccount) {
                    $oldAccount->share_balance += $oldShares;
                    $oldAccount->save();
                }

                // Apply new shares (subtract)
                $shareAccount->share_balance -= $newShares;
                $shareAccount->last_transaction_date = $request->withdrawal_date;
                $shareAccount->updated_by = $user->id;
                $shareAccount->save();

                // Create GL Transactions
                $customerId = $shareAccount->customer_id;
                $description = "Share withdrawal for {$shareAccount->account_number} - " . ($request->notes ?: "Withdrawal #{$withdrawal->id}");

                // Credit: Bank Account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $customerId,
                    'amount' => $totalAmount,
                    'nature' => 'credit',
                    'transaction_id' => $withdrawal->id,
                    'transaction_type' => 'share_withdrawal',
                    'date' => $request->withdrawal_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Debit: Share Capital Account (if provided), otherwise Liability Account
                $debitAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
                
                GlTransaction::create([
                    'chart_account_id' => $debitAccountId,
                    'customer_id' => $customerId,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $withdrawal->id,
                    'transaction_type' => 'share_withdrawal',
                    'date' => $request->withdrawal_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();

            return redirect()->route('shares.withdrawals.index')
                ->with('success', 'Share withdrawal updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Withdrawal Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update share withdrawal: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified share withdrawal
     */
    public function destroy($id)
    {
        try {
            $withdrawal = ShareWithdrawal::with('shareAccount')->findOrFail(Hashids::decode($id)[0]);

            DB::beginTransaction();

            // Reverse share account balance if approved (add back the shares)
            if ($withdrawal->status === 'approved' && $withdrawal->shareAccount) {
                $withdrawal->shareAccount->share_balance += $withdrawal->number_of_shares;
                $withdrawal->shareAccount->save();
            }

            // Delete related GL transactions
            GlTransaction::where('transaction_id', $withdrawal->id)
                ->where('transaction_type', 'share_withdrawal')
                ->delete();

            $withdrawal->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Share withdrawal deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Withdrawal Deletion Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete share withdrawal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get share account details for AJAX request
     */
    public function getShareAccountDetails(Request $request)
    {
        $accountId = $request->input('account_id');
        $account = ShareAccount::with('customer', 'shareProduct')->find($accountId);

        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        return response()->json([
            'account_number' => $account->account_number,
            'customer_name' => $account->customer->name ?? 'N/A',
            'share_product_name' => $account->shareProduct->share_name ?? 'N/A',
            'nominal_price' => $account->shareProduct->nominal_price ?? 0,
            'current_balance' => $account->share_balance ?? 0,
            'minimum_withdrawal_amount' => $account->shareProduct->minimum_withdrawal_amount,
            'maximum_withdrawal_amount' => $account->shareProduct->maximum_withdrawal_amount,
            'allow_partial_withdrawal' => $account->shareProduct->allow_partial_withdrawal ?? false,
            'withdrawal_fee' => $account->shareProduct->withdrawal_fee,
            'withdrawal_fee_type' => $account->shareProduct->withdrawal_fee_type,
        ]);
    }
}
