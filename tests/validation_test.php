<?php

declare(strict_types=1);
require_once __DIR__ . '/../Investigator/lookup.php';
require_once __DIR__ . '/../WeatherApp/forecast.php';

function expect(bool $yes, string $why): void
{
    if (!$yes) { throw new RuntimeException('FAILED: ' . $why); }
}
function mustReject(callable $fn): void
{
    try { $fn(); } catch (InvalidArgumentException $e) { return; }
    throw new RuntimeException('Expected invalid input to be rejected');
}
expect(InvestigatorLookup::validatedIp('8.8.8.8') === '8.8.8.8', 'IPv4 accepted');
expect(InvestigatorLookup::validatedIp('2001:4860:4860::8888') === '2001:4860:4860::8888', 'IPv6 accepted');
foreach (['8.8.8.8/24', 'anything', '8.8.8.8\nX', 'https://test.example/'] as $bad) {
    mustReject(static fn () => InvestigatorLookup::validatedIp($bad));
}
expect(WeatherForecast::validateCity('Thessaloniki, Greece') === 'Thessaloniki, Greece', 'city accepted');
expect(WeatherForecast::validateCity('San José') === 'San José', 'UTF-8 city accepted');
foreach (['', '<script>', '/etc/passwd', str_repeat('a', 86), "X\r\nHeader: y"] as $bad) {
    mustReject(static fn () => WeatherForecast::validateCity($bad));
}
$summary = InvestigatorLookup::summary(['ip' => '1.1.1.1', 'country' => 'Australia', 'connection' => ['isp' => 'Example ISP']]);
expect($summary['ISP'] === 'Example ISP', 'nested fields summarized');
echo "PASS: IP and city validation\n";
