<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share Certificate</title>
    <style>
        @page {
            size: landscape;
            margin: 0;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 0;
            color: #000;
            background: #fff;
        }
        
        .certificate-wrapper {
            width: 100%;
            height: 100vh;
            position: relative;
        }
        
        .certificate-background-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        
        /* Company Logo - top left corner, near CERTIFICATE word */
        .company-logo-container {
            position: absolute;
            top: 80px;
            left: 50px;
            z-index: 2;
        }
        
        .company-logo {
            max-height: 70px;
            max-width: 180px;
        }
        
        /* Name position - should be above the name line */
        .member-name {
            position: absolute;
            top: 42%;
            left: 50%;
            transform: translateX(-50%);
            font-size: 32px;
            color: #d4af37;
            font-weight: bold;
            font-family: 'Brush Script MT', 'Lucida Handwriting', cursive;
            text-align: center;
            z-index: 2;
            white-space: nowrap;
        }
        
        /* Certificate text - should be below the name line */
        .certificate-text {
            position: absolute;
            top: 54%;
            left: 50%;
            transform: translateX(-50%);
            width: 85%;
            font-size: 16px;
            color: #333;
            line-height: 1.7;
            text-align: center;
            z-index: 2;
            max-width: 900px;
        }
        
        .certificate-text strong {
            font-weight: bold;
        }
        
        /* Footer with Certificate Number */
        .footer-info {
            position: absolute;
            bottom: 100px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 250px;
            z-index: 2;
        }
        
        .certificate-number-footer {
            text-align: center;
            font-size: 12px;
            color: #333;
            font-weight: bold;
        }
        
        .certificate-number-footer strong {
            color: #d4af37;
        }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        @if(file_exists(public_path('assets/certificate-template.png')))
        <img src="{{ public_path('assets/certificate-template.png') }}" alt="Certificate Background" class="certificate-background-image">
        @endif
        
        <!-- Company Logo - top left corner, near CERTIFICATE word -->
        @if(isset($company) && $company && $company->logo)
        <div class="company-logo-container">
            <img src="{{ public_path('storage/' . $company->logo) }}" alt="{{ $company->name ?? 'Company Logo' }}" class="company-logo">
        </div>
        @endif
        
        <!-- Member Name - positioned on the name line -->
        <div class="member-name">{{ $account->customer->name ?? 'N/A' }}</div>
        
        <!-- Certificate Text - positioned below the name line -->
        <div class="certificate-text">
            This is to certify that <strong>{{ $account->customer->name ?? 'N/A' }}</strong> (Membership Number: <strong>{{ $account->customer->customerNo ?? 'N/A' }}</strong>) is the registered holder of <strong>{{ number_format($account->share_balance, 2) }}</strong> shares in <strong>{{ $account->shareProduct->share_name ?? 'N/A' }}</strong>. The Nominal Value per Share is <strong>{{ number_format($account->nominal_value, 2) }}</strong>, resulting in a Total Value of <strong>{{ number_format($account->share_balance * $account->nominal_value, 2) }}</strong>.
        </div>
        
        <!-- Footer with Certificate Number -->
        <div class="footer-info">
            <div class="certificate-number-footer">
                Certificate Number: <strong>{{ $account->certificate_number ?? $account->account_number }}</strong>
            </div>
        </div>
    </div>
</body>
</html>
