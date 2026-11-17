<?php

declare(strict_types=1);

final class GitspyApi
{
    public static function validateUsername(string $username): string
    {
        $username = trim($username);
        // GitHub account names use ASCII alphanumerics and interior hyphens.
        if (!preg_match('/^(?=.{1,39}$)[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/D', $username)) {
            throw new InvalidArgumentException('Enter a valid GitHub username (1–39 characters).');
        }
        return $username;
    }

    /** @return array<string,mixed>|list<mixed> */
    public static function fetch(string $endpoint): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required.');
        }
        if (!preg_match('~^/users/[a-zA-Z0-9-]{1,39}(?:/(?:repos|followers|following))?(?:\?per_page=30)?$~D', $endpoint)) {
            throw new InvalidArgumentException('Invalid API route.');
        }
        $body = '';
        $oversize = false;
        $curl = curl_init('https://api.github.com' . $endpoint);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize GitHub API client.');
        }
        $headers = ['Accept: application/vnd.github+json', 'X-GitHub-Api-Version: 2022-11-28'];
        $token = getenv('GITHUB_TOKEN');
        if (is_string($token) && $token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
        try {
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => false,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_USERAGENT => 'new92-gitspy/2.0',
                CURLOPT_WRITEFUNCTION => static function ($resource, string $chunk) use (&$body, &$oversize): int {
                    if (strlen($body) + strlen($chunk) > 1024 * 1024) {
                        $oversize = true;
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
            if ($ok === false || $oversize) {
                throw new RuntimeException('Unable to receive a complete response from GitHub.');
            }
            if ($status === 404) {
                throw new RuntimeException('GitHub user was not found.');
            }
            if ($status === 403 || $status === 429) {
                throw new RuntimeException('GitHub API rate limit or access restriction encountered. Try later or configure GITHUB_TOKEN.');
            }
            if ($status !== 200) {
                throw new RuntimeException('GitHub API returned HTTP ' . $status . '.');
            }
            $data = json_decode($body, true, 64, JSON_THROW_ON_ERROR);
            if (!is_array($data)) {
                throw new RuntimeException('GitHub returned an unexpected response.');
            }
            return $data;
        } catch (JsonException $exception) {
            throw new RuntimeException('GitHub returned invalid JSON.');
        } finally {
            curl_close($curl);
        }
    }

    public static function escape(mixed $value): string
    {
        if (is_bool($value)) {
            $value = $value ? 'Yes' : 'No';
        }
        return htmlspecialchars(is_scalar($value) ? (string) $value : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function profileUrl(string $username): string
    {
        return 'https://github.com/' . rawurlencode(self::validateUsername($username));
    }
}
