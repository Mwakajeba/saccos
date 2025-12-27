<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Ledger Report</title>
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
            border-bottom: 3px solid #17a2b8;
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
            color: #17a2b8;
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
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #17a2b8;
        }
        
        .account-info h3 {
            margin: 0 0 15px 0;
            color: #17a2b8;
            font-size: 18px;
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
            width: 150px;
            color: #555;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #333;
        }
        
        .report-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            font-size: 10px;
        }
        
        .data-table thead {
            background: #17a2b8;
            color: white;
        }
        
        .data-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 2px solid #0d6efd;
        }
        
        .data-table td {
            padding: 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .data-table tbody tr.opening-balance {
            background: #e7f3ff;
            font-weight: bold;
        }
        
        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .text-success {
            color: #28a745;
        }
        
        .text-danger {
            color: #dc3545;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
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
                <h1>Member Ledger Report</h1>
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
                <div class="info-label">Certificate Number:</div>
                <div class="info-value">{{ $account->certificate_number ?? 'N/A' }}</div>
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
                <div class="info-label">Share Product:</div>
                <div class="info-value">{{ $account->shareProduct->share_name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nominal Value:</div>
                <div class="info-value">{{ number_format($account->nominal_value, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="report-info">
        <strong>Report Period:</strong> {{ \Carbon\Carbon::parse($dateFrom)->format('M d, Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}
    </div>

    @if($transactions->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Date</th>
                    <th style="width: 12%;">Type</th>
                    <th style="width: 12%;">Reference</th>
                    <th style="width: 30%;">Description</th>
                    <th style="width: 12%;" class="number">Shares</th>
                    <th style="width: 12%;" class="number">Balance</th>
                    <th style="width: 10%;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="opening-balance">
                    <td><strong>{{ \Carbon\Carbon::parse($dateFrom)->subDay()->format('Y-m-d') }}</strong></td>
                    <td><strong>Opening Balance</strong></td>
                    <td>-</td>
                    <td>-</td>
                    <td class="number"><strong>{{ number_format($openingBalance, 2) }}</strong></td>
                    <td class="number"><strong>{{ number_format($openingBalance, 2) }}</strong></td>
                    <td>-</td>
                </tr>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction['date']->format('Y-m-d') }}</td>
                    <td>{{ $transaction['type'] }}</td>
                    <td>{{ $transaction['reference'] }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td class="number {{ $transaction['shares'] < 0 ? 'text-danger' : 'text-success' }}">
                        {{ $transaction['shares'] >= 0 ? '+' : '' }}{{ number_format($transaction['shares'], 2) }}
                    </td>
                    <td class="number"><strong>{{ number_format($transaction['balance'], 2) }}</strong></td>
                    <td>{{ ucfirst($transaction['status']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 40px; color: #666;">
            <p>No transactions found for the selected period.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>

