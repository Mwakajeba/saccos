#!/bin/bash

# Queue Worker Startup Script for Bulk Repayment Import
# This ensures the queue worker is always running

QUEUE_WORKER="php artisan queue:work"
QUEUE_PID_FILE="/tmp/saccos_queue.pid"

# Check if queue worker is already running
if [ -f "$QUEUE_PID_FILE" ]; then
    PID=$(cat "$QUEUE_PID_FILE")
    if ps -p "$PID" > /dev/null 2>&1; then
        echo "Queue worker is already running (PID: $PID)"
        exit 0
    else
        echo "Stale PID file found. Removing..."
        rm -f "$QUEUE_PID_FILE"
    fi
fi

# Start queue worker
echo "Starting queue worker..."
nohup php artisan queue:work --queue=default --tries=3 --timeout=300 --sleep=3 --max-jobs=1000 > storage/logs/queue.log 2>&1 &

# Save PID
echo $! > "$QUEUE_PID_FILE"

echo "Queue worker started (PID: $(cat $QUEUE_PID_FILE))"
echo "Logs: storage/logs/queue.log"
echo ""
echo "To stop: kill $(cat $QUEUE_PID_FILE)"
echo "To monitor: tail -f storage/logs/queue.log"
