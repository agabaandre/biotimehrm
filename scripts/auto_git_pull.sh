#!/bin/bash
# Auto git pull for the attend repo.
# Scheduled via ~/Library/LaunchAgents/com.attend.gitpull.plist.
#
# Behaviour:
#   - cd into the repo
#   - fetch remote
#   - if no new commits, exit silently (still logs heartbeat)
#   - if working tree is dirty, auto-stash with a timestamped label
#   - git pull --ff-only (refuses non-fast-forward, never rewrites history)
#   - if stash was created, pop it and log conflicts (non-fatal)
#   - all output appended to logs/auto_git_pull.log with timestamps
#
# Exit codes:
#   0  success or up-to-date
#   1  pull failed (non-fast-forward, conflicts, network, etc.) — see log

set -u

REPO_DIR="/opt/homebrew/var/www/attend"
LOG_FILE="$REPO_DIR/logs/auto_git_pull.log"
BRANCH="main"
GIT_BIN="/usr/bin/git"

# Make sure git can find Homebrew tools and SSH/Keychain helpers even when
# launchd runs us with a minimal PATH.
export PATH="/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin"
export GIT_TERMINAL_PROMPT=0

log() {
    printf '[%s] %s\n' "$(date '+%Y-%m-%d %H:%M:%S')" "$*" >>"$LOG_FILE"
}

mkdir -p "$(dirname "$LOG_FILE")"
touch "$LOG_FILE"

cd "$REPO_DIR" || { log "ERROR: cannot cd into $REPO_DIR"; exit 1; }

if [ ! -d ".git" ]; then
    log "ERROR: $REPO_DIR is not a git repository"
    exit 1
fi

CURRENT_BRANCH="$("$GIT_BIN" rev-parse --abbrev-ref HEAD 2>/dev/null || echo unknown)"
if [ "$CURRENT_BRANCH" != "$BRANCH" ]; then
    log "SKIP: current branch is '$CURRENT_BRANCH', expected '$BRANCH'"
    exit 0
fi

if ! "$GIT_BIN" fetch --quiet origin "$BRANCH" 2>>"$LOG_FILE"; then
    log "ERROR: git fetch failed"
    exit 1
fi

LOCAL_SHA="$("$GIT_BIN" rev-parse HEAD)"
REMOTE_SHA="$("$GIT_BIN" rev-parse "origin/$BRANCH")"

if [ "$LOCAL_SHA" = "$REMOTE_SHA" ]; then
    # Heartbeat once an hour only, to keep the log small.
    if [ "$(date '+%M')" = "00" ]; then
        log "OK: up to date (HEAD=$LOCAL_SHA)"
    fi
    exit 0
fi

log "INFO: new commits on origin/$BRANCH ($LOCAL_SHA -> $REMOTE_SHA)"

STASHED=0
if ! "$GIT_BIN" diff --quiet || ! "$GIT_BIN" diff --cached --quiet; then
    STASH_MSG="auto_git_pull $(date '+%Y-%m-%d %H:%M:%S')"
    if "$GIT_BIN" stash push --include-untracked -m "$STASH_MSG" >>"$LOG_FILE" 2>&1; then
        STASHED=1
        log "INFO: stashed local changes as '$STASH_MSG'"
    else
        log "ERROR: failed to stash local changes; aborting pull"
        exit 1
    fi
fi

if "$GIT_BIN" pull --ff-only --quiet origin "$BRANCH" >>"$LOG_FILE" 2>&1; then
    NEW_SHA="$("$GIT_BIN" rev-parse HEAD)"
    log "OK: pulled to $NEW_SHA"
    PULL_RC=0
else
    log "ERROR: git pull --ff-only failed (likely diverged or conflict)"
    PULL_RC=1
fi

if [ "$STASHED" -eq 1 ]; then
    if "$GIT_BIN" stash pop >>"$LOG_FILE" 2>&1; then
        log "INFO: restored stashed local changes"
    else
        log "WARN: stash pop reported conflicts; resolve with 'git stash list' / 'git stash pop'"
    fi
fi

exit "$PULL_RC"
