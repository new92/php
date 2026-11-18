<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; script-src 'self'; object-src 'none'; base-uri 'none'; frame-ancestors 'none'");

function respond(int $status, string $message): never
{
    http_response_code($status);
    $safe = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $success = $status >= 200 && $status < 300;
    ?>
<!doctype html><html lang="en" data-theme="dark"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#07111c"><meta name="description" content="Outcome of private local image upload."><title>Upload result · PHP Utilities</title><link rel="stylesheet" href="static/ui.css"><script src="static/ui.js" defer></script></head><body data-project="uploader"><a class="skip-link" href="#main">Skip to main content</a><main id="main" class="shell"><header class="topbar"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">⇧</span>File Uploader</a><nav class="top-actions" aria-label="Page actions"><a class="toplink" href="index.html">Upload another</a><button type="button" class="theme-toggle" data-theme-toggle aria-label="Switch to light theme"><span aria-hidden="true">◐</span> <span class="theme-label">Light mode</span></button></nav></header>
<section class="result-head"><div class="eyebrow">PRIVATE STORAGE / OUTCOME</div><h1><?= $success ? 'Upload <span class="highlight">complete.</span>' : 'Upload <span class="highlight">not completed.</span>' ?></h1><p>Your image is validated and stored only when server-side checks succeed.</p></section>
<section class="card result-card-narrow"><div class="form-header"><h2><?= $success ? 'Image accepted' : 'Action needed' ?></h2><span class="pill <?= $success ? '' : 'neutral' ?>"><?= $success ? 'Saved privately' : 'Not saved' ?></span></div>
<div class="notice <?= $success ? 'success' : 'error' ?>" role="status"><h2><?= $success ? 'Storage successful' : 'Upload rejected' ?></h2><p><?= $safe ?></p></div><a class="button" href="index.html">Select another image ↗</a>
</section><section class="notice warning"><h2>Storage is private</h2><p>Uploaded images are not displayed or made publicly downloadable by this demo. No server storage path or random file name is shared in the response.</p></section>
<footer class="footer"><span>File Uploader · Educational utility · Local use</span><span>Built with PHP · No external frontend dependencies</span></footer></main></body></html>
<?php
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    respond(405, 'Only POST requests are supported.');
}

if (!isset($_FILES['file']) || !is_array($_FILES['file']) || is_array($_FILES['file']['error'] ?? null)) {
    respond(400, 'Select a file to upload.');
}

$file = $_FILES['file'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    respond(400, 'The file upload failed. Please try again.');
}

$maxSize = 500000; // 500 KB
$tmp = $file['tmp_name'] ?? null;
$size = $file['size'] ?? null;
if (!is_string($tmp) || !is_int($size) || $size < 1 || $size > $maxSize || !is_uploaded_file($tmp)) {
    respond(400, 'Upload a valid image no larger than 500 KB.');
}

// Check actual content, not the client-controlled extension or MIME header.
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
$image = @getimagesize($tmp);
if ($mime === false || !isset($allowed[$mime]) || $image === false || ($image['mime'] ?? '') !== $mime) {
    respond(415, 'Only valid JPEG, PNG and GIF images are accepted.');
}

// Never save uploads under the web document root and never reuse user-supplied names.
// Set PHP_DEMO_UPLOAD_DIR to a private directory if needed.
$directory = getenv('PHP_DEMO_UPLOAD_DIR') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'new92-php-image-uploads';
if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
    respond(500, 'The private upload directory could not be created.');
}
if (!is_writable($directory)) {
    respond(500, 'The private upload directory is not writable.');
}

try {
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
} catch (Throwable $exception) {
    respond(500, 'Unable to generate a safe file name.');
}

$destination = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
if (!move_uploaded_file($tmp, $destination)) {
    respond(500, 'Unable to store the image.');
}
@chmod($destination, 0600);
respond(201, 'Image uploaded successfully to private storage.');
