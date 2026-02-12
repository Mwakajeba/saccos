<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Interest Accrued Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a237e;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
            color: #333;
        }
        .loan-info {
            margin-bottom: 15px;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }
        .loan-info-row {
            display: flex;
            margin-bottom: 3px;
        }
        .loan-info-label {
            font-weight: bold;
            width: 120px;
        }
        .loan-info-value {
            flex: 1;
        }
        .summary-cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .summary-card {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            background: #e3f2fd;
            border: 1px solid #90caf9;
        }
        .summary-card.success {
            background: #e8f5e9;
            border-color: #a5d6a7;
        }
        .summary-card.info {
            background: #e0f7fa;
            border-color: #80deea;
        }
        .summary-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background: #1a237e;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        td {
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        tfoot td {
            background: #e8e8e8;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($company && $company->logo)
            <img src="{{ public_path('storage/' . $company->logo) }}" alt="Logo" style="height: 50px; margin-bottom: 5px;">
        @endif
        <div class="company-name">{{ $company->name ?? 'Company Name' }}</div>
        <div class="report-title">Daily Interest Accrued Report</div>
    </div>

    <div class="loan-info">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 2px 5px;"><strong>Loan Number:</strong></td>
                <td style="border: none; padding: 2px 5px;">{{ $loan->loanNo ?? '#' . $loan->id }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Principal Amount:</strong></td>
                <td style="border: none; padding: 2px 5px;">TZS {{ number_format($loan->amount, 2) }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px 5px;"><strong>Customer:</strong></td>
                <td style="border: none; padding: 2px 5px;">{{ $loan->customer->name ?? 'N/A' }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Interest Rate:</strong></td>
                <td style="border: none; padding: 2px 5px;">{{ $loan->interest ?? ($loan->product->interest ?? 'N/A') }}% p.a.</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px 5px;"><strong>Product:</strong></td>
                <td style="border: none; padding: 2px 5px;">{{ $loan->product->name ?? 'N/A' }}</td>
                <td style="border: none; padding: 2px 5px;"><strong>Branch:</strong></td>
                <td style="border: none; padding: 2px 5px;">{{ $loan->branch->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="border: none; padding: 2px 5px;"><strong>Generated On:</strong></td>
                <td style="border: none; padding: 2px 5px;" colspan="3">{{ $exportDate }}</td>
            </tr>
        </table>
    </div>

    <div class="summary-cards">
        <div class="summary-card">
            <div class="summary-label">Total Accrual Days</div>
            <div class="summary-value">{{ $dailyInterestAccruals->count() }}</div>
        </div>
        <div class="summary-card success">
            <div class="summary-label">Total Interest Accrued</div>
            <div class="summary-value">TZS {{ number_format($totalInterest, 2) }}</div>
        </div>
        <div class="summary-card info">
            <div class="summary-label">Average Daily Interest</div>
            <div class="summary-value">TZS {{ $dailyInterestAccruals->count() > 0 ? number_format($totalInterest / $dailyInterestAccruals->count(), 2) : '0.00' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Accrual Date</th>
                <th style="width: 20%;" class="text-right">Principal Balance</th>
                <th style="width: 15%;" class="text-right">Interest Rate (Daily)</th>
                <th style="width: 20%;" class="text-right">Daily Interest</th>
                <th style="width: 15%;">Branch</th>
                <th style="width: 10%;">Created At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyInterestAccruals as $index => $accrual)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $accrual->accrual_date->format('d-m-Y') }}</td>
                    <td class="text-right">TZS {{ number_format($accrual->principal_balance, 2) }}</td>
                    <td class="text-right">{{ number_format($accrual->interest_rate * 100, 6) }}%</td>
                    <td class="text-right">TZS {{ number_format($accrual->daily_interest_amount, 2) }}</td>
                    <td>{{ $accrual->branch->name ?? 'N/A' }}</td>
                    <td>{{ $accrual->created_at->format('d-m-Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No daily interest accruals found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right"><strong>TOTAL INTEREST ACCRUED:</strong></td>
                <td class="text-right"><strong>TZS {{ number_format($totalInterest, 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated by {{ auth()->user()->name ?? 'System' }} on {{ $exportDate }}</p>
        <p>{{ $company->name ?? '' }} - {{ $company->address ?? '' }}</p>
    </div>
</body>
</html>
