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
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>DirReader — Results</title>
  <style>body{font:16px/1.6 system-ui,sans-serif;max-width:800px;margin:4rem auto;padding:0 1.5rem;color:#18212b}table{width:100%;border-collapse:collapse}td,th{padding:.65rem;text-align:left;border-bottom:1px solid #ddd}a{color:#155ca2}</style>
</head>
<body>
  <p><a href="form.html">&larr; Choose another directory</a></p>
  <h1>Directory contents</h1>
  <?php if ($error !== null): ?>
    <p role="alert"><?= escapeHtml($error) ?></p>
  <?php else: ?>
    <p>Location: <code><?= escapeHtml($result['path']) ?></code></p>
    <p><?= $result['files'] ?> files &middot; <?= $result['directories'] ?> directories</p>
    <table><thead><tr><th scope="col">Name</th><th scope="col">Type</th></tr></thead><tbody>
      <?php foreach ($result['entries'] as $entry): ?>
        <tr><td><?= escapeHtml($entry['name']) ?></td><td><?= escapeHtml($entry['type']) ?></td></tr>
      <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
</body>
</html>
