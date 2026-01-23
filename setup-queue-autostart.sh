#!/bin/bash

# SACCOS Queue Auto-Start Setup Script
# This script configures the queue worker to start automatically using Supervisor

echo "=========================================="
echo "SACCOS Queue Auto-Start Setup"
echo "=========================================="
echo ""

# Check if supervisor is installed
if ! command -v supervisorctl &> /dev/null; then
    echo "⚠️  Supervisor is not installed. Installing..."
    sudo apt-get update
    sudo apt-get install -y supervisor
    echo "✓ Supervisor installed"
else
    echo "✓ Supervisor is already installed"
fi

# Copy supervisor config
echo ""
echo "Configuring Supervisor..."
sudo cp saccos-queue-worker.conf /etc/supervisor/conf.d/

# Create log directory if it doesn't exist
mkdir -p storage/logs

# Reload supervisor
echo "Reloading Supervisor configuration..."
sudo supervisorctl reread
sudo supervisorctl update

# Start the queue worker
echo "Starting queue worker..."
sudo supervisorctl start saccos-queue-worker:*

# Check status
echo ""
echo "=========================================="
echo "Queue Worker Status:"
echo "=========================================="
sudo supervisorctl status saccos-queue-worker:*

echo ""
echo "=========================================="
echo "Setup Complete!"
echo "=========================================="
echo ""
echo "Useful commands:"
echo "  - Check status:  sudo supervisorctl status saccos-queue-worker:*"
echo "  - Stop worker:   sudo supervisorctl stop saccos-queue-worker:*"
echo "  - Start worker:  sudo supervisorctl start saccos-queue-worker:*"
echo "  - Restart:       sudo supervisorctl restart saccos-queue-worker:*"
echo "  - View logs:     tail -f storage/logs/queue-worker.log"
echo ""
