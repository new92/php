<?php

declare(strict_types=1);

/**
 * List only immediate children of a directory inside an allowed root.
 * Paths are canonicalized so ../ and symlink escapes cannot bypass the root.
 *
 * @return array{path: string, entries: list<array{name: string, type: string}>, files: int, directories: int}
 */
function readDirectory(string $root, string $requested): array
{
    $base = realpath($root);
    if ($base === false || !is_dir($base)) {
        throw new RuntimeException('The configured root directory is unavailable.');
    }

    $requested = trim($requested);
    if ($requested === '' || str_contains($requested, "\0")) {
        throw new InvalidArgumentException('Enter a valid directory.');
    }

    $candidate = realpath($base . DIRECTORY_SEPARATOR . $requested);
    if ($candidate === false || !is_dir($candidate)) {
        throw new InvalidArgumentException('Directory not found.');
    }

    // Canonical path comparison, with the directory separator as a boundary.
    $compareBase = PHP_OS_FAMILY === 'Windows' ? strtolower($base) : $base;
    $compareCandidate = PHP_OS_FAMILY === 'Windows' ? strtolower($candidate) : $candidate;
    $prefix = rtrim($compareBase, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if ($compareCandidate !== $compareBase && !str_starts_with($compareCandidate, $prefix)) {
        throw new InvalidArgumentException('Directory is outside the allowed root.');
    }

    $names = scandir($candidate);
    if ($names === false) {
        throw new RuntimeException('Unable to read directory.');
    }

    $entries = [];
    $files = 0;
    $directories = 0;
    foreach ($names as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $path = $candidate . DIRECTORY_SEPARATOR . $name;
        if (is_link($path)) {
            $type = 'symbolic link'; // Do not follow links during classification.
        } elseif (is_dir($path)) {
            $type = 'directory';
            $directories++;
        } else {
            $type = 'file';
            $files++;
        }
        $entries[] = ['name' => $name, 'type' => $type];
    }

    return ['path' => $candidate, 'entries' => $entries, 'files' => $files, 'directories' => $directories];
}
