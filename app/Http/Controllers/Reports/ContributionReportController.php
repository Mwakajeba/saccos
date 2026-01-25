<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ContributionAccount;
use App\Models\ContributionProduct;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ContributionRegisterExport;
use App\Exports\ContributionMemberLedgerExport;
use Maatwebsite\Excel\Facades\Excel;

class ContributionReportController extends Controller
{
    /**
     * Contribution Register Report
     */
    public function contributionRegister(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $contributionProductId = $request->input('contribution_product_id');
        $status = $request->input('status');
        $asOfDate = $request->input('as_of_date', Carbon::today()->toDateString());
        $branchId = $request->input('branch_id');

        // Base query
        $accounts = ContributionAccount::with([
            'customer',
            'contributionProduct',
            'branch',
            'company'
        ]);

        // Apply filters
        if ($contributionProductId) {
            $accounts->where('contribution_product_id', $contributionProductId);
        }

        if ($status) {
            $accounts->where('status', $status);
        }

        if ($branchId && $branchId !== 'all') {
            $accounts->where('branch_id', $branchId);
        }

        // Filter by company
        if ($user->company_id) {
            $accounts->where('contribution_accounts.company_id', $user->company_id);
        }

        $accounts = $accounts->orderBy('account_number')
            ->get();

        // Get contribution products for filter dropdown
        $contributionProducts = ContributionProduct::where('is_active', true)
            ->orderBy('product_name')
            ->get();

        // Get branches for filter dropdown
        $branches = Branch::where('company_id', $user->company_id)
            ->distinct()
            ->orderBy('name')
            ->get();

        if ($request->input('export') === 'pdf') {
            $generatedAt = Carbon::now();
            $contributionProduct = $contributionProductId ? ContributionProduct::find($contributionProductId) : null;

            $pdf = Pdf::loadView('reports.contributions.contribution-register-pdf', compact(
                'accounts',
                'company',
                'generatedAt',
                'contributionProduct',
                'status',
                'asOfDate',
                'branchId'
            ))->setPaper('a4', 'landscape');

            $filename = 'contribution_register_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        }

        if ($request->input('export') === 'excel') {
            $contributionProduct = $contributionProductId ? ContributionProduct::find($contributionProductId) : null;
            $filename = 'contribution_register_' . date('Y-m-d_His') . '.xlsx';
            
            return Excel::download(
                new ContributionRegisterExport($accounts, $company, $contributionProduct, $status, $asOfDate),
                $filename
            );
        }

        return view('reports.contributions.contribution-register', compact(
            'accounts',
            'company',
            'contributionProducts',
            'branches',
            'contributionProductId',
            'status',
            'asOfDate',
            'branchId'
        ));
    }

    /**
     * Member Contribution Ledger Report
     */
    public function memberLedger(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $customerId = $request->input('customer_id');
        $accountId = $request->input('account_id');
        $fromDate = $request->input('from_date', Carbon::now()->subMonths(3)->toDateString());
        $toDate = $request->input('to_date', Carbon::today()->toDateString());

        $customer = null;
        $account = null;
        $transactions = collect();
        $openingBalance = 0;

        if ($accountId) {
            $account = ContributionAccount::with(['customer', 'contributionProduct', 'branch'])
                ->findOrFail($accountId);
            $customer = $account->customer;

            // Get opening balance from gl_transactions - only credit transactions increase balance
            $openingBalance = DB::table('gl_transactions')
                ->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_opening_balance'])
                ->where('customer_id', $account->customer_id)
                ->where(function($query) use ($account) {
                    $query->where('chart_account_id', $account->contributionProduct->liability_account_id)
                          ->orWhere(function($q) use ($account) {
                              $q->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_opening_balance'])
                                ->where('customer_id', $account->customer_id);
                          });
                })
                ->where('date', '<', $fromDate)
                ->sum(DB::raw('CASE WHEN nature = "credit" THEN amount WHEN nature = "debit" THEN -amount ELSE 0 END'));

            // Get transactions from gl_transactions - get all contribution transactions for this customer
            $transactions = DB::table('gl_transactions')
                ->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_opening_balance'])
                ->where('customer_id', $account->customer_id)
                ->where(function($query) use ($account) {
                    $query->where('chart_account_id', $account->contributionProduct->liability_account_id)
                          ->orWhere(function($q) use ($account) {
                              $q->whereIn('transaction_type', ['contribution_deposit', 'contribution_withdrawal', 'contribution_opening_balance'])
                                ->where('customer_id', $account->customer_id);
                          });
                })
                ->whereBetween('date', [$fromDate, $toDate])
                ->orderBy('date')
                ->orderBy('created_at')
                ->get()
                ->map(function ($transaction, $index) use ($openingBalance) {
                    static $runningBalance = null;
                    if ($runningBalance === null) {
                        $runningBalance = $openingBalance;
                    }

                    // Map gl_transaction nature and type to transaction_type
                    if (in_array($transaction->transaction_type, ['contribution_deposit', 'contribution_opening_balance'])) {
                        $transaction->transaction_type = 'deposit';
                        if ($transaction->nature === 'credit') {
                            $runningBalance += $transaction->amount;
                        }
                    } else {
                        $transaction->transaction_type = 'withdrawal';
                        if ($transaction->nature === 'debit') {
                            $runningBalance -= $transaction->amount;
                        }
                    }
                    
                    $transaction->transaction_date = \Carbon\Carbon::parse($transaction->date);
                    $transaction->running_balance = $runningBalance;
                    
                    // Get user who created the transaction
                    $transaction->created_by_user = (object)['name' => DB::table('users')->where('id', $transaction->user_id)->value('name') ?? 'System'];
                    
                    return $transaction;
                });
        }

        // Get customers for dropdown
        $customers = Customer::where('company_id', $user->company_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get accounts for selected customer
        $accounts = $customerId ? ContributionAccount::with('contributionProduct')
            ->where('customer_id', $customerId)
            ->get() : collect();

        if ($request->input('export') === 'pdf' && $account) {
            $generatedAt = Carbon::now();

            $pdf = Pdf::loadView('reports.contributions.member-ledger-pdf', compact(
                'account',
                'customer',
                'transactions',
                'openingBalance',
                'company',
                'generatedAt',
                'fromDate',
                'toDate'
            ))->setPaper('a4');

            $filename = 'contribution_ledger_' . $customer->customer_no . '_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        }

        if ($request->input('export') === 'excel' && $account) {
            $filename = 'contribution_ledger_' . $customer->customer_no . '_' . date('Y-m-d_His') . '.xlsx';
            
            return Excel::download(
                new ContributionMemberLedgerExport($account, $transactions, $company, $fromDate, $toDate),
                $filename
            );
        }

        return view('reports.contributions.member-ledger', compact(
            'customers',
            'accounts',
            'customer',
            'account',
            'transactions',
            'openingBalance',
            'company',
            'customerId',
            'accountId',
            'fromDate',
            'toDate'
        ));
    }
}
