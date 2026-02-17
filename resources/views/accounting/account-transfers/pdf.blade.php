<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inter-Account Transfer - {{ $transfer->transfer_number }}</title>
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
        .document-title {
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

        .invoice-box td:nth-child(even) {
            text-align: right;
        }

        .invoice-box strong {
            color: #1e40af;
        }

        /* Account boxes (From / To) */
        .account-block {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
        }

        .account-block.from {
            background-color: #fef2f2;
            border-left: 4px solid #dc2626;
        }

        .account-block.to {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
        }

        .account-block-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }

        .account-block-name {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
        }

        /* Items / GL table */
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

        /* Status badge */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-draft { background-color: #64748b; color: #fff; }
        .status-submitted { background-color: #0ea5e9; color: #fff; }
        .status-approved { background-color: #16a34a; color: #fff; }
        .status-rejected { background-color: #dc2626; color: #fff; }
        .status-posted { background-color: #1e40af; color: #fff; }

        /* Footer */
        .footer {
            margin-top: 20px;
            font-size: 10px;
        }

        .footer strong {
            color: #1e40af;
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

        .footer hr {
            border-top: 1px solid #dbeafe;
            margin: 15px 0;
        }

        .notes-box {
            margin-bottom: 10px;
            padding: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">

        {{-- Header (same as purchase invoice) --}}
        <div class="text-left">
            @php
                $company = $transfer->company ?? ($transfer->branch->company ?? null);
            @endphp
            @if($company && $company->logo)
                @php
                    $logo = $company->logo;
                    $logoPath = public_path('storage/' . ltrim($logo, '/'));
                    $logoBase64 = null;
                    if (file_exists($logoPath)) {
                        $imageData = file_get_contents($logoPath);
                        $imageInfo = getimagesize($logoPath);
                        if ($imageInfo !== false) {
                            $mimeType = $imageInfo['mime'];
                            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                        }
                    }
                @endphp
                @if($logoBase64)
                    <div class="logo-section" style="float: left; width: 45%;">
                        <img src="{{ $logoBase64 }}" alt="{{ ($company->name ?? 'Company') . ' logo' }}" class="company-logo">
                    </div>
                @endif
            @endif
            <div style="float: right; width: 50%; text-align: left; margin-left: 15%;">
                <div class="company-name">{{ $company->name ?? 'Company' }}</div>
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

        <div class="document-title">INTER-ACCOUNT TRANSFER</div>
        <hr>

        {{-- From Account (left) + Transfer Info (right) --}}
        <div class="info-section">
            <div class="bill-to" style="float: left; width: 48%;">
                <strong>From Account:</strong><br>
                <div class="account-block from" style="margin-top: 6px;">
                    <div class="account-block-label">{{ ucfirst(str_replace('_', ' ', $transfer->from_account_type)) }}</div>
                    <div class="account-block-name">
                        @php $fromAccount = $transfer->fromAccount; @endphp
                        {{ $fromAccount ? ($fromAccount->name ?? 'N/A') : 'N/A' }}
                    </div>
                </div>
                <strong style="margin-top: 10px; display: block;">To Account:</strong><br>
                <div class="account-block to" style="margin-top: 6px;">
                    <div class="account-block-label">{{ ucfirst(str_replace('_', ' ', $transfer->to_account_type)) }}</div>
                    <div class="account-block-name">
                        @php $toAccount = $transfer->toAccount; @endphp
                        {{ $toAccount ? ($toAccount->name ?? 'N/A') : 'N/A' }}
                    </div>
                </div>
                @if($transfer->createdBy)
                    <strong style="margin-top: 10px;">Created By:</strong><br>
                    {{ $transfer->createdBy->name }}
                @endif
            </div>

            <div class="invoice-box" style="text-align: right; float: left; width: 48%;">
                <table style="margin-top: 8px;">
                    <tr>
                        <td><strong>Transfer No:</strong></td>
                        <td>{{ $transfer->transfer_number }}</td>
                        <td><strong>Date:</strong></td>
                        <td>{{ $transfer->transfer_date->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td><span class="status-badge status-{{ $transfer->status }}">{{ ucfirst($transfer->status) }}</span></td>
                        <td><strong>Currency:</strong></td>
                        <td>{{ $transfer->currency->currency_code ?? 'TZS' }}</td>
                    </tr>
                    @if($transfer->exchange_rate && (float) $transfer->exchange_rate != 1)
                    <tr>
                        <td><strong>Exchange Rate:</strong></td>
                        <td>{{ number_format($transfer->exchange_rate, 4) }}</td>
                        <td><strong>Time:</strong></td>
                        <td>{{ $transfer->created_at->format('H:i:s') }}</td>
                    </tr>
                    @else
                    <tr>
                        <td><strong>Time:</strong></td>
                        <td colspan="3">{{ $transfer->created_at->format('H:i:s') }}</td>
                    </tr>
                    @endif
                    @if($transfer->reference_number)
                    <tr>
                        <td><strong>Reference:</strong></td>
                        <td colspan="3">{{ $transfer->reference_number }}</td>
                    </tr>
                    @endif
                    @if($transfer->branch)
                    <tr>
                        <td><strong>Branch:</strong></td>
                        <td colspan="3">{{ $transfer->branch->name ?? 'N/A' }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        <div style="clear: both; margin-bottom: 10px;"></div>

        @if($transfer->description)
        <div class="notes-box">
            <strong>Description:</strong><br>
            {{ $transfer->description }}
        </div>
        @endif

        {{-- Totals (same style as purchase invoice) --}}
        @php
            $totalAmount = (float) $transfer->amount + (float) ($transfer->charges ?? 0);
            $currencyCode = $transfer->currency->currency_code ?? 'TZS';
        @endphp
        <table class="totals-table">
            <tr>
                <td colspan="4" style="text-align: right;">Transfer Amount:</td>
                <td>{{ number_format($transfer->amount, 2) }} {{ $currencyCode }}</td>
            </tr>
            @if($transfer->charges > 0)
            <tr>
                <td colspan="4" style="text-align: right;">Charges:</td>
                <td>{{ number_format($transfer->charges, 2) }} {{ $currencyCode }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="4" style="text-align: right;"><strong>GRAND TOTAL:</strong></td>
                <td><strong>{{ number_format($totalAmount, 2) }} {{ $currencyCode }}</strong></td>
            </tr>
        </table>

        @if($transfer->charges > 0 && $transfer->chargesAccount)
        <div class="notes-box" style="margin-top: 10px;">
            <strong>Charges Account:</strong> {{ $transfer->chargesAccount->account_code }} - {{ $transfer->chargesAccount->account_name }}
        </div>
        @endif

        {{-- General Ledger Entries (same table style as purchase invoice items) --}}
        @if($transfer->journal_id && $transfer->journal && $transfer->journal->items && $transfer->journal->items->isNotEmpty())
        <div style="margin-top: 15px;">
            <div style="font-weight: bold; color: #1e40af; margin-bottom: 8px; font-size: 11px;">GENERAL LEDGER ENTRIES</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th class="text-center">Nature</th>
                        <th class="text-right">Amount</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transfer->journal->items as $item)
                    <tr>
                        <td>{{ $item->chartAccount->account_code ?? 'N/A' }}</td>
                        <td>{{ $item->chartAccount->account_name ?? 'N/A' }}</td>
                        <td class="text-center">
                            @if($item->nature === 'debit')
                                <span style="color: #16a34a; font-weight: bold;">Debit</span>
                            @else
                                <span style="color: #dc2626; font-weight: bold;">Credit</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($item->amount, 2) }} {{ $currencyCode }}</td>
                        <td>{{ $item->description ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #e0f2fe; font-weight: bold;">
                        <td colspan="3" class="text-right">Total:</td>
                        <td class="text-right">{{ number_format($transfer->journal->items->sum('amount'), 2) }} {{ $currencyCode }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        {{-- Footer (same as purchase invoice) --}}
        <hr>
        <div class="footer">
            <div style="margin-bottom: 10px;">This is a computer-generated inter-account transfer document.</div>

            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Prepared By</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">
                        {{ $transfer->createdBy->name ?? 'N/A' }}
                    </div>
                </div>

                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Approved By</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">
                        @if($transfer->approvedBy)
                            {{ $transfer->approvedBy->name }}
                            @if($transfer->approved_at)
                                <br><small>{{ $transfer->approved_at->format('d M Y, H:i') }}</small>
                            @endif
                        @else
                            <span style="color: #999;">{{ in_array($transfer->status, ['approved', 'posted']) ? 'N/A' : 'Pending Approval' }}</span>
                        @endif
                    </div>
                </div>

                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div style="margin-top: 5px; font-size: 11px;"><strong>Authorized By</strong></div>
                    <div style="margin-top: 2px; font-size: 10px;">
                        <span style="color: #999;">-</span>
                    </div>
                </div>
            </div>

            @if($transfer->approval_notes)
            <div style="margin-top: 10px; padding: 6px; background-color: #f8fafc; border-radius: 3px; font-size: 10px;">
                <strong>Approval Notes:</strong> {{ $transfer->approval_notes }}
            </div>
            @endif
            @if($transfer->rejection_reason)
            <div style="margin-top: 10px; padding: 6px; background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 3px; font-size: 10px; color: #dc2626;">
                <strong>Rejection Reason:</strong> {{ $transfer->rejection_reason }}
            </div>
            @endif

            <div class="text-center" style="font-size: 9px; margin-top: 20px;">
                Inter-Account Transfer No: {{ $transfer->transfer_number }} &nbsp;|&nbsp;
                Generated: {{ now()->format('d M Y, H:i') }} &nbsp;|&nbsp;
                Page 1 of 1
            </div>
        </div>
    </div>
</body>
</html>
