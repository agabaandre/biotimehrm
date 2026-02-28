#!/bin/bash

# Duty Roster Summary Cron Job Script
# This script should be run daily at 2 AM via cron
# Cron entry: 0 2 * * * /path/to/this/script/duty_roster_cron.sh

# Set variables
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$(dirname "$SCRIPT_DIR")")")"
LOG_FILE="$PROJECT_ROOT/logs/duty_roster_cron.log"
PHP_PATH="/usr/bin/php"
CI_INDEX="$PROJECT_ROOT/index.php"

# Create logs directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

# Log function
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Start logging
log_message "Starting Duty Roster Summary Cron Job"

# Check if PHP is available
if ! command -v "$PHP_PATH" &> /dev/null; then
    log_message "ERROR: PHP not found at $PHP_PATH"
    exit 1
fi

# Check if CodeIgniter index file exists
if [ ! -f "$CI_INDEX" ]; then
    log_message "ERROR: CodeIgniter index file not found at $CI_INDEX"
    exit 1
fi

# Change to project directory
cd "$PROJECT_ROOT" || {
    log_message "ERROR: Failed to change to project directory"
    exit 1
}

# Run the cron job
log_message "Executing duty roster summary cron job..."
"$PHP_PATH" "$CI_INDEX" cronjobs/DutyRosterSummaryCron/updateDutyRosterSummary

# Check exit status
if [ $? -eq 0 ]; then
    log_message "Duty roster summary cron job completed successfully"
else
    log_message "ERROR: Duty roster summary cron job failed with exit code $?"
fi

# End logging
log_message "Duty Roster Summary Cron Job finished"
log_message "----------------------------------------"
