<?php

namespace App\Http\Controllers;

use App\Models\ShareTransfer;
use App\Models\ShareAccount;
use App\Models\ShareProduct;
use App\Models\BankAccount;
use App\Models\JournalReference;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ShareTransferController extends Controller
{
    /**
     * Display a listing of share transfers
     */
    public function index()
    {
        return view('shares.transfers.index');
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getShareTransfersData(Request $request)
    {
        if ($request->ajax()) {
            try {
                $transfers = ShareTransfer::with([
                    'fromAccount.customer',
                    'fromAccount.shareProduct',
                    'toAccount.customer',
                    'toAccount.shareProduct',
                    'bankAccount',
                    'journalReference',
                    'branch',
                    'company'
                ])->select('share_transfers.*');

                return DataTables::eloquent($transfers)
                ->addIndexColumn()
                ->addColumn('from_customer_name', function ($transfer) {
                    return $transfer->fromAccount->customer->name ?? 'N/A';
                })
                ->addColumn('from_customer_number', function ($transfer) {
                    return $transfer->fromAccount->customer->customerNo ?? 'N/A';
                })
                ->addColumn('to_customer_name', function ($transfer) {
                    return $transfer->toAccount->customer->name ?? 'N/A';
                })
                ->addColumn('to_customer_number', function ($transfer) {
                    return $transfer->toAccount->customer->customerNo ?? 'N/A';
                })
                ->addColumn('share_product_name', function ($transfer) {
                    return $transfer->fromAccount->shareProduct->share_name ?? 'N/A';
                })
                ->addColumn('number_of_shares_formatted', function ($transfer) {
                    return number_format($transfer->number_of_shares, 4);
                })
                ->addColumn('transfer_amount_formatted', function ($transfer) {
                    return number_format($transfer->transfer_amount, 2);
                })
                ->addColumn('transfer_fee_formatted', function ($transfer) {
                    return $transfer->transfer_fee ? number_format($transfer->transfer_fee, 2) : '0.00';
                })
                ->addColumn('transfer_date_formatted', function ($transfer) {
                    return $transfer->transfer_date ? $transfer->transfer_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('bank_account_name', function ($transfer) {
                    return $transfer->bankAccount->name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($transfer) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'approved' => '<span class="badge bg-success">Approved</span>',
                        'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    ];
                    return $badges[$transfer->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('actions', function ($transfer) {
                    $actions = '';
                    $encodedId = Hashids::encode($transfer->id);

                    // View action
                    $actions .= '<a href="' . route('shares.transfers.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Edit action
                    $actions .= '<a href="' . route('shares.transfers.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';

                    // Delete action
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="Transfer #' . $transfer->id . '" title="Delete"><i class="bx bx-trash"></i></button>';

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
            } catch (\Exception $e) {
                Log::error('Share Transfers DataTable Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'draw' => $request->input('draw', 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to load share transfers data. Please refresh the page.'
                ], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show the form for creating a new share transfer
     */
    public function create()
    {
        // Get active share accounts with their relationships (only those that allow transfers)
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->whereHas('shareProduct', function($query) {
                $query->where('allow_share_transfers', true);
            })
            ->orderBy('account_number')
            ->get();

        // Get bank accounts (for fee payment)
        $bankAccounts = BankAccount::orderBy('name')->get();

        // Get journal references
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        
        $journalReferences = JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('shares.transfers.create', compact('shareAccounts', 'bankAccounts', 'journalReferences'));
    }

    /**
     * Store a newly created share transfer
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:share_accounts,id',
            'to_account_id' => 'required|exists:share_accounts,id|different:from_account_id',
            'transfer_date' => 'required|date',
            'number_of_shares' => 'required|numeric|min:0.0001',
            'transaction_reference' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'journal_reference_id' => 'nullable|exists:journal_references,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Get source and destination accounts with their products
            $fromAccount = ShareAccount::with('shareProduct')->findOrFail($request->from_account_id);
            $toAccount = ShareAccount::with('shareProduct')->findOrFail($request->to_account_id);
            
            $fromProduct = $fromAccount->shareProduct;
            $toProduct = $toAccount->shareProduct;

            // Check if transfers are allowed for source product
            if (!$fromProduct->allow_share_transfers) {
                throw new \Exception('Share transfers are not allowed for the source share product.');
            }

            // Validate that both accounts are for the same product
            if ($fromProduct->id !== $toProduct->id) {
                throw new \Exception('Cannot transfer shares between different share products.');
            }

            // Get chart accounts from share product
            $liabilityAccountId = $fromProduct->liability_account_id;
            $shareCapitalAccountId = $fromProduct->share_capital_account_id;
            $feeIncomeAccountId = $fromProduct->fee_income_account_id;
            
            if (!$liabilityAccountId) {
                throw new \Exception('Share product does not have a liability account configured. Please configure chart accounts in the share product.');
            }

            // Calculate transfer amount based on number of shares
            $nominalPrice = $fromProduct->nominal_price ?? 0;
            $transferAmount = $request->number_of_shares * $nominalPrice;

            // Check if source account has sufficient balance
            if ($fromAccount->share_balance < $request->number_of_shares) {
                $validator->errors()->add('number_of_shares', 'Insufficient share balance in source account. Available: ' . number_format($fromAccount->share_balance, 4));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Calculate transfer fee if product has transfer fees
            $transferFee = 0;
            if ($fromProduct->transfer_fee && $fromProduct->transfer_fee > 0) {
                if ($fromProduct->transfer_fee_type === 'fixed') {
                    $transferFee = $fromProduct->transfer_fee;
                } elseif ($fromProduct->transfer_fee_type === 'percentage') {
                    $transferFee = ($transferAmount * $fromProduct->transfer_fee) / 100;
                }
            }

            // Get journal reference if provided, otherwise use product's journal reference
            $journalReferenceId = $request->journal_reference_id ?? $fromProduct->journal_reference_id;

            // Get bank account for fee payment (if fee exists)
            $bankAccountId = null;
            if ($transferFee > 0) {
                if (!$request->bank_account_id) {
                    throw new \Exception('Bank account is required when transfer fee is applicable.');
                }
                $bankAccountId = $request->bank_account_id;
            }

            $user = auth()->user();

            // Create transfer
            $transfer = ShareTransfer::create([
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'transfer_date' => $request->transfer_date,
                'number_of_shares' => $request->number_of_shares,
                'transfer_amount' => $transferAmount,
                'transfer_fee' => $transferFee,
                'transaction_reference' => $request->transaction_reference,
                'bank_account_id' => $bankAccountId,
                'journal_reference_id' => $journalReferenceId,
                'fee_income_account_id' => $feeIncomeAccountId,
                'notes' => $request->notes,
                'status' => 'approved', // Auto-approve for now
                'branch_id' => $user->branch_id ?? null,
                'company_id' => $user->company_id ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Create GL Transactions
            $fromCustomerId = $fromAccount->customer_id;
            $toCustomerId = $toAccount->customer_id;
            $description = "Share transfer from {$fromAccount->account_number} to {$toAccount->account_number} - " . ($request->notes ?: "Transfer #{$transfer->id}");

            // Determine which account to use (share capital if available, otherwise liability)
            $debitAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
            $creditAccountId = $shareCapitalAccountId ?? $liabilityAccountId;

            // Debit: Destination account's Share Capital/Liability Account (shares coming in)
            GlTransaction::create([
                'chart_account_id' => $debitAccountId,
                'customer_id' => $toCustomerId,
                'amount' => $transferAmount,
                'nature' => 'debit',
                'transaction_id' => $transfer->id,
                'transaction_type' => 'share_transfer',
                'date' => $request->transfer_date,
                'description' => $description . ' - To Account',
                'branch_id' => $user->branch_id ?? null,
                'user_id' => $user->id,
            ]);

            // Credit: Source account's Share Capital/Liability Account (shares going out)
            GlTransaction::create([
                'chart_account_id' => $creditAccountId,
                'customer_id' => $fromCustomerId,
                'amount' => $transferAmount,
                'nature' => 'credit',
                'transaction_id' => $transfer->id,
                'transaction_type' => 'share_transfer',
                'date' => $request->transfer_date,
                'description' => $description . ' - From Account',
                'branch_id' => $user->branch_id ?? null,
                'user_id' => $user->id,
            ]);

            // Fee GL Transactions (if fee exists and fee income account is configured)
            if ($transferFee > 0 && $feeIncomeAccountId && $bankAccountId) {
                $bankAccount = BankAccount::findOrFail($bankAccountId);

                // Debit: Bank Account (fee paid)
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $fromCustomerId,
                    'amount' => $transferFee,
                    'nature' => 'debit',
                    'transaction_id' => $transfer->id,
                    'transaction_type' => 'share_transfer',
                    'date' => $request->transfer_date,
                    'description' => $description . ' - Fee',
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Credit: Fee Income Account
                GlTransaction::create([
                    'chart_account_id' => $feeIncomeAccountId,
                    'customer_id' => $fromCustomerId,
                    'amount' => $transferFee,
                    'nature' => 'credit',
                    'transaction_id' => $transfer->id,
                    'transaction_type' => 'share_transfer',
                    'date' => $request->transfer_date,
                    'description' => $description . ' - Fee Income',
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);
            }

            // Update share account balances
            $fromAccount->share_balance -= $request->number_of_shares;
            $fromAccount->last_transaction_date = $request->transfer_date;
            $fromAccount->updated_by = $user->id;
            $fromAccount->save();

            $toAccount->share_balance += $request->number_of_shares;
            $toAccount->last_transaction_date = $request->transfer_date;
            $toAccount->updated_by = $user->id;
            $toAccount->save();

            DB::commit();

            return redirect()->route('shares.transfers.index')
                ->with('success', 'Share transfer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Transfer Creation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Failed to create share transfer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified share transfer
     */
    public function show($id)
    {
        $transfer = ShareTransfer::with([
            'fromAccount.customer',
            'fromAccount.shareProduct',
            'toAccount.customer',
            'toAccount.shareProduct',
            'bankAccount',
            'journalReference',
            'feeIncomeAccount',
            'branch',
            'company',
            'createdBy',
            'updatedBy'
        ])->findOrFail(Hashids::decode($id)[0]);

        return view('shares.transfers.show', compact('transfer'));
    }

    /**
     * Show the form for editing the specified share transfer
     */
    public function edit($id)
    {
        $transfer = ShareTransfer::with(['fromAccount.shareProduct', 'toAccount.shareProduct'])->findOrFail(Hashids::decode($id)[0]);
        
        // Get active share accounts (only those that allow transfers)
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->whereHas('shareProduct', function($query) {
                $query->where('allow_share_transfers', true);
            })
            ->orderBy('account_number')
            ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::orderBy('name')->get();

        // Get journal references
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;
        
        $journalReferences = JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('shares.transfers.edit', compact('transfer', 'shareAccounts', 'bankAccounts', 'journalReferences'));
    }

    /**
     * Update the specified share transfer
     */
    public function update(Request $request, $id)
    {
        $transfer = ShareTransfer::with(['fromAccount.shareProduct', 'toAccount.shareProduct'])->findOrFail(Hashids::decode($id)[0]);

        $validator = Validator::make($request->all(), [
            'from_account_id' => 'required|exists:share_accounts,id',
            'to_account_id' => 'required|exists:share_accounts,id|different:from_account_id',
            'transfer_date' => 'required|date',
            'number_of_shares' => 'required|numeric|min:0.0001',
            'transaction_reference' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'journal_reference_id' => 'nullable|exists:journal_references,id',
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

            $fromAccount = ShareAccount::with('shareProduct')->findOrFail($request->from_account_id);
            $toAccount = ShareAccount::with('shareProduct')->findOrFail($request->to_account_id);
            
            $fromProduct = $fromAccount->shareProduct;
            $toProduct = $toAccount->shareProduct;

            // Validate that both accounts are for the same product
            if ($fromProduct->id !== $toProduct->id) {
                throw new \Exception('Cannot transfer shares between different share products.');
            }

            // Get chart accounts
            $liabilityAccountId = $fromProduct->liability_account_id;
            $shareCapitalAccountId = $fromProduct->share_capital_account_id;
            $feeIncomeAccountId = $fromProduct->fee_income_account_id;

            // Calculate transfer amount
            $nominalPrice = $fromProduct->nominal_price ?? 0;
            $transferAmount = $request->number_of_shares * $nominalPrice;

            // Calculate transfer fee
            $transferFee = 0;
            if ($fromProduct->transfer_fee && $fromProduct->transfer_fee > 0) {
                if ($fromProduct->transfer_fee_type === 'fixed') {
                    $transferFee = $fromProduct->transfer_fee;
                } elseif ($fromProduct->transfer_fee_type === 'percentage') {
                    $transferFee = ($transferAmount * $fromProduct->transfer_fee) / 100;
                }
            }

            // Get journal reference
            $journalReferenceId = $request->journal_reference_id ?? $fromProduct->journal_reference_id;

            // Get bank account for fee payment
            $bankAccountId = null;
            if ($transferFee > 0) {
                $bankAccountId = $request->bank_account_id;
            }

            // If changing accounts or shares, reverse old balances and apply new
            $oldFromShares = $transfer->number_of_shares;
            $oldToShares = $transfer->number_of_shares;
            $newShares = $request->number_of_shares;

            // Delete old GL transactions
            GlTransaction::where('transaction_id', $transfer->id)
                ->where('transaction_type', 'share_transfer')
                ->delete();

            $user = auth()->user();

            // Update transfer
            $transfer->update([
                'from_account_id' => $request->from_account_id,
                'to_account_id' => $request->to_account_id,
                'transfer_date' => $request->transfer_date,
                'number_of_shares' => $newShares,
                'transfer_amount' => $transferAmount,
                'transfer_fee' => $transferFee,
                'transaction_reference' => $request->transaction_reference,
                'bank_account_id' => $bankAccountId,
                'journal_reference_id' => $journalReferenceId,
                'fee_income_account_id' => $feeIncomeAccountId,
                'notes' => $request->notes,
                'status' => $request->status,
                'updated_by' => $user->id,
            ]);

            // Update share account balances (only if approved)
            if ($request->status === 'approved') {
                // Reverse old balances
                $oldFromAccount = $transfer->fromAccount;
                $oldToAccount = $transfer->toAccount;
                
                if ($oldFromAccount) {
                    $oldFromAccount->share_balance += $oldFromShares;
                    $oldFromAccount->save();
                }
                if ($oldToAccount) {
                    $oldToAccount->share_balance -= $oldToShares;
                    $oldToAccount->save();
                }

                // Apply new balances
                $fromAccount->share_balance -= $newShares;
                $fromAccount->last_transaction_date = $request->transfer_date;
                $fromAccount->updated_by = $user->id;
                $fromAccount->save();

                $toAccount->share_balance += $newShares;
                $toAccount->last_transaction_date = $request->transfer_date;
                $toAccount->updated_by = $user->id;
                $toAccount->save();

                // Create GL Transactions
                $fromCustomerId = $fromAccount->customer_id;
                $toCustomerId = $toAccount->customer_id;
                $description = "Share transfer from {$fromAccount->account_number} to {$toAccount->account_number} - " . ($request->notes ?: "Transfer #{$transfer->id}");

                $debitAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
                $creditAccountId = $shareCapitalAccountId ?? $liabilityAccountId;

                // Debit: Destination account
                GlTransaction::create([
                    'chart_account_id' => $debitAccountId,
                    'customer_id' => $toCustomerId,
                    'amount' => $transferAmount,
                    'nature' => 'debit',
                    'transaction_id' => $transfer->id,
                    'transaction_type' => 'share_transfer',
                    'date' => $request->transfer_date,
                    'description' => $description . ' - To Account',
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Credit: Source account
                GlTransaction::create([
                    'chart_account_id' => $creditAccountId,
                    'customer_id' => $fromCustomerId,
                    'amount' => $transferAmount,
                    'nature' => 'credit',
                    'transaction_id' => $transfer->id,
                    'transaction_type' => 'share_transfer',
                    'date' => $request->transfer_date,
                    'description' => $description . ' - From Account',
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Fee GL Transactions (if fee exists)
                if ($transferFee > 0 && $feeIncomeAccountId && $bankAccountId) {
                    $bankAccount = BankAccount::findOrFail($bankAccountId);

                    GlTransaction::create([
                        'chart_account_id' => $bankAccount->chart_account_id,
                        'customer_id' => $fromCustomerId,
                        'amount' => $transferFee,
                        'nature' => 'debit',
                        'transaction_id' => $transfer->id,
                        'transaction_type' => 'share_transfer',
                        'date' => $request->transfer_date,
                        'description' => $description . ' - Fee',
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);

                    GlTransaction::create([
                        'chart_account_id' => $feeIncomeAccountId,
                        'customer_id' => $fromCustomerId,
                        'amount' => $transferFee,
                        'nature' => 'credit',
                        'transaction_id' => $transfer->id,
                        'transaction_type' => 'share_transfer',
                        'date' => $request->transfer_date,
                        'description' => $description . ' - Fee Income',
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('shares.transfers.index')
                ->with('success', 'Share transfer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Transfer Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update share transfer: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified share transfer
     */
    public function destroy($id)
    {
        try {
            $transfer = ShareTransfer::with(['fromAccount', 'toAccount'])->findOrFail(Hashids::decode($id)[0]);

            DB::beginTransaction();

            // Reverse share account balances if approved (add back to source, subtract from destination)
            if ($transfer->status === 'approved') {
                if ($transfer->fromAccount) {
                    $transfer->fromAccount->share_balance += $transfer->number_of_shares;
                    $transfer->fromAccount->save();
                }
                if ($transfer->toAccount) {
                    $transfer->toAccount->share_balance -= $transfer->number_of_shares;
                    $transfer->toAccount->save();
                }
            }

            // Delete related GL transactions
            GlTransaction::where('transaction_id', $transfer->id)
                ->where('transaction_type', 'share_transfer')
                ->delete();

            $transfer->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Share transfer deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Transfer Deletion Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete share transfer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get share account details for AJAX request
     */
    public function getAccountDetails(Request $request)
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
            'transfer_fee' => $account->shareProduct->transfer_fee,
            'transfer_fee_type' => $account->shareProduct->transfer_fee_type,
            'allow_share_transfers' => $account->shareProduct->allow_share_transfers ?? false,
        ]);
    }
}
