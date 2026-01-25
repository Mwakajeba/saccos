<!DOCTYPE html>
<html>
<head>
    <title>Contribution Register Report</title>
</head>
<body>
    <table>
        <tr>
            <td colspan="11">Contribution Register Report</td>
        </tr>
        <tr>
            <td colspan="11">{{ $company->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td colspan="11">Generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="11">📊 DASHBOARD SUMMARY</td>
        </tr>
        <tr>
            <td colspan="2">Total Accounts</td>
            <td colspan="2">{{ $accounts->count() }}</td>
            <td colspan="2">Total Balance</td>
            <td colspan="2">{{ number_format($totalBalance, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">💰 Total Deposits</td>
            <td colspan="2">{{ number_format($totalDeposits, 2) }}</td>
            <td colspan="2">💸 Total Withdrawals</td>
            <td colspan="2">{{ number_format($totalWithdrawals, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2">🔄 Total Transfers</td>
            <td colspan="2">{{ number_format($totalTransfers ?? 0, 2) }}</td>
            <td colspan="2">📊 Net Position</td>
            <td colspan="2">{{ number_format($totalDeposits - $totalWithdrawals, 2) }}</td>
        </tr>
        @if($contributionProduct)
        <tr>
            <td colspan="2">Product Filter</td>
            <td colspan="2">{{ $contributionProduct->product_name }}</td>
            <td colspan="2">Status Filter</td>
            <td colspan="2">{{ $status ? ucfirst($status) : 'All' }}</td>
        </tr>
        @endif
        @if($asOfDate)
        <tr>
            <td colspan="2">As Of Date</td>
            <td colspan="2">{{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</td>
        </tr>
        @endif
        <tr></tr>
        <tr>
            <th>SN</th>
            <th>Account Number</th>
            <th>Member Name</th>
            <th>Member Number</th>
            <th>Product</th>
            <th>Branch</th>
            <th>Balance</th>
            <th>Total Deposits</th>
            <th>Total Withdrawals</th>
            <th>Opening Date</th>
            <th>Status</th>
        </tr>
        @foreach($accounts as $index => $account)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $account->account_number }}</td>
            <td>{{ $account->customer->name ?? 'N/A' }}</td>
            <td>{{ $account->customer->customerNo ?? 'N/A' }}</td>
            <td>{{ $account->contributionProduct->product_name ?? 'N/A' }}</td>
            <td>{{ $account->branch->name ?? 'N/A' }}</td>
            <td>{{ number_format($account->balance, 2) }}</td>
            <td>{{ number_format($account->total_deposits, 2) }}</td>
            <td>{{ number_format($account->total_withdrawals, 2) }}</td>
            <td>{{ $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A' }}</td>
            <td>{{ ucfirst($account->status) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="6">TOTAL</td>
            <td>{{ number_format($totalBalance, 2) }}</td>
            <td>{{ number_format($totalDeposits, 2) }}</td>
            <td>{{ number_format($totalWithdrawals, 2) }}</td>
            <td colspan="2"></td>
        </tr>
    </table>
</body>
</html>
