<?php

declare(strict_types=1);
require_once __DIR__ . '/github_api.php';
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; img-src 'self' https://avatars.githubusercontent.com; frame-ancestors 'none'; object-src 'none'; base-uri 'none'");
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Use the Gitspy form.');
}
$error = null;
$profile = [];
$repos = [];
$followers = [];
$following = [];
$username = '';
try {
    if (!is_string($_POST['username'] ?? null)) {
        throw new InvalidArgumentException('Enter a GitHub username.');
    }
    $username = GitspyApi::validateUsername($_POST['username']);
    $path = '/users/' . rawurlencode($username);
    $profile = GitspyApi::fetch($path);
    $repos = GitspyApi::fetch($path . '/repos?per_page=30');
    $followers = GitspyApi::fetch($path . '/followers?per_page=30');
    $following = GitspyApi::fetch($path . '/following?per_page=30');
} catch (Throwable $exception) {
    $error = $exception->getMessage();
    http_response_code(422);
}
$avatar = $profile['avatar_url'] ?? '';
$avatarHost = is_string($avatar) ? parse_url($avatar, PHP_URL_HOST) : false;
$avatar = ($avatarHost === 'avatars.githubusercontent.com' && str_starts_with($avatar, 'https://')) ? $avatar : null;
?>
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="Public developer profile dashboard."><title>GitHub profile explorer · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="gitspy"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">⌘</span>Gitspy</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="index.html">Explore another profile</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="result-head"><div class="eyebrow">DEVELOPER INTELLIGENCE / PROFILE</div><h1>Public profile <span class="highlight">overview.</span></h1><p>Information from GitHub’s public API. Lists are limited to a short preview, while totals come from the account profile.</p></section>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>We couldn’t retrieve this profile</h2><p><?= GitspyApi::escape($error) ?></p><p>Check the username, API availability and rate limits, then try again.</p></section>
<?php else: ?>
<section class="profile-row"><div><?php if ($avatar !== null): ?><img class="avatar" src="<?= GitspyApi::escape($avatar) ?>" alt="Public avatar for <?= GitspyApi::escape($username) ?>" width="92" height="92"><?php else: ?><span class="avatar avatar-fallback" aria-hidden="true">⌘</span><?php endif; ?></div><div><div class="eyebrow">PROFILE FOUND</div><h1><?= GitspyApi::escape(($profile['name'] ?? '') ?: ($profile['login'] ?? $username)) ?></h1><p><a target="_blank" rel="noopener noreferrer" href="<?= GitspyApi::escape(GitspyApi::profileUrl($username)) ?>">@<?= GitspyApi::escape($username) ?> ↗</a></p><?php if (!empty($profile['bio'])): ?><p><?= GitspyApi::escape($profile['bio']) ?></p><?php endif; ?></div></section>
<div class="summary"><div class="stat-card"><span class="stat-label">Public repositories</span><p class="metric"><?= (int) ($profile['public_repos'] ?? 0) ?></p></div><div class="stat-card"><span class="stat-label">Followers</span><p class="metric"><?= (int) ($profile['followers'] ?? 0) ?></p></div><div class="stat-card"><span class="stat-label">Following</span><p class="metric accent"><?= (int) ($profile['following'] ?? 0) ?></p></div></div>
<section class="section"><div class="section-head"><h2 class="section-title">Repositories</h2><span class="pill neutral">Up to 30 public repos</span></div>
<div class="toolbar"><label class="field" for="repo-search">Filter <span class="field-optional">(loaded items)</span></label><input class="search-input" id="repo-search" type="search" placeholder="Search by name, language or description" data-filter="repos-list"><span class="count" id="repos-list-count"></span></div>
<div class="repo-grid" id="repos-list">
<?php foreach ($repos as $repo): if (!is_array($repo)) continue; $name = $repo['name'] ?? ''; if (!is_string($name) || !preg_match('/^[A-Za-z0-9_.-]{1,100}$/D', $name)) continue; ?>
<article class="repo-card" data-search-item><h3><a href="<?= GitspyApi::escape(GitspyApi::profileUrl($username) . '/' . rawurlencode($name)) ?>" rel="noopener noreferrer" target="_blank"><?= GitspyApi::escape($name) ?> ↗</a></h3><p><?= GitspyApi::escape($repo['description'] ?? 'No description provided.') ?></p><div class="repo-meta"><span>★ <b><?= (int) ($repo['stargazers_count'] ?? 0) ?></b></span><span>Forks <b><?= (int) ($repo['forks_count'] ?? 0) ?></b></span><span><?= GitspyApi::escape($repo['language'] ?? 'Unspecified language') ?></span></div></article>
<?php endforeach; ?></div><p class="filter-empty" id="repos-list-empty" hidden>No repositories match the filter.</p>
<?php if ($repos === []): ?><p class="empty-state">No repositories returned in this preview.</p><?php endif; ?>
</section>
<div class="grid-two"><section class="data-panel"><div class="section-head"><h2 class="section-title">Followers</h2><span class="pill neutral">Preview</span></div><div class="toolbar"><input type="search" aria-label="Filter follower preview" class="search-input" placeholder="Find a username" data-filter="follower-list"><span class="count" id="follower-list-count"></span></div><ul class="user-list" id="follower-list">
<?php foreach ($followers as $user): if (!is_array($user) || !is_string($user['login'] ?? null)) continue; try { $link = GitspyApi::profileUrl($user['login']); } catch (InvalidArgumentException $e) { continue; } ?><li data-search-item><a href="<?= GitspyApi::escape($link) ?>" rel="noopener noreferrer" target="_blank"><?= GitspyApi::escape($user['login']) ?></a></li><?php endforeach; ?>
</ul><p class="filter-empty" id="follower-list-empty" hidden>No users match.</p></section>
<section class="data-panel"><div class="section-head"><h2 class="section-title">Following</h2><span class="pill neutral">Preview</span></div><div class="toolbar"><input type="search" aria-label="Filter following preview" class="search-input" placeholder="Find a username" data-filter="following-list"><span class="count" id="following-list-count"></span></div><ul class="user-list" id="following-list">
<?php foreach ($following as $user): if (!is_array($user) || !is_string($user['login'] ?? null)) continue; try { $link = GitspyApi::profileUrl($user['login']); } catch (InvalidArgumentException $e) { continue; } ?><li data-search-item><a href="<?= GitspyApi::escape($link) ?>" rel="noopener noreferrer" target="_blank"><?= GitspyApi::escape($user['login']) ?></a></li><?php endforeach; ?>
</ul><p class="filter-empty" id="following-list-empty" hidden>No users match.</p></section></div>
<?php endif; ?>
<section class="notice warning"><h2>Public data is not a complete profile</h2><p>Gitspy does not access private data. The repositories and user lists each contain at most 30 API results; use GitHub itself for the full profile.</p></section>
<footer class="footer"><span>Gitspy · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
