<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt Voucher - {{ $receiptVoucher->reference ?? 'RCP-' . $receiptVoucher->id }}</title>
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

        .invoice-title {
            font-weight: bold;
            text-align: center;
            font-size: 18px;
            margin: 10px 0;
            color: #1e40af;
        }

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

        .footer {
            margin-top: 20px;
            font-size: 10px;
        }

        .footer strong {
            color: #1e40af;
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

        {{-- Header: Logo + Company (same as payment voucher: branch/user/auth fallback) --}}
        <div class="text-left">
            @php
                $company = optional($receiptVoucher->branch)->company
                    ?? optional($receiptVoucher->user)->company
                    ?? (auth()->check() ? auth()->user()->company : null);
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
                        <img src="{{ $logoBase64 }}" alt="{{ (optional($company)->name ?? '') . ' logo' }}" class="company-logo">
                    </div>
                @endif
            @endif
            <div style="float: right; width: 50%; text-align: left; margin-left: 15%;">
                <div class="company-name">{{ optional($company)->name ?? 'SmartFinance' }}</div>
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

        <div class="invoice-title">RECEIPT VOUCHER</div>
        <hr>

        {{-- Received From + Receipt Info (same layout as invoice: bill-to left, invoice-box right) --}}
        <div class="info-section">
            <div class="bill-to" style="float: left; width: 48%;">
                <strong>Received from:</strong><br>
                @if($receiptVoucher->payee_type === 'customer' && $receiptVoucher->customer)
                    <strong>{{ $receiptVoucher->customer->name }}</strong><br>
                    @if($receiptVoucher->customer->phone){{ $receiptVoucher->customer->phone }}<br>@endif
                    @if($receiptVoucher->customer->email){{ $receiptVoucher->customer->email }}<br>@endif
                    @if($receiptVoucher->customer->address){{ $receiptVoucher->customer->address }}<br>@endif
                @elseif($receiptVoucher->payee_type === 'employee' && $receiptVoucher->employee)
                    <strong>{{ $receiptVoucher->employee->full_name }}</strong><br>
                    @if($receiptVoucher->employee->employee_number)Employee No: {{ $receiptVoucher->employee->employee_number }}<br>@endif
                @elseif($receiptVoucher->payee_type === 'other' || $receiptVoucher->payee_name)
                    <strong>{{ $receiptVoucher->payee_name ?? 'N/A' }}</strong><br>
                @elseif(isset($invoice) && $invoice->customer)
                    <strong>{{ $invoice->customer->name ?? 'Walk-in Customer' }}</strong><br>
                    @if($invoice->customer->phone){{ $invoice->customer->phone }}<br>@endif
                    @if($invoice->customer->email){{ $invoice->customer->email }}<br>@endif
                    @if($invoice->customer->address){{ $invoice->customer->address }}<br>@endif
                @else
                    <strong>{{ $receiptVoucher->payee_name ?? 'N/A' }}</strong><br>
                @endif
                <br>
                <strong>Created By:</strong><br>
                @php
                    $creator = $receiptVoucher->user ?? null;
                    $creatorRole = $creator && method_exists($creator, 'roles') ? $creator->roles->first() : null;
                @endphp
                @if($creator)
                    {{ $creator->name }}@if($creatorRole) ({{ $creatorRole->name }})@endif
                @else
                    System
                @endif
            </div>

            <div class="invoice-box" style="text-align: right; float: left; width: 48%;">
                <table style="margin-top: 8px;">
                    <tr>
                        <td><strong>Receipt no:</strong></td>
                        <td>{{ $receiptVoucher->reference ?? 'RCP-' . $receiptVoucher->id }}</td>
                        <td><strong>Date:</strong></td>
                        <td>{{ $receiptVoucher->date ? $receiptVoucher->date->format('d F Y') : 'N/A' }}</td>
                    </tr>
                    @if(isset($invoice))
                    <tr>
                        <td><strong>Invoice no:</strong></td>
                        <td colspan="3">{{ $invoice->invoice_number }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Currency:</strong></td>
                        <td>{{ $receiptVoucher->currency ?? 'TZS' }}</td>
                        <td><strong>Ex Rate:</strong></td>
                        <td>{{ number_format($receiptVoucher->exchange_rate ?? 1, 2) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment:</strong></td>
                        <td colspan="3">
                            @if($receiptVoucher->bankAccount)
                                {{ $receiptVoucher->bankAccount->name ?? 'Bank' }} ({{ $receiptVoucher->bankAccount->account_number ?? 'N/A' }})
                            @else
                                {{ ucfirst(str_replace('_', ' ', $receiptVoucher->payment_method ?? 'Cash')) }}
                            @endif
                        </td>
                    </tr>
                    @if($receiptVoucher->branch)
                    <tr>
                        <td><strong>Branch:</strong></td>
                        <td colspan="3">{{ $receiptVoucher->branch->name ?? 'N/A' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Time:</strong></td>
                        <td colspan="3">{{ $receiptVoucher->created_at ? $receiptVoucher->created_at->format('H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        <div style="clear: both; margin-bottom: 10px;"></div>

        @if($receiptVoucher->description)
        <div style="clear: both; margin-bottom: 10px;">
            <strong>Description:</strong><br>
            {{ $receiptVoucher->description }}
        </div>
        @endif

        {{-- Line items (same table style as purchase invoice) --}}
        @if($receiptVoucher->receiptItems && $receiptVoucher->receiptItems->count() > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Account</th>
                    <th>Account Code</th>
                    <th>Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receiptVoucher->receiptItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ optional($item->chartAccount)->account_name ?? 'N/A' }}</td>
                    <td class="text-center">{{ optional($item->chartAccount)->account_code ?? 'N/A' }}</td>
                    <td>{{ $item->description ?: '-' }}</td>
                    <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="margin-top: 10px; padding: 10px; text-align: center; color: #999; font-style: italic;">No receipt items found</div>
        @endif

        {{-- Totals (same style as purchase invoice: optional rows then GRAND TOTAL) --}}
        @php
            $hasWHT = (float)($receiptVoucher->wht_amount ?? 0) > 0;
            $hasVAT = (float)($receiptVoucher->vat_amount ?? 0) > 0;
            $colspan = 4;
        @endphp
        <table class="totals-table">
            @if($hasVAT && ($receiptVoucher->base_amount ?? 0) > 0)
            <tr>
                <td colspan="{{ $colspan }}" style="text-align: right;">Base Amount:</td>
                <td>{{ number_format($receiptVoucher->base_amount ?? $receiptVoucher->amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan }}" style="text-align: right;">VAT:</td>
                <td>{{ number_format($receiptVoucher->vat_amount ?? 0, 2) }}</td>
            </tr>
            @endif
            @if($hasWHT)
            <tr>
                <td colspan="{{ $colspan }}" style="text-align: right;">WHT ({{ number_format($receiptVoucher->wht_rate ?? 0, 1) }}%):</td>
                <td>{{ number_format($receiptVoucher->wht_amount ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td colspan="{{ $colspan }}" style="text-align: right;">Net Receivable:</td>
                <td>{{ number_format($receiptVoucher->net_receivable ?? $receiptVoucher->amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="{{ $colspan }}" style="text-align: right;"><strong>GRAND TOTAL:</strong></td>
                <td><strong>{{ number_format($receiptVoucher->amount, 2) }}</strong></td>
            </tr>
        </table>

        @if(method_exists($receiptVoucher, 'getAmountInWords'))
        <div style="margin-top:5px;font-style:italic;">
            <strong>{{ ucwords($receiptVoucher->getAmountInWords()) }}</strong>
        </div>
        @endif

        @if(isset($invoice) && $invoice)
        @php
            $invTotal = $invoice->total_amount ?? 0;
            $invPaid = $invoice->total_paid ?? $invoice->paid_amount ?? 0;
            $invBalance = method_exists($invoice, 'getBalanceDue') ? $invoice->getBalanceDue() : ($invoice->balance_due ?? max(0, (float)$invTotal - (float)$invPaid));
        @endphp
        <div style="margin-top:10px; padding: 8px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 3px;">
            <div style="font-weight: bold; color: #1e40af; margin-bottom: 6px;">INVOICE SUMMARY</div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span>Total Invoice:</span><span><strong>{{ number_format($invTotal, 2) }}</strong></span></div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;"><span>Total Paid:</span><span><strong>{{ number_format($invPaid, 2) }}</strong></span></div>
            <div style="display: flex; justify-content: space-between;"><span>Balance Due:</span><span><strong>{{ number_format($invBalance, 2) }}</strong></span></div>
        </div>
        @endif

        {{-- Footer (same as purchase invoice) --}}
        <hr>
        <div class="footer">
            <div style="margin-bottom: 10px;">Thank you for your payment!</div>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Prepared By</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">{{ optional($receiptVoucher->user)->name ?? 'N/A' }}</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Approved By</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">
                        @if($receiptVoucher->approved && $receiptVoucher->approvedBy)
                            {{ $receiptVoucher->approvedBy->name }}
                        @elseif($receiptVoucher->approved)
                            {{ optional($receiptVoucher->user)->name ?? 'N/A' }}
                        @else
                            <span style="color: #999;">Pending Approval</span>
                        @endif
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Received By</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">
                        @if($receiptVoucher->payee_type === 'customer' && $receiptVoucher->customer)
                            {{ $receiptVoucher->customer->name }}
                        @elseif($receiptVoucher->payee_type === 'employee' && $receiptVoucher->employee)
                            {{ $receiptVoucher->employee->full_name }}
                        @elseif(isset($invoice) && $invoice->customer)
                            {{ $invoice->customer->name ?? 'Customer' }}
                        @else
                            {{ $receiptVoucher->payee_name ?? 'N/A' }}
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-center" style="font-size:9px; margin-top: 20px;">
                Receipt Voucher No: {{ $receiptVoucher->reference ?? 'RCP-' . $receiptVoucher->id }}<br>
                {{ optional($company)->name ?? 'SmartFinance' }} · Page 1 of 1
            </div>
        </div>
    </div>
</body>
</html>
