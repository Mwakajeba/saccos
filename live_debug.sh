#!/bin/bash

# Live Debug Mode - Watch everything in real-time
# Usage: bash live_debug.sh

echo "================================================="
echo "🔍 Live Debug Mode - Role Permissions"
echo "================================================="
echo ""
echo "This will open multiple terminal windows to watch:"
echo "1. Laravel logs (real-time)"
echo "2. Database changes (real-time)"
echo ""
echo "Instructions:"
echo "1. Keep this terminal visible"
echo "2. Open your browser to /roles"
echo "3. Open browser console (F12)"
echo "4. Create a new role with permissions"
echo "5. Watch this terminal for debug info"
echo ""
echo "Press Ctrl+C to stop"
echo ""
echo "================================================="
echo ""

# Change to project directory
cd /home/julius-mwakajeba/saccos/saccos

# Start watching logs
echo "📋 Watching Laravel logs..."
echo "================================================="
tail -f storage/logs/laravel.log | grep --line-buffered -E "(Role (creation|update)|Permissions|ERROR|WARN)" --color=always
