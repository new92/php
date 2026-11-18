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
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="Five-day local-first weather forecast."><title>Weather forecast · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="weather"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true">☀</span>WeatherApp</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="index.php">New forecast</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="hero hero-tight"><div><div class="eyebrow">07 / WEATHER OUTLOOK</div><h1>Plan with a little <span class="highlight">clarity.</span></h1><p>View the next five forecast days for any city, in familiar metric units. Data is supplied by Visual Crossing.</p><div class="chips"><span class="chip">Five-day outlook</span><span class="chip">°C metric units</span><span class="chip">Server-side API key</span></div></div><aside class="hero-visual" aria-label="Forecast overview"><div class="visual-top"><span class="visual-code">WEATHER / FIVE DAYS</span><span class="visual-dot"></span></div><div class="visual-glyph" aria-hidden="true">☀</div><div class="visual-lines"><div class="visual-row"><span>Provider</span><strong>Visual Crossing</strong></div><div class="visual-row"><span>Forecast units</span><strong>Celsius</strong></div><div class="visual-row"><span>Time span</span><strong>5 days</strong></div></div></aside></section>
<section class="workspace"><form class="form-card" action="index.php" method="post" data-submit-state><div class="form-header"><h2>Find your forecast</h2><span class="form-index">01 / CITY</span></div><label class="field" for="city">City or location</label><input class="input" id="city" name="city" type="text" placeholder="Thessaloniki, Greece" maxlength="85" value="<?= demo_escape($city) ?>" required autocomplete="off"><p class="hint">A Visual Crossing API key must be configured on your server. It is not displayed in this page.</p><button class="button full" type="submit">Show five-day forecast ↗</button></form>
<aside class="card"><div class="form-header"><h2>At a glance</h2><span class="form-index">02 / CONTEXT</span></div><div class="mini-steps"><div class="mini-step"><span class="step-index">01</span><div><strong>Search a location</strong><p>Use a city name, country or other recognizable place.</p></div></div><div class="mini-step"><span class="step-index">02</span><div><strong>Compare the days</strong><p>Review high and low temperatures with reported conditions.</p></div></div><div class="mini-step"><span class="step-index">03</span><div><strong>Stay informed</strong><p>Forecast conditions can change. For safety decisions, use official advisories.</p></div></div></div></aside></section>
<?php if ($error !== null): ?><section class="notice error" role="alert"><h2>Forecast unavailable</h2><p><?= demo_escape($error) ?></p></section><?php endif; ?>
<?php if ($days !== []): ?><section class="section"><div class="section-head"><h2 class="section-title">Five-day outlook for <?= demo_escape($city) ?></h2><span class="pill">°C</span></div><div class="forecast-grid">
<?php foreach ($days as $day): if (!is_array($day)) continue;
    $condition = is_scalar($day['conditions'] ?? null) ? (string) $day['conditions'] : 'Conditions unavailable';
    $lower = strtolower($condition);
    $symbol = str_contains($lower, 'rain') || str_contains($lower, 'storm') ? '☂' : (str_contains($lower, 'cloud') || str_contains($lower, 'overcast') ? '☁' : (str_contains($lower, 'snow') ? '❄' : '☀'));
    $high = is_numeric($day['tempmax'] ?? null) ? round((float) $day['tempmax']) . '°C' : '—';
    $low = is_numeric($day['tempmin'] ?? null) ? round((float) $day['tempmin']) . '°C' : '—';
?>
<article class="forecast-card"><div class="date"><?= demo_escape(is_scalar($day['datetime'] ?? null) ? (string) $day['datetime'] : 'Unknown date') ?></div><span class="forecast-symbol" aria-hidden="true"><?= $symbol ?></span><h3><?= demo_escape($condition) ?></h3><div class="temp"><?= demo_escape($high) ?></div><div class="low">Low <?= demo_escape($low) ?></div></article>
<?php endforeach; ?></div></section><?php endif; ?>
<section class="notice warning"><h2>Forecasts are estimates</h2><p>Forecast values change over time and do not replace official weather alerts. Check your local weather authority when conditions affect safety.</p></section>
<footer class="footer"><span>WeatherApp · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
