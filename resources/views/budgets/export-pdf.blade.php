<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Budget Export - {{ $budget->name }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
        }

        .container {
            width: 100%;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        hr {
            border: none;
            border-top: 2px solid #3b82f6;
            margin: 8px 0;
        }

        /* Header */
        .logo-section {
            margin-bottom: 10px;
        }

        .company-logo {
            max-height: 80px;
            max-width: 120px;
            object-fit: contain;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #1e40af;
        }

        .company-details {
            font-size: 10px;
        }

        /* Document title */
        .invoice-title {
            font-weight: bold;
            text-align: center;
            font-size: 18px;
            margin: 10px 0;
            color: #1e40af;
        }

        /* Info section */
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .bill-to {
            width: 48%;
            font-size: 10px;
        }

        .bill-to strong {
            color: #1e40af;
        }

        .invoice-box {
            width: 48%;
            text-align: right;
        }

        .invoice-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-left: auto;
        }

        .invoice-box td {
            border: 1px solid #cbd5e1;
            padding: 4px;
        }

        .invoice-box td:nth-child(odd) {
            font-weight: bold;
            color: #1e40af;
        }

        .invoice-box td:nth-child(even) {
            text-align: right;
        }

        .invoice-box strong {
            color: #1e40af;
        }

        /* Budget lines table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #cbd5e1;
            padding: 5px;
        }

        .items-table th {
            text-align: center;
            font-weight: bold;
            background-color: #1e3a8a;
            color: #fff;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #dbeafe;
        }

        .items-table tbody tr:nth-child(odd) {
            background-color: #fff;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        .category-revenue { color: #15803d; font-weight: 500; }
        .category-expense { color: #b91c1c; font-weight: 500; }
        .category-capital { color: #a16207; font-weight: 500; }

        /* Totals */
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }

        .totals-table td {
            padding: 4px 5px;
            border: none;
        }

        .totals-table td:last-child {
            text-align: right;
            padding-right: 5px;
        }

        .totals-table tr:last-child td {
            background-color: #1e3a8a;
            color: #fff;
            font-weight: bold;
            padding: 8px 5px;
        }

        .totals-table tr:last-child td:last-child {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 8px;
            border-radius: 3px;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            font-size: 10px;
        }

        .footer strong {
            color: #1e40af;
        }

        .signature {
            margin-top: 20px;
        }

        .footer hr {
            border-top: 1px solid #dbeafe;
            margin: 15px 0;
        }

        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .signature-box {
            text-align: center;
            width: 30%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 2px;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header: Logo + Company --}}
        <div class="text-left">
            @php
                $company = $budget->company ?? ($budget->branch->company ?? null);
            @endphp
            @if($company && $company->logo)
                @php
                    $logo = $company->logo;
                    $logoPath = public_path('storage/' . ltrim($logo, '/'));
                    $logoBase64 = null;
                    if (file_exists($logoPath)) {
                        $imageData = file_get_contents($logoPath);
                        $imageInfo = @getimagesize($logoPath);
                        if ($imageInfo !== false) {
                            $mimeType = $imageInfo['mime'];
                            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        }
                    }
                @endphp
                @if($logoBase64 ?? null)
                    <div class="logo-section" style="float: left; width: 45%;">
                        <img src="{{ $logoBase64 }}" alt="{{ ($company->name ?? '') . ' logo' }}" class="company-logo">
                    </div>
                @endif
            @endif
            <div style="float: right; width: 50%; text-align: left; margin-left: 15%;">
                <div class="company-name">{{ $company->name ?? 'SmartFinance' }}</div>
                <div class="company-details">
                    @if($company && $company->address)
                        P.O Box: {{ $company->address }} <br>
                    @endif
                    @if($company && $company->phone)
                        Phone: {{ $company->phone }} <br>
                    @endif
                    @if($company && $company->email)
                        Email: {{ $company->email }}
                    @endif
                </div>
            </div>
        </div>
        <div style="clear: both;"></div>

        <div class="invoice-title">BUDGET EXPORT</div>
        <hr>

        {{-- Budget info: left block + right table --}}
        <div class="info-section">
            <div class="bill-to" style="float: left; width: 48%;">
                <strong>Budget details</strong><br><br>
                <strong>{{ $budget->name }}</strong><br>
                @if($budget->description)
                    {{ Str::limit($budget->description, 120) }}<br>
                @endif
                <br>
                <strong>Created by:</strong><br>
                {{ $budget->user->name ?? 'N/A' }}
            </div>

            <div class="invoice-box" style="text-align: right; float: left; width: 48%;">
                <table style="margin-top: 8px;">
                    <tr>
                        <td><strong>Year:</strong></td>
                        <td>{{ $budget->year }}</td>
                    </tr>
                    <tr>
                        <td><strong>Branch:</strong></td>
                        <td>{{ $budget->branch->name ?? 'All Branches' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Created date:</strong></td>
                        <td>{{ $budget->created_at->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Generated:</strong></td>
                        <td>{{ now()->format('d F Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>{{ ucfirst(str_replace('_', ' ', $budget->status ?? 'draft')) }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="clear: both; margin-bottom: 10px;"></div>

        {{-- Budget lines --}}
        @php $totalAmount = 0; @endphp
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Account Code</th>
                    <th>Account Name</th>
                    <th class="text-right">Amount (TZS)</th>
                    <th class="text-center">Category</th>
                </tr>
            </thead>
            <tbody>
                @foreach($budget->budgetLines as $index => $line)
                    @php $totalAmount += $line->amount; @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $line->account->account_code ?? 'N/A' }}</td>
                        <td>{{ $line->account->account_name ?? 'N/A' }}</td>
                        <td class="text-right">{{ number_format($line->amount, 2) }}</td>
                        <td class="text-center category-{{ strtolower(str_replace(' ', '-', $line->category ?? '')) }}">
                            {{ $line->category ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <table class="totals-table">
            <tr>
                <td colspan="3" style="text-align: right;"><strong>TOTAL BUDGET (TZS):</strong></td>
                <td><strong>{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </table>

        {{-- Footer --}}
        <hr>
        <div class="footer">
            <div style="margin-bottom: 10px;">This budget export was generated on {{ now()->format('d/m/Y H:i') }}. Budget period: {{ $budget->year }}.</div>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Prepared by</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">{{ $budget->user->name ?? 'N/A' }}</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Approved by</strong></div>
                    <div style="margin-top: 2px; font-size: 10px; color: #666;">{{ $budget->approved_at ? (optional($budget->approvedBy)->name ?? 'N/A') : '—' }}</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Authorised by</strong></div>
                    <div style="margin-top: 2px; font-size: 10px; color: #666;">—</div>
                </div>
            </div>

            <div class="text-center" style="font-size: 9px; margin-top: 20px;">
                Budget: {{ $budget->name }} ({{ $budget->year }}) · {{ $company->name ?? 'SmartFinance' }}<br>
                Page 1 of 1
            </div>
        </div>
    </div>
</body>
</html>
