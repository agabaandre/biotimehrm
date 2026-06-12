#!/bin/bash
# Install supervisord (if missing) and register Attend background jobs.
#
# Usage:
#   sudo ./scripts/setup_supervisor_jobs.sh
#   sudo ./scripts/setup_supervisor_jobs.sh --app-root /var/www/attend.health.go.ug
#   sudo ./scripts/setup_supervisor_jobs.sh --deployment education --run-user www-data
#   ./scripts/setup_supervisor_jobs.sh --dry-run
#
# Programs created:
#   attend-jobs-master  — runs `php index.php jobs master` every minute (all deployments)
#   biotimejobs         — MOH only, on-demand BioTime attendance fetch (autostart=false)

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
DEFAULT_APP_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"

APP_ROOT="$DEFAULT_APP_ROOT"
RUN_USER="${ATTEND_RUN_USER:-www-data}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
DEPLOYMENT="${ATTEND_DEPLOYMENT:-auto}"
DRY_RUN=0
SKIP_INSTALL=0

CONF_NAME="attend-jobs.conf"
SUPERVISOR_LOG_DIR="/var/log/supervisor"
LOOP_SCRIPT="$SCRIPT_DIR/run_jobs_master_loop.sh"

usage() {
	cat <<'EOF'
Attend supervisor jobs setup

Options:
  --app-root PATH       Application root (default: repo root)
  --deployment TYPE     moh | education | auto (default: auto)
  --run-user USER       Unix user for processes (default: www-data)
  --php PATH            PHP binary (default: /usr/bin/php)
  --skip-install        Do not install supervisord if missing
  --dry-run             Print actions without applying
  -h, --help            Show this help

Environment:
  ATTEND_DEPLOYMENT, ATTEND_RUN_USER, PHP_BIN, APP_ROOT
EOF
}

log() {
	printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*"
}

die() {
	log "ERROR: $*"
	exit 1
}

while [ $# -gt 0 ]; do
	case "$1" in
		--app-root)
			shift
			APP_ROOT="${1:?--app-root requires a path}"
			;;
		--deployment)
			shift
			DEPLOYMENT="${1:?--deployment requires moh, education, or auto}"
			;;
		--run-user)
			shift
			RUN_USER="${1:?--run-user requires a username}"
			;;
		--php)
			shift
			PHP_BIN="${1:?--php requires a path}"
			;;
		--skip-install)
			SKIP_INSTALL=1
			;;
		--dry-run)
			DRY_RUN=1
			;;
		-h|--help)
			usage
			exit 0
			;;
		*)
			die "Unknown option: $1"
			;;
	esac
	shift
done

APP_ROOT="$(cd "$APP_ROOT" && pwd)"
LOOP_SCRIPT="$(cd "$(dirname "$LOOP_SCRIPT")" && pwd)/$(basename "$LOOP_SCRIPT")"

[ -f "$APP_ROOT/index.php" ] || die "index.php not found in $APP_ROOT"
[ -f "$LOOP_SCRIPT" ] || die "Loop script not found: $LOOP_SCRIPT"

if [ "$DEPLOYMENT" = "auto" ]; then
	if [ -x "$PHP_BIN" ] && [ -f "$APP_ROOT/index.php" ]; then
		DEPLOYMENT="$("$PHP_BIN" "$APP_ROOT/index.php" jobs deployment_type 2>/dev/null | tr -d '\r\n' || true)"
	fi
	case "$DEPLOYMENT" in
		education|moh) ;;
		*) DEPLOYMENT="moh"
			log "Could not auto-detect deployment; defaulting to moh"
			;;
	esac
fi

case "$DEPLOYMENT" in
	education|moh) ;;
	*) die "--deployment must be moh, education, or auto"
esac

OS_EARLY="$(uname -s)"
if [ "$OS_EARLY" = "Darwin" ] && [ "$RUN_USER" = "www-data" ] && ! id www-data >/dev/null 2>&1; then
	RUN_USER="$(whoami)"
	log "macOS: using run user $RUN_USER (www-data not present)"
fi

log "App root:     $APP_ROOT"
log "Deployment:   $DEPLOYMENT"
log "Run user:     $RUN_USER"
log "PHP:          $PHP_BIN"

detect_os() {
	case "$(uname -s)" in
		Linux) echo "linux" ;;
		Darwin) echo "darwin" ;;
		*) echo "unknown" ;;
	esac
}

detect_linux_distro() {
	if [ -f /etc/os-release ]; then
		# shellcheck disable=SC1091
		. /etc/os-release
		echo "${ID:-unknown}"
		return
	fi
	echo "unknown"
}

