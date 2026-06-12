#!/bin/bash

# Rebuild Switch Facility district/facility cache (MOH: ihrisdata; education: local tables).
# Run weekly at midnight (Sunday 00:00):
#   0 0 * * 0 /path/to/attend/application/modules/cronjobs/scripts/facility_switch_cache_cron.sh

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$(dirname "$(dirname "$SCRIPT_DIR")")")")"
LOG_FILE="$PROJECT_ROOT/application/logs/facility_switch_cache_cron.log"
PHP_PATH="${PHP_PATH:-/usr/bin/php}"
CI_INDEX="$PROJECT_ROOT/index.php"

mkdir -p "$(dirname "$LOG_FILE")"

log_message() {
	echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log_message "Starting facility switch cache rebuild"

if [ ! -f "$CI_INDEX" ]; then
	log_message "ERROR: index.php not found at $CI_INDEX"
	exit 1
fi

cd "$PROJECT_ROOT" || exit 1

"$PHP_PATH" "$CI_INDEX" cronjobs/FacilitySwitchCacheCron/rebuild >> "$LOG_FILE" 2>&1
exit $?
