<?php

declare(strict_types=1);

final class WeatherForecast
{
    public static function validateCity(string $city): string
    {
        $city = trim($city);
        if ($city === '' || mbSafeLength($city) > 85 || !preg_match('/^[\p{L}\p{N}\s.,\x27\-]{1,85}$/uD', $city)) {
            throw new InvalidArgumentException('Enter a city name of up to 85 letters, numbers or ordinary punctuation.');
        }
        return $city;
    }

    public static function fetch(string $city): array
    {
        $city = self::validateCity($city);
        $key = getenv('VISUAL_CROSSING_API_KEY');
        if (!is_string($key) || trim($key) === '') {
            throw new RuntimeException('Set VISUAL_CROSSING_API_KEY before starting the PHP server.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required.');
        }
        $url = 'https://weather.visualcrossing.com/VisualCrossingWebServices/rest/services/timeline/'
            . rawurlencode($city) . '?' . http_build_query([
                'unitGroup' => 'metric', 'include' => 'days', 'contentType' => 'json',
                'key' => $key,
            ], '', '&', PHP_QUERY_RFC3986);
        $curl = curl_init($url);
        if ($curl === false) { throw new RuntimeException('Unable to create weather request.'); }
        $body = '';
        $max = 1024 * 1024;
        try {
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT => 12,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'new92-weather-app/2.0',
                CURLOPT_WRITEFUNCTION => static function ($resource, string $chunk) use (&$body, $max): int {
                    if (strlen($body) + strlen($chunk) > $max) { return 0; }
                    $body .= $chunk;
                    return strlen($chunk);
                },
            ]);
            if (defined('CURLOPT_PROTOCOLS_STR')) {
                curl_setopt($curl, CURLOPT_PROTOCOLS_STR, 'https');
            } else {
                curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            }
            $ok = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            if ($ok === false) {
                throw new RuntimeException('Could not complete weather request.');
            }
            if ($status !== 200) {
                throw new RuntimeException($status === 401 || $status === 403
                    ? 'The weather API key was rejected or its quota was exceeded.'
                    : 'The weather service returned HTTP ' . $status . '.');
            }
            $data = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
            if (!is_array($data) || !isset($data['days']) || !is_array($data['days'])) {
                throw new RuntimeException('The weather service returned an unexpected result.');
            }
            return array_slice($data['days'], 0, 5);
        } catch (JsonException $exception) {
            throw new RuntimeException('The weather service returned invalid JSON.');
        } finally {
            curl_close($curl);
        }
    }
}

// Does not require mbstring; preg_match above handles Unicode validation.
function mbSafeLength(string $s): int
{
    return preg_match_all('/./us', $s) ?: 0;
}
