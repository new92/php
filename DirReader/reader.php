<?php

declare(strict_types=1);

require_once __DIR__ . '/lib.php';

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$result = null;
$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !is_string($_POST['dir'] ?? null)) {
    http_response_code(400);
    $error = 'Submit a directory using the form.';
} else {
    try {
        $result = readDirectory(getenv('DIRREADER_ROOT') ?: __DIR__, $_POST['dir']);
    } catch (InvalidArgumentException $exception) {
        http_response_code(400);
        $error = $exception->getMessage();
    } catch (RuntimeException $exception) {
        http_response_code(500);
        $error = $exception->getMessage();
    }
}

function escapeHtml(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="A constrained directory listing with searchable results."><title>Directory results · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="dirreader"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="form.html"><span class="brand-mark" aria-hidden="true">▤</span>DirReader</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="form.html">New directory</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="result-head"><div class="eyebrow">LOCAL FILESYSTEM / RESULT</div><h1>Directory <span class="highlight">contents.</span></h1><p>Immediate directory entries only. Paths are resolved against the configured permitted root.</p></section>
<?php if ($error !== null): ?>
<section class="notice error" role="alert"><h2>Unable to list directory</h2><p><?= escapeHtml($error) ?></p><p>Choose a valid relative path within your configured root and try again.</p></section>
<?php else: ?>
<div class="summary"><div class="stat-card"><span class="stat-label">Files</span><div class="metric"><?= (int) $result['files'] ?></div></div><div class="stat-card"><span class="stat-label">Directories</span><div class="metric"><?= (int) $result['directories'] ?></div></div><div class="stat-card"><span class="stat-label">Entries found</span><div class="metric accent"><?= count($result['entries']) ?></div></div></div>
<section class="data-panel"><div class="section-head"><h2 class="section-title">Browse entries</h2><span class="pill neutral">Read only</span></div><p class="footnote break">Location: <code><?= escapeHtml($result['path']) ?></code></p>
<div class="toolbar"><label for="entry-search" class="field">Filter entries <span class="field-optional">(local)</span></label><input id="entry-search" class="search-input" type="search" placeholder="Filter filenames…" data-filter="dir-entries" aria-label="Filter directory entries"><span class="count" id="dir-entries-count"></span></div>
<div class="table-scroll"><table><thead><tr><th scope="col">Name</th><th scope="col">Type</th></tr></thead><tbody id="dir-entries">
<?php foreach ($result['entries'] as $entry): ?><tr data-search-item><td class="mono break"><?= escapeHtml($entry['name']) ?></td><td><span class="pill neutral"><?= escapeHtml($entry['type']) ?></span></td></tr><?php endforeach; ?>
</tbody></table></div><p class="filter-empty" id="dir-entries-empty" hidden>No entries match your filter.</p>
<?php if ($result['entries'] === []): ?><p class="empty-state">This directory is empty.</p><?php endif; ?>
</section>
<?php endif; ?>
<section class="notice warning"><h2>Directory boundaries remain enforced</h2><p>This viewer does not follow symbolic links or enumerate children recursively. Do not expose file names from sensitive directories to untrusted users.</p></section>
<footer class="footer"><span>DirReader · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
