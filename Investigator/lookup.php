<?php

declare(strict_types=1);

final class InvestigatorLookup
{
    public static function validatedIp(string $input): string
    {
        $ip = trim($input);
        if (strlen($ip) > 45 || !filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Enter a valid IPv4 or IPv6 address.');
        }
        return $ip;
    }

    public static function fetch(string $ip): array
    {
        $ip = self::validatedIp($ip);
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required.');
        }
        $curl = curl_init('https://ipwho.is/' . rawurlencode($ip));
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize IP lookup.');
        }
        $body = '';
        $max = 128 * 1024;
        try {
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'new92-investigator/2.0',
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
            if ($ok === false || $status !== 200) {
                throw new RuntimeException('IP lookup service is unavailable or rate limited.');
            }
            $data = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
            if (!is_array($data) || ($data['success'] ?? null) === false) {
                throw new RuntimeException('The lookup provider could not return data for this IP.');
            }
            return $data;
        } catch (JsonException $error) {
            throw new RuntimeException('The lookup provider returned invalid JSON.');
        } finally {
            curl_close($curl);
        }
    }

    public static function summary(array $response): array
    {
        return [
            'IP address' => $response['ip'] ?? null,
            'IP type' => $response['type'] ?? null,
            'Continent' => $response['continent'] ?? null,
            'Country' => $response['country'] ?? null,
            'Region' => $response['region'] ?? null,
            'City' => $response['city'] ?? null,
            'Latitude' => $response['latitude'] ?? null,
            'Longitude' => $response['longitude'] ?? null,
            'ISP' => $response['connection']['isp'] ?? null,
            'ASN' => $response['connection']['asn'] ?? null,
        ];
    }
}
