<!DOCTYPE html>
<html>
<head>
    <title>Contribution Member Ledger</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="8">Contribution Member Ledger</td>
        </tr>
        <tr>
            <td colspan="8">{{ $company->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="8">Generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="8">📋 ACCOUNT INFORMATION</td>
        </tr>
        <tr>
            <td colspan="2">Account Number</td>
            <td colspan="2">{{ $account->account_number }}</td>
            <td colspan="2">Current Balance</td>
            <td colspan="2">{{ number_format($account->balance, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">Member Name</td>
            <td colspan="2">{{ $account->customer->name ?? 'N/A' }}</td>
            <td colspan="2">Member Number</td>
            <td colspan="2">{{ $account->customer->customerNo ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="2">Product</td>
            <td colspan="2">{{ $account->contributionProduct->product_name ?? 'N/A' }}</td>
            <td colspan="2">Status</td>
            <td colspan="2">{{ ucfirst($account->status) }}</td>
        </tr>
        <tr>
            <td colspan="2">Branch</td>
            <td colspan="2">{{ $account->branch->name ?? 'N/A' }}</td>
            <td colspan="2">Opening Date</td>
            <td colspan="2">{{ $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A' }}</td>
        </tr>
        @if($startDate && $endDate)
        <tr>
            <td colspan="2">Date Range</td>
            <td colspan="6">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</td>
        </tr>
        @endif
        <tr></tr>
        <tr>
            <th>Date</th>
            <th>Transaction ID</th>
            <th>Type</th>
            <th>Description</th>
            <th>Deposits</th>
            <th>Withdrawals</th>
            <th>Balance</th>
            <th>Posted By</th>
        </tr>
        @php $runningBalance = 0; @endphp
        @foreach($transactions as $transaction)
            @php
                if($transaction->transaction_type === 'deposit') {
                    $runningBalance += $transaction->amount;
                } else {
                    $runningBalance -= $transaction->amount;
                }
            @endphp
        <tr>
            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
            <td>{{ $transaction->transaction_id }}</td>
            <td>{{ strtoupper($transaction->transaction_type) }}</td>
            <td>{{ $transaction->description ?? 'N/A' }}</td>
            <td>{{ $transaction->transaction_type === 'deposit' ? number_format($transaction->amount, 2) : '-' }}</td>
            <td>{{ $transaction->transaction_type === 'withdrawal' ? number_format($transaction->amount, 2) : '-' }}</td>
            <td>{{ number_format($runningBalance, 2) }}</td>
            <td>{{ $transaction->created_by_user->name ?? 'System' }}</td>
        </tr>
        @endforeach
        <tr></tr>
        <tr>
            <td colspan="8">📊 TRANSACTION SUMMARY</td>
        </tr>
        <tr>
            <td colspan="2">💰 Total Deposits</td>
            <td colspan="2">{{ number_format($totalDeposits, 2) }}</td>
            <td colspan="2">💸 Total Withdrawals</td>
            <td colspan="2">{{ number_format($totalWithdrawals, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">🔄 Total Transfers</td>
            <td colspan="2">{{ number_format($transactions->where('transaction_type', 'transfer')->sum('amount'), 2) }}</td>
            <td colspan="2">📈 Net Movement</td>
            <td colspan="2">{{ number_format($totalDeposits - $totalWithdrawals, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">📋 Total Transactions</td>
            <td colspan="2">{{ $transactions->count() }}</td>
            <td colspan="2">📅 Period</td>
            <td colspan="2">{{ $transactions->count() }}</td>
        </tr>
    </table>
</body>
</html>
