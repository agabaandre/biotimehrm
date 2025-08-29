#!/bin/bash

# Attendance Summary Cron Job Execution Script
# This script should be called by the system cron daemon

# Configuration
BASE_URL="http://localhost/attend"
CRON_ENDPOINT="/cronjobs/AttendanceSummaryCron/updateAttendanceSummary"
LOG_FILE="/opt/homebrew/var/www/attend/application/logs/cron/attendance_summary_$(date +\%Y\%m\%d).log"
PID_FILE="/tmp/attendance_summary_cron.pid"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Function to check if another instance is running
check_running() {
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if ps -p "$PID" > /dev/null 2>&1; then
            log_message "${YELLOW}Warning: Another instance is already running (PID: $PID)${NC}"
            return 1
        else
            log_message "${YELLOW}Removing stale PID file${NC}"
            rm -f "$PID_FILE"
        fi
    fi
    return 0
}

# Function to create PID file
create_pid() {
    echo $$ > "$PID_FILE"
}

# Function to cleanup PID file
cleanup() {
    rm -f "$PID_FILE"
    log_message "${GREEN}Cron job completed${NC}"
    exit 0
}

# Set up signal handlers
trap cleanup EXIT INT TERM

# Main execution
main() {
    log_message "${GREEN}Starting Attendance Summary Cron Job${NC}"
    
    # Check if another instance is running
    if ! check_running; then
        exit 1
    fi
    
    # Create PID file
    create_pid
    
    # Check if we should run (only after May 1st, 2025)
    CURRENT_DATE=$(date +%Y-%m-%d)
    START_DATE="2025-05-01"
    
    if [[ "$CURRENT_DATE" < "$START_DATE" ]]; then
        log_message "${YELLOW}Not yet May 1st, 2025. Skipping execution.${NC}"
        exit 0
    fi
    
    # Create log directory if it doesn't exist
    mkdir -p "$(dirname "$LOG_FILE")"
    
    # Execute the cron job via HTTP request
    log_message "Executing cron job via HTTP request..."
    
    RESPONSE=$(curl -s -w "\n%{http_code}" \
        --max-time 300 \
        --user-agent "AttendanceSystem-Cron/1.0" \
        "$BASE_URL$CRON_ENDPOINT")
    
    HTTP_CODE=$(echo "$RESPONSE" | tail -n1)
    RESPONSE_BODY=$(echo "$RESPONSE" | head -n -1)
    
    if [ "$HTTP_CODE" = "200" ]; then
        log_message "${GREEN}Success: $RESPONSE_BODY${NC}"
    else
        log_message "${RED}Error: HTTP $HTTP_CODE - $RESPONSE_BODY${NC}"
        exit 1
    fi
    
    log_message "${GREEN}Attendance Summary Cron Job completed successfully${NC}"
}

# Execute main function
main "$@"
