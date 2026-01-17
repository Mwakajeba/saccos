#!/bin/bash
# Script to restart the Laravel development server

echo "Stopping any existing php artisan serve processes..."
pkill -f "php artisan serve" 2>/dev/null
sleep 1

echo "Starting php artisan serve..."
cd "$(dirname "$0")"
php artisan serve --host=127.0.0.1 --port=8000 > /dev/null 2>&1 &

echo "Server restarted! The bcmath extension should now be loaded."
echo ""
echo "You can verify it's working by visiting: http://127.0.0.1:8000/customers"
echo ""
echo "To check if bcmath is loaded, run: php artisan check:bcmath"