supervisor_paths() {
	local os="$1"
	case "$os" in
		darwin)
			local brew_prefix
			brew_prefix="$(brew --prefix supervisor 2>/dev/null || brew --prefix 2>/dev/null || echo /opt/homebrew)"
			SUPERVISORD_BIN="${brew_prefix}/bin/supervisord"
			SUPERVISORCTL_BIN="${brew_prefix}/bin/supervisorctl"
			SUPERVISOR_CONF_DIR="${brew_prefix}/etc/supervisord.d"
			SUPERVISOR_MAIN_CONF="${brew_prefix}/etc/supervisord.conf"
			SUPERVISOR_SERVICE="brew services start supervisor"
			SUPERVISOR_LOG_DIR="${APP_ROOT}/application/logs/supervisor"
			;;
		linux)
			SUPERVISORD_BIN="/usr/bin/supervisord"
			SUPERVISORCTL_BIN="/usr/bin/supervisorctl"
			SUPERVISOR_CONF_DIR="/etc/supervisor/conf.d"
			SUPERVISOR_MAIN_CONF="/etc/supervisor/supervisord.conf"
			if [ -f /etc/supervisord.conf ] && [ ! -f "$SUPERVISOR_MAIN_CONF" ]; then
				SUPERVISOR_MAIN_CONF="/etc/supervisord.conf"
				SUPERVISOR_CONF_DIR="/etc/supervisord.d"
			fi
			SUPERVISOR_SERVICE="systemctl enable --now supervisor 2>/dev/null || systemctl enable --now supervisord"
			;;
		*)
			die "Unsupported OS for automatic supervisor setup"
			;;
	esac
}

run_cmd() {
	if [ "$DRY_RUN" -eq 1 ]; then
		log "[dry-run] $*"
	else
		log "Running: $*"
		eval "$@"
	fi
}

install_supervisor() {
	local os="$1"
	local distro="$2"

	if command -v supervisord >/dev/null 2>&1 && command -v supervisorctl >/dev/null 2>&1; then
		log "supervisord already installed"
		return 0
	fi

	[ "$SKIP_INSTALL" -eq 0 ] || die "supervisord not installed and --skip-install was set"

	log "Installing supervisord..."

	case "$os" in
		darwin)
			command -v brew >/dev/null 2>&1 || die "Homebrew is required on macOS"
			run_cmd "brew install supervisor"
			;;
		linux)
			if command -v apt-get >/dev/null 2>&1; then
				run_cmd "apt-get update"
				run_cmd "DEBIAN_FRONTEND=noninteractive apt-get install -y supervisor"
			elif command -v dnf >/dev/null 2>&1; then
				run_cmd "dnf install -y supervisor"
			elif command -v yum >/dev/null 2>&1; then
				run_cmd "yum install -y supervisor"
			else
				die "No supported package manager found (apt, dnf, yum)"
			fi
			;;
	esac

	if [ "$DRY_RUN" -eq 0 ]; then
		command -v supervisord >/dev/null 2>&1 || die "supervisord install failed"
	fi
}

ensure_supervisor_running() {
	local os="$1"
	case "$os" in
		darwin)
			if ! pgrep -x supervisord >/dev/null 2>&1; then
				run_cmd "brew services start supervisor || \"$SUPERVISORD_BIN\" -c \"$SUPERVISOR_MAIN_CONF\""
			fi
			;;
		linux)
			run_cmd "$SUPERVISOR_SERVICE"
			;;
	esac
}

