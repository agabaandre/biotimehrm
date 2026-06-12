#!/bin/bash
# Verify Redis is reachable from the OS and from PHP (same runtime as the web app).
#
# Usage (on the server, from the app root):
#   ./scripts/redis_cache_check.sh
#   ./scripts/redis_cache_check.sh /var/www/html/healthattend

set -euo pipefail

APP_ROOT="${1:-$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)}"
cd "$APP_ROOT"

echo "=== Redis service (OS) ==="
if command -v redis-cli >/dev/null 2>&1; then
	redis-cli ping 2>/dev/null || echo "redis-cli ping failed"
	echo "Key count (sample):"
	redis-cli --scan 2>/dev/null | head -10 || true
	echo "Pattern attend_* :"
	redis-cli --scan --pattern 'attend_*' 2>/dev/null | head -10 || true
else
	echo "redis-cli not found"
fi

echo ""
echo "=== PHP Redis extension ==="
PHP_BIN="${PHP_BIN:-php}"
if ! command -v "$PHP_BIN" >/dev/null 2>&1; then
	echo "php not found"
	exit 1
fi
"$PHP_BIN" -r "echo 'PHP: '.PHP_VERSION.PHP_EOL; echo 'redis ext: '.(extension_loaded('redis')?'yes':'NO').PHP_EOL; echo 'memcached ext: '.(extension_loaded('memcached')||extension_loaded('memcache')?'yes':'no').PHP_EOL;"

if ! "$PHP_BIN" -m 2>/dev/null | grep -qi '^redis$'; then
	echo ""
	echo ">>> PHP redis extension is NOT loaded. The app will skip Redis and use DB/file only."
	echo ">>> Install (Ubuntu example): sudo apt install php-redis && sudo systemctl restart php*-fpm apache2"
	exit 0
fi

echo ""
echo "=== CodeIgniter cache write test ==="
"$PHP_BIN" "$APP_ROOT/index.php" cronjobs/CacheDiagnostics/redisPing
