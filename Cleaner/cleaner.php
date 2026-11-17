<?php

declare(strict_types=1);

// Deliberately CLI-only: visiting a web page must never delete files.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    exit("Cleaner is a CLI-only tool.\n");
}

$options = getopt('', ['dir:', 'apply', 'help']);
if (isset($options['help']) || !isset($options['dir']) || !is_string($options['dir'])) {
    echo "Usage: php cleaner.php --dir=/path/to/temp-subdirectory [--apply]\n";
    echo "Dry-run is the default. --apply deletes contents (not the chosen folder).\n";
    exit(isset($options['help']) ? 0 : 2);
}

$tempRoot = realpath(sys_get_temp_dir());
$target = realpath($options['dir']);
if ($tempRoot === false || $target === false || !is_dir($target)) {
    fwrite(STDERR, "Error: The directory does not exist.\n");
    exit(2);
}

// Never touch the system temp root itself or paths outside it.
$compareRoot = PHP_OS_FAMILY === 'Windows' ? strtolower($tempRoot) : $tempRoot;
$compareTarget = PHP_OS_FAMILY === 'Windows' ? strtolower($target) : $target;
$prefix = rtrim($compareRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
if (!str_starts_with($compareTarget, $prefix)) {
    fwrite(STDERR, "Error: Select a subdirectory inside the system temporary folder.\n");
    exit(2);
}

$apply = isset($options['apply']);
$count = 0;
$bytes = 0;
$failures = 0;
try {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($iterator as $entry) {
        $path = $entry->getPathname();
        // Directory symlinks are removed as links, never traversed.
        $isDirectory = $entry->isDir() && !$entry->isLink();
        if (!$isDirectory) {
            $bytes += $entry->isLink() ? 0 : $entry->getSize();
        }
        echo ($apply ? 'Delete: ' : 'Would delete: ') . $path . PHP_EOL;
        $count++;
        if ($apply) {
            $success = $isDirectory ? @rmdir($path) : @unlink($path);
            if (!$success) {
                $failures++;
                fwrite(STDERR, "Failed to remove: $path\n");
            }
        }
    }
} catch (UnexpectedValueException $exception) {
    fwrite(STDERR, "Error scanning directory: {$exception->getMessage()}\n");
    exit(1);
}

printf("%s: %d entries, %d bytes (estimated).\n", $apply ? 'Processed' : 'Dry run', $count, $bytes);
if (!$apply) {
    echo "No files were deleted. Pass --apply to remove the listed items.\n";
}
exit($failures === 0 ? 0 : 1);
