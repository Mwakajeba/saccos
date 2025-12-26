# PowerShell script to backup current PHP installation before upgrade
# Run this script BEFORE updating PHP

$xamppPath = "C:\xampp"
$phpPath = "$xamppPath\php"
$backupPath = "$xamppPath\php_backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

Write-Host "Backing up PHP installation..." -ForegroundColor Yellow

if (Test-Path $phpPath) {
    # Get current PHP version
    $phpVersion = & php -v 2>&1 | Select-Object -First 1
    Write-Host "Current PHP: $phpVersion" -ForegroundColor Cyan
    
    # Create backup
    Write-Host "Creating backup at: $backupPath" -ForegroundColor Yellow
    Copy-Item -Path $phpPath -Destination $backupPath -Recurse -Force
    
    Write-Host "Backup completed successfully!" -ForegroundColor Green
    Write-Host "Backup location: $backupPath" -ForegroundColor Green
} else {
    Write-Host "Error: PHP directory not found at $phpPath" -ForegroundColor Red
    exit 1
}

