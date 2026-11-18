#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
PORT=19956
LOG=$(mktemp)
php -S "127.0.0.1:$PORT" -t "$ROOT" >"$LOG" 2>&1 &
SERVER_PID=$!
cleanup() { kill "$SERVER_PID" 2>/dev/null || true; wait "$SERVER_PID" 2>/dev/null || true; rm -f "$LOG"; }
trap cleanup EXIT
for n in $(seq 1 40); do
  if curl --silent --fail "http://127.0.0.1:$PORT/index.html" -o /dev/null; then break; fi
  sleep 0.1
done

pages=(
  'index.html'
  'DirReader/form.html'
  'File%20uploader/index.html'
  'Gitspy/index.html'
  'Investigator/index.html'
  'Protector/input.html'
  'Spider/input.html'
  'WeatherApp/index.php'
)

for path in "${pages[@]}"; do
  response=$(curl --silent --show-error --fail "http://127.0.0.1:$PORT/$path")
  if [[ "$response" != *'data-theme-toggle'* || "$response" != *'<form'* && "$path" != 'index.html' ]]; then
    echo "Missing expected GUI structure: $path" >&2
    exit 1
  fi
  if [[ "$response" != *'static/ui.css'* && "$path" != 'index.html' ]]; then
    echo "Missing stylesheet reference: $path" >&2
    exit 1
  fi
done

for folder in 'DirReader' 'File%20uploader' 'Gitspy' 'Investigator' 'Protector' 'Spider' 'WeatherApp'; do
  for file in ui.css ui.js; do
    curl --silent --show-error --fail "http://127.0.0.1:$PORT/$folder/static/$file" -o /dev/null
  done
done
curl --silent --show-error --fail "http://127.0.0.1:$PORT/ui.css" -o /dev/null
curl --silent --show-error --fail "http://127.0.0.1:$PORT/ui.js" -o /dev/null

# Backend results should render the new UI and keep existing validation.
dir_page=$(curl --silent --show-error --fail --data-urlencode 'dir=.' "http://127.0.0.1:$PORT/DirReader/reader.php")
if [[ "$dir_page" != *'dir-entries'* || "$dir_page" != *'data-search-item'* ]]; then
  echo 'DirReader results dashboard is missing' >&2
  exit 1
fi
upload_code=$(curl --silent --show-error -o /tmp/php-gui-upload-check.html -w '%{http_code}' -X POST "http://127.0.0.1:$PORT/File%20uploader/upload.php")
if [[ "$upload_code" != 400 ]] || ! grep -q 'Upload rejected' /tmp/php-gui-upload-check.html; then
  echo 'Uploader error page is missing or invalid' >&2
  exit 1
fi
rm -f /tmp/php-gui-upload-check.html

echo 'PASS: 8 entry pages, 16 project assets, root assets, DirReader and upload result UI'
