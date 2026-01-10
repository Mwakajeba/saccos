<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Share Account Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 200px;
        }
        .info-value {
            flex: 1;
        }
        .summary-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            font-weight: bold;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 11px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .period-info {
            text-align: center;
            margin: 15px 0;
            font-size: 14px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->name ?? 'SmartFinance' }}</h1>
        <h2>Share Account Statement</h2>
        <div class="period-info">
            Period: {{ $startDate->format('d M, Y') }} to {{ $endDate->format('d M, Y') }}
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Account Number:</div>
            <div class="info-value">{{ $shareAccount->account_number }}</div>
        </div>
        @if($shareAccount->certificate_number)
        <div class="info-row">
            <div class="info-label">Certificate Number:</div>
            <div class="info-value">{{ $shareAccount->certificate_number }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Member Name:</div>
            <div class="info-value">{{ $shareAccount->customer->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Member Number:</div>
            <div class="info-value">{{ $shareAccount->customer->customerNo ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Share Product:</div>
            <div class="info-value">{{ $product->share_name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Share Balance:</div>
            <div class="info-value">{{ number_format($shareAccount->share_balance ?? 0, 4) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Nominal Value:</div>
            <div class="info-value">{{ number_format($shareAccount->nominal_value ?? 0, 2) }} TZS</div>
        </div>
        <div class="info-row">
            <div class="info-label">Opening Date:</div>
            <div class="info-value">{{ $shareAccount->opening_date->format('d M, Y') }}</div>
        </div>
        @if($branch)
        <div class="info-row">
            <div class="info-label">Branch:</div>
            <div class="info-value">{{ $branch->name ?? 'N/A' }}</div>
        </div>
        @endif
    </div>

    <div class="summary-section">
        <div class="summary-row">
            <div class="summary-label">Opening Balance (as of {{ $startDate->copy()->subDay()->format('d M, Y') }}):</div>
            <div class="summary-value text-right">{{ number_format($openingBalance, 2) }} TZS</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Closing Balance (as of {{ $endDate->format('d M, Y') }}):</div>
            <div class="summary-value text-right">{{ number_format($closingBalance, 2) }} TZS</div>
        </div>
        <div class="summary-row">
            <div class="summary-label">Net Movement:</div>
            <div class="summary-value text-right">{{ number_format($closingBalance - $openingBalance, 2) }} TZS</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Trx ID</th>
                <th>Date</th>
                <th>Description</th>
                <th>Type</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @if($transactions->count() > 0)
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction['trx_id'] }}</td>
                    <td>{{ $transaction['date'] }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td>{{ $transaction['type'] }}</td>
                    <td class="text-right">{{ $transaction['credit'] > 0 ? number_format($transaction['credit'], 2) : '-' }}</td>
                    <td class="text-right">{{ $transaction['debit'] > 0 ? number_format($transaction['debit'], 2) : '-' }}</td>
                    <td class="text-right">{{ number_format($transaction['balance'], 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center">No transactions found for the selected period.</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on: {{ now()->format('d M, Y H:i:s') }}</p>
        <p>{{ $company->name ?? 'SmartFinance' }} - Share Account Statement</p>
    </div>
</body>
</html>

