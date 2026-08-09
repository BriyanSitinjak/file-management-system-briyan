#!/usr/bin/env bash
# Serve Laravel with upload limits that actually apply to request workers.
# `php -d ... artisan serve` does NOT pass -d flags to the child PHP server.
set -euo pipefail

cd "$(dirname "$0")"

HOST="${HOST:-127.0.0.1}"
PORT="${PORT:-8000}"

ROUTER="server.php"
if [[ ! -f "$ROUTER" ]]; then
  ROUTER="vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php"
fi

exec php \
  -d upload_max_filesize=64M \
  -d post_max_size=64M \
  -d max_file_uploads=20 \
  -S "${HOST}:${PORT}" \
  -t public \
  "$ROUTER"
