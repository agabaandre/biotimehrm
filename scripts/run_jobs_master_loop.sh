#!/bin/bash
# Runs jobs master once per minute, aligned to the clock.
# Managed by supervisord — see scripts/setup_supervisor_jobs.sh

set -u

APP_ROOT="${APP_ROOT:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
LOG_FILE="${ATTEND_JOBS_LOG:-$APP_ROOT/application/logs/jobs_master_supervisor.log}"

mkdir -p "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"

cd "$APP_ROOT" || exit 1

if [ ! -f "$APP_ROOT/index.php" ]; then
	echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: index.php not found in $APP_ROOT" >>"$LOG_FILE"
	exit 1
fi

if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
	echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: PHP not found at $PHP_BIN" >>"$LOG_FILE"
	exit 1
fi

while true; do
	now=$(date +%s)
	sleep_sec=$((60 - now % 60))
	if [ "$sleep_sec" -lt 1 ]; then
		sleep_sec=60
	fi
	sleep "$sleep_sec"

	echo "[$(date '+%Y-%m-%d %H:%M:%S')] jobs master tick" >>"$LOG_FILE"
	"$PHP_BIN" "$APP_ROOT/index.php" jobs master >>"$LOG_FILE" 2>&1
done
