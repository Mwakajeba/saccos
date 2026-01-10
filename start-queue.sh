#!/bin/bash

# Customer Import Queue Worker
# This script starts the Laravel queue worker to process customer import jobs

echo "Starting customer import queue worker..."
echo "Press Ctrl+C to stop the worker."
echo ""

php artisan queue:work --queue=default --tries=3 --timeout=300

