<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ContributionProduct;
use App\Models\ChartAccount;
use App\Models\BankAccount;
use App\Models\Fee;

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
        $user = auth()->user();
        $branchId = $user->branch_id;
        
        $products = ContributionProduct::where('branch_id', $branchId)
            ->latest()
            ->get();
            
        return view('contributions.products', compact('products'));
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
            'can_withdraw' => 'boolean',
            'has_charge' => 'boolean',
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

        $validated['can_withdraw'] = $request->has('can_withdraw') ? true : false;
        $validated['has_charge'] = $request->has('has_charge') ? true : false;
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
        return view('contributions.deposits');
    }

    public function withdrawals()
    {
        return view('contributions.withdrawals');
    }

    public function transfers()
    {
        return view('contributions.transfers');
    }

    public function pendingTransfers()
    {
        return view('contributions.pending_transfers');
    }

    public function balanceReport()
    {
        return view('contributions.reports.balance');
    }

    public function transactionsReport()
    {
        return view('contributions.reports.transactions');
    }
}