write_supervisor_config() {
	local conf_file="$SUPERVISOR_CONF_DIR/$CONF_NAME"
	local programs="attend-jobs-master"
	local biotime_block=""

	if [ "$DEPLOYMENT" = "moh" ]; then
		programs="attend-jobs-master,biotimejobs"
		biotime_block="
[program:biotimejobs]
command=${PHP_BIN} index.php biotimejobs fetch_daily_attendance
directory=${APP_ROOT}
autostart=false
autorestart=false
startsecs=0
stopwaitsecs=7200
stdout_logfile=${SUPERVISOR_LOG_DIR}/biotimejobs.log
stderr_logfile=${SUPERVISOR_LOG_DIR}/biotimejobs.err.log
user=${RUN_USER}
environment=HOME=\"${APP_ROOT}\",APP_ROOT=\"${APP_ROOT}\",PHP_BIN=\"${PHP_BIN}\"
"
	fi

	local conf_content
	conf_content="$(cat <<EOF
; Attend background jobs — generated by scripts/setup_supervisor_jobs.sh
; Deployment: ${DEPLOYMENT}
; App root: ${APP_ROOT}

[group:attend]
programs=${programs}

[program:attend-jobs-master]
command=${LOOP_SCRIPT}
directory=${APP_ROOT}
autostart=true
autorestart=true
startsecs=5
stopwaitsecs=30
stdout_logfile=${SUPERVISOR_LOG_DIR}/attend-jobs-master.log
stderr_logfile=${SUPERVISOR_LOG_DIR}/attend-jobs-master.err.log
user=${RUN_USER}
environment=HOME=\"${APP_ROOT}\",APP_ROOT=\"${APP_ROOT}\",PHP_BIN=\"${PHP_BIN}\",ATTEND_JOBS_LOG=\"${APP_ROOT}/application/logs/jobs_master_supervisor.log\"
${biotime_block}
EOF
)"

	if [ "$DRY_RUN" -eq 1 ]; then
		log "[dry-run] Would write $conf_file"
		printf '%s\n' "$conf_content"
		return 0
	fi

	mkdir -p "$SUPERVISOR_CONF_DIR" "$SUPERVISOR_LOG_DIR"
	printf '%s\n' "$conf_content" >"$conf_file"
	chmod 644 "$conf_file"
	log "Wrote $conf_file"
}

reload_supervisor() {
	if [ "$DRY_RUN" -eq 1 ]; then
		log "[dry-run] supervisorctl reread && update"
		return 0
	fi

	"$SUPERVISORCTL_BIN" -c "$SUPERVISOR_MAIN_CONF" reread
	"$SUPERVISORCTL_BIN" -c "$SUPERVISOR_MAIN_CONF" update

	"$SUPERVISORCTL_BIN" -c "$SUPERVISOR_MAIN_CONF" start attend:attend-jobs-master || \
		"$SUPERVISORCTL_BIN" -c "$SUPERVISOR_MAIN_CONF" restart attend:attend-jobs-master || true

	log "Supervisor status:"
	"$SUPERVISORCTL_BIN" -c "$SUPERVISOR_MAIN_CONF" status attend:* || true
}

prepare_permissions() {
	mkdir -p "$APP_ROOT/application/logs"
	if [ "$DRY_RUN" -eq 1 ]; then
		log "[dry-run] chmod +x $LOOP_SCRIPT"
		log "[dry-run] chown -R $RUN_USER $APP_ROOT/application/logs (if user exists)"
		return 0
	fi

	chmod +x "$LOOP_SCRIPT"
	if id "$RUN_USER" >/dev/null 2>&1; then
		chown -R "$RUN_USER" "$APP_ROOT/application/logs" 2>/dev/null || true
	fi
}

main() {
	local os distro
	os="$(detect_os)"
	distro="$(detect_linux_distro)"

	if [ "$os" = "linux" ] && [ "$(id -u)" -ne 0 ]; then
		die "Run as root on Linux (sudo ./scripts/setup_supervisor_jobs.sh)"
	fi

	if [ "$os" = "darwin" ] && ! id "$RUN_USER" >/dev/null 2>&1; then
		RUN_USER="$(whoami)"
		log "Using run user $RUN_USER"
	fi

	supervisor_paths "$os"
	install_supervisor "$os" "$distro"
	supervisor_paths "$os"

	if [ "$DRY_RUN" -eq 0 ]; then
		[ -x "$SUPERVISORD_BIN" ] || SUPERVISORD_BIN="$(command -v supervisord || true)"
		[ -x "$SUPERVISORCTL_BIN" ] || SUPERVISORCTL_BIN="$(command -v supervisorctl || true)"
		[ -n "$SUPERVISORD_BIN" ] && [ -n "$SUPERVISORCTL_BIN" ] || die "supervisord binaries not found after install"
	fi

	if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
		PHP_BIN="$(command -v php || true)"
		[ -n "$PHP_BIN" ] || die "PHP binary not found; pass --php /path/to/php"
		log "Using PHP at $PHP_BIN"
	fi

	prepare_permissions
	ensure_supervisor_running "$os"
	write_supervisor_config
	reload_supervisor

	log "Done."
	log "  attend-jobs-master runs jobs master every minute via supervisor."
	if [ "$DEPLOYMENT" = "moh" ]; then
		log "  biotimejobs is registered (on-demand): supervisorctl start biotimejobs"
	fi
	log "  Logs: $APP_ROOT/application/logs/jobs_master_supervisor.log"
	log "  Remove cron line '* * * * * php index.php jobs master' if you switch fully to supervisor."
}

main "$@"
