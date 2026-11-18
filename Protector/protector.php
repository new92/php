<?php
declare(strict_types=1);
require_once __DIR__ . '/../shared/safe_http.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'none'");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Submit a URL using the local form.');
}
$url = $_POST['url'] ?? null;
$error = null;
$result = null;
if (!is_string($url)) {
    $error = 'Enter an HTTPS URL.';
} else {
    try {
        $result = DemoSafeHttp::fetch(trim($url), true);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}
if ($error !== null) {http_response_code(422);}
$headerRows = $result['headers'] ?? [];
ksort($headerRows);
?>
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="HTTP response headers for authorized network analysis."><title>HTTP inspection results · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="protector"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="input.html"><span class="brand-mark" aria-hidden="true">✳</span>Protector</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="input.html">New inspection</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="result-head"><div class="eyebrow">HTTPS OBSERVABILITY / INSPECTION</div><h1>Response <span class="highlight">details.</span></h1><p>Request metadata from an explicitly authorized public HTTPS host. A response is not proof that the site is safe or legitimate.</p></section>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Inspection unavailable</h2><p><?= demo_escape($error) ?></p><p>Set <code>PHP_DEMO_ALLOWED_HOSTS</code> to the exact hostname on the server, then retry.</p></section>
<?php else: ?>
<div class="summary"><div class="stat-card"><span class="stat-label">HTTP status</span><p class="metric accent"><?= (int) $result['status'] ?></p></div><div class="stat-card"><span class="stat-label">Connected IP</span><p class="metric small mono break"><?= demo_escape($result['ip']) ?></p></div><div class="stat-card"><span class="stat-label">Headers returned</span><p class="metric"><?= count($headerRows) ?></p></div></div>
<section class="data-panel"><h2 class="section-title">Inspected address</h2><p class="mono break"><?= demo_escape((string) $url) ?></p></section>
<section class="data-panel"><div class="section-head"><h2 class="section-title">Response headers</h2><span class="pill neutral">Read-only metadata</span></div><div class="toolbar"><input class="search-input" type="search" id="header-search" placeholder="Filter headers or values…" aria-label="Filter response headers" data-filter="header-rows"><span id="header-rows-count" class="count"></span></div>
<div class="table-scroll"><table><thead><tr><th scope="col">Header</th><th scope="col">Value</th></tr></thead><tbody id="header-rows">
<?php foreach ($headerRows as $name => $values): ?><tr data-search-item><th scope="row" class="mono break"><?= demo_escape((string) $name) ?></th><td class="break"><?= demo_escape(implode(', ', $values)) ?></td></tr><?php endforeach; ?>
</tbody></table></div><p class="filter-empty" id="header-rows-empty" hidden>No matching headers.</p>
</section>
<?php endif; ?>
<section class="notice warning"><h2>This is not a website safety verdict</h2><p>HTTPS, public hosting and response headers cannot prove whether content is malicious. Protector does not execute remote HTML or assign a phishing score.</p></section>
<footer class="footer"><span>Protector · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
