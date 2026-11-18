<?php

declare(strict_types=1);
require_once __DIR__ . '/../shared/safe_http.php';
require_once __DIR__ . '/lookup.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; object-src 'none'; frame-ancestors 'none'; base-uri 'none'");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the Investigator form.');
}
$error = null;
$ip = '';
$details = [];
try {
    if (!is_string($_POST['ip'] ?? null)) {
        throw new InvalidArgumentException('Enter an IP address.');
    }
    $ip = InvestigatorLookup::validatedIp($_POST['ip']);
    $details = InvestigatorLookup::summary(InvestigatorLookup::fetch($ip));
} catch (Throwable $exception) {
    $error = $exception->getMessage();
    http_response_code(422);
}
?>
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="Public IP metadata for network research."><title>IP lookup results · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="investigator"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">◎</span>Investigator</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="index.html">New lookup</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="result-head"><div class="eyebrow">NETWORK CONTEXT / LOOKUP</div><h1>Network data. <span class="highlight">No guesses.</span></h1><p>Approximate public metadata returned by the lookup provider, displayed as a readable technical summary.</p></section>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Lookup unavailable</h2><p><?= demo_escape($error) ?></p><p>Please use a valid IPv4 or IPv6 address and try again.</p></section>
<?php else: ?>
<div class="summary"><div class="stat-card"><span class="stat-label">IP address</span><p class="metric small mono break"><?= demo_escape($ip) ?></p></div><div class="stat-card"><span class="stat-label">Metadata fields</span><p class="metric"><?= count($details) ?></p></div><div class="stat-card"><span class="stat-label">Data classification</span><p class="metric small accent">Approximate</p></div></div>
<section class="data-panel"><div class="section-head"><h2 class="section-title">Returned metadata</h2><span class="pill neutral">Public information</span></div>
<div class="toolbar"><input id="detail-search" type="search" class="search-input" aria-label="Filter metadata fields" placeholder="Find a field or value…" data-filter="metadata-rows"><span class="count" id="metadata-rows-count"></span></div>
<div class="table-scroll"><table><thead><tr><th scope="col">Field</th><th scope="col">Reported value</th></tr></thead><tbody id="metadata-rows">
<?php foreach ($details as $field => $value): if (!is_scalar($value)) continue; ?><tr data-search-item><th scope="row"><?= demo_escape((string) $field) ?></th><td class="break"><?= demo_escape((string) $value) ?></td></tr><?php endforeach; ?>
</tbody></table></div><p class="filter-empty" id="metadata-rows-empty" hidden>No fields match.</p>
</section>
<?php endif; ?>
<section class="notice warning"><h2>Location is not identity</h2><p>IP geolocation is an estimate and should never be used to identify a person or exact address. Requests are sent to the <a href="https://ipwho.is/" target="_blank" rel="noopener noreferrer">ipwho.is</a> service over HTTPS; provider terms apply.</p></section>
<footer class="footer"><span>Investigator · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
