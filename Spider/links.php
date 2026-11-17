<?php

declare(strict_types=1);

final class SpiderLinks
{
    private const SOCIAL_HOSTS = [
        'facebook.com', 'instagram.com', 'x.com', 'twitter.com', 'linkedin.com',
        'youtube.com', 'tiktok.com', 'reddit.com', 'discord.com', 'telegram.org',
        'pinterest.com', 'twitch.tv', 'snapchat.com', 'tumblr.com', 'medium.com',
    ];

    private static function sameDomain(string $host, string $domain): bool
    {
        return $host === $domain || str_ends_with($host, '.' . $domain);
    }

    public static function resolve(string $base, string $href): ?string
    {
        $href = trim(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($href === '' || strlen($href) > 2048 || str_starts_with($href, '#') || preg_match('/[\x00-\x20\x7f\\\\]/', $href)) {
            return null;
        }
        if (preg_match('/^mailto:/i', $href)) {
            $mail = rawurldecode(substr($href, 7));
            $mail = explode('?', $mail, 2)[0];
            return filter_var($mail, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $mail : null;
        }
        $baseParts = parse_url($base);
        if (!is_array($baseParts) || !isset($baseParts['host'])) {
            return null;
        }
        if (str_starts_with($href, '//')) {
            $href = 'https:' . $href;
        }
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $href)) {
            $parsed = parse_url($href);
            if (!is_array($parsed) || strtolower((string) ($parsed['scheme'] ?? '')) !== 'https' || !isset($parsed['host']) || isset($parsed['user']) || isset($parsed['pass'])) {
                return null;
            }
            return preg_replace('/#.*$/s', '', $href);
        }
        $origin = 'https://' . $baseParts['host'];
        if (isset($baseParts['port'])) {
            $origin .= ':' . $baseParts['port'];
        }
        if (str_starts_with($href, '?')) {
            return $origin . ($baseParts['path'] ?? '/') . $href;
        }
        $partial = parse_url($href);
        if ($partial === false) {
            return null;
        }
        $path = $partial['path'] ?? '';
        if (!str_starts_with($path, '/')) {
            $parent = dirname($baseParts['path'] ?? '/');
            $path = rtrim(str_replace('\\', '/', $parent), '/') . '/' . $path;
        }
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                array_pop($segments);
            } elseif ($segment !== '' && $segment !== '.') {
                $segments[] = $segment;
            }
        }
        $suffix = str_ends_with($path, '/') ? '/' : '';
        $normalized = '/' . implode('/', $segments) . $suffix;
        if (isset($partial['query'])) {
            $normalized .= '?' . $partial['query'];
        }
        return $origin . $normalized;
    }

    public static function category(string $url, string $origin): string
    {
        if (str_starts_with($url, 'mailto:')) {
            return 'Email';
        }
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $baseHost = strtolower((string) parse_url($origin, PHP_URL_HOST));
        foreach (self::SOCIAL_HOSTS as $social) {
            if (self::sameDomain($host, $social)) {
                return 'Social';
            }
        }
        return self::sameDomain($host, $baseHost) ? 'Internal' : 'External';
    }

    /** @return list<array{url:string,type:string}> */
    public static function extract(string $html, string $origin, int $limit = 200): array
    {
        if (!class_exists(DOMDocument::class)) {
            throw new RuntimeException('The PHP DOM/XML extension is required for Spider.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new DOMDocument();
            if (!$document->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_NONET | LIBXML_NOWARNING | LIBXML_NOERROR)) {
                throw new RuntimeException('Unable to parse the remote page.');
            }
            $results = [];
            $seen = [];
            foreach ($document->getElementsByTagName('a') as $link) {
                if (!$link->hasAttribute('href')) {
                    continue;
                }
                $resolved = self::resolve($origin, $link->getAttribute('href'));
                if ($resolved === null || isset($seen[$resolved])) {
                    continue;
                }
                $seen[$resolved] = true;
                $results[] = ['url' => $resolved, 'type' => self::category($resolved, $origin)];
                if (count($results) >= $limit) {
                    break;
                }
            }
            return $results;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
