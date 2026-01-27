<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contribution Register Report</title>
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
        
        .report-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        
        .report-info h3 {
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
            table-layout: fixed;
            font-size: 9px;
        }
        
        .data-table thead {
            background: #28a745;
            color: white;
        }
        
        .data-table th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }
        
        .data-table td {
            padding: 6px 4px;
            border-bottom: 1px solid #dee2e6;
            font-size: 8px;
            word-wrap: break-word;
        }
        
        .data-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .data-table tfoot {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .data-table tfoot td {
            border-top: 2px solid #28a745;
            padding: 8px 4px;
        }
        
        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
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
                <h1>Contribution Register Report</h1>
                @if($company)
                    <div class="company-name">{{ $company->name }}</div>
                @endif
                <div class="subtitle">Generated on {{ $generatedAt->format('F d, Y \a\t g:i A') }}</div>
            </div>
        </div>
    </div>

    <div class="report-info">
        <h3>Report Parameters</h3>
        <div class="info-grid">
            @if($contributionProduct)
            <div class="info-row">
                <div class="info-label">Contribution Product:</div>
                <div class="info-value">{{ $contributionProduct->product_name }}</div>
            </div>
            @endif
            @if($status)
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($status) }}</div>
            </div>
            @endif
            @if($asOfDate)
            <div class="info-row">
                <div class="info-label">As Of Date:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($asOfDate)->format('M d, Y') }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Total Records:</div>
                <div class="info-value">{{ $accounts->count() }}</div>
            </div>
        </div>
    </div>

    @if($accounts->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">SN</th>
                    <th style="width: 9%;">Account #</th>
                    <th style="width: 15%;">Member Name</th>
                    <th style="width: 9%;">Member #</th>
                    <th style="width: 12%;">Product</th>
                    <th style="width: 10%;">Branch</th>
                    <th style="width: 10%;" class="number">Balance</th>
                    <th style="width: 10%;" class="number">Total Deposits</th>
                    <th style="width: 10%;" class="number">Total Withdrawals</th>
                    <th style="width: 7%;">Open Date</th>
                    <th style="width: 5%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($accounts as $index => $account)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $account->account_number }}</td>
                    <td>{{ $account->customer->name ?? 'N/A' }}</td>
                    <td>{{ $account->customer->customerNo ?? 'N/A' }}</td>
                    <td>{{ $account->contributionProduct->product_name ?? 'N/A' }}</td>
                    <td>{{ $account->branch->name ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($account->balance, 2) }}</td>
                    <td class="number">{{ number_format($account->total_deposits, 2) }}</td>
                    <td class="number">{{ number_format($account->total_withdrawals, 2) }}</td>
                    <td>{{ $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ ucfirst($account->status) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($accounts->sum('balance'), 2) }}</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($accounts->sum('total_deposits'), 2) }}</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($accounts->sum('total_withdrawals'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No contribution accounts found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>
