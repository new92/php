#!/usr/bin/env bash
set -euo pipefail

TMP_ROOT="$(php -r 'echo sys_get_temp_dir();')"
TEST_DIR="$(mktemp -d "${TMP_ROOT%/}/cleaner-test-XXXXXX")"
trap 'rm -rf -- "$TEST_DIR"' EXIT
mkdir -p "$TEST_DIR/subdir"
printf 'test' > "$TEST_DIR/subdir/sample.txt"

php Cleaner/cleaner.php --dir="$TEST_DIR" > /dev/null
[[ -f "$TEST_DIR/subdir/sample.txt" ]] || { echo 'Dry-run unexpectedly deleted files' >&2; exit 1; }

if php Cleaner/cleaner.php --dir="$TMP_ROOT" > /dev/null 2>&1; then
  echo 'Cleaner unexpectedly accepted the temp root' >&2; exit 1
fi

php Cleaner/cleaner.php --dir="$TEST_DIR" --apply > /dev/null
[[ ! -e "$TEST_DIR/subdir/sample.txt" ]] || { echo 'Apply failed to delete test file' >&2; exit 1; }
[[ -d "$TEST_DIR" ]] || { echo 'Cleaner should preserve target folder' >&2; exit 1; }
echo 'Cleaner tests passed.'
