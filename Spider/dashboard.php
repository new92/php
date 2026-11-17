<?php

declare(strict_types=1);
require_once __DIR__ . '/../shared/safe_http.php';
require_once __DIR__ . '/links.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'none'");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the local Spider form to submit a URL.');
}
$url = $_POST['url'] ?? null;
$links = [];
$error = null;
if (!is_string($url)) {
    $error = 'Enter an HTTPS URL.';
} else {
    try {
        $target = trim($url);
        $resp = DemoSafeHttp::fetch($target, false, 524288);
        $type = strtolower(implode(', ', $resp['headers']['content-type'] ?? []));
        if ($type !== '' && !str_contains($type, 'text/html') && !str_contains($type, 'application/xhtml+xml')) {
            throw new RuntimeException('The remote resource is not an HTML page.');
        }
        $links = SpiderLinks::extract($resp['body'], $target, 200);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
if ($error !== null) { http_response_code(422); }
$counts = array_count_values(array_column($links, 'type'));
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Results · Spider</title><link rel="stylesheet" href="static/ui.css"></head>
<body><main class="container">
<header class="nav"><a class="brand" href="input.html">◉ Spider</a><a href="input.html">New inspection ↗</a></header>
<div class="eyebrow">Single-page link analysis · local only</div><h1>Links, organized.<br><span class="muted">Clearly and safely.</span></h1>
<p class="lead">Read up to 200 unique links from one approved HTML page. Spider does not recursively crawl, follow redirects, or access arbitrary hosts.</p>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Unable to inspect page</h2><p><?= demo_escape($error) ?></p><p>Set <code>PHP_DEMO_ALLOWED_HOSTS</code> to the exact public hostname before running this demo.</p></section>
<?php else: ?>
<section class="summary"><div><span class="label">Scanned URL</span><p class="mono break"><?= demo_escape($url) ?></p></div><div><span class="label">Unique links</span><p class="metric"><?= count($links) ?></p></div><div><span class="label">Internal / external</span><p class="metric"><?= (int) ($counts['Internal'] ?? 0) ?> / <?= (int) ($counts['External'] ?? 0) ?></p></div></section>
<section class="notice"><h2>Classification notes</h2><p>Social links and email addresses are counted separately. Internal means the same hostname or its subdomains; links are extracted from a single page, not followed. Only HTTPS links and valid email addresses are displayed.</p></section>
<section><h2>Extracted links</h2><div class="table-scroll"><table><thead><tr><th scope="col">Category</th><th scope="col">Destination</th></tr></thead><tbody>
<?php foreach ($links as $link): ?>
<tr><td><span class="pill"><?= demo_escape($link['type']) ?></span></td><td class="break">
<?php if ($link['type'] === 'Email'): ?><?= demo_escape(substr($link['url'], 7)) ?>
<?php else: ?><a target="_blank" rel="noopener noreferrer nofollow" href="<?= demo_escape($link['url']) ?>"><?= demo_escape($link['url']) ?></a><?php endif; ?>
</td></tr>
<?php endforeach; ?>
</tbody></table></div></section>
<?php endif; ?>
<footer>Educational demonstration · one HTTPS request · no recursive crawling</footer>
</main></body></html>
