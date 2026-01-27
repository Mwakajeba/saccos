<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution Member Ledger</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 15px;
            color: #333;
            background: #fff;
        }
        
        .header {
            margin-bottom: 20px;
            border-bottom: 3px solid #28a745;
            padding-bottom: 15px;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        
        .logo-section {
            flex-shrink: 0;
        }
        
        .company-logo {
            max-height: 80px;
            max-width: 120px;
            object-fit: contain;
        }
        
        .title-section {
            text-align: center;
            flex-grow: 1;
        }
        
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .company-name {
            color: #333;
            margin: 5px 0;
            font-size: 16px;
            font-weight: 600;
        }
        
        .header .subtitle {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .account-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        
        .account-info h3 {
            margin: 0 0 10px 0;
            color: #28a745;
            font-size: 16px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 15px 5px 0;
            width: 120px;
            color: #555;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            font-size: 10px;
        }
        
        .data-table thead {
            background: #28a745;
            color: white;
        }
        
        .data-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .data-table td {
            padding: 7px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .text-center {
            text-align: center;
        }
        
        .deposit-row {
            background: #e8f5e9;
        }
        
        .withdrawal-row {
            background: #ffebee;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #28a745;
        }
        
        .summary-box h4 {
            margin: 0 0 10px 0;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            @if($company && $company->logo)
                <div class="logo-section">
                    <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name }}" class="company-logo">
                </div>
            @endif
            <div class="title-section">
                <h1>Contribution Member Ledger</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="account-info">
        <h3>Account Information</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Account Number:</div>
                <div class="info-value">{{ $account->account_number }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Member Name:</div>
                <div class="info-value">{{ $account->customer->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Member Number:</div>
                <div class="info-value">{{ $account->customer->customerNo ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Product:</div>
                <div class="info-value">{{ $account->contributionProduct->product_name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($account->status) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Current Balance:</div>
                <div class="info-value"><strong>{{ number_format($account->balance, 2) }}</strong></div>
            </div>
            @if($startDate && $endDate)
            <div class="info-row">
                <div class="info-label">Date Range:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</div>
            </div>
            @endif
        </div>
    </div>

    @if($transactions->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">Date</th>
                    <th style="width: 15%;">Transaction ID</th>
                    <th style="width: 10%;">Type</th>
                    <th style="width: 27%;">Description</th>
                    <th style="width: 10%;" class="number">Deposits</th>
                    <th style="width: 10%;" class="number">Withdrawals</th>
                    <th style="width: 10%;" class="number">Balance</th>
                    <th style="width: 10%;">Posted By</th>
                </tr>
            </thead>
            <tbody>
                @php $runningBalance = 0; @endphp
                @foreach($transactions as $transaction)
                    @php
                        if($transaction->transaction_type === 'deposit') {
                            $runningBalance += $transaction->amount;
                        } else {
                            $runningBalance -= $transaction->amount;
                        }
                    @endphp
                    <tr class="{{ $transaction->transaction_type === 'deposit' ? 'deposit-row' : 'withdrawal-row' }}">
                        <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                        <td>{{ $transaction->transaction_id }}</td>
                        <td class="text-center">
                            <span style="padding: 2px 8px; border-radius: 3px; font-weight: bold; color: white; background-color: {{ $transaction->transaction_type === 'deposit' ? '#28a745' : '#dc3545' }};">
                                {{ strtoupper($transaction->transaction_type) }}
                            </span>
                        </td>
                        <td>{{ $transaction->description ?? 'N/A' }}</td>
                        <td class="number">{{ $transaction->transaction_type === 'deposit' ? number_format($transaction->amount, 2) : '-' }}</td>
                        <td class="number">{{ $transaction->transaction_type === 'withdrawal' ? number_format($transaction->amount, 2) : '-' }}</td>
                        <td class="number"><strong>{{ number_format($runningBalance, 2) }}</strong></td>
                        <td>{{ $transaction->created_by_user->name ?? 'System' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary-box">
            <h4>Transaction Summary</h4>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Total Deposits:</div>
                    <div class="info-value"><strong>{{ number_format($transactions->where('transaction_type', 'deposit')->sum('amount'), 2) }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Withdrawals:</div>
                    <div class="info-value"><strong>{{ number_format($transactions->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Net Movement:</div>
                    <div class="info-value"><strong>{{ number_format($transactions->where('transaction_type', 'deposit')->sum('amount') - $transactions->where('transaction_type', 'withdrawal')->sum('amount'), 2) }}</strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total Transactions:</div>
                    <div class="info-value"><strong>{{ $transactions->count() }}</strong></div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <h3>No Transactions Found</h3>
            <p>No transactions found for this account in the selected period.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>
