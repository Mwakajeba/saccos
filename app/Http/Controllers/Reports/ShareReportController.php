<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\ShareAccount;
use App\Models\ShareDeposit;
use App\Models\ShareWithdrawal;
use App\Models\ShareTransfer;
use App\Models\ShareProduct;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Vinkla\Hashids\Facades\Hashids;

class ShareReportController extends Controller
{
    /**
     * Share Register Report
     */
    public function shareRegister(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $shareProductId = $request->input('share_product_id');
        $status = $request->input('status');
        $asOfDate = $request->input('as_of_date', Carbon::today()->toDateString());
        $branchId = $request->input('branch_id');

        // Base query
        $accounts = ShareAccount::with([
            'customer',
            'shareProduct',
            'branch',
            'company'
        ]);

        // Apply filters
        if ($shareProductId) {
            $accounts->where('share_product_id', $shareProductId);
        }

        if ($status) {
            $accounts->where('status', $status);
        }

        if ($branchId && $branchId !== 'all') {
            $accounts->where('branch_id', $branchId);
        }

        // Filter by company and branch scope
        if ($user->company_id) {
            $accounts->where('share_accounts.company_id', $user->company_id);
        }

        $accounts = $accounts->orderBy('certificate_number')
            ->orderBy('account_number')
            ->get();

        // Get share products for filter dropdown
        $shareProducts = ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();

        // Get branches for filter dropdown (unique branches from database)
        $branches = Branch::where('company_id', $user->company_id)
            ->distinct()
            ->orderBy('name')
            ->get();

        if ($request->input('export') === 'pdf') {
            $generatedAt = Carbon::now();
            $shareProduct = $shareProductId ? ShareProduct::find($shareProductId) : null;

            $pdf = Pdf::loadView('reports.shares.share-register-pdf', compact(
                'accounts',
                'company',
                'generatedAt',
                'shareProduct',
                'status',
                'asOfDate',
                'branchId'
            ))->setPaper('a4', 'landscape');

            $filename = 'share_register_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        }

        return view('reports.shares.share-register', compact(
            'accounts',
            'company',
            'shareProducts',
            'branches',
            'shareProductId',
            'status',
            'asOfDate',
            'branchId'
        ));
    }

    /**
     * Member Ledger Report
     */
    public function memberLedger(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        $accountId = $request->input('account_id');
        $customerId = $request->input('customer_id');
        $shareProductId = $request->input('share_product_id');
        $dateFrom = $request->input('date_from', Carbon::now()->startOfYear()->toDateString());
        $dateTo = $request->input('date_to', Carbon::today()->toDateString());

        $account = null;
        $transactions = collect();

        if ($accountId) {
            $account = ShareAccount::with([
                'customer',
                'shareProduct',
                'branch',
                'company'
            ])->find(Hashids::decode($accountId)[0] ?? null);
        } elseif ($customerId && $shareProductId) {
            $account = ShareAccount::with([
                'customer',
                'shareProduct',
                'branch',
                'company'
            ])
            ->where('customer_id', $customerId)
            ->where('share_product_id', $shareProductId)
            ->first();
        }

        $openingBalance = 0;
        if ($account) {
            // Get opening balance (balance before date_from)
            $openingDeposits = ShareDeposit::where('share_account_id', $account->id)
                ->whereDate('deposit_date', '<', $dateFrom)
                ->where('status', 'approved')
                ->sum('number_of_shares');
            
            $openingWithdrawals = ShareWithdrawal::where('share_account_id', $account->id)
                ->whereDate('withdrawal_date', '<', $dateFrom)
                ->where('status', 'approved')
                ->sum('number_of_shares');
            
            $openingTransfersOut = ShareTransfer::where('from_account_id', $account->id)
                ->whereDate('transfer_date', '<', $dateFrom)
                ->where('status', 'approved')
                ->sum('number_of_shares');
            
            $openingTransfersIn = ShareTransfer::where('to_account_id', $account->id)
                ->whereDate('transfer_date', '<', $dateFrom)
                ->where('status', 'approved')
                ->sum('number_of_shares');

            $openingBalance = $openingDeposits - $openingWithdrawals - $openingTransfersOut + $openingTransfersIn;

            // Get all deposits
            $deposits = ShareDeposit::where('share_account_id', $account->id)
                ->whereDate('deposit_date', '>=', $dateFrom)
                ->whereDate('deposit_date', '<=', $dateTo)
                ->orderBy('deposit_date')
                ->orderBy('id')
                ->get()
                ->map(function ($deposit) {
                    return [
                        'date' => $deposit->deposit_date,
                        'type' => 'Deposit',
                        'reference' => $deposit->transaction_reference ?? 'DEP-' . $deposit->id,
                        'shares' => $deposit->number_of_shares,
                        'amount' => $deposit->deposit_amount,
                        'balance' => null, // Will calculate below
                        'status' => $deposit->status,
                        'description' => $deposit->notes ?? 'Share Deposit'
                    ];
                });

            // Get all withdrawals
            $withdrawals = ShareWithdrawal::where('share_account_id', $account->id)
                ->whereDate('withdrawal_date', '>=', $dateFrom)
                ->whereDate('withdrawal_date', '<=', $dateTo)
                ->orderBy('withdrawal_date')
                ->orderBy('id')
                ->get()
                ->map(function ($withdrawal) {
                    return [
                        'date' => $withdrawal->withdrawal_date,
                        'type' => 'Withdrawal',
                        'reference' => $withdrawal->transaction_reference ?? 'WD-' . $withdrawal->id,
                        'shares' => -$withdrawal->number_of_shares, // Negative for withdrawal
                        'amount' => -$withdrawal->withdrawal_amount, // Negative for withdrawal
                        'balance' => null, // Will calculate below
                        'status' => $withdrawal->status,
                        'description' => $withdrawal->notes ?? 'Share Withdrawal'
                    ];
                });

            // Get all transfers (both from and to)
            $transfersOut = ShareTransfer::with(['toAccount.customer'])
                ->where('from_account_id', $account->id)
                ->whereDate('transfer_date', '>=', $dateFrom)
                ->whereDate('transfer_date', '<=', $dateTo)
                ->orderBy('transfer_date')
                ->orderBy('id')
                ->get()
                ->map(function ($transfer) {
                    return [
                        'date' => $transfer->transfer_date,
                        'type' => 'Transfer Out',
                        'reference' => $transfer->transaction_reference ?? 'TRF-' . $transfer->id,
                        'shares' => -$transfer->number_of_shares, // Negative for transfer out
                        'amount' => -$transfer->transfer_amount, // Negative for transfer out
                        'balance' => null, // Will calculate below
                        'status' => $transfer->status,
                        'description' => ($transfer->toAccount->customer->name ?? 'N/A') . ' - ' . ($transfer->notes ?? 'Share Transfer')
                    ];
                });

            $transfersIn = ShareTransfer::with(['fromAccount.customer'])
                ->where('to_account_id', $account->id)
                ->whereDate('transfer_date', '>=', $dateFrom)
                ->whereDate('transfer_date', '<=', $dateTo)
                ->orderBy('transfer_date')
                ->orderBy('id')
                ->get()
                ->map(function ($transfer) {
                    return [
                        'date' => $transfer->transfer_date,
                        'type' => 'Transfer In',
                        'reference' => $transfer->transaction_reference ?? 'TRF-' . $transfer->id,
                        'shares' => $transfer->number_of_shares, // Positive for transfer in
                        'amount' => $transfer->transfer_amount, // Positive for transfer in
                        'balance' => null, // Will calculate below
                        'status' => $transfer->status,
                        'description' => ($transfer->fromAccount->customer->name ?? 'N/A') . ' - ' . ($transfer->notes ?? 'Share Transfer')
                    ];
                });

            // Merge all transactions and sort by date
            $transactions = $deposits->merge($withdrawals)
                ->merge($transfersOut)
                ->merge($transfersIn)
                ->sortBy('date')
                ->values();

            // Calculate running balance
            $runningBalance = $openingBalance;
            $transactions = $transactions->map(function ($transaction) use (&$runningBalance) {
                if ($transaction['status'] === 'approved') {
                    $runningBalance += $transaction['shares'];
                }
                $transaction['balance'] = $runningBalance;
                return $transaction;
            });
        }

        // Get customers and share products for filter dropdowns
        $customers = Customer::orderBy('name')->get();
        $shareProducts = ShareProduct::where('is_active', true)
            ->orderBy('share_name')
            ->get();

        // Get all share accounts for account_id dropdown
        $shareAccounts = ShareAccount::with(['customer', 'shareProduct'])
            ->when($user->company_id, function ($query) use ($user) {
                $query->where('company_id', $user->company_id);
            })
            ->orderBy('account_number')
            ->get()
            ->map(function ($acc) {
                return [
                    'id' => Hashids::encode($acc->id),
                    'label' => $acc->account_number . ' - ' . ($acc->customer->name ?? 'N/A') . ' - ' . ($acc->shareProduct->share_name ?? 'N/A')
                ];
            });

        if ($request->input('export') === 'pdf' && $account) {
            $generatedAt = Carbon::now();

            $pdf = Pdf::loadView('reports.shares.member-ledger-pdf', compact(
                'account',
                'transactions',
                'company',
                'generatedAt',
                'dateFrom',
                'dateTo',
                'openingBalance'
            ))->setPaper('a4', 'portrait');

            $filename = 'member_ledger_' . ($account->account_number ?? 'account') . '_' . date('Y-m-d_His') . '.pdf';
            return $pdf->download($filename);
        }

        return view('reports.shares.member-ledger', compact(
            'account',
            'transactions',
            'company',
            'customers',
            'shareProducts',
            'shareAccounts',
            'accountId',
            'customerId',
            'shareProductId',
            'dateFrom',
            'dateTo',
            'openingBalance'
        ));
    }
}
