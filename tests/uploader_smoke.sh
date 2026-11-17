#!/usr/bin/env bash
set -euo pipefail

FIXTURE_DIR="$(mktemp -d)"
PRIVATE_DIR="$(mktemp -d)"
PORT=19881
SERVER_PID=""
cleanup() {
  [[ -n "$SERVER_PID" ]] && kill "$SERVER_PID" 2>/dev/null || true
  rm -rf -- "$FIXTURE_DIR" "$PRIVATE_DIR"
}
trap cleanup EXIT

printf '%s' 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO5ZbqEAAAAASUVORK5CYII=' | base64 --decode > "$FIXTURE_DIR/valid.png"
printf 'not an image' > "$FIXTURE_DIR/fake.png"

PHP_DEMO_UPLOAD_DIR="$PRIVATE_DIR" php -S "127.0.0.1:$PORT" -t 'File uploader' > "$FIXTURE_DIR/server.log" 2>&1 &
SERVER_PID=$!
sleep 1

status="$(curl --silent --output /dev/null --write-out '%{http_code}' --retry 5 --retry-connrefused --retry-delay 1 -F "file=@$FIXTURE_DIR/valid.png" "http://127.0.0.1:$PORT/upload.php")"
[[ "$status" == '201' ]] || { echo "Expected HTTP 201 for image, got $status" >&2; exit 1; }
status="$(curl --silent --output /dev/null --write-out '%{http_code}' -F "file=@$FIXTURE_DIR/fake.png" "http://127.0.0.1:$PORT/upload.php")"
[[ "$status" == '415' ]] || { echo "Expected HTTP 415 for fake image, got $status" >&2; exit 1; }
[[ "$(find "$PRIVATE_DIR" -maxdepth 1 -type f | wc -l)" -eq 1 ]] || { echo 'Expected one stored image' >&2; exit 1; }
echo 'File uploader tests passed.'
