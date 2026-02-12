<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NPL Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .header p {
            margin: 5px 0;
            font-size: 10px;
            color: #888;
        }
        .summary {
            margin-bottom: 20px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .summary-table .label {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #666;
            font-size: 9px;
        }
        .summary-table .value {
            font-weight: bold;
            font-size: 12px;
        }
        .summary-table .danger { color: #dc3545; background-color: #fff5f5; }
        .summary-table .warning { color: #856404; background-color: #fff3cd; }
        .summary-table .info { color: #0c5460; background-color: #d1ecf1; }
        
        table.loans-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.loans-table th {
            background-color: #343a40;
            color: white;
            padding: 8px 5px;
            font-size: 9px;
            text-align: left;
            border: 1px solid #333;
        }
        table.loans-table td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        table.loans-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        table.loans-table .text-end {
            text-align: right;
        }
        table.loans-table .text-center {
            text-align: center;
        }
        table.loans-table tfoot td {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .text-danger { color: #dc3545; }
        .text-warning { color: #856404; }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-primary { background-color: #007bff; color: white; }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #888;
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
        <h1>{{ $company->name ?? 'Company Name' }}</h1>
        <h2>NON-PERFORMING LOANS (NPL) REPORT</h2>
        <p>Classification: {{ $nplClassification->status }} ({{ $nplClassification->bucket_label }} days) | Provision Rate: {{ $nplClassification->provision_percentage }}%</p>
        <p>Generated on: {{ $exportDate }}</p>
    </div>

    <div class="summary">
        <table class="summary-table">
            <tr>
                <td class="label">Total NPL Loans</td>
                <td class="label">Total Outstanding Balance</td>
                <td class="label">Total Provision Amount</td>
                <td class="label">Provision Rate</td>
            </tr>
            <tr>
                <td class="value danger">{{ $loans->count() }} Loans</td>
                <td class="value danger">TSHS {{ number_format($totalOutstanding, 2) }}</td>
                <td class="value warning">TSHS {{ number_format($totalProvision, 2) }}</td>
                <td class="value info">{{ $nplClassification->provision_percentage }}%</td>
            </tr>
        </table>
    </div>

    <table class="loans-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Loan No.</th>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Loan Amount</th>
                <th>Outstanding Balance</th>
                <th>Days in Arrears</th>
                <th>Provision %</th>
                <th>Provision Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loans as $index => $loan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $loan->loan_number ?? 'N/A' }}</td>
                <td>{{ $loan->customer->name ?? 'N/A' }}</td>
                <td>{{ $loan->product->name ?? 'N/A' }}</td>
                <td class="text-end">{{ number_format($loan->amount, 2) }}</td>
                <td class="text-end text-danger">{{ number_format($loan->outstanding_balance, 2) }}</td>
                <td class="text-center">{{ $loan->days_in_arrears }}</td>
                <td class="text-center">{{ number_format($loan->provision_percentage, 2) }}%</td>
                <td class="text-end text-warning">{{ number_format($loan->provision_amount, 2) }}</td>
                <td class="text-center">{{ ucfirst($loan->status) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-end"><strong>TOTALS:</strong></td>
                <td class="text-end text-danger"><strong>{{ number_format($totalOutstanding, 2) }}</strong></td>
                <td colspan="2"></td>
                <td class="text-end text-warning"><strong>{{ number_format($totalProvision, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>{{ $company->name ?? 'Company' }} | NPL Report | Page 1 | Generated: {{ $exportDate }}</p>
    </div>
</body>
</html>
