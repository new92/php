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
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gitspy · Public GitHub profile</title><link rel="stylesheet" href="static/ui.css"></head><body><main class="container">
<header class="nav"><a class="brand" href="index.html">◇ Gitspy</a><a href="index.html">Another profile ↗</a></header>
<div class="eyebrow">Public GitHub profile research</div>
<?php if ($error !== null): ?>
<h1>Couldn’t load<br><span class="muted">this profile.</span></h1><section class="notice error" role="alert"><h2>Request failed</h2><p><?= GitspyApi::escape($error) ?></p></section>
<?php else: ?>
<div class="profile-header"><?php if ($avatar !== null): ?><img class="avatar" src="<?= GitspyApi::escape($avatar) ?>" alt="Public GitHub avatar" width="88" height="88"><?php endif; ?><div><h1><?= GitspyApi::escape(($profile['name'] ?? '') ?: ($profile['login'] ?? $username)) ?></h1><p class="lead"><a href="<?= GitspyApi::escape(GitspyApi::profileUrl($username)) ?>" target="_blank" rel="noopener noreferrer">@<?= GitspyApi::escape($username) ?></a><?php if (!empty($profile['bio'])): ?> · <?= GitspyApi::escape($profile['bio']) ?><?php endif; ?></p></div></div>
<section class="summary"><div><span class="label">Public repositories</span><p class="metric"><?= (int) ($profile['public_repos'] ?? 0) ?></p></div><div><span class="label">Followers</span><p class="metric"><?= (int) ($profile['followers'] ?? 0) ?></p></div><div><span class="label">Following</span><p class="metric"><?= (int) ($profile['following'] ?? 0) ?></p></div></section>
<section class="notice"><h2>Public data only</h2><p>Gitspy displays publicly accessible GitHub API data. Lists below show up to 30 items each; totals above come from the account profile, not the page length. No authentication or private profile access is attempted.</p></section>
<section><h2>Repositories <span class="label">first 30 returned by API</span></h2><div class="repo-list">
<?php foreach ($repos as $repo): if (!is_array($repo)) continue; $name = $repo['name'] ?? ''; if (!is_string($name) || !preg_match('/^[A-Za-z0-9_.-]{1,100}$/D', $name)) continue; ?>
<article class="repo"><h3><a href="<?= GitspyApi::escape(GitspyApi::profileUrl($username) . '/' . rawurlencode($name)) ?>" rel="noopener noreferrer" target="_blank"><?= GitspyApi::escape($name) ?></a></h3><p><?= GitspyApi::escape($repo['description'] ?? 'No description') ?></p><div class="label">★ <?= (int) ($repo['stargazers_count'] ?? 0) ?> &nbsp; · &nbsp; Forks <?= (int) ($repo['forks_count'] ?? 0) ?> &nbsp; · &nbsp; <?= GitspyApi::escape($repo['language'] ?? 'Unknown language') ?></div></article>
<?php endforeach; ?></div></section>
<div class="columns"><section><h2>Followers</h2><ul><?php foreach ($followers as $user): if (!is_array($user) || !is_string($user['login'] ?? null)) continue; try { $link = GitspyApi::profileUrl($user['login']); } catch (InvalidArgumentException $e) { continue; } ?><li><a rel="noopener noreferrer" target="_blank" href="<?= GitspyApi::escape($link) ?>"><?= GitspyApi::escape($user['login']) ?></a></li><?php endforeach; ?></ul></section>
<section><h2>Following</h2><ul><?php foreach ($following as $user): if (!is_array($user) || !is_string($user['login'] ?? null)) continue; try { $link = GitspyApi::profileUrl($user['login']); } catch (InvalidArgumentException $e) { continue; } ?><li><a rel="noopener noreferrer" target="_blank" href="<?= GitspyApi::escape($link) ?>"><?= GitspyApi::escape($user['login']) ?></a></li><?php endforeach; ?></ul></section></div>
<?php endif; ?>
<footer>Public GitHub data · Gitspy is not affiliated with GitHub · local educational use</footer>
</main></body></html>
