<?php

namespace App\Http\Controllers;

use App\Models\ShareDeposit;
use App\Models\ShareAccount;
use App\Models\ShareProduct;
use App\Models\BankAccount;
use App\Models\GlTransaction;
use App\Models\SystemSetting;
use App\Models\Company;
use App\Exports\ShareDepositImportTemplateExport;
use App\Exports\ShareOpeningBalanceImportTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ShareDepositController extends Controller
{
    /**
     * Display a listing of share deposits
     */
    public function index()
    {
        // Get active share products for the opening balance import dropdown
        $shareProducts = ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get(['id', 'share_name']);
        
        return view('shares.deposits.index', compact('shareProducts'));
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getShareDepositsData(Request $request)
    {
        if ($request->ajax()) {
            try {
                $deposits = ShareDeposit::with([
                    'shareAccount.customer',
                    'shareAccount.shareProduct',
                    'bankAccount',
                    'branch',
                    'company'
                ])->select('share_deposits.*');

                // Apply filters
                if ($request->filled('share_product_id')) {
                    $deposits->whereHas('shareAccount', function ($query) use ($request) {
                        $query->where('share_product_id', $request->share_product_id);
                    });
                }

                if ($request->filled('status')) {
                    $deposits->where('status', $request->status);
                }

                if ($request->filled('date_from')) {
                    $deposits->whereDate('deposit_date', '>=', $request->date_from);
                }

                if ($request->filled('date_to')) {
                    $deposits->whereDate('deposit_date', '<=', $request->date_to);
                }

                return DataTables::eloquent($deposits)
                ->addIndexColumn()
                ->addColumn('account_number', function ($deposit) {
                    return $deposit->shareAccount->account_number ?? 'N/A';
                })
                ->filterColumn('account_number', function ($query, $keyword) {
                    $query->whereHas('shareAccount', function ($q) use ($keyword) {
                        $q->whereRaw("LOWER(account_number) LIKE LOWER(?)", ["%{$keyword}%"]);
                    });
                })
                ->addColumn('customer_name', function ($deposit) {
                    return $deposit->shareAccount->customer->name ?? 'N/A';
                })
                ->filterColumn('customer_name', function ($query, $keyword) {
                    $query->whereHas('shareAccount.customer', function ($q) use ($keyword) {
                        $q->whereRaw("LOWER(name) LIKE LOWER(?)", ["%{$keyword}%"]);
                    });
                })
                ->addColumn('customer_number', function ($deposit) {
                    return $deposit->shareAccount->customer->customerNo ?? 'N/A';
                })
                ->filterColumn('customer_number', function ($query, $keyword) {
                    $query->whereHas('shareAccount.customer', function ($q) use ($keyword) {
                        $q->whereRaw("LOWER(customerNo) LIKE LOWER(?)", ["%{$keyword}%"]);
                    });
                })
                ->addColumn('share_product_name', function ($deposit) {
                    return $deposit->shareAccount->shareProduct->share_name ?? 'N/A';
                })
                ->filterColumn('share_product_name', function ($query, $keyword) {
                    $query->whereHas('shareAccount.shareProduct', function ($q) use ($keyword) {
                        $q->whereRaw("LOWER(share_name) LIKE LOWER(?)", ["%{$keyword}%"]);
                    });
                })
                ->addColumn('deposit_amount_formatted', function ($deposit) {
                    return number_format($deposit->deposit_amount, 2);
                })
                ->addColumn('number_of_shares_formatted', function ($deposit) {
                    return number_format($deposit->number_of_shares, 4);
                })
                ->addColumn('charge_amount_formatted', function ($deposit) {
                    return $deposit->charge_amount ? number_format($deposit->charge_amount, 2) : '0.00';
                })
                ->addColumn('total_amount_formatted', function ($deposit) {
                    return number_format($deposit->total_amount, 2);
                })
                ->addColumn('deposit_date_formatted', function ($deposit) {
                    return $deposit->deposit_date ? $deposit->deposit_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('bank_account_name', function ($deposit) {
                    return $deposit->bankAccount->name ?? 'N/A';
                })
                ->addColumn('status_badge', function ($deposit) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'approved' => '<span class="badge bg-success">Approved</span>',
                        'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    ];
                    return $badges[$deposit->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('actions', function ($deposit) {
                    $actions = '';
                    $encodedId = Hashids::encode($deposit->id);

                    // View action
                    $actions .= '<a href="' . route('shares.deposits.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Edit action
                    $actions .= '<a href="' . route('shares.deposits.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';

                    // Change status action
                    $actions .= '<button class="btn btn-sm btn-outline-secondary change-status-btn me-1" data-id="' . $encodedId . '" data-name="Deposit #' . $deposit->id . '" data-status="' . e($deposit->status) . '" title="Change Status"><i class="bx bx-transfer"></i></button>';

                    // Delete action
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="Deposit #' . $deposit->id . '" title="Delete"><i class="bx bx-trash"></i></button>';

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['status_badge', 'actions'])
                ->make(true);
            } catch (\Exception $e) {
                Log::error('Share Deposits DataTable Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                
                return response()->json([
                    'draw' => $request->input('draw', 0),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                    'error' => 'Failed to load share deposits data. Please refresh the page.'
                ], 500);
            }
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Show the form for creating a new share deposit
     */
    public function create()
    {
        // Get active share accounts with their relationships
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->orderBy('account_number')
            ->get();

        // Get bank accounts for payment method
        $bankAccounts = BankAccount::orderBy('name')->get();

        return view('shares.deposits.create', compact('shareAccounts', 'bankAccounts'));
    }

    /**
     * Store a newly created share deposit
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'share_account_id' => 'required|exists:share_accounts,id',
            'deposit_date' => 'required|date',
            'deposit_amount' => 'required|numeric|min:0.01',
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

            // Get share account and product (with chart accounts)
            $shareAccount = ShareAccount::with('shareProduct')->findOrFail($request->share_account_id);
            $shareProduct = $shareAccount->shareProduct;
            
            // Get chart accounts from share product
            $liabilityAccountId = $shareProduct->liability_account_id;
            $shareCapitalAccountId = $shareProduct->share_capital_account_id;
            
            if (!$liabilityAccountId) {
                throw new \Exception('Share product does not have a liability account configured. Please configure chart accounts in the share product.');
            }

            // Validate deposit amount against product constraints
            if ($shareProduct->minimum_purchase_amount && $request->deposit_amount < $shareProduct->minimum_purchase_amount) {
                $validator->errors()->add('deposit_amount', 'Deposit amount must be at least ' . number_format($shareProduct->minimum_purchase_amount, 2));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if ($shareProduct->maximum_purchase_amount && $request->deposit_amount > $shareProduct->maximum_purchase_amount) {
                $validator->errors()->add('deposit_amount', 'Deposit amount must not exceed ' . number_format($shareProduct->maximum_purchase_amount, 2));
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // Calculate charge amount if product has charges
            $chargeAmount = 0;
            if ($shareProduct->has_charges && $shareProduct->charge_amount) {
                if ($shareProduct->charge_type === 'fixed') {
                    $chargeAmount = $shareProduct->charge_amount;
                } elseif ($shareProduct->charge_type === 'percentage') {
                    $chargeAmount = ($request->deposit_amount * $shareProduct->charge_amount) / 100;
                }
            }

            $totalAmount = $request->deposit_amount + $chargeAmount;

            // Get bank account for GL transaction
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $user = auth()->user();

            // Create deposit (using chart accounts from share product)
            $deposit = ShareDeposit::create([
                'share_account_id' => $request->share_account_id,
                'deposit_date' => $request->deposit_date,
                'deposit_amount' => $request->deposit_amount,
                'number_of_shares' => $request->number_of_shares,
                'charge_amount' => $chargeAmount,
                'total_amount' => $totalAmount,
                'transaction_reference' => $request->transaction_reference,
                'bank_account_id' => $request->bank_account_id,
                'liability_account_id' => $liabilityAccountId,
                'share_capital_account_id' => $shareCapitalAccountId,
                'cheque_number' => $request->cheque_number,
                'notes' => $request->notes,
                'status' => $request->status,
                'branch_id' => $user->branch_id ?? null,
                'company_id' => $user->company_id ?? null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            // Create GL Transactions (only if approved)
            if ($request->status === 'approved') {
                $customerId = $shareAccount->customer_id;
                $description = "Share deposit for {$shareAccount->account_number} - " . ($request->notes ?: "Deposit #{$deposit->id}");

                // Debit: Bank Account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $customerId,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $deposit->id,
                    'transaction_type' => 'share_deposit',
                    'date' => $request->deposit_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Credit: Share Capital Account (if provided), otherwise Liability Account
                // Only credit one account to maintain double-entry balance
                $creditAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
                
                GlTransaction::create([
                    'chart_account_id' => $creditAccountId,
                    'customer_id' => $customerId,
                    'amount' => $totalAmount,
                    'nature' => 'credit',
                    'transaction_id' => $deposit->id,
                    'transaction_type' => 'share_deposit',
                    'date' => $request->deposit_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Update share account balance
                $shareAccount->share_balance += $request->number_of_shares;
                $shareAccount->last_transaction_date = $request->deposit_date;
                $shareAccount->updated_by = $user->id;
                $shareAccount->save();
            }

            DB::commit();

            return redirect()->route('shares.deposits.index')
                ->with('success', 'Share deposit created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Deposit Creation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Failed to create share deposit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified share deposit
     */
    public function show($id)
    {
        $deposit = ShareDeposit::with([
            'shareAccount.customer',
            'shareAccount.shareProduct',
            'bankAccount',
            'branch',
            'company',
            'createdBy',
            'updatedBy'
        ])->findOrFail(Hashids::decode($id)[0]);

        return view('shares.deposits.show', compact('deposit'));
    }

    /**
     * Show the form for editing the specified share deposit
     */
    public function edit($id)
    {
        $deposit = ShareDeposit::with(['shareAccount.shareProduct'])->findOrFail(Hashids::decode($id)[0]);
        
        // Get active share accounts
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->where('status', 'active')
            ->orderBy('account_number')
            ->get();

        // Get bank accounts for payment method
        $bankAccounts = BankAccount::orderBy('name')->get();

        return view('shares.deposits.edit', compact('deposit', 'shareAccounts', 'bankAccounts'));
    }

    /**
     * Update the specified share deposit
     */
    public function update(Request $request, $id)
    {
        $deposit = ShareDeposit::with(['shareAccount.shareProduct'])->findOrFail(Hashids::decode($id)[0]);

        $validator = Validator::make($request->all(), [
            'share_account_id' => 'required|exists:share_accounts,id',
            'deposit_date' => 'required|date',
            'deposit_amount' => 'required|numeric|min:0.01',
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
                throw new \Exception('Share product does not have a liability account configured. Please configure chart accounts in the share product.');
            }

            // If changing account, reverse old balance and apply new
            $oldShares = $deposit->number_of_shares;
            $newShares = $request->number_of_shares;

            // Calculate charge amount
            $chargeAmount = 0;
            if ($shareProduct->has_charges && $shareProduct->charge_amount) {
                if ($shareProduct->charge_type === 'fixed') {
                    $chargeAmount = $shareProduct->charge_amount;
                } elseif ($shareProduct->charge_type === 'percentage') {
                    $chargeAmount = ($request->deposit_amount * $shareProduct->charge_amount) / 100;
                }
            }

            $totalAmount = $request->deposit_amount + $chargeAmount;

            // Get bank account for GL transaction
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);
            $user = auth()->user();

            // Delete old GL transactions
            GlTransaction::where('transaction_id', $deposit->id)
                ->where('transaction_type', 'share_deposit')
                ->delete();

            // Update deposit (using chart accounts from share product)
            $deposit->update([
                'share_account_id' => $request->share_account_id,
                'deposit_date' => $request->deposit_date,
                'deposit_amount' => $request->deposit_amount,
                'number_of_shares' => $newShares,
                'charge_amount' => $chargeAmount,
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
                // Reverse old shares
                $oldAccount = $deposit->shareAccount;
                if ($oldAccount) {
                    $oldAccount->share_balance -= $oldShares;
                    $oldAccount->save();
                }

                // Apply new shares
                $shareAccount->share_balance += $newShares;
                $shareAccount->last_transaction_date = $request->deposit_date;
                $shareAccount->updated_by = $user->id;
                $shareAccount->save();

                // Create GL Transactions (only if approved)
                $customerId = $shareAccount->customer_id;
                $description = "Share deposit for {$shareAccount->account_number} - " . ($request->notes ?: "Deposit #{$deposit->id}");

                // Debit: Bank Account
                GlTransaction::create([
                    'chart_account_id' => $bankAccount->chart_account_id,
                    'customer_id' => $customerId,
                    'amount' => $totalAmount,
                    'nature' => 'debit',
                    'transaction_id' => $deposit->id,
                    'transaction_type' => 'share_deposit',
                    'date' => $request->deposit_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);

                // Credit: Share Capital Account (if provided), otherwise Liability Account
                // Only credit one account to maintain double-entry balance
                $creditAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
                
                GlTransaction::create([
                    'chart_account_id' => $creditAccountId,
                    'customer_id' => $customerId,
                    'amount' => $totalAmount,
                    'nature' => 'credit',
                    'transaction_id' => $deposit->id,
                    'transaction_type' => 'share_deposit',
                    'date' => $request->deposit_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);
            }

            DB::commit();

            return redirect()->route('shares.deposits.index')
                ->with('success', 'Share deposit updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Deposit Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update share deposit: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified share deposit
     */
    public function destroy($id)
    {
        try {
            $deposit = ShareDeposit::with('shareAccount')->findOrFail(Hashids::decode($id)[0]);

            DB::beginTransaction();

            // Reverse share account balance if approved
            if ($deposit->status === 'approved' && $deposit->shareAccount) {
                $deposit->shareAccount->share_balance -= $deposit->number_of_shares;
                $deposit->shareAccount->save();
            }

            // Delete related GL transactions
            GlTransaction::where('transaction_id', $deposit->id)
                ->where('transaction_type', 'share_deposit')
                ->delete();

            $deposit->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Share deposit deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Deposit Deletion Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete share deposit: ' . $e->getMessage()
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
            'minimum_purchase_amount' => $account->shareProduct->minimum_purchase_amount,
            'maximum_purchase_amount' => $account->shareProduct->maximum_purchase_amount,
            'share_purchase_increment' => $account->shareProduct->share_purchase_increment,
            'has_charges' => $account->shareProduct->has_charges ?? false,
            'charge_type' => $account->shareProduct->charge_type,
            'charge_amount' => $account->shareProduct->charge_amount,
        ]);
    }

    /**
     * Download import template
     */
    public function downloadTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'share_product_id' => 'required|exists:share_products,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $shareProductId = $request->share_product_id;
            $fileName = 'share_deposit_import_template_' . date('Y-m-d') . '.xlsx';

            return Excel::download(
                new ShareDepositImportTemplateExport($shareProductId),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Share Deposit Template Download Error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Failed to generate template: ' . $e->getMessage()], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Import share deposits from Excel
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Read Excel file
            $rows = Excel::toArray([], $request->file('import_file'));
            $rows = $rows[0]; // Get first sheet

            // Get header row and create mapping
            $header = array_shift($rows);
            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $header);

            // Find column indices
            $accountNumberIndex = array_search('account_number', $header);
            $customerNameIndex = array_search('customer_name', $header); // For reference only, not used in processing
            $depositDateIndex = array_search('deposit_date', $header);
            $depositAmountIndex = array_search('deposit_amount', $header);
            $bankAccountNameIndex = array_search('bank_account_name', $header);
            $transactionReferenceIndex = array_search('transaction_reference', $header);
            $chequeNumberIndex = array_search('cheque_number', $header);
            $notesIndex = array_search('notes', $header);

            if ($accountNumberIndex === false || $depositDateIndex === false || 
                $depositAmountIndex === false || $bankAccountNameIndex === false) {
                return redirect()->back()
                    ->with('error', 'Excel file must contain account_number, deposit_date, deposit_amount, and bank_account_name columns')
                    ->withInput();
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Get values by column index
                    $accountNumber = trim($row[$accountNumberIndex] ?? '');
                    $depositDate = trim($row[$depositDateIndex] ?? '');
                    $depositAmount = trim($row[$depositAmountIndex] ?? '');
                    $bankAccountName = trim($row[$bankAccountNameIndex] ?? '');
                    $transactionReference = isset($row[$transactionReferenceIndex]) ? trim($row[$transactionReferenceIndex]) : '';
                    $chequeNumber = isset($row[$chequeNumberIndex]) ? trim($row[$chequeNumberIndex]) : '';
                    $notes = isset($row[$notesIndex]) ? trim($row[$notesIndex]) : '';

                    if (empty($accountNumber) || empty($depositDate) || empty($depositAmount) || empty($bankAccountName)) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": account_number, deposit_date, deposit_amount, and bank_account_name are required";
                        continue;
                    }

                    // Find share account by account number
                    $shareAccount = ShareAccount::where('account_number', $accountNumber)
                        ->with('shareProduct')
                        ->first();

                    if (!$shareAccount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Share account with number '{$accountNumber}' not found";
                        continue;
                    }

                    // Find bank account by name
                    $bankAccount = BankAccount::where('name', $bankAccountName)->first();

                    if (!$bankAccount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Bank account with name '{$bankAccountName}' not found";
                        continue;
                    }

                    $shareProduct = $shareAccount->shareProduct;

                    // Validate deposit amount
                    $depositAmount = (float) $depositAmount;
                    if ($depositAmount <= 0) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Deposit amount must be greater than 0";
                        continue;
                    }

                    // Validate deposit amount against product constraints
                    if ($shareProduct->minimum_purchase_amount && $depositAmount < $shareProduct->minimum_purchase_amount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Deposit amount must be at least " . number_format($shareProduct->minimum_purchase_amount, 2);
                        continue;
                    }

                    if ($shareProduct->maximum_purchase_amount && $depositAmount > $shareProduct->maximum_purchase_amount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Deposit amount must not exceed " . number_format($shareProduct->maximum_purchase_amount, 2);
                        continue;
                    }

                    // Calculate number of shares
                    $nominalPrice = $shareProduct->nominal_price ?? 1;
                    $numberOfShares = $depositAmount / $nominalPrice;

                    // Calculate charge amount if product has charges
                    $chargeAmount = 0;
                    if ($shareProduct->has_charges && $shareProduct->charge_amount) {
                        if ($shareProduct->charge_type === 'fixed') {
                            $chargeAmount = $shareProduct->charge_amount;
                        } elseif ($shareProduct->charge_type === 'percentage') {
                            $chargeAmount = ($depositAmount * $shareProduct->charge_amount) / 100;
                        }
                    }

                    $totalAmount = $depositAmount + $chargeAmount;

                    // Get chart accounts from share product
                    $liabilityAccountId = $shareProduct->liability_account_id;
                    $shareCapitalAccountId = $shareProduct->share_capital_account_id;
                    
                    if (!$liabilityAccountId) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Share product does not have a liability account configured";
                        continue;
                    }

                    // Create deposit
                    $deposit = ShareDeposit::create([
                        'share_account_id' => $shareAccount->id,
                        'deposit_date' => $depositDate,
                        'deposit_amount' => $depositAmount,
                        'number_of_shares' => $numberOfShares,
                        'charge_amount' => $chargeAmount,
                        'total_amount' => $totalAmount,
                        'transaction_reference' => $transactionReference,
                        'bank_account_id' => $bankAccount->id,
                        'liability_account_id' => $liabilityAccountId,
                        'share_capital_account_id' => $shareCapitalAccountId,
                        'cheque_number' => $chequeNumber,
                        'notes' => $notes,
                        'status' => 'approved',
                        'branch_id' => $user->branch_id ?? null,
                        'company_id' => $user->company_id ?? null,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    // Create GL Transactions
                    $customerId = $shareAccount->customer_id;
                    $description = "Share deposit for {$shareAccount->account_number} - " . ($notes ?: "Deposit #{$deposit->id}");

                    // Debit: Bank Account
                    GlTransaction::create([
                        'chart_account_id' => $bankAccount->chart_account_id,
                        'customer_id' => $customerId,
                        'amount' => $totalAmount,
                        'nature' => 'debit',
                        'transaction_id' => $deposit->id,
                        'transaction_type' => 'share_deposit',
                        'date' => $depositDate,
                        'description' => $description,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);

                    // Credit: Share Capital Account (if provided), otherwise Liability Account
                    $creditAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
                    
                    GlTransaction::create([
                        'chart_account_id' => $creditAccountId,
                        'customer_id' => $customerId,
                        'amount' => $totalAmount,
                        'nature' => 'credit',
                        'transaction_id' => $deposit->id,
                        'transaction_type' => 'share_deposit',
                        'date' => $depositDate,
                        'description' => $description,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);

                    // Update share account balance
                    $shareAccount->share_balance += $numberOfShares;
                    $shareAccount->last_transaction_date = $depositDate;
                    $shareAccount->updated_by = $user->id;
                    $shareAccount->save();

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Import completed. {$successCount} deposit(s) created successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} error(s) occurred.";
                if (count($errors) > 0) {
                    Log::warning('Share Deposit Import Errors', ['errors' => $errors]);
                }
            }

            return redirect()->route('shares.deposits.index')
                ->with('success', $message)
                ->with('import_errors', $errors ?? []);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Deposit Import Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to import share deposits: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Download opening balance import template
     */
    public function downloadOpeningBalanceTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'share_product_id' => 'required|exists:share_products,id',
            'opening_balance_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $shareProductId = $request->share_product_id;
            $openingDate = $request->opening_balance_date;
            $fileName = 'share_opening_balance_import_template_' . date('Y-m-d') . '.xlsx';

            return Excel::download(
                new ShareOpeningBalanceImportTemplateExport($shareProductId, $openingDate),
                $fileName
            );
        } catch (\Exception $e) {
            Log::error('Share Opening Balance Template Download Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Import share opening balances from Excel
     */
    public function importOpeningBalance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'share_product_id' => 'required|exists:share_products,id',
            'opening_balance_date' => 'required|date',
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $openingBalanceDate = $request->opening_balance_date;

            // Get SHARES opening balance account from settings
            $sharesOpeningBalanceAccountId = SystemSetting::getValue('shares_opening_balance_account_id', null);
            
            if (!$sharesOpeningBalanceAccountId) {
                return redirect()->back()
                    ->with('error', 'SHARES opening balance account is not configured. Please configure it in Settings > Opening Balance Accounts.')
                    ->withInput();
            }

            // Read Excel file
            $rows = Excel::toArray([], $request->file('import_file'));
            $rows = $rows[0]; // Get first sheet

            // Get header row and create mapping
            $header = array_shift($rows);
            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $header);

            // Find column indices
            $accountNumberIndex = array_search('account_number', $header);
            $openingBalanceDateIndex = array_search('opening_balance_date', $header);
            $openingBalanceAmountIndex = array_search('opening_balance_amount', $header);
            $openingBalanceDescriptionIndex = array_search('opening_balance_description', $header);
            $transactionReferenceIndex = array_search('transaction_reference', $header);
            $notesIndex = array_search('notes', $header);

            if ($accountNumberIndex === false || $openingBalanceAmountIndex === false) {
                return redirect()->back()
                    ->with('error', 'Excel file must contain account_number and opening_balance_amount columns')
                    ->withInput();
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($rows as $index => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Get values by column index
                    $accountNumber = trim($row[$accountNumberIndex] ?? '');
                    $openingBalanceDate = isset($row[$openingBalanceDateIndex]) ? trim($row[$openingBalanceDateIndex]) : $request->opening_balance_date;
                    $openingBalanceAmount = trim($row[$openingBalanceAmountIndex] ?? '');
                    $openingBalanceDescription = isset($row[$openingBalanceDescriptionIndex]) ? trim($row[$openingBalanceDescriptionIndex]) : '';
                    $transactionReference = isset($row[$transactionReferenceIndex]) ? trim($row[$transactionReferenceIndex]) : '';
                    $notes = isset($row[$notesIndex]) ? trim($row[$notesIndex]) : '';

                    if (empty($accountNumber) || empty($openingBalanceAmount)) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": account_number and opening_balance_amount are required";
                        continue;
                    }

                    // Find share account by account number
                    $shareAccount = ShareAccount::where('account_number', $accountNumber)
                        ->with('shareProduct')
                        ->first();

                    if (!$shareAccount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Share account with number '{$accountNumber}' not found";
                        continue;
                    }

                    $shareProduct = $shareAccount->shareProduct;

                    // Validate opening balance amount
                    $openingBalanceAmount = (float) $openingBalanceAmount;
                    if ($openingBalanceAmount <= 0) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Opening balance amount must be greater than 0";
                        continue;
                    }

                    // Validate opening balance amount against product constraints
                    if ($shareProduct->minimum_purchase_amount && $openingBalanceAmount < $shareProduct->minimum_purchase_amount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Opening balance amount must be at least " . number_format($shareProduct->minimum_purchase_amount, 2);
                        continue;
                    }

                    if ($shareProduct->maximum_purchase_amount && $openingBalanceAmount > $shareProduct->maximum_purchase_amount) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Opening balance amount must not exceed " . number_format($shareProduct->maximum_purchase_amount, 2);
                        continue;
                    }

                    // Calculate number of shares
                    $nominalPrice = $shareProduct->nominal_price ?? 1;
                    if ($nominalPrice <= 0) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Share product nominal price is not set or is zero";
                        continue;
                    }
                    $numberOfShares = $openingBalanceAmount / $nominalPrice;

                    // Calculate charge amount if product has charges
                    $chargeAmount = 0;
                    if ($shareProduct->has_charges && $shareProduct->charge_amount) {
                        if ($shareProduct->charge_type === 'fixed') {
                            $chargeAmount = $shareProduct->charge_amount;
                        } elseif ($shareProduct->charge_type === 'percentage') {
                            $chargeAmount = ($openingBalanceAmount * $shareProduct->charge_amount) / 100;
                        }
                    }

                    $totalAmount = $openingBalanceAmount + $chargeAmount;

                    // Get chart accounts from share product
                    $liabilityAccountId = $shareProduct->liability_account_id;
                    $shareCapitalAccountId = $shareProduct->share_capital_account_id;
                    
                    if (!$liabilityAccountId) {
                        $errorCount++;
                        $errors[] = "Row " . ($index + 2) . ": Share product does not have a liability account configured";
                        continue;
                    }

                    // For opening balance, we don't use a bank account
                    // Instead, we use the SHARES opening balance account from settings
                    // We'll set bank_account_id to null, but we'll use the opening balance account for GL transactions

                    // Create deposit record (bank_account_id will be null for opening balance)
                    $deposit = ShareDeposit::create([
                        'share_account_id' => $shareAccount->id,
                        'deposit_date' => $openingBalanceDate,
                        'deposit_amount' => $openingBalanceAmount,
                        'number_of_shares' => $numberOfShares,
                        'charge_amount' => $chargeAmount,
                        'total_amount' => $totalAmount,
                        'transaction_reference' => $transactionReference ?: 'Opening Balance',
                        'bank_account_id' => null, // Opening balance doesn't use bank account
                        'liability_account_id' => $liabilityAccountId,
                        'share_capital_account_id' => $shareCapitalAccountId,
                        'cheque_number' => null,
                        'notes' => $openingBalanceDescription ?: ($notes ?: 'Opening Balance Import'),
                        'status' => 'approved',
                        'branch_id' => $user->branch_id ?? null,
                        'company_id' => $user->company_id ?? null,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    // Create GL Transactions for opening balance
                    // Opening Balance Account (from settings) = Equity account (Equity category)
                    // Liability Account (from share product) = Liabilities account (Liabilities category)
                    // Both Equity and Liabilities follow: Credits increase, Debits decrease
                    $customerId = $shareAccount->customer_id;
                    // Use opening_balance_description if provided, otherwise use notes, otherwise default
                    $descriptionText = $openingBalanceDescription ?: ($notes ?: "Opening Balance #{$deposit->id}");
                    $description = "Share opening balance for {$shareAccount->account_number} - " . $descriptionText;

                    // Get the Liability Account (from share product) - Share Capital Account if provided, otherwise Liability Account
                    // This account is in Liabilities category
                    $liabilityAccountIdToUse = $shareCapitalAccountId ?? $liabilityAccountId;

                    // Credit: Liability Account (Liabilities category) - INCREASING share capital/liability
                    GlTransaction::create([
                        'chart_account_id' => $liabilityAccountIdToUse,
                        'customer_id' => $customerId,
                        'amount' => $totalAmount,
                        'nature' => 'credit',
                        'transaction_id' => $deposit->id,
                        'transaction_type' => 'share_deposit',
                        'date' => $openingBalanceDate,
                        'description' => $description,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);

                    // Debit: SHARES Opening Balance Account (Equity category from settings) - DECREASING opening balance
                    GlTransaction::create([
                        'chart_account_id' => $sharesOpeningBalanceAccountId,
                        'customer_id' => $customerId,
                        'amount' => $totalAmount,
                        'nature' => 'debit',
                        'transaction_id' => $deposit->id,
                        'transaction_type' => 'share_deposit',
                        'date' => $openingBalanceDate,
                        'description' => $description,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);

                    // Update share account balance
                    $shareAccount->share_balance += $numberOfShares;
                    $shareAccount->last_transaction_date = $openingBalanceDate;
                    $shareAccount->updated_by = $user->id;
                    $shareAccount->save();

                    // Create opening balance log
                    \App\Models\OpeningBalanceLog::create([
                        'type' => 'share',
                        'customer_id' => $customerId,
                        'share_account_id' => $shareAccount->id,
                        'share_product_id' => $shareProduct->id,
                        'amount' => $totalAmount,
                        'date' => $openingBalanceDate,
                        'description' => $openingBalanceDescription ?: ($notes ?: "Opening balance for {$shareProduct->share_name}"),
                        'transaction_reference' => $transactionReference ?: $deposit->transaction_reference,
                        'receipt_id' => null,
                        'journal_id' => null,
                        'share_deposit_id' => $deposit->id,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    Log::error("Share Opening Balance Import Row Error (Row " . ($index + 2) . "): " . $e->getMessage());
                }
            }

            DB::commit();

            $message = "Opening balance import completed. {$successCount} deposit(s) created successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} error(s) occurred.";
                if (count($errors) > 0) {
                    Log::warning('Share Opening Balance Import Errors', ['errors' => $errors]);
                }
                return redirect()->route('shares.deposits.index')
                    ->with('error', $message)
                    ->with('import_errors', $errors ?? []);
            }

            return redirect()->route('shares.deposits.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Opening Balance Import Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Failed to import share opening balances: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Change the status of a share deposit
     */
    public function changeStatus(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Share deposit not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }

        try {
            DB::beginTransaction();
            
            $deposit = ShareDeposit::with(['shareAccount.shareProduct', 'bankAccount'])->findOrFail($decoded[0]);
            $oldStatus = $deposit->status;
            $newStatus = $request->status;
            
            // If changing from approved to something else, reverse GL transactions and balance
            if ($oldStatus === 'approved' && $newStatus !== 'approved') {
                // Reverse share account balance
                if ($deposit->shareAccount) {
                    $deposit->shareAccount->share_balance -= $deposit->number_of_shares;
                    $deposit->shareAccount->save();
                }
                
                // Delete GL transactions
                GlTransaction::where('transaction_id', $deposit->id)
                    ->where('transaction_type', 'share_deposit')
                    ->delete();
            }
            
            // If changing to approved, create GL transactions and update balance
            if ($oldStatus !== 'approved' && $newStatus === 'approved') {
                $shareAccount = $deposit->shareAccount;
                if (!$shareAccount) {
                    throw new \Exception('Share account not found for this deposit.');
                }
                
                $shareProduct = $shareAccount->shareProduct;
                if (!$shareProduct) {
                    throw new \Exception('Share product not found for this account.');
                }
                
                $liabilityAccountId = $shareProduct->liability_account_id;
                $shareCapitalAccountId = $shareProduct->share_capital_account_id;
                
                if (!$liabilityAccountId) {
                    throw new \Exception('Share product does not have a liability account configured.');
                }
                
                $customerId = $shareAccount->customer_id;
                $description = "Share deposit for {$shareAccount->account_number} - " . ($deposit->notes ?: "Deposit #{$deposit->id}");
                $user = Auth::user();
                
                // Check if this is an opening balance deposit (bank_account_id is null)
                if ($deposit->bank_account_id === null) {
                    // Opening balance deposit - use SHARES opening balance account from settings
                    $sharesOpeningBalanceAccountId = SystemSetting::getValue('shares_opening_balance_account_id', null);
                    
                    if (!$sharesOpeningBalanceAccountId) {
                        throw new \Exception('SHARES opening balance account is not configured in settings.');
                    }
                    
                    // Debit: SHARES Opening Balance Account
                    GlTransaction::create([
                        'chart_account_id' => $sharesOpeningBalanceAccountId,
                        'customer_id' => $customerId,
                        'amount' => $deposit->total_amount,
                        'nature' => 'debit',
                        'transaction_id' => $deposit->id,
                        'transaction_type' => 'share_deposit',
                        'date' => $deposit->deposit_date,
                        'description' => $description,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);
                } else {
                    // Regular deposit - use bank account
                    $bankAccount = $deposit->bankAccount;
                    if (!$bankAccount) {
                        throw new \Exception('Bank account not found for this deposit.');
                    }
                    
                    // Debit: Bank Account
                    GlTransaction::create([
                        'chart_account_id' => $bankAccount->chart_account_id,
                        'customer_id' => $customerId,
                        'amount' => $deposit->total_amount,
                        'nature' => 'debit',
                        'transaction_id' => $deposit->id,
                        'transaction_type' => 'share_deposit',
                        'date' => $deposit->deposit_date,
                        'description' => $description,
                        'branch_id' => $user->branch_id ?? null,
                        'user_id' => $user->id,
                    ]);
                }

                // Credit: Share Capital Account (if provided), otherwise Liability Account
                $creditAccountId = $shareCapitalAccountId ?? $liabilityAccountId;
                
                GlTransaction::create([
                    'chart_account_id' => $creditAccountId,
                    'customer_id' => $customerId,
                    'amount' => $deposit->total_amount,
                    'nature' => 'credit',
                    'transaction_id' => $deposit->id,
                    'transaction_type' => 'share_deposit',
                    'date' => $deposit->deposit_date,
                    'description' => $description,
                    'branch_id' => $user->branch_id ?? null,
                    'user_id' => $user->id,
                ]);
                
                // Update share account balance
                $shareAccount->share_balance += $deposit->number_of_shares;
                $shareAccount->last_transaction_date = $deposit->deposit_date;
                $shareAccount->updated_by = $user->id;
                $shareAccount->save();
            }
            
            // Update deposit status
            $deposit->status = $newStatus;
            $deposit->updated_by = Auth::id();
            $deposit->save();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Deposit status updated successfully.',
                'status' => $deposit->status
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Share Deposit Status Change Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update deposit status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export share deposits to PDF
     */
    public function export(Request $request)
    {
        try {
            $user = Auth::user();
            $company = $user->company;
            
            // Get filtered deposits
            $deposits = ShareDeposit::with([
                'shareAccount.customer',
                'shareAccount.shareProduct',
                'bankAccount',
                'branch',
                'company'
            ]);

            // Apply filters
            if ($request->filled('share_product_id')) {
                $deposits->whereHas('shareAccount', function ($query) use ($request) {
                    $query->where('share_product_id', $request->share_product_id);
                });
            }

            if ($request->filled('status')) {
                $deposits->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $deposits->whereDate('deposit_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $deposits->whereDate('deposit_date', '<=', $request->date_to);
            }

            $deposits = $deposits->orderBy('deposit_date', 'desc')->get();
            
            $generatedAt = Carbon::now();
            $shareProduct = $request->filled('share_product_id') ? ShareProduct::find($request->share_product_id) : null;
            
            $pdf = Pdf::loadView('shares.deposits.export', compact(
                'deposits',
                'company',
                'generatedAt',
                'shareProduct',
                'request'
            ))->setPaper('a4', 'landscape');
            
            $filename = 'share_deposits_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Share Deposits Export Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to export share deposits: ' . $e->getMessage());
        }
    }
}
