# PowerShell script to verify PHP installation after upgrade

Write-Host "Checking PHP installation..." -ForegroundColor Yellow

# Check PHP version
Write-Host "`nPHP Version:" -ForegroundColor Cyan
php -v

# Check PHP location
Write-Host "`nPHP Location:" -ForegroundColor Cyan
where.exe php

# Check required extensions
Write-Host "`nChecking required extensions..." -ForegroundColor Cyan
$requiredExtensions = @('mysqli', 'pdo_mysql', 'curl', 'openssl', 'mbstring', 'fileinfo', 'gd')

foreach ($ext in $requiredExtensions) {
    $result = php -m | Select-String -Pattern "^$ext$"
    if ($result) {
        Write-Host "  ✓ $ext" -ForegroundColor Green
    } else {
        Write-Host "  ✗ $ext (MISSING)" -ForegroundColor Red
    }
}

# Check Composer platform requirements
Write-Host "`nChecking if platform check can be removed..." -ForegroundColor Cyan
$platformCheckFile = "vendor\composer\platform_check.php"
if (Test-Path $platformCheckFile) {
    $content = Get-Content $platformCheckFile -Raw
    if ($content -match "// Temporarily bypassed") {
        Write-Host "  ⚠ Platform check is still bypassed" -ForegroundColor Yellow
        Write-Host "  Remove the 'return;' line after verifying PHP 8.3+" -ForegroundColor Yellow
    } else {
        Write-Host "  ✓ Platform check is active" -ForegroundColor Green
    }
}

Write-Host "`nVerification complete!" -ForegroundColor Green

