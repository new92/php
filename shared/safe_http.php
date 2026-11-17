<?php

declare(strict_types=1);

/**
 * Safe, intentionally constrained HTTP client for educational demos.
 * Never expose a public proxy/crawler without authentication, quotas and egress controls.
 */
final class DemoSafeHttp
{
    public static function allowedHosts(): array
    {
        $raw = getenv('PHP_DEMO_ALLOWED_HOSTS');
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        return array_values(array_unique(array_filter(array_map(
            static fn (string $item): string => strtolower(trim($item)),
            explode(',', $raw)
        ))));
    }

    public static function validateTarget(string $url, array $allowedHosts): array
    {
        if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f\\\\]/', $url)) {
            throw new InvalidArgumentException('Enter a valid HTTPS URL without spaces or control characters.');
        }
        $parsed = parse_url($url);
        if (!is_array($parsed) || strtolower((string) ($parsed['scheme'] ?? '')) !== 'https') {
            throw new InvalidArgumentException('Only HTTPS URLs are allowed.');
        }
        if (isset($parsed['user'], $parsed['pass']) || isset($parsed['user']) || isset($parsed['fragment'])) {
            throw new InvalidArgumentException('Credentials and URL fragments are not allowed.');
        }
        $host = strtolower((string) ($parsed['host'] ?? ''));
        if (strlen($host) > 253 || !preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/D', $host)) {
            throw new InvalidArgumentException('Enter a normal public DNS hostname, not an IP address or local hostname.');
        }
        if (isset($parsed['port']) && $parsed['port'] !== 443) {
            throw new InvalidArgumentException('Only standard HTTPS port 443 is supported.');
        }
        $allowed = array_map('strtolower', $allowedHosts);
        if (!in_array($host, $allowed, true)) {
            throw new InvalidArgumentException('Host is not in PHP_DEMO_ALLOWED_HOSTS. Contact the local administrator.');
        }
        return ['url' => $url, 'host' => $host];
    }

    public static function publicIpv4(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return false;
        }
        $num = ip2long($ip);
        if ($num === false) {
            return false;
        }
        $u = sprintf('%u', $num);
        // Additional special-purpose ranges (including shared carrier and benchmarking networks).
        foreach ([['100.64.0.0', 10], ['198.18.0.0', 15], ['192.0.0.0', 24], ['192.0.2.0', 24], ['198.51.100.0', 24], ['203.0.113.0', 24], ['240.0.0.0', 4], ['0.0.0.0', 8]] as [$net, $prefix]) {
            $mask = (0xFFFFFFFF << (32 - $prefix)) & 0xFFFFFFFF;
            if ((((int) $u) & $mask) === ((int) sprintf('%u', ip2long($net)) & $mask)) {
                return false;
            }
        }
        return true;
    }

    /** @return list<string> */
    public static function resolvePublicIpv4(string $host): array
    {
        $addresses = gethostbynamel($host);
        if (!is_array($addresses) || $addresses === []) {
            throw new RuntimeException('Unable to resolve target hostname to a public IPv4 address.');
        }
        $addresses = array_values(array_unique($addresses));
        foreach ($addresses as $ip) {
            if (!self::publicIpv4($ip)) {
                throw new RuntimeException('Target resolved to a private, reserved or unsafe address.');
            }
        }
        return $addresses;
    }

    /** @return array{status:int,headers:array<string,list<string>>,body:string,ip:string} */
    public static function fetch(string $url, bool $headersOnly = false, int $maxBytes = 524288): array
    {
        $target = self::validateTarget($url, self::allowedHosts());
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP curl extension must be installed and enabled.');
        }
        $ipAddresses = self::resolvePublicIpv4($target['host']);
        $curl = curl_init();
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize HTTPS request.');
        }
        $headers = [];
        $body = '';
        $headerBytes = 0;
        $tooLarge = false;
        $resolve = array_map(
            static fn (string $ip): string => $target['host'] . ':443:' . $ip,
            $ipAddresses
        );
        try {
            curl_setopt_array($curl, [
                CURLOPT_URL => $target['url'],
                CURLOPT_RESOLVE => $resolve,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_PROXY => '',
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS => 0,
                CURLOPT_NOSIGNAL => true,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT => 'new92-php-demos/2.0 (local educational client)',
                CURLOPT_NOBODY => $headersOnly,
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_HEADERFUNCTION => static function ($resource, string $line) use (&$headers, &$headerBytes): int {
                    $headerBytes += strlen($line);
                    if ($headerBytes > 16384) {
                        return 0;
                    }
                    if (preg_match('/^HTTP\/\S+\s+\d{3}/i', $line)) {
                        $headers = []; // Don't combine intermediate headers.
                    }
                    if (preg_match('/^([a-z0-9-]{1,64}):\s*(.*)\r?\n?$/i', $line, $m)) {
                        $name = strtolower($m[1]);
                        if (!in_array($name, ['set-cookie', 'set-cookie2', 'authorization', 'proxy-authenticate'], true)) {
                            $headers[$name][] = trim($m[2]);
                        }
                    }
                    return strlen($line);
                },
                CURLOPT_WRITEFUNCTION => static function ($resource, string $chunk) use (&$body, &$tooLarge, $maxBytes): int {
                    if (strlen($body) + strlen($chunk) > $maxBytes) {
                        $tooLarge = true;
                        return 0;
                    }
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
            $ip = (string) curl_getinfo($curl, CURLINFO_PRIMARY_IP);
            if ($ok === false) {
                throw new RuntimeException($tooLarge ? 'Response exceeded size limit.' : 'HTTPS request failed or timed out.');
            }
            if (!in_array($ip, $ipAddresses, true)) {
                throw new RuntimeException('Network response did not originate from the validated address.');
            }
            if ($status >= 300 && $status < 400) {
                throw new RuntimeException('Redirects are blocked. Inspect the destination separately after allowlisting it.');
            }
            if ($status < 200 || $status >= 400) {
                throw new RuntimeException('Remote server returned HTTP ' . $status . '.');
            }
            return ['status' => $status, 'headers' => $headers, 'body' => $body, 'ip' => $ip];
        } finally {
            curl_close($curl);
        }
    }
}

function demo_escape(string|int|float|null $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
