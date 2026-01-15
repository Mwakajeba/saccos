# Installing PHP BCMath Extension

The Hashids library requires either `bcmath` or `gmp` PHP extension to be installed.

## Installation Instructions

### For Ubuntu/Debian systems:

1. Install the php-bcmath extension:
```bash
sudo apt-get update
sudo apt-get install php8.3-bcmath
```

2. Verify installation:
```bash
php -m | grep bcmath
```

3. Restart your web server:
   - If using PHP-FPM:
   ```bash
   sudo systemctl restart php8.3-fpm
   ```
   
   - If using Apache:
   ```bash
   sudo systemctl restart apache2
   ```
   
   - If using Nginx:
   ```bash
   sudo systemctl restart nginx
   ```

### Alternative: Install GMP extension instead

If bcmath doesn't work, you can install gmp:

```bash
sudo apt-get install php8.3-gmp
sudo systemctl restart php8.3-fpm  # or apache2/nginx
```

## Verify After Installation

After installing, verify that the extension is loaded:

```bash
php -m | grep -E "bcmath|gmp"
```

You should see either `bcmath` or `gmp` in the output.

## After Installation

Once installed, refresh the customers page and it should load properly.