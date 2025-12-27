<?php

namespace App\Http\Controllers;

use App\Models\ContributionAccount;
use App\Models\ContributionProduct;
use App\Models\Customer;
use App\Models\GlTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ContributionAccountController extends Controller
{
    public function index()
    {
        return view('contributions.accounts.index');
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getContributionAccountsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $branchId = $user->branch_id;
            $companyId = $user->company_id;

            $accounts = ContributionAccount::with([
                'customer',
                'contributionProduct',
                'branch',
                'company'
            ])
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->select('contribution_accounts.*');

            return DataTables::eloquent($accounts)
                ->addColumn('customer_name', function ($account) {
                    return $account->customer->name ?? 'N/A';
                })
                ->addColumn('customer_number', function ($account) {
                    return $account->customer->customerNo ?? 'N/A';
                })
                ->addColumn('product_name', function ($account) {
                    return $account->contributionProduct->product_name ?? 'N/A';
                })
                ->addColumn('product_category', function ($account) {
                    $category = $account->contributionProduct->category ?? 'N/A';
                    $color = $category === 'Mandatory' ? 'warning' : 'info';
                    return '<span class="badge bg-' . $color . '">' . e($category) . '</span>';
                })
                ->addColumn('balance_formatted', function ($account) {
                    return number_format($account->balance ?? 0, 2);
                })
                ->addColumn('opening_date_formatted', function ($account) {
                    return $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A';
                })
                ->addColumn('status_badge', function ($account) {
                    $status = $account->status ?? 'active';
                    if ($status === 'active') {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Blocked</span>';
                    }
                })
                ->addColumn('actions', function ($account) {
                    $actions = '';
                    $encodedId = Hashids::encode($account->id);

                    // View action
                    $actions .= '<a href="' . route('contributions.accounts.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Block/Unblock action
                    $status = $account->status ?? 'active';
                    if ($status === 'active') {
                        $actions .= '<button class="btn btn-sm btn-warning block-btn me-1" data-id="' . $encodedId . '" data-name="' . e($account->account_number) . '" title="Block Account"><i class="bx bx-lock"></i></button>';
                    } else {
                        $actions .= '<button class="btn btn-sm btn-success unblock-btn me-1" data-id="' . $encodedId . '" data-name="' . e($account->account_number) . '" title="Unblock Account"><i class="bx bx-lock-open"></i></button>';
                    }

                    // Check if account has transactions
                    $product = $account->contributionProduct;
                    $hasTransactions = false;
                    if ($product && $product->liability_account_id) {
                        $hasTransactions = GlTransaction::where('chart_account_id', $product->liability_account_id)
                            ->where('customer_id', $account->customer_id)
                            ->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_transfer', 'journal'])
                            ->exists();
                    }

                    // Delete action - only enabled if no transactions
                    if ($hasTransactions) {
                        $actions .= '<button class="btn btn-sm btn-danger me-1" disabled title="Cannot delete account with transactions" style="opacity: 0.5; cursor: not-allowed;"><i class="bx bx-trash"></i></button>';
                    } else {
                        $actions .= '<button class="btn btn-sm btn-danger delete-btn me-1" data-id="' . $encodedId . '" data-name="' . e($account->account_number) . '" title="Delete"><i class="bx bx-trash"></i></button>';
                    }

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['product_category', 'status_badge', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function create()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        $products = ContributionProduct::where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();
        
        $customers = Customer::orderBy('name')->get();

        return view('contributions.accounts.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        // Validate multiple lines
        $rules = [];
        $messages = [];

        if ($request->has('lines')) {
            foreach ($request->lines as $index => $line) {
                $rules["lines.{$index}.customer_id"] = 'required|exists:customers,id';
                $rules["lines.{$index}.contribution_product_id"] = 'required|exists:contribution_products,id';
                $rules["lines.{$index}.opening_date"] = 'required|date';
                $rules["lines.{$index}.notes"] = 'nullable|string';

                $messages["lines.{$index}.customer_id.required"] = "Line " . ($index + 1) . ": Member name is required";
                $messages["lines.{$index}.contribution_product_id.required"] = "Line " . ($index + 1) . ": Contribution product is required";
                $messages["lines.{$index}.opening_date.required"] = "Line " . ($index + 1) . ": Opening date is required";
            }
        } else {
            // Fallback to single line validation
            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'contribution_product_id' => 'required|exists:contribution_products,id',
                'opening_date' => 'required|date',
                'notes' => 'nullable|string',
            ];
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        // Additional validation: Check for duplicates within the same request
        $lines = $request->lines ?? [
            [
                'customer_id' => $request->customer_id,
                'contribution_product_id' => $request->contribution_product_id,
                'opening_date' => $request->opening_date,
                'notes' => $request->notes,
            ]
        ];

        // Check for duplicates within the same request
        $combinations = [];
        foreach ($lines as $index => $line) {
            if (!empty($line['customer_id']) && !empty($line['contribution_product_id'])) {
                $combination = $line['customer_id'] . '_' . $line['contribution_product_id'];
                if (isset($combinations[$combination])) {
                    $validator->errors()->add(
                        "lines.{$index}.customer_id",
                        "Line " . ($index + 1) . ": This member already has this contribution product selected in another line."
                    );
                } else {
                    $combinations[$combination] = $index;
                }
            }
        }

        // Check for duplicates against existing records in database
        foreach ($lines as $index => $line) {
            if (!empty($line['customer_id']) && !empty($line['contribution_product_id'])) {
                $exists = ContributionAccount::where('customer_id', $line['customer_id'])
                    ->where('contribution_product_id', $line['contribution_product_id'])
                    ->exists();
                
                if ($exists) {
                    $customer = Customer::find($line['customer_id']);
                    $product = ContributionProduct::find($line['contribution_product_id']);
                    $customerName = $customer ? $customer->name : 'Unknown';
                    $productName = $product ? $product->product_name : 'Unknown';
                    
                    $validator->errors()->add(
                        "lines.{$index}.customer_id",
                        "Line " . ($index + 1) . ": Member \"{$customerName}\" already has a contribution account for product \"{$productName}\"."
                    );
                }
            }
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $createdCount = 0;
        $createdInBatch = [];

        foreach ($lines as $lineIndex => $line) {
            // Skip empty lines
            if (empty($line['customer_id']) || empty($line['contribution_product_id'])) {
                continue;
            }

            $combination = $line['customer_id'] . '_' . $line['contribution_product_id'];
            
            // Skip if already created in this batch
            if (isset($createdInBatch[$combination])) {
                continue;
            }
            
            // Final check: Verify this combination doesn't exist in database
            $exists = ContributionAccount::where('customer_id', $line['customer_id'])
                ->where('contribution_product_id', $line['contribution_product_id'])
                ->exists();
            
            if ($exists) {
                continue;
            }

            // Generate 16-character account number
            $accountNumber = $this->generateAccountNumber();

            ContributionAccount::create([
                'customer_id' => $line['customer_id'],
                'contribution_product_id' => $line['contribution_product_id'],
                'account_number' => $accountNumber,
                'opening_date' => $line['opening_date'],
                'notes' => $line['notes'] ?? null,
                'balance' => 0,
                'status' => 'active',
                'branch_id' => auth()->user()->branch_id ?? null,
                'company_id' => auth()->user()->company_id ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $createdInBatch[$combination] = true;
            $createdCount++;
        }

        if ($createdCount > 0) {
            return redirect()->route('contributions.accounts.index')
                ->with('success', $createdCount . ' contribution account(s) created successfully.');
        } else {
            return redirect()->back()
                ->with('error', 'No valid accounts to create.');
        }
    }

    /**
     * Display the specified contribution account
     */
    public function show($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            abort(404, 'Contribution account not found.');
        }

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        $account = ContributionAccount::where('id', $decoded[0])
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->with([
                'customer',
                'contributionProduct',
                'branch',
                'company'
            ])
            ->firstOrFail();

        $product = $account->contributionProduct;
        if (!$product || !$product->liability_account_id) {
            abort(404, 'Contribution product or liability account not configured.');
        }

        // Calculate statistics for this specific account
        // Get deposits (credits to liability account for this customer)
        $totalDeposits = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
            ->where('nature', 'credit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Get withdrawals (debits to liability account for this customer)
        $totalWithdrawals = GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'journal'])
            ->where('nature', 'debit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Get transfers out (debits for transfers from this account)
        $totalTransfersOut = GlTransaction::where('transaction_type', 'contribution_transfer')
            ->where('nature', 'debit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Get transfers in (credits for transfers to this account)
        $totalTransfersIn = GlTransaction::where('transaction_type', 'contribution_transfer')
            ->where('nature', 'credit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Calculate net transfers (out - in)
        $totalTransfers = $totalTransfersOut - $totalTransfersIn;

        // Current balance
        $currentBalance = $account->balance;

        return view('contributions.accounts.show', compact(
            'account',
            'product',
            'totalDeposits',
            'totalWithdrawals',
            'totalTransfers',
            'currentBalance'
        ));
    }

    /**
     * Get account transactions data for DataTable
     */
    public function getAccountTransactionsData(Request $request, $encodedId)
    {
        if ($request->ajax()) {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return response()->json(['error' => 'Invalid account ID'], 400);
            }

            $user = auth()->user();
            $branchId = $user->branch_id;

            $account = ContributionAccount::find($decoded[0]);
            if (!$account) {
                return response()->json(['error' => 'Account not found'], 400);
            }

            $product = $account->contributionProduct;
            if (!$product || !$product->liability_account_id) {
                return response()->json(['error' => 'Product or liability account not configured'], 400);
            }

            // Get date filters
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');

            // Build query for transactions
            $query = GlTransaction::where('chart_account_id', $product->liability_account_id)
                ->where('customer_id', $account->customer_id)
                ->where('branch_id', $branchId)
                ->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_transfer', 'journal']);

            // Apply date filters
            if ($startDate) {
                $query->whereDate('date', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('date', '<=', $endDate);
            }

            // Calculate opening balance (before start date)
            $openingBalance = 0;
            if ($startDate) {
                $openingDeposits = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
                    ->where('nature', 'credit')
                    ->where('chart_account_id', $product->liability_account_id)
                    ->where('customer_id', $account->customer_id)
                    ->where('branch_id', $branchId)
                    ->whereDate('date', '<', $startDate)
                    ->sum('amount');

                $openingWithdrawals = GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'contribution_transfer', 'journal'])
                    ->where('nature', 'debit')
                    ->where('chart_account_id', $product->liability_account_id)
                    ->where('customer_id', $account->customer_id)
                    ->where('branch_id', $branchId)
                    ->whereDate('date', '<', $startDate)
                    ->sum('amount');

                $openingBalance = $openingDeposits - $openingWithdrawals;
            }

            $transactions = $query->with(['customer', 'chartAccount'])
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Calculate running balance
            $runningBalance = $openingBalance;
            $transactionsData = $transactions->map(function ($transaction) use (&$runningBalance) {
                if ($transaction->nature === 'credit') {
                    $runningBalance += $transaction->amount;
                } else {
                    $runningBalance -= $transaction->amount;
                }

                // Generate transaction ID
                $trxId = '';
                if ($transaction->transaction_type === 'journal') {
                    $journal = \App\Models\Journal::find($transaction->transaction_id);
                    $trxId = $journal ? $journal->reference : 'JRN-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                } elseif ($transaction->transaction_type === 'contribution_transfer') {
                    $journal = \App\Models\Journal::find($transaction->transaction_id);
                    $trxId = $journal ? $journal->reference : 'CT-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                } else {
                    $prefix = $transaction->transaction_type === 'contribution_deposit' ? 'CD' : 'CW';
                    $trxId = $prefix . '-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                }

                return [
                    'id' => $transaction->id,
                    'trx_id' => $trxId,
                    'date' => $transaction->date->format('M d, Y'),
                    'description' => $transaction->description,
                    'credit' => $transaction->nature === 'credit' ? number_format($transaction->amount, 2) : '-',
                    'debit' => $transaction->nature === 'debit' ? number_format($transaction->amount, 2) : '-',
                    'balance' => number_format($runningBalance, 2),
                    'type' => ucfirst(str_replace('_', ' ', $transaction->transaction_type)),
                ];
            });

            return response()->json([
                'data' => $transactionsData,
                'opening_balance' => number_format($openingBalance, 2),
                'closing_balance' => number_format($runningBalance, 2),
            ]);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Export account statement to PDF
     */
    public function exportStatement(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            abort(404, 'Contribution account not found.');
        }

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        $account = ContributionAccount::where('id', $decoded[0])
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->with(['customer', 'contributionProduct', 'branch', 'company'])
            ->firstOrFail();

        $product = $account->contributionProduct;
        if (!$product || !$product->liability_account_id) {
            abort(404, 'Contribution product or liability account not configured.');
        }

        // Get date filters
        $startDate = $request->get('start_date', $account->opening_date->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        // Calculate opening balance
        $openingDeposits = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
            ->where('nature', 'credit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->whereDate('date', '<', $startDate)
            ->sum('amount');

        $openingWithdrawals = GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'contribution_transfer', 'journal'])
            ->where('nature', 'debit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->whereDate('date', '<', $startDate)
            ->sum('amount');

        $openingBalance = $openingDeposits - $openingWithdrawals;

        // Get transactions in date range
        $transactions = GlTransaction::where('chart_account_id', $product->liability_account_id)
            ->where('customer_id', $account->customer_id)
            ->where('branch_id', $branchId)
            ->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_transfer', 'journal'])
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with(['customer', 'chartAccount'])
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate running balance
        $runningBalance = $openingBalance;
        $transactionsData = $transactions->map(function ($transaction) use (&$runningBalance) {
            if ($transaction->nature === 'credit') {
                $runningBalance += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
            }

            // Generate transaction ID
            $trxId = '';
            if ($transaction->transaction_type === 'journal') {
                $journal = \App\Models\Journal::find($transaction->transaction_id);
                $trxId = $journal ? $journal->reference : 'JRN-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
            } elseif ($transaction->transaction_type === 'contribution_transfer') {
                $journal = \App\Models\Journal::find($transaction->transaction_id);
                $trxId = $journal ? $journal->reference : 'CT-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
            } else {
                $prefix = $transaction->transaction_type === 'contribution_deposit' ? 'CD' : 'CW';
                $trxId = $prefix . '-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
            }

            return [
                'trx_id' => $trxId,
                'date' => $transaction->date->format('M d, Y'),
                'description' => $transaction->description,
                'credit' => $transaction->nature === 'credit' ? $transaction->amount : 0,
                'debit' => $transaction->nature === 'debit' ? $transaction->amount : 0,
                'balance' => $runningBalance,
                'type' => ucfirst(str_replace('_', ' ', $transaction->transaction_type)),
            ];
        });

        $closingBalance = $runningBalance;

        $pdf = Pdf::loadView('contributions.accounts.statement-pdf', [
            'account' => $account,
            'product' => $product,
            'startDate' => Carbon::parse($startDate),
            'endDate' => Carbon::parse($endDate),
            'openingBalance' => $openingBalance,
            'closingBalance' => $closingBalance,
            'transactions' => $transactionsData,
            'company' => $user->company,
            'branch' => $user->branch,
        ]);

        $filename = 'Contribution_Statement_' . $account->account_number . '_' . $startDate . '_to_' . $endDate . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Delete a contribution account
     */
    public function destroy($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid account ID'], 400);
        }

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        $account = ContributionAccount::where('id', $decoded[0])
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        // Check if account has transactions
        $product = $account->contributionProduct;
        if ($product && $product->liability_account_id) {
            $hasTransactions = GlTransaction::where('chart_account_id', $product->liability_account_id)
                ->where('customer_id', $account->customer_id)
                ->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_transfer', 'journal'])
                ->exists();

            if ($hasTransactions) {
                return response()->json(['error' => 'Cannot delete account with existing transactions'], 400);
            }
        }

        try {
            $accountNumber = $account->account_number;
            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Contribution account ' . $accountNumber . ' deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete account: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle account status (block/unblock)
     */
    public function toggleStatus($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            return response()->json(['error' => 'Invalid account ID'], 400);
        }

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        $account = ContributionAccount::where('id', $decoded[0])
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            return response()->json(['error' => 'Account not found'], 404);
        }

        try {
            $newStatus = ($account->status ?? 'active') === 'active' ? 'inactive' : 'active';
            $account->status = $newStatus;
            $account->updated_by = $user->id;
            $account->save();

            $statusText = $newStatus === 'active' ? 'unblocked' : 'blocked';
            return response()->json([
                'success' => true,
                'message' => 'Contribution account ' . $account->account_number . ' has been ' . $statusText . ' successfully.',
                'status' => $newStatus
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update account status: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate a unique 16-character account number
     */
    private function generateAccountNumber()
    {
        do {
            // Generate 16-character account number: CA (2 chars) + 14 random alphanumeric
            $accountNumber = 'CA' . strtoupper(Str::random(14));
        } while (ContributionAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }
}
