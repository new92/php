<?php

declare(strict_types=1);

require_once __DIR__ . '/../DirReader/lib.php';

function check(bool $valid, string $message): void
{
    if (!$valid) {
        fwrite(STDERR, "FAIL: $message\n");
        exit(1);
    }
}

$base = sys_get_temp_dir() . '/dirreader-test-' . bin2hex(random_bytes(6));
mkdir($base);
mkdir($base . '/nested');
file_put_contents($base . '/test-file.txt', 'hello');
file_put_contents($base . '/<script>.txt', 'test');
try {
    $result = readDirectory($base, '.');
    check($result['files'] === 2, 'Counts regular files');
    check($result['directories'] === 1, 'Counts directories');
    check(count($result['entries']) === 3, 'Omits dot directories');
    $nested = readDirectory($base, 'nested');
    check($nested['files'] === 0, 'Handles empty directory');
    foreach (['..', '../', $base . '/..'] as $outside) {
        try {
            readDirectory($base, $outside);
            check(false, "Allowed traversal: $outside");
        } catch (InvalidArgumentException $expected) {
            // The requested path is correctly rejected.
        }
    }
    if (function_exists('symlink') && @symlink(sys_get_temp_dir(), $base . '/escape')) {
        try {
            readDirectory($base, 'escape');
            check(false, 'Allowed a symlink escape');
        } catch (InvalidArgumentException $expected) {
            // Correctly rejected.
        }
        unlink($base . '/escape');
    }
    echo "DirReader tests passed.\n";
} finally {
    @unlink($base . '/test-file.txt');
    @unlink($base . '/<script>.txt');
    @rmdir($base . '/nested');
    @rmdir($base);
}
