#!/usr/bin/env bash
set -euo pipefail
ROOT=$(cd "$(dirname "$0")/.." && pwd)
port=19912
server_pid=''
trap '[[ -z "$server_pid" ]] || kill "$server_pid" 2>/dev/null || true' EXIT
launch() {
  local folder="$1"
  port=$((port + 1))
  php -S "127.0.0.1:$port" -t "$ROOT/$folder" > /tmp/php-demos-guard-smoke.log 2>&1 &
  server_pid=$!
  for n in $(seq 1 50); do
    if curl -fsS "http://127.0.0.1:$port/$2" -o /dev/null 2>/dev/null; then return; fi
    sleep 0.1
  done
  echo 'Local PHP server failed to start' >&2
  return 1
}
check() {
  local endpoint="$1"; shift
  local response
  response=$(curl -sS -w '\n%{http_code}' -X POST "http://127.0.0.1:$port/$endpoint" "$@")
  local code="${response##*$'\n'}"
  if [[ "$code" != 422 ]]; then echo "Expected 422 for $endpoint, got $code" >&2; exit 1; fi
}
launch Protector input.html
check protector.php --data-urlencode 'url=http://127.0.0.1/'
kill "$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true; server_pid=''
launch Spider input.html
check dashboard.php --data-urlencode 'url=https://localhost/'
kill "$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true; server_pid=''
launch Gitspy index.html
check spy.php --data-urlencode 'username=<script>'
kill "$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true; server_pid=''
launch Investigator index.html
check output.php --data-urlencode 'ip=<script>'
kill "$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true; server_pid=''
launch WeatherApp index.php
check index.php --data-urlencode 'city=<script>'
echo 'PASS: invalid network inputs rejected by each local web demo'
