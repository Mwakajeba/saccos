# How to Update PHP to 8.3+ in XAMPP

## Step 1: Backup Current PHP Installation
1. Go to `C:\xampp\php`
2. Copy the entire `php` folder and save it as a backup (e.g., `C:\xampp\php_backup_8.2.12`)

## Step 2: Stop XAMPP Services
1. Open XAMPP Control Panel
2. Stop Apache (and MySQL if running)
3. Close XAMPP Control Panel

## Step 3: Download PHP 8.3
1. Visit: https://windows.php.net/download/
2. Download **PHP 8.3.x** (Thread Safe version, ZIP package)
   - Choose: `VS16 x64 Non Thread Safe` or `VS16 x64 Thread Safe` (matching your current build)
3. Extract the ZIP file to a temporary location (e.g., `C:\temp\php83`)

## Step 4: Backup php.ini
1. Copy `C:\xampp\php\php.ini` to `C:\xampp\php\php.ini.backup`

## Step 5: Replace PHP Files
1. Delete all files in `C:\xampp\php` (EXCEPT `php.ini` and `php.ini-production`)
2. Copy all files from the extracted PHP 8.3 folder to `C:\xampp\php`
3. Copy your backup `php.ini` back to `C:\xampp\php\php.ini`

## Step 6: Update php.ini (if needed)
1. Open `C:\xampp\php\php.ini`
2. Ensure these extensions are uncommented (remove the semicolon `;` at the start):
   ```
   extension=mysqli
   extension=pdo_mysql
   extension=curl
   extension=openssl
   extension=mbstring
   extension=fileinfo
   extension=gd
   extension=intl
   extension=zip
   ```
3. Verify `extension_dir` points to the correct location (usually `extension_dir = "ext"`)

## Step 7: Verify PHP Version
1. Open PowerShell/Command Prompt
2. Run: `php -v`
3. You should see PHP 8.3.x

## Step 8: Restore Platform Check
Remove the temporary bypass in `vendor/composer/platform_check.php` by removing the `return;` line.

## Step 9: Restart XAMPP
1. Open XAMPP Control Panel
2. Start Apache
3. Test your application

## Alternative: Use XAMPP with PHP 8.3 (if available)
Check if there's a newer XAMPP version that includes PHP 8.3:
- Visit: https://www.apachefriends.org/
- Download the latest XAMPP version (if it includes PHP 8.3+)

