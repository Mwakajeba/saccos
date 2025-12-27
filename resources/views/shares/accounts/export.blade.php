<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Accounts Report</title>
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
        
        .report-info {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #17a2b8;
        }
        
        .report-info h3 {
            margin: 0 0 10px 0;
            color: #17a2b8;
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
        }
        
        .data-table thead {
            background: #17a2b8;
            color: white;
        }
        
        .data-table th {
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            word-wrap: break-word;
        }
        
        .data-table th:nth-child(1) { width: 5%; }
        .data-table th:nth-child(2) { width: 10%; }
        .data-table th:nth-child(3) { width: 15%; }
        .data-table th:nth-child(4) { width: 10%; }
        .data-table th:nth-child(5) { width: 12%; }
        .data-table th:nth-child(6) { width: 10%; }
        .data-table th:nth-child(7) { width: 10%; }
        .data-table th:nth-child(8) { width: 10%; }
        .data-table th:nth-child(9) { width: 8%; }
        
        .data-table td {
            padding: 8px 6px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
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
            border-top: 2px solid #17a2b8;
            padding: 10px 6px;
        }
        
        .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .text-success {
            color: #28a745;
            font-weight: 600;
        }
        
        .text-danger {
            color: #dc3545;
            font-weight: 600;
        }
        
        .text-warning {
            color: #ffc107;
            font-weight: 600;
        }
        
        .text-info {
            color: #17a2b8;
            font-weight: 600;
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
        
        .reference-info {
            font-size: 8px;
            color: #666;
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
                <h1>Share Accounts Report</h1>
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
            @if($shareProduct)
            <div class="info-row">
                <div class="info-label">Share Product:</div>
                <div class="info-value">{{ $shareProduct->share_name }}</div>
            </div>
            @endif
            @if($request->status)
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">{{ ucfirst($request->status) }}</div>
            </div>
            @endif
            @if($request->opening_date_from && $request->opening_date_to)
            <div class="info-row">
                <div class="info-label">Opening Date:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($request->opening_date_from)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($request->opening_date_to)->format('M d, Y') }}</div>
            </div>
            @elseif($request->opening_date_from)
            <div class="info-row">
                <div class="info-label">Opening Date From:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($request->opening_date_from)->format('M d, Y') }}</div>
            </div>
            @elseif($request->opening_date_to)
            <div class="info-row">
                <div class="info-label">Opening Date To:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($request->opening_date_to)->format('M d, Y') }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Total Records:</div>
                <div class="info-value">{{ $shareAccounts->count() }}</div>
            </div>
        </div>
    </div>

    @if($shareAccounts->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Account Number</th>
                    <th>Member Name</th>
                    <th>Member Number</th>
                    <th>Share Product</th>
                    <th class="number">Share Balance</th>
                    <th class="number">Nominal Value</th>
                    <th>Opening Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($shareAccounts as $index => $account)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $account->account_number }}</td>
                    <td>{{ $account->customer->name ?? 'N/A' }}</td>
                    <td>{{ $account->customer->customerNo ?? 'N/A' }}</td>
                    <td>{{ $account->shareProduct->share_name ?? 'N/A' }}</td>
                    <td class="number">{{ number_format($account->share_balance, 2) }}</td>
                    <td class="number">{{ number_format($account->nominal_value, 2) }}</td>
                    <td>{{ $account->opening_date ? $account->opening_date->format('Y-m-d') : 'N/A' }}</td>
                    <td>
                        @if($account->status === 'active')
                            <span class="text-success">Active</span>
                        @elseif($account->status === 'inactive')
                            <span class="text-warning">Inactive</span>
                        @elseif($account->status === 'closed')
                            <span class="text-danger">Closed</span>
                        @else
                            <span>{{ ucfirst($account->status) }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" style="text-align: right; font-weight: bold;">TOTAL:</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($shareAccounts->sum('share_balance'), 2) }}</td>
                    <td class="number" style="font-weight: bold;">{{ number_format($shareAccounts->sum('nominal_value'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            <h3>No Data Available</h3>
            <p>No share accounts found for the selected criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>This report was generated by Smart Accounting System</p>
        <p>Report ID: {{ strtoupper(uniqid()) }}</p>
    </div>
</body>
</html>

