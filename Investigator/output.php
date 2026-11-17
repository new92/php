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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Results · Investigator</title><link rel="stylesheet" href="static/ui.css"></head><body><main class="container">
<header class="nav"><a class="brand" href="index.html">◎ Investigator</a><a href="index.html">New lookup ↗</a></header>
<div class="eyebrow">Public network metadata</div><h1>IP intelligence.<br><span class="muted">With context.</span></h1>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Lookup unavailable</h2><p><?= demo_escape($error) ?></p></section>
<?php else: ?>
<p class="lead mono"><?= demo_escape($ip) ?></p><div class="table-scroll"><table><tbody><?php foreach ($details as $field => $value): if (!is_scalar($value)) continue; ?><tr><th scope="row"><?= demo_escape($field) ?></th><td><?= demo_escape((string) $value) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<section class="notice"><h2>Approximate information</h2><p>IP geolocation is approximate and does not establish an individual's physical location or identity. This request sends the entered IP to <a href="https://ipwhois.io/" target="_blank" rel="noopener noreferrer">ipwho.is</a> over HTTPS; the provider's usage terms and limits apply.</p></section>
<footer>Local educational demo · public IP metadata is not proof of identity</footer></main></body></html>
