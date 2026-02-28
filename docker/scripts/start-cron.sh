#!/bin/bash

# Start supervisor
echo "Starting supervisor..."
/usr/bin/supervisord -c /etc/supervisor/supervisord.conf

# Wait for supervisor to start
sleep 2

# Check if cron is running
if supervisorctl status cron | grep -q "RUNNING"; then
    echo "Cron service started successfully"
else
    echo "Failed to start cron service"
    exit 1
fi

# Keep the container running
tail -f /dev/null
