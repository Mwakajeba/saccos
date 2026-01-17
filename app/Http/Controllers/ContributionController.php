<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ContributionProduct;
use App\Models\ContributionAccount;
use App\Models\ChartAccount;
use App\Models\BankAccount;
use App\Models\Fee;
use App\Models\JournalReference;
use App\Models\Journal;
use App\Models\JournalItem;
use App\Models\GlTransaction;
use App\Models\Customer;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Payment;
use App\Models\PaymentItem;
use Vinkla\Hashids\Facades\Hashids;
use Yajra\DataTables\Facades\DataTables;

class ContributionController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get counts for each contribution type
        $stats = [
            'products' => $this->getCount('contribution_products', $branchId, $companyId),
            'accounts' => $this->getCount('contribution_accounts', $branchId, $companyId),
            'deposits' => $this->getCount('contribution_deposits', $branchId, $companyId),
            'withdrawals' => $this->getCount('contribution_withdrawals', $branchId, $companyId),
            'transfers' => $this->getCount('contribution_transfers', $branchId, $companyId),
            'opening_balances' => 0, // Placeholder for opening balance count
        ];

        return view('contributions.index', compact('stats'));
    }

    /**
     * Get count from a table, handling cases where table might not exist
     */
    private function getCount($tableName, $branchId = null, $companyId = null)
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable($tableName)) {
                return 0;
            }

            $query = DB::table($tableName);
            
            // Add branch filter if branch_id column exists
            if ($branchId && DB::getSchemaBuilder()->hasColumn($tableName, 'branch_id')) {
                $query->where('branch_id', $branchId);
            }
            
            // Add company filter if company_id column exists
            if ($companyId && DB::getSchemaBuilder()->hasColumn($tableName, 'company_id')) {
                $query->where('company_id', $companyId);
            }

            return $query->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function products()
    {
        return view('contributions.products.index');
    }

    /**
     * Ajax endpoint for DataTables
     */
    public function getContributionProductsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $branchId = $user->branch_id;
            $companyId = $user->company_id;

            $products = ContributionProduct::with([
                'bankAccount',
                'journalReference',
                'ribaJournal',
                'payLoanJournal',
                'liabilityAccount',
                'expenseAccount',
                'ribaPayableAccount',
                'withholdingAccount',
                'ribaPayableJournal',
                'charge'
            ])
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->select('contribution_products.*');

            return DataTables::eloquent($products)
                ->addColumn('interest_formatted', function ($product) {
                    return number_format($product->interest, 2) . '%';
                })
                ->addColumn('category_badge', function ($product) {
                    $color = $product->category === 'Mandatory' ? 'warning' : 'info';
                    return '<span class="badge bg-' . $color . '">' . e($product->category) . '</span>';
                })
                ->addColumn('status_badge', function ($product) {
                    if ($product->is_active ?? true) {
                        return '<span class="badge bg-success">Active</span>';
                    } else {
                        return '<span class="badge bg-danger">Inactive</span>';
                    }
                })
                ->addColumn('lockin_period_display', function ($product) {
                    return $product->lockin_period_frequency . ' ' . $product->lockin_period_frequency_type;
                })
                ->addColumn('can_withdraw_badge', function ($product) {
                    if ($product->can_withdraw) {
                        return '<span class="badge bg-success">Yes</span>';
                    } else {
                        return '<span class="badge bg-secondary">No</span>';
                    }
                })
                ->addColumn('actions', function ($product) {
                    $actions = '';
                    $encodedId = Hashids::encode($product->id);

                    // View action
                    $actions .= '<a href="' . route('contributions.products.show', $encodedId) . '" class="btn btn-sm btn-info me-1" title="View"><i class="bx bx-show"></i></a>';

                    // Edit action
                    $actions .= '<a href="' . route('contributions.products.edit', $encodedId) . '" class="btn btn-sm btn-warning me-1" title="Edit"><i class="bx bx-edit"></i></a>';

                    // Delete action
                    $actions .= '<button class="btn btn-sm btn-danger delete-btn" data-id="' . $encodedId . '" data-name="' . e($product->product_name) . '" title="Delete"><i class="bx bx-trash"></i></button>';

                    return '<div class="text-center d-flex justify-content-center gap-1">' . $actions . '</div>';
                })
                ->rawColumns(['category_badge', 'status_badge', 'can_withdraw_badge', 'actions'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Display the specified contribution product
     */
    public function productsShow($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            abort(404, 'Contribution product not found.');
        }

        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        $product = ContributionProduct::where('id', $decoded[0])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->with([
                'bankAccount',
                'journalReference',
                'ribaJournal',
                'payLoanJournal',
                'liabilityAccount',
                'expenseAccount',
                'ribaPayableAccount',
                'withholdingAccount',
                'ribaPayableJournal',
                'charge'
            ])
            ->firstOrFail();

        // Get statistics
        $totalCustomers = ContributionAccount::where('contribution_product_id', $product->id)
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->count();

        // Get total deposits (credits to liability account) - including deposits and journal credits
        $totalDeposits = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
            ->where('nature', 'credit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Get total withdrawals (debits to liability account) - including withdrawals, transfers, and journal debits
        $totalWithdrawals = GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'contribution_transfer', 'journal'])
            ->where('nature', 'debit')
            ->where('chart_account_id', $product->liability_account_id)
            ->where('branch_id', $branchId)
            ->sum('amount');

        // Calculate balance
        $balance = $totalDeposits - $totalWithdrawals;

        return view('contributions.products.show', compact('product', 'totalCustomers', 'totalDeposits', 'totalWithdrawals', 'balance'));
    }

    /**
     * Ajax endpoint for Product Transactions DataTable
     */
    public function getProductTransactionsData(Request $request, $encodedId)
    {
        if ($request->ajax()) {
            $decoded = Hashids::decode($encodedId);
            if (empty($decoded)) {
                return response()->json(['error' => 'Invalid product ID'], 400);
            }

            $user = auth()->user();
            $branchId = $user->branch_id;

            $product = ContributionProduct::find($decoded[0]);
            if (!$product || !$product->liability_account_id) {
                return response()->json(['error' => 'Product not found or liability account not configured'], 400);
            }

            // Get all transactions for this product's liability account
            // For transfers: filter by journal items to identify which product the transaction belongs to
            // This handles cases where multiple products share the same liability account
            $transactions = GlTransaction::where('chart_account_id', $product->liability_account_id)
                ->where('branch_id', $branchId)
                ->where(function($query) use ($product) {
                    // Deposits, withdrawals, and journals - all transactions for this product
                    $query->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'journal']);
                    
                    // For transfers: filter by journal items to match the product
                    // Match the GL transaction's nature with journal item's nature AND product name in description
                    $query->orWhere(function($q) use ($product) {
                        $q->where('transaction_type', 'contribution_transfer')
                          ->whereExists(function($subQuery) use ($product) {
                              $subQuery->select(DB::raw(1))
                                  ->from('journal_items')
                                  ->join('journals', 'journal_items.journal_id', '=', 'journals.id')
                                  ->whereColumn('journals.id', 'gl_transactions.transaction_id')
                                  ->whereColumn('journal_items.nature', 'gl_transactions.nature') // Match nature (debit/credit)
                                  ->where('journal_items.chart_account_id', $product->liability_account_id)
                                  ->where(function($descQuery) use ($product) {
                                      // Match journal items that mention this product in description
                                      $productName = $product->product_name;
                                      $descQuery->where('journal_items.description', 'LIKE', "%{$productName}%");
                                  });
                          });
                    });
                })
                ->with(['customer', 'chartAccount'])
                ->select('gl_transactions.*');

            return DataTables::eloquent($transactions)
                ->addColumn('trx_id', function ($transaction) {
                    if ($transaction->transaction_type === 'journal') {
                        $journal = Journal::find($transaction->transaction_id);
                        return $journal ? $journal->reference : 'JRN-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                    } elseif ($transaction->transaction_type === 'contribution_transfer') {
                        $journal = Journal::find($transaction->transaction_id);
                        return $journal ? $journal->reference : 'CT-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                    }
                    $prefix = $transaction->transaction_type === 'contribution_deposit' ? 'CD' : 'CW';
                    return $prefix . '-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                })
                ->addColumn('date_formatted', function ($transaction) {
                    return $transaction->date->format('M d, Y');
                })
                ->addColumn('customer_name', function ($transaction) {
                    return $transaction->customer ? $transaction->customer->name : 'N/A';
                })
                ->addColumn('type_badge', function ($transaction) {
                    if ($transaction->transaction_type === 'contribution_deposit') {
                        return '<span class="badge bg-success">Deposit</span>';
                    } elseif ($transaction->transaction_type === 'contribution_withdrawal') {
                        return '<span class="badge bg-danger">Withdrawal</span>';
                    } elseif ($transaction->transaction_type === 'contribution_transfer') {
                        return '<span class="badge bg-warning">Transfer</span>';
                    } elseif ($transaction->transaction_type === 'journal') {
                        return '<span class="badge bg-info">Journal</span>';
                    }
                    return '<span class="badge bg-secondary">' . ucfirst($transaction->transaction_type) . '</span>';
                })
                ->addColumn('credit', function ($transaction) {
                    return $transaction->nature === 'credit' ? number_format($transaction->amount, 2) : '-';
                })
                ->addColumn('debit', function ($transaction) {
                    return $transaction->nature === 'debit' ? number_format($transaction->amount, 2) : '-';
                })
                ->addColumn('description_text', function ($transaction) {
                    if ($transaction->transaction_type === 'journal') {
                        $journal = Journal::find($transaction->transaction_id);
                        return $transaction->description ?: ($journal ? $journal->description : 'Journal Entry');
                    } elseif ($transaction->transaction_type === 'contribution_transfer') {
                        return $transaction->description ?: 'Contribution Transfer';
                    }
                    return $transaction->description ?: ($transaction->transaction_type === 'contribution_deposit' ? 'Contribution Deposit' : 'Contribution Withdrawal');
                })
                ->orderColumn('date', 'date DESC')
                ->rawColumns(['trx_id', 'date_formatted', 'customer_name', 'type_badge', 'credit', 'debit', 'description_text'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function productsCreate()
    {
        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;
        
        $chartAccounts = ChartAccount::all();
        $bankAccounts = BankAccount::all();
        $journalReferences = \App\Models\JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->get();
        
        return view('contributions.products.create', compact('chartAccounts', 'bankAccounts', 'journalReferences'));
    }

    public function productsStore(Request $request)
    {
        try {
            // Normalize checkbox values before validation (checkboxes don't send value when unchecked)
            $request->merge([
                'can_withdraw' => $request->has('can_withdraw') ? 1 : 0,
                'has_charge' => $request->has('has_charge') ? 1 : 0,
            ]);
            
            $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'interest' => 'required|numeric|min:0|max:100',
            'category' => 'required|in:Voluntary,Mandatory',
            'auto_create' => 'required|in:Yes,No',
            'compound_period' => 'required|in:Daily,Monthly',
            'interest_posting_period' => 'nullable|in:Monthly,Quarterly,Annually',
            'interest_calculation_type' => 'required|in:Daily,Monthly,Annually',
            'lockin_period_frequency' => 'required|integer|min:0',
            'lockin_period_frequency_type' => 'required|in:Days,Months',
            'automatic_opening_balance' => 'required|numeric|min:0',
            'minimum_balance_for_interest_calculations' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'can_withdraw' => 'required|boolean',
            'has_charge' => 'required|boolean',
            'charge_id' => 'nullable|exists:fees,id',
            'charge_type' => 'nullable|required_if:has_charge,1|in:Fixed,Percentage',
            'charge_amount' => 'nullable|required_if:has_charge,1|numeric|min:0',
            'bank_account_id' => 'required|exists:chart_accounts,id',
            'journal_reference_id' => 'required|exists:journal_references,id',
            'riba_journal_id' => 'required|exists:journal_references,id',
            'pay_loan_journal_id' => 'required|exists:journal_references,id',
            'liability_account_id' => 'required|exists:chart_accounts,id',
            'expense_account_id' => 'required|exists:chart_accounts,id',
            'riba_payable_account_id' => 'required|exists:chart_accounts,id',
            'withholding_account_id' => 'required|exists:chart_accounts,id',
            'withholding_percentage' => 'nullable|numeric|min:0|max:100',
            'riba_payable_journal_id' => 'required|exists:journal_references,id',
        ]);

        // Ensure boolean values are properly cast (already normalized above, but ensure boolean type)
        $validated['can_withdraw'] = (bool) $validated['can_withdraw'];
        $validated['has_charge'] = (bool) $validated['has_charge'];
        $validated['company_id'] = auth()->user()->company_id;
        $validated['branch_id'] = auth()->user()->branch_id;

            ContributionProduct::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contribution product created successfully!'
                ]);
            }

            return redirect()->route('contributions.products.index')
                ->with('success', 'Contribution product created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    public function accounts()
    {
        return view('contributions.accounts');
    }

    public function deposits()
    {
        return view('contributions.deposits.index');
    }

    /**
     * Ajax endpoint for Deposits DataTable
     */
    public function getDepositsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $branchId = $user->branch_id;

            $deposits = GlTransaction::where('transaction_type', 'contribution_deposit')
                ->where('nature', 'credit') // Deposits are credits to liability account
                ->where('branch_id', $branchId)
                ->with(['customer', 'chartAccount'])
                ->select('gl_transactions.*');

            return DataTables::eloquent($deposits)
                ->addColumn('trx_id', function ($deposit) {
                    return 'CD-' . str_pad($deposit->transaction_id, 6, '0', STR_PAD_LEFT);
                })
                ->addColumn('date_formatted', function ($deposit) {
                    return $deposit->date->format('M d, Y');
                })
                ->addColumn('customer_name', function ($deposit) {
                    return $deposit->customer ? $deposit->customer->name : 'N/A';
                })
                ->addColumn('product_name', function ($deposit) {
                    $product = ContributionProduct::where('liability_account_id', $deposit->chart_account_id)
                        ->where('branch_id', $deposit->branch_id)
                        ->first();
                    return $product ? $product->product_name : 'N/A';
                })
                ->addColumn('amount_formatted', function ($deposit) {
                    return number_format($deposit->amount, 2);
                })
                ->addColumn('description_text', function ($deposit) {
                    return $deposit->description ?: 'Contribution Deposit';
                })
                ->orderColumn('date', 'date DESC')
                ->rawColumns(['trx_id', 'date_formatted', 'customer_name', 'product_name', 'amount_formatted', 'description_text'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function depositsCreate(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get product ID from query parameter if provided
        $productId = $request->get('product_id');
        
        // Get active contribution products
        $productsQuery = ContributionProduct::where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->where('is_active', true);
        
        // If product_id is provided, filter to that product only
        if ($productId) {
            $decoded = Hashids::decode($productId);
            if (!empty($decoded)) {
                $productsQuery->where('id', $decoded[0]);
            }
        }
        
        $products = $productsQuery->orderBy('product_name')->get();

        // Get customers with contribution accounts for current branch/company
        $customersQuery = Customer::whereHas('contributionAccounts', function($query) use ($branchId, $companyId, $productId) {
            $query->where('branch_id', $branchId)
                  ->where('company_id', $companyId);
            // If product_id is provided, filter customers by that product
            if ($productId) {
                $decoded = Hashids::decode($productId);
                if (!empty($decoded)) {
                    $query->where('contribution_product_id', $decoded[0]);
                }
            }
        })
        ->where('branch_id', $branchId)
        ->where('company_id', $companyId);

        $customers = $customersQuery->orderBy('name')->get();

        // Get bank accounts
        $bankAccounts = BankAccount::with('chartAccount')
            ->orderBy('name')
            ->get();

        return view('contributions.deposits.create', compact('products', 'customers', 'bankAccounts', 'productId'));
    }

    public function depositsStore(Request $request)
    {
        $request->validate([
            'contribution_product_id' => 'required|exists:contribution_products,id',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        DB::beginTransaction();
        try {
            // Get contribution product and account
            $product = ContributionProduct::findOrFail($request->contribution_product_id);
            $contributionAccount = ContributionAccount::where('customer_id', $request->customer_id)
                ->where('contribution_product_id', $request->contribution_product_id)
                ->where('branch_id', $branchId)
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Get bank account
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Get liability account from product
            if (!$product->liability_account_id) {
                return back()->withErrors(['contribution_product_id' => 'Contribution product does not have a liability account configured.'])->withInput();
            }

            // Create receipt (for deposit - money coming in)
            $receipt = Receipt::create([
                'reference' => 'CD-' . strtoupper(uniqid()),
                'reference_type' => 'Contribution Deposit',
                'reference_number' => null,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
                'user_id' => $user->id,
                'bank_account_id' => $request->bank_account_id,
                'payee_type' => 'customer',
                'customer_id' => $request->customer_id,
                'branch_id' => $branchId,
                'approved' => true, // Auto-approve contribution deposits
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Create receipt item (liability account)
            ReceiptItem::create([
                'receipt_id' => $receipt->id,
                'chart_account_id' => $product->liability_account_id,
                'amount' => $request->amount,
                'description' => $request->description ?: "Contribution deposit for {$product->product_name}",
            ]);

            // Create GL transactions
            $glDescription = $request->description ?: "Contribution deposit - {$product->product_name}";

            // Debit bank account (money coming in)
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $receipt->id,
                'transaction_type' => 'contribution_deposit',
                'date' => $request->date,
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Credit liability account (contribution account increases)
            GlTransaction::create([
                'chart_account_id' => $product->liability_account_id,
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $receipt->id,
                'transaction_type' => 'contribution_deposit',
                'date' => $request->date,
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Update contribution account balance
            $contributionAccount->increment('balance', $request->amount);

            DB::commit();

            return redirect()->route('contributions.deposits.index')
                ->with('success', "Contribution deposit of " . number_format($request->amount, 2) . " successfully recorded.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process deposit: ' . $e->getMessage()])->withInput();
        }
    }

    public function withdrawals()
    {
        return view('contributions.withdrawals.index');
    }

    /**
     * Ajax endpoint for Withdrawals DataTable
     */
    public function getWithdrawalsData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $branchId = $user->branch_id;

            $withdrawals = GlTransaction::where('transaction_type', 'contribution_withdrawal')
                ->where('nature', 'debit') // Withdrawals are debits to liability account
                ->where('branch_id', $branchId)
                ->with(['customer', 'chartAccount'])
                ->select('gl_transactions.*');

            return DataTables::eloquent($withdrawals)
                ->addColumn('trx_id', function ($withdrawal) {
                    return 'CW-' . str_pad($withdrawal->transaction_id, 6, '0', STR_PAD_LEFT);
                })
                ->addColumn('date_formatted', function ($withdrawal) {
                    return $withdrawal->date->format('M d, Y');
                })
                ->addColumn('customer_name', function ($withdrawal) {
                    return $withdrawal->customer ? $withdrawal->customer->name : 'N/A';
                })
                ->addColumn('product_name', function ($withdrawal) {
                    $product = ContributionProduct::where('liability_account_id', $withdrawal->chart_account_id)
                        ->where('branch_id', $withdrawal->branch_id)
                        ->first();
                    return $product ? $product->product_name : 'N/A';
                })
                ->addColumn('amount_formatted', function ($withdrawal) {
                    return number_format($withdrawal->amount, 2);
                })
                ->addColumn('description_text', function ($withdrawal) {
                    return $withdrawal->description ?: 'Contribution Withdrawal';
                })
                ->orderColumn('date', 'date DESC')
                ->rawColumns(['trx_id', 'date_formatted', 'customer_name', 'product_name', 'amount_formatted', 'description_text'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    public function withdrawalsCreate(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get product ID from query parameter if provided
        $productId = $request->get('product_id');
        
        // Get active contribution products
        $productsQuery = ContributionProduct::where('is_active', true)
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId);
        
        // If product_id is provided, filter to that product only
        if ($productId) {
            $decoded = Hashids::decode($productId);
            if (!empty($decoded)) {
                $productsQuery->where('id', $decoded[0]);
            }
        }
        
        $products = $productsQuery->orderBy('product_name')->get();

        // Get customers with contribution accounts for current branch/company
        $customers = Customer::whereHas('contributionAccounts', function($query) use ($branchId, $companyId) {
            $query->where('branch_id', $branchId)
                  ->where('company_id', $companyId);
        })
        ->where('branch_id', $branchId)
        ->where('company_id', $companyId)
        ->orderBy('name')
        ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::with('chartAccount')
            ->orderBy('name')
            ->get();

        return view('contributions.withdrawals.create', compact('products', 'customers', 'bankAccounts'));
    }

    public function withdrawalsStore(Request $request)
    {
        $request->validate([
            'contribution_product_id' => 'required|exists:contribution_products,id',
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        DB::beginTransaction();
        try {
            // Get contribution product and account
            $product = ContributionProduct::findOrFail($request->contribution_product_id);
            $contributionAccount = ContributionAccount::where('customer_id', $request->customer_id)
                ->where('contribution_product_id', $request->contribution_product_id)
                ->where('branch_id', $branchId)
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Check if account is active (not blocked)
            if (($contributionAccount->status ?? 'active') === 'inactive') {
                return back()->withErrors(['customer_id' => 'This contribution account is blocked. Withdrawals are not allowed.'])->withInput();
            }

            // Check if account has sufficient balance
            if ($contributionAccount->balance < $request->amount) {
                return back()->withErrors(['amount' => 'Insufficient balance. Available balance: ' . number_format($contributionAccount->balance, 2)])->withInput();
            }

            // Get bank account
            $bankAccount = BankAccount::findOrFail($request->bank_account_id);

            // Get liability account from product
            if (!$product->liability_account_id) {
                return back()->withErrors(['contribution_product_id' => 'Contribution product does not have a liability account configured.'])->withInput();
            }

            // Create payment (for withdrawal - money going out)
            $payment = Payment::create([
                'reference' => 'CW-' . strtoupper(uniqid()),
                'reference_type' => 'Contribution Withdrawal',
                'reference_number' => null,
                'amount' => $request->amount,
                'date' => $request->date,
                'description' => $request->description,
                'user_id' => $user->id,
                'bank_account_id' => $request->bank_account_id,
                'payee_type' => 'customer',
                'customer_id' => $request->customer_id,
                'branch_id' => $branchId,
                'approved' => true, // Auto-approve contribution withdrawals
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);

            // Create payment item (liability account)
            PaymentItem::create([
                'payment_id' => $payment->id,
                'chart_account_id' => $product->liability_account_id,
                'amount' => $request->amount,
                'description' => $request->description ?: "Contribution withdrawal for {$product->product_name}",
            ]);

            // Create GL transactions
            $glDescription = $request->description ?: "Contribution withdrawal - {$product->product_name}";

            // Credit bank account (money going out)
            GlTransaction::create([
                'chart_account_id' => $bankAccount->chart_account_id,
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $payment->id,
                'transaction_type' => 'contribution_withdrawal',
                'date' => $request->date,
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Debit liability account (contribution account decreases)
            GlTransaction::create([
                'chart_account_id' => $product->liability_account_id,
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $payment->id,
                'transaction_type' => 'contribution_withdrawal',
                'date' => $request->date,
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Update contribution account balance (decrease)
            $contributionAccount->decrement('balance', $request->amount);

            DB::commit();

            return redirect()->route('contributions.withdrawals.index')
                ->with('success', "Contribution withdrawal of " . number_format($request->amount, 2) . " successfully recorded.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process withdrawal: ' . $e->getMessage()])->withInput();
        }
    }

    public function transfers()
    {
        return view('contributions.transfers.index');
    }

    public function transfersCreate()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get all customers with contribution accounts
        $customers = Customer::whereHas('contributionAccounts', function($query) use ($branchId, $companyId) {
            $query->where('branch_id', $branchId)
                  ->where('company_id', $companyId);
        })
        ->where('branch_id', $branchId)
        ->where('company_id', $companyId)
        ->orderBy('name')
        ->get();

        // Get all active products
        $products = ContributionProduct::where('is_active', true)
            ->where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->orderBy('product_name')
            ->get();

        return view('contributions.transfers.create', compact('customers', 'products'));
    }

    public function transfersStore(Request $request)
    {
        $request->validate([
            'source_customer_id' => 'required|exists:customers,id',
            'source_product_id' => 'required|exists:contribution_products,id',
            'destination_customer_id' => 'required|exists:customers,id',
            'destination_product_id' => 'required|exists:contribution_products,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Validate that source and destination are different
        if ($request->source_customer_id == $request->destination_customer_id && 
            $request->source_product_id == $request->destination_product_id) {
            return back()->withErrors(['error' => 'Source and destination cannot be the same.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Get source product and account
            $sourceProduct = ContributionProduct::findOrFail($request->source_product_id);
            $sourceAccount = ContributionAccount::where('customer_id', $request->source_customer_id)
                ->where('contribution_product_id', $request->source_product_id)
                ->where('branch_id', $branchId)
                ->where('company_id', $companyId)
                ->firstOrFail();

            // Check if source account is active (not blocked)
            if (($sourceAccount->status ?? 'active') === 'inactive') {
                return back()->withErrors(['source_customer_id' => 'Source contribution account is blocked. Transfers are not allowed.'])->withInput();
            }

            // Check if source account has sufficient balance
            if ($sourceAccount->balance < $request->amount) {
                return back()->withErrors(['amount' => 'Insufficient balance in source account. Available balance: ' . number_format($sourceAccount->balance, 2)])->withInput();
            }

            // Get destination product and account (create if doesn't exist for same customer different product)
            $destinationProduct = ContributionProduct::findOrFail($request->destination_product_id);
            $destinationAccount = ContributionAccount::where('customer_id', $request->destination_customer_id)
                ->where('contribution_product_id', $request->destination_product_id)
                ->where('branch_id', $branchId)
                ->where('company_id', $companyId)
                ->first();

            // Create destination account if it doesn't exist
            if (!$destinationAccount) {
                // Generate account number
                $accountNumber = $this->generateContributionAccountNumber();
                
                $destinationAccount = ContributionAccount::create([
                    'customer_id' => $request->destination_customer_id,
                    'contribution_product_id' => $request->destination_product_id,
                    'account_number' => $accountNumber,
                    'opening_date' => $request->date,
                    'balance' => 0,
                    'status' => 'active',
                    'branch_id' => $branchId,
                    'company_id' => $companyId,
                    'created_by' => $user->id,
                ]);
            }

            // Validate liability accounts
            if (!$sourceProduct->liability_account_id) {
                return back()->withErrors(['source_product_id' => 'Source product does not have a liability account configured.'])->withInput();
            }
            if (!$destinationProduct->liability_account_id) {
                return back()->withErrors(['destination_product_id' => 'Destination product does not have a liability account configured.'])->withInput();
            }

            $glDescription = $request->description ?: "Contribution transfer from {$sourceProduct->product_name} to {$destinationProduct->product_name}";

            // Generate journal reference
            $nextId = Journal::max('id') + 1;
            $reference = 'CT-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

            // Create Journal entry
            $journal = Journal::create([
                'date' => $request->date,
                'reference' => $reference,
                'reference_type' => 'Contribution Transfer',
                'customer_id' => $request->source_customer_id, // Primary customer (source)
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Create Journal Items and GL Transactions
            // 1. Debit source liability account (transfer out)
            JournalItem::create([
                'journal_id' => $journal->id,
                'chart_account_id' => $sourceProduct->liability_account_id,
                'amount' => $request->amount,
                'nature' => 'debit',
                'description' => "Transfer out from {$sourceProduct->product_name}",
            ]);

            GlTransaction::create([
                'chart_account_id' => $sourceProduct->liability_account_id,
                'customer_id' => $request->source_customer_id,
                'amount' => $request->amount,
                'nature' => 'debit',
                'transaction_id' => $journal->id,
                'transaction_type' => 'contribution_transfer',
                'date' => $request->date,
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // 2. Credit destination liability account (transfer in)
            JournalItem::create([
                'journal_id' => $journal->id,
                'chart_account_id' => $destinationProduct->liability_account_id,
                'amount' => $request->amount,
                'nature' => 'credit',
                'description' => "Transfer in to {$destinationProduct->product_name}",
            ]);

            GlTransaction::create([
                'chart_account_id' => $destinationProduct->liability_account_id,
                'customer_id' => $request->destination_customer_id,
                'amount' => $request->amount,
                'nature' => 'credit',
                'transaction_id' => $journal->id,
                'transaction_type' => 'contribution_transfer',
                'date' => $request->date,
                'description' => $glDescription,
                'branch_id' => $branchId,
                'user_id' => $user->id,
            ]);

            // Update account balances
            $sourceAccount->decrement('balance', $request->amount);
            $destinationAccount->increment('balance', $request->amount);

            DB::commit();

            return redirect()->route('contributions.transfers.index')
                ->with('success', "Transfer of " . number_format($request->amount, 2) . " successfully completed.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to process transfer: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Ajax endpoint for Transfers DataTable
     */
    public function getTransfersData(Request $request)
    {
        if ($request->ajax()) {
            $user = auth()->user();
            $branchId = $user->branch_id;
            $companyId = $user->company_id;

            // Get all liability account IDs from contribution products
            $liabilityAccountIds = ContributionProduct::where('branch_id', $branchId)
                ->where('company_id', $companyId)
                ->whereNotNull('liability_account_id')
                ->pluck('liability_account_id')
                ->unique()
                ->toArray();

            if (empty($liabilityAccountIds)) {
                return DataTables::of(collect([]))->make(true);
            }

            // Get transfer transactions - debit transactions to liability accounts (source side)
            $transactions = GlTransaction::where('transaction_type', 'contribution_transfer')
                ->where('branch_id', $branchId)
                ->where('nature', 'debit')
                ->whereIn('chart_account_id', $liabilityAccountIds)
                ->with(['customer', 'chartAccount'])
                ->select('gl_transactions.*');

            // Get transaction IDs first (need to execute query to get IDs)
            $transactionIds = GlTransaction::where('transaction_type', 'contribution_transfer')
                ->where('branch_id', $branchId)
                ->where('nature', 'debit')
                ->whereIn('chart_account_id', $liabilityAccountIds)
                ->pluck('transaction_id')
                ->unique()
                ->toArray();

            if (empty($transactionIds)) {
                return DataTables::of(collect([]))->make(true);
            }

            // Pre-load all journals
            $journals = Journal::whereIn('id', $transactionIds)
                ->pluck('reference', 'id')
                ->toArray();
            
            // Get all destination transactions (credits to liability accounts) for these journals
            $destinationTransactions = GlTransaction::where('transaction_type', 'contribution_transfer')
                ->where('nature', 'credit')
                ->whereIn('transaction_id', $transactionIds)
                ->whereIn('chart_account_id', $liabilityAccountIds)
                ->with('customer')
                ->get()
                ->keyBy('transaction_id');

            // Pre-load products by liability account ID
            $productsByLiabilityAccount = ContributionProduct::whereIn('liability_account_id', $liabilityAccountIds)
                ->where('branch_id', $branchId)
                ->where('company_id', $companyId)
                ->pluck('product_name', 'liability_account_id')
                ->toArray();

            return DataTables::eloquent($transactions)
                ->addColumn('trx_id', function ($transaction) use ($journals) {
                    return $journals[$transaction->transaction_id] ?? 'CT-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT);
                })
                ->addColumn('date_formatted', function ($transaction) {
                    return $transaction->date->format('M d, Y');
                })
                ->addColumn('source_customer', function ($transaction) {
                    return $transaction->customer ? $transaction->customer->name : 'N/A';
                })
                ->addColumn('source_product', function ($transaction) use ($productsByLiabilityAccount) {
                    return $productsByLiabilityAccount[$transaction->chart_account_id] ?? 'N/A';
                })
                ->addColumn('destination_customer', function ($transaction) use ($destinationTransactions) {
                    $destinationTx = $destinationTransactions->get($transaction->transaction_id);
                    return $destinationTx && $destinationTx->customer ? $destinationTx->customer->name : 'N/A';
                })
                ->addColumn('destination_product', function ($transaction) use ($destinationTransactions, $productsByLiabilityAccount) {
                    $destinationTx = $destinationTransactions->get($transaction->transaction_id);
                    if ($destinationTx) {
                        return $productsByLiabilityAccount[$destinationTx->chart_account_id] ?? 'N/A';
                    }
                    return 'N/A';
                })
                ->addColumn('amount_formatted', function ($transaction) {
                    return number_format($transaction->amount, 2);
                })
                ->addColumn('description_text', function ($transaction) {
                    return $transaction->description ?: 'Contribution Transfer';
                })
                ->orderColumn('date', 'date DESC')
                ->rawColumns(['trx_id', 'date_formatted', 'source_customer', 'source_product', 'destination_customer', 'destination_product', 'amount_formatted', 'description_text'])
                ->make(true);
        }

        return response()->json(['error' => 'Invalid request'], 400);
    }

    /**
     * Generate unique contribution account number
     */
    private function generateContributionAccountNumber()
    {
        do {
            $accountNumber = strtoupper(substr(md5(uniqid(rand(), true)), 0, 16));
        } while (ContributionAccount::where('account_number', $accountNumber)->exists());

        return $accountNumber;
    }

    public function pendingTransfers()
    {
        return view('contributions.pending_transfers');
    }

    public function balanceReport()
    {
        return view('contributions.reports.balance');
    }

    public function transactionsReport(Request $request)
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get all contribution products for filter
        $products = ContributionProduct::where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();

        // Get filter values
        $productId = $request->get('product_id');
        $startDate = $request->get('start_date', date('Y-m-01')); // First day of current month
        $endDate = $request->get('end_date', date('Y-m-d')); // Today

        // Build query for contribution transactions
        // Get deposits, withdrawals, transfers, and journal entries from GL transactions
        $query = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_transfer', 'journal'])
            ->where('branch_id', $branchId)
            ->with(['customer', 'chartAccount']);

        // Filter by product if selected
        if ($productId) {
            $product = ContributionProduct::find($productId);
            if ($product && $product->liability_account_id) {
                $query->where('chart_account_id', $product->liability_account_id);
            }
        }

        // Filter by date range
        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        // Get all transactions for opening balance calculation
        $allTransactions = clone $query;
        $allTransactions = $allTransactions->orderBy('date')->orderBy('id')->get();

        // Calculate opening balance (sum of deposits/credits minus withdrawals/debits before start date, including journals)
        $openingBalance = 0;
        if ($startDate) {
            $openingDeposits = GlTransaction::whereIn('transaction_type', ['contribution_deposit', 'journal'])
                ->where('nature', 'credit')
                ->where('branch_id', $branchId)
                ->whereDate('date', '<', $startDate);

            $openingWithdrawals = GlTransaction::whereIn('transaction_type', ['contribution_withdrawal', 'contribution_transfer', 'journal'])
                ->where('nature', 'debit')
                ->where('branch_id', $branchId)
                ->whereDate('date', '<', $startDate);

            if ($productId && $product && $product->liability_account_id) {
                $openingDeposits->where('chart_account_id', $product->liability_account_id);
                $openingWithdrawals->where('chart_account_id', $product->liability_account_id);
            }

            $totalDeposits = $openingDeposits->sum('amount');
            $totalWithdrawals = $openingWithdrawals->sum('amount');
            $openingBalance = $totalDeposits - $totalWithdrawals;
        }

        // Get transactions for display (default: 20 newest, or all if filtered)
        $limit = $request->has('product_id') || $request->has('start_date') || $request->has('end_date') ? null : 20;
        
        if ($limit) {
            $transactions = $query->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->limit($limit)
                ->get()
                ->reverse(); // Reverse to show oldest first
        } else {
            $transactions = $query->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        }

        // Calculate running balance and get product name for each transaction
        $runningBalance = $openingBalance;
        $transactionsData = [];

        // Add opening balance row
        $transactionsData[] = [
            'trx_id' => 'OB',
            'date' => $startDate ?: $transactions->first()?->date?->format('Y-m-d'),
            'description' => 'Opening Balance',
            'product_name' => $productId ? ($products->find($productId)?->product_name ?? 'All Products') : 'All Products',
            'credit' => 0,
            'debit' => 0,
            'balance' => $openingBalance,
        ];

        // Process transactions
        foreach ($transactions as $transaction) {
            // Get product name from liability account
            $product = ContributionProduct::where('liability_account_id', $transaction->chart_account_id)
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->first();

            // Determine transaction type and amounts
            $isDeposit = $transaction->transaction_type === 'contribution_deposit' || 
                        ($transaction->transaction_type === 'journal' && $transaction->nature === 'credit');
            $isWithdrawal = $transaction->transaction_type === 'contribution_withdrawal' || 
                           ($transaction->transaction_type === 'journal' && $transaction->nature === 'debit') ||
                           $transaction->transaction_type === 'contribution_transfer';
            
            $credit = $transaction->nature === 'credit' ? $transaction->amount : 0;
            $debit = $transaction->nature === 'debit' ? $transaction->amount : 0;
            
            $runningBalance += $credit - $debit;

            // Generate transaction ID prefix and description
            if ($transaction->transaction_type === 'journal') {
                $journal = Journal::find($transaction->transaction_id);
                $trxPrefix = $journal ? $journal->reference : 'JRN';
                $trxDescription = $transaction->description ?: ($journal ? $journal->description : 'Journal Entry');
            } elseif ($transaction->transaction_type === 'contribution_transfer') {
                $journal = Journal::find($transaction->transaction_id);
                $trxPrefix = $journal ? $journal->reference : 'CT';
                $trxDescription = $transaction->description ?: 'Contribution Transfer';
            } else {
                $trxPrefix = $isDeposit ? 'CD' : 'CW';
                $trxDescription = $transaction->description ?: ($isDeposit ? 'Contribution Deposit' : 'Contribution Withdrawal');
            }

            $transactionsData[] = [
                'trx_id' => ($transaction->transaction_type === 'journal' || $transaction->transaction_type === 'contribution_transfer') 
                    ? $trxPrefix 
                    : $trxPrefix . '-' . str_pad($transaction->transaction_id, 6, '0', STR_PAD_LEFT),
                'date' => $transaction->date->format('Y-m-d'),
                'description' => $trxDescription,
                'product_name' => $product ? $product->product_name : 'N/A',
                'credit' => $credit,
                'debit' => $debit,
                'balance' => $runningBalance,
            ];
        }

        return view('contributions.reports.transactions', compact(
            'transactionsData',
            'products',
            'productId',
            'startDate',
            'endDate',
            'openingBalance'
        ));
    }

    /**
     * Show the form for editing the specified contribution product
     */
    public function productsEdit($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            abort(404, 'Contribution product not found.');
        }

        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        $product = ContributionProduct::where('id', $decoded[0])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        $chartAccounts = ChartAccount::all();
        $bankAccounts = BankAccount::all();
        $journalReferences = JournalReference::where('company_id', $companyId)
            ->where(function($query) use ($branchId) {
                $query->where('branch_id', $branchId)
                      ->orWhereNull('branch_id');
            })
            ->where('is_active', true)
            ->get();

        return view('contributions.products.edit', compact('product', 'chartAccounts', 'bankAccounts', 'journalReferences'));
    }

    /**
     * Update the specified contribution product
     */
    public function productsUpdate(Request $request, $encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            abort(404, 'Contribution product not found.');
        }

        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        $product = ContributionProduct::where('id', $decoded[0])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        try {
            // Normalize checkbox values before validation
            $request->merge([
                'can_withdraw' => $request->has('can_withdraw') ? 1 : 0,
                'has_charge' => $request->has('has_charge') ? 1 : 0,
            ]);

            $validated = $request->validate([
                'product_name' => 'required|string|max:255|unique:contribution_products,product_name,' . $product->id,
                'interest' => 'required|numeric|min:0|max:100',
                'category' => 'required|in:Voluntary,Mandatory',
                'auto_create' => 'required|in:Yes,No',
                'compound_period' => 'required|in:Daily,Monthly',
                'interest_posting_period' => 'nullable|in:Monthly,Quarterly,Annually',
                'interest_calculation_type' => 'required|in:Daily,Monthly,Annually',
                'lockin_period_frequency' => 'required|integer|min:0',
                'lockin_period_frequency_type' => 'required|in:Days,Months',
                'automatic_opening_balance' => 'required|numeric|min:0',
                'minimum_balance_for_interest_calculations' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'can_withdraw' => 'required|boolean',
                'has_charge' => 'required|boolean',
                'charge_id' => 'nullable|exists:fees,id',
                'charge_type' => 'nullable|required_if:has_charge,1|in:Fixed,Percentage',
                'charge_amount' => 'nullable|required_if:has_charge,1|numeric|min:0',
                'bank_account_id' => 'required|exists:chart_accounts,id',
                'journal_reference_id' => 'required|exists:journal_references,id',
                'riba_journal_id' => 'required|exists:journal_references,id',
                'pay_loan_journal_id' => 'required|exists:journal_references,id',
                'liability_account_id' => 'required|exists:chart_accounts,id',
                'expense_account_id' => 'required|exists:chart_accounts,id',
                'riba_payable_account_id' => 'required|exists:chart_accounts,id',
                'withholding_account_id' => 'required|exists:chart_accounts,id',
                'withholding_percentage' => 'nullable|numeric|min:0|max:100',
                'riba_payable_journal_id' => 'required|exists:journal_references,id',
            ]);

            // Ensure boolean values are properly cast
            $validated['can_withdraw'] = (bool) $validated['can_withdraw'];
            $validated['has_charge'] = (bool) $validated['has_charge'];

            $product->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contribution product updated successfully!'
                ]);
            }

            return redirect()->route('contributions.products.index')
                ->with('success', 'Contribution product updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    /**
     * Remove the specified contribution product
     */
    public function productsDestroy($encodedId)
    {
        $decoded = Hashids::decode($encodedId);
        if (empty($decoded)) {
            abort(404, 'Contribution product not found.');
        }

        $user = auth()->user();
        $companyId = $user->company_id;
        $branchId = $user->branch_id;

        $product = ContributionProduct::where('id', $decoded[0])
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->firstOrFail();

        try {
            $product->delete();

            return redirect()->route('contributions.products.index')
                ->with('success', 'Contribution product deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('contributions.products.index')
                ->with('error', 'Error deleting contribution product: ' . $e->getMessage());
        }
    }

    /**
     * Show opening balance import form
     */
    public function openingBalanceIndex()
    {
        $user = auth()->user();
        $branchId = $user->branch_id;
        $companyId = $user->company_id;

        // Get active contribution products
        $products = ContributionProduct::where('branch_id', $branchId)
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('product_name')
            ->get();

        // Get bank accounts
        $bankAccounts = BankAccount::with('chartAccount')
            ->orderBy('name')
            ->get();

        return view('contributions.opening-balance.index', compact('products', 'bankAccounts'));
    }

    /**
     * Download opening balance import template
     */
    public function downloadOpeningBalanceTemplate(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'contribution_product_id' => 'required|exists:contribution_products,id',
            'opening_balance_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $contributionProductId = $request->contribution_product_id;
            $openingDate = $request->opening_balance_date;
            $fileName = 'contribution_opening_balance_import_template_' . date('Y-m-d') . '.xlsx';

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\ContributionOpeningBalanceImportTemplateExport($contributionProductId, $openingDate),
                $fileName
            );
        } catch (\Exception $e) {
            \Log::error('Contribution Opening Balance Template Download Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Import contribution opening balances from Excel
     */
    public function importOpeningBalance(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'contribution_product_id' => 'required|exists:contribution_products,id',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'opening_balance_date' => 'required|date',
            'import_file' => 'required|file|mimes:xlsx,xls',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = auth()->user();
            $openingBalanceDate = $request->opening_balance_date;

            // Read Excel file
            $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $request->file('import_file'));
            $rows = $rows[0]; // Get first sheet

            // Get header row and create mapping
            $header = array_shift($rows);
            $header = array_map(function ($h) {
                return strtolower(trim((string) $h));
            }, $header);

            // Find column indices
            $customerNoIndex = array_search('customer_no', $header);
            $openingBalanceDateIndex = array_search('opening_balance_date', $header);
            $openingBalanceAmountIndex = array_search('opening_balance_amount', $header);
            $openingBalanceDescriptionIndex = array_search('opening_balance_description', $header);
            $transactionReferenceIndex = array_search('transaction_reference', $header);
            $notesIndex = array_search('notes', $header);

            if ($customerNoIndex === false || $openingBalanceAmountIndex === false) {
                return redirect()->back()
                    ->with('error', 'Excel file must contain customer_no and opening_balance_amount columns')
                    ->withInput();
            }

            // Prepare data for job
            $dataRows = [];
            foreach ($rows as $row) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $dataRows[] = [
                    'customer_no' => trim($row[$customerNoIndex] ?? ''),
                    'opening_balance_date' => isset($row[$openingBalanceDateIndex]) && !empty(trim($row[$openingBalanceDateIndex])) 
                        ? trim($row[$openingBalanceDateIndex]) 
                        : $openingBalanceDate,
                    'opening_balance_amount' => trim($row[$openingBalanceAmountIndex] ?? ''),
                    'opening_balance_description' => isset($row[$openingBalanceDescriptionIndex]) ? trim($row[$openingBalanceDescriptionIndex]) : '',
                    'transaction_reference' => isset($row[$transactionReferenceIndex]) ? trim($row[$transactionReferenceIndex]) : '',
                    'notes' => isset($row[$notesIndex]) ? trim($row[$notesIndex]) : '',
                ];
            }

            // Dispatch job for processing
            \App\Jobs\BulkContributionOpeningBalanceJob::dispatch(
                $dataRows,
                $request->contribution_product_id,
                $request->bank_account_id,
                $openingBalanceDate,
                $user->id
            );

            // Auto-start queue worker to process the job immediately
            try {
                \Illuminate\Support\Facades\Artisan::call('queue:work', [
                    '--once' => true,
                    '--timeout' => 300,
                    '--tries' => 1,
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to auto-start queue worker: ' . $e->getMessage());
            }

            return redirect()->back()
                ->with('success', 'Opening balance import has been queued and processing has started. Check logs for progress.');

        } catch (\Exception $e) {
            \Log::error('Contribution Opening Balance Import Error: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Failed to process import: ' . $e->getMessage())
                ->withInput();
        }
    }
}
