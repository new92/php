<?php

declare(strict_types=1);
require_once __DIR__ . '/../shared/safe_http.php';
require_once __DIR__ . '/forecast.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'none'");
$city = '';
$days = [];
$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        if (!is_string($_POST['city'] ?? null)) {
            throw new InvalidArgumentException('Enter a city name.');
        }
        $city = WeatherForecast::validateCity($_POST['city']);
        $days = WeatherForecast::fetch($city);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
        http_response_code(422);
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Minimal weather forecast built with PHP."><title>WeatherApp · Forecast viewer</title><link rel="stylesheet" href="static/ui.css"></head><body><main class="container">
<header class="nav"><span class="brand">☼ WeatherApp</span><span class="label">Local forecast demo</span></header>
<div class="eyebrow">Weather outlook</div><h1>Clear information.<br><span class="muted">Whatever the weather.</span></h1>
<p class="lead">Search for a city and see a simple five-day forecast in metric units. Uses the Visual Crossing Timeline Weather API.</p>
<form method="post" action="index.php" class="form-card"><label for="city">City or place</label><input id="city" name="city" type="text" placeholder="Thessaloniki, Greece" maxlength="85" value="<?= demo_escape($city) ?>" required><p class="hint">A Visual Crossing API key must be configured in the server environment. It is never shown in this page.</p><button type="submit">Get forecast ↗</button></form>
<?php if ($error !== null): ?><section class="notice error" role="alert"><h2>Forecast unavailable</h2><p><?= demo_escape($error) ?></p></section><?php endif; ?>
<?php if ($days !== []): ?><section><h2>Five-day forecast · <?= demo_escape($city) ?></h2><div class="forecast-grid">
<?php foreach ($days as $day): if (!is_array($day)) continue; ?>
<article class="forecast"><div class="label"><?= demo_escape(is_scalar($day['datetime'] ?? null) ? $day['datetime'] : '') ?></div><h3><?= demo_escape(is_scalar($day['conditions'] ?? null) ? $day['conditions'] : 'Conditions unknown') ?></h3><p class="metric"><?= is_numeric($day['tempmax'] ?? null) ? demo_escape(round((float) $day['tempmax'])) . '°' : '—' ?></p><p class="hint">Low: <?= is_numeric($day['tempmin'] ?? null) ? demo_escape(round((float) $day['tempmin'])) . '°C' : 'unknown' ?></p></article>
<?php endforeach; ?></div></section><?php endif; ?>
<section class="notice"><h2>Forecasts change</h2><p>Weather forecasts are estimates, not guarantees. Check your local weather authority before making safety-sensitive decisions.</p></section><footer>Weather data: Visual Crossing · independent educational demo</footer>
</main></body></html>
