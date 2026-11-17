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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Inspection · Protector</title><link rel="stylesheet" href="static/ui.css"></head>
<body><main class="container">
<header class="nav"><a class="brand" href="input.html">✳ Protector</a><a href="input.html">New inspection ↗</a></header>
<div class="eyebrow">Experimental response inspector · local only</div>
<h1>Inspect the response.<br><span class="muted">Not the reputation.</span></h1>
<p class="lead">Protector observes HTTPS response metadata for a pre-approved host. This is <strong>not a phishing detector or website safety verdict</strong>.</p>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Inspection unavailable</h2><p><?= demo_escape($error) ?></p><p>Configure <code>PHP_DEMO_ALLOWED_HOSTS</code> with the exact hostname to use this local demo.</p></section>
<?php else: ?>
<section class="summary">
<div><span class="label">URL inspected</span><p class="mono break"><?= demo_escape($url) ?></p></div>
<div><span class="label">HTTP status</span><p class="metric"><?= (int) $result['status'] ?></p></div>
<div><span class="label">Connected public IP</span><p class="mono"><?= demo_escape($result['ip']) ?></p></div>
</section>
<section class="notice"><h2>Important limitation</h2><p>A successful HTTPS response does not prove legitimacy. Many phishing sites use valid TLS certificates and public hosting. No remote HTML was fetched or executed.</p></section>
<section><h2>Response headers</h2><div class="table-scroll"><table><thead><tr><th>Header</th><th>Value</th></tr></thead><tbody>
<?php foreach ($headerRows as $name => $values): ?>
<tr><th scope="row"><?= demo_escape($name) ?></th><td class="break"><?= demo_escape(implode(', ', $values)) ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></section>
<?php endif; ?>
<footer>Educational demonstration · HTTPS verification enabled · redirects blocked · no safety certification</footer>
</main></body></html>
