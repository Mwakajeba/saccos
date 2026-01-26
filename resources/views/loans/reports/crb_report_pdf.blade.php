<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CRB Report - {{ $reportingDate }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 15px;
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
            color: #333;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #555;
            margin-top: 5px;
        }
        .report-info {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .summary-section {
            margin: 15px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        .summary-label {
            font-weight: bold;
            color: #555;
        }
        .summary-value {
            color: #000;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
        <div class="report-title">CREDIT REFERENCE BUREAU (CRB) REPORT</div>
        <div class="report-info">
            Reporting Date: {{ \Carbon\Carbon::parse($reportingDate)->format('F d, Y') }}
            @if($branch)
                | Branch: {{ $branch->name }}
            @endif
            @if($loanOfficer)
                | Loan Officer: {{ $loanOfficer->name }}
            @endif
        </div>
    </div>

    <div class="summary-section">
        <div class="summary-item">
            <span class="summary-label">Total Loans:</span>
            <span class="summary-value">{{ number_format($summary['total_loans']) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Loan Amount:</span>
            <span class="summary-value">TZS {{ number_format($summary['total_loan_amount'], 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Outstanding:</span>
            <span class="summary-value">TZS {{ number_format($summary['total_outstanding'], 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Past Due:</span>
            <span class="summary-value">TZS {{ number_format($summary['total_past_due'], 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Full Name</th>
                <th>Contract Code</th>
                <th>Customer Code</th>
                <th>Branch</th>
                <th>Status</th>
                <th>Type</th>
                <th>Purpose</th>
                <th>Rate</th>
                <th class="text-end">Total Loan</th>
                <th class="text-end">Loan Taken</th>
                <th class="text-end">Installment</th>
                <th class="text-center">No. Install</th>
                <th class="text-center">Outstanding</th>
                <th class="text-end">Outstanding Amt</th>
                <th class="text-end">Past Due</th>
                <th class="text-center">Days</th>
                <th class="text-center">Due Install</th>
                <th>Last Payment</th>
                <th class="text-end">Monthly Paid</th>
                <th>Periodicity</th>
                <th>Start</th>
                <th>End</th>
                <th>Real End</th>
                <th>Collateral</th>
                <th class="text-end">Coll. Value</th>
                <th>Role</th>
                <th>Currency</th>
            </tr>
        </thead>
        <tbody>
            @forelse($crbData as $data)
                <tr>
                    <td>{{ $data['reporting_date'] }}</td>
                    <td>{{ $data['fullname'] }}</td>
                    <td>{{ $data['contract_code'] }}</td>
                    <td>{{ $data['customer_code'] }}</td>
                    <td>{{ $data['branch'] }}</td>
                    <td>
                        <span class="badge badge-{{ $data['loan_status'] == 'Active' ? 'success' : ($data['loan_status'] == 'Defaulted' ? 'danger' : 'primary') }}">
                            {{ $data['loan_status'] }}
                        </span>
                    </td>
                    <td>{{ $data['type_of_contract'] }}</td>
                    <td>{{ $data['loan_purpose'] }}</td>
                    <td>{{ $data['interest_rate'] }}%</td>
                    <td class="text-end">{{ number_format($data['total_loan'], 2) }}</td>
                    <td class="text-end">{{ number_format($data['total_loan_taken'], 2) }}</td>
                    <td class="text-end">{{ number_format($data['installment_amount'], 2) }}</td>
                    <td class="text-center">{{ $data['number_of_installments'] }}</td>
                    <td class="text-center">{{ $data['number_of_outstanding_installments'] }}</td>
                    <td class="text-end">{{ number_format($data['outstanding_amount'], 2) }}</td>
                    <td class="text-end">{{ number_format($data['past_due_amount'], 2) }}</td>
                    <td class="text-center">
                        @if($data['past_due_days'] > 0)
                            <span class="badge badge-danger">{{ $data['past_due_days'] }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $data['number_of_due_installments'] }}</td>
                    <td>{{ $data['date_of_last_payment'] ? \Carbon\Carbon::parse($data['date_of_last_payment'])->format('d-m-Y') : 'N/A' }}</td>
                    <td class="text-end">{{ number_format($data['total_monthly_payment'], 2) }}</td>
                    <td>{{ $data['payment_periodicity'] }}</td>
                    <td>{{ $data['start_date'] ? \Carbon\Carbon::parse($data['start_date'])->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $data['end_date'] ? \Carbon\Carbon::parse($data['end_date'])->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $data['real_end_date'] ? \Carbon\Carbon::parse($data['real_end_date'])->format('d-m-Y') : 'N/A' }}</td>
                    <td>{{ $data['collateral_type'] }}</td>
                    <td class="text-end">{{ number_format($data['collateral_value'], 2) }}</td>
                    <td>{{ $data['role_of_customer'] }}</td>
                    <td>{{ $data['currency'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="28" class="text-center">No loan data available.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ \Carbon\Carbon::now()->format('F d, Y \a\t g:i A') }}</p>
        <p><strong>{{ $company->name ?? 'SmartFinance' }} - CRB Report</strong></p>
    </div>
</body>
</html>
