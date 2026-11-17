<?php

declare(strict_types=1);
require_once __DIR__ . '/../shared/safe_http.php';
require_once __DIR__ . '/../Spider/links.php';
require_once __DIR__ . '/../Gitspy/github_api.php';

function assertCheck(bool $value, string $message): void
{
    if (!$value) {
        throw new RuntimeException('FAILED: ' . $message);
    }
}

function rejects(callable $test, string $label): void
{
    try {
        $test();
    } catch (InvalidArgumentException $exception) {
        return;
    }
    throw new RuntimeException('FAILED: accepted ' . $label);
}

assertCheck(DemoSafeHttp::allowedHosts() === [], 'no configured hosts allowed by default');
$allow = ['github.com', 'www.example.com'];
foreach ([
    'http://github.com/', 'https://127.0.0.1', 'https://localhost/',
    'https://github.com.evil.test/', 'https://github.com@evil.com/',
    'https://github.com:8443/', 'https://github.com\\@127.0.0.1/',
    'https://github.com/#fragment', 'https://github.com/hi\nbye',
    'file:///etc/passwd', 'https://[::1]/', 'https://169.254.169.254/latest/',
] as $bad) {
    rejects(static fn () => DemoSafeHttp::validateTarget($bad, $allow), $bad);
}
$valid = DemoSafeHttp::validateTarget('https://github.com/new92/php', $allow);
assertCheck($valid['host'] === 'github.com', 'valid exact host');
foreach (['127.0.0.1', '192.168.1.1', '10.10.10.10', '169.254.169.254', '100.64.0.1', '198.18.0.4', '192.0.2.11', '203.0.113.4', '0.0.0.0'] as $ip) {
    assertCheck(!DemoSafeHttp::publicIpv4($ip), 'blocked IP ' . $ip);
}
assertCheck(DemoSafeHttp::publicIpv4('1.1.1.1'), 'accept public address');
assertCheck(DemoSafeHttp::publicIpv4('8.8.8.8'), 'accept public DNS');

assertCheck(SpiderLinks::resolve('https://example.com/dir/page.html', '../about') === 'https://example.com/about', 'relative URL resolution');
assertCheck(SpiderLinks::resolve('https://example.com/dir/page.html', '/a?b=2#x') === 'https://example.com/a?b=2', 'absolute URL and fragment');
assertCheck(SpiderLinks::resolve('https://example.com/dir/page.html', '?a=1') === 'https://example.com/dir/page.html?a=1', 'query-only URL');
assertCheck(SpiderLinks::resolve('https://example.com/', 'javascript:alert(1)') === null, 'block JS links');
assertCheck(SpiderLinks::resolve('https://example.com/', 'data:text/html,evil') === null, 'block data links');
assertCheck(SpiderLinks::resolve('https://example.com/', 'mailto:hello@example.com') === 'mailto:hello@example.com', 'email category');
assertCheck(SpiderLinks::category('https://example.com/x', 'https://example.com/y') === 'Internal', 'internal same domain');
assertCheck(SpiderLinks::category('https://evil-example.com/', 'https://example.com/') === 'External', 'no substring host matching');
assertCheck(SpiderLinks::category('https://instagram.com.evil.org/', 'https://example.com/') === 'External', 'social domain boundary');
assertCheck(SpiderLinks::category('https://www.instagram.com/a', 'https://example.com/') === 'Social', 'social category');
assertCheck(str_contains(demo_escape('<script>'), '&lt;script&gt;'), 'XSS escaping');

assertCheck(GitspyApi::validateUsername('new92') === 'new92', 'valid GitHub username');
assertCheck(GitspyApi::profileUrl('new92') === 'https://github.com/new92', 'safe GitHub profile URL');
foreach (['-username', 'foo--bar', 'foo/bar', '<script>', str_repeat('a', 40), 'user.name'] as $invalid) {
    rejects(static fn () => GitspyApi::validateUsername($invalid), 'Gitspy username ' . $invalid);
}
assertCheck(GitspyApi::escape('<img src=x onerror=alert(1)>') === '&lt;img src=x onerror=alert(1)&gt;', 'escaped GitHub API data');
echo "PASS: URL restrictions, IP guard, link classification and Gitspy validation\n";
