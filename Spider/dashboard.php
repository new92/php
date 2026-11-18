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
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="Categorized links from one authorized page."><title>Link analysis results · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="spider"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="input.html"><span class="brand-mark" aria-hidden="true">◈</span>Spider</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="input.html">Analyze another page</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="result-head"><div class="eyebrow">LINK INTELLIGENCE / ANALYSIS</div><h1>A page of links. <span class="highlight">Organized.</span></h1><p>Results come from one permitted HTML page. Spider does not recurse into linked destinations or follow redirects.</p></section>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Unable to analyze this page</h2><p><?= demo_escape($error) ?></p><p>Use a public HTTPS URL whose hostname you have approved in <code>PHP_DEMO_ALLOWED_HOSTS</code>.</p></section>
<?php else: ?>
<div class="summary"><div class="stat-card"><span class="stat-label">Total links</span><p class="metric accent"><?= count($links) ?></p></div><div class="stat-card"><span class="stat-label">Internal links</span><p class="metric"><?= (int) ($counts['Internal'] ?? 0) ?></p></div><div class="stat-card"><span class="stat-label">External links</span><p class="metric"><?= (int) ($counts['External'] ?? 0) ?></p></div></div>
<section class="data-panel"><h2 class="section-title">Scanned page</h2><p class="mono break"><?= demo_escape((string) $url) ?></p><div class="chips"><span class="chip">Social <?= (int) ($counts['Social'] ?? 0) ?></span><span class="chip">Email <?= (int) ($counts['Email'] ?? 0) ?></span><span class="chip">Maximum 200 unique URLs</span></div></section>
<section class="data-panel"><div class="section-head"><h2 class="section-title">Extracted links</h2><span class="pill neutral">Not followed</span></div><div class="toolbar"><input class="search-input" id="link-search" type="search" placeholder="Filter links or categories…" aria-label="Filter extracted links" data-filter="links-list"><span class="count" id="links-list-count"></span></div>
<div class="table-scroll"><table><thead><tr><th scope="col">Type</th><th scope="col">Destination</th></tr></thead><tbody id="links-list">
<?php foreach ($links as $link): ?><tr data-search-item><td><span class="pill"><?= demo_escape($link['type']) ?></span></td><td class="break"><?php if ($link['type'] === 'Email'): ?><span class="mono"><?= demo_escape(substr($link['url'], 7)) ?></span><?php else: ?><a href="<?= demo_escape($link['url']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= demo_escape($link['url']) ?> ↗</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div><p class="filter-empty" id="links-list-empty" hidden>No links match your filter.</p>
<?php if ($links === []): ?><p class="empty-state">No supported links were found on this page.</p><?php endif; ?>
</section>
<?php endif; ?>
<section class="notice warning"><h2>Classification is descriptive, not a trust assessment</h2><p>Internal indicates same host or subdomain; social and email links are classified separately. Spider does not confirm the safety of linked destinations.</p></section>
<footer class="footer"><span>Spider · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
