<?php

declare(strict_types=1);
require_once __DIR__ . '/../Spider/links.php';

if (!class_exists(DOMDocument::class)) {
    echo "SKIP: DOM extension not available in this PHP runtime\n";
    exit(0);
}
$html = <<<HTML
<!doctype html><html><body>
<a href="/docs">Docs</a>
<a href="../about">About</a>
<a href="javascript:alert(1)">Bad link</a>
<a href="https://instagram.com.evil.test/trick">Fake social</a>
<a href="https://www.instagram.com/new92">Real social</a>
<a href="mailto:hello@example.com">Email</a>
<a href="/docs">Duplicate</a>
</body></html>
HTML;
$items = SpiderLinks::extract($html, 'https://example.com/pages/index.html');
if (count($items) !== 5) {
    throw new RuntimeException('Expected 5 unique safe links, got ' . count($items));
}
$categories = array_column($items, 'type', 'url');
$expected = [
    'https://example.com/docs' => 'Internal',
    'https://example.com/about' => 'Internal',
    'https://instagram.com.evil.test/trick' => 'External',
    'https://www.instagram.com/new92' => 'Social',
    'mailto:hello@example.com' => 'Email',
];
if ($categories !== $expected) {
    throw new RuntimeException('Incorrect links/categories: ' . var_export($categories, true));
}
if (count(SpiderLinks::extract($html, 'https://example.com/', 2)) !== 2) {
    throw new RuntimeException('Link cap not enforced.');
}
echo "PASS: DOM link extraction, resolution, classification and caps\n";
