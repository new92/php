<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function respond(int $status, string $message): never
{
    http_response_code($status);
    $safe = htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Upload result</title><body style="font:16px/1.6 system-ui,sans-serif;max-width:38rem;margin:4rem auto;padding:0 1rem">';
    echo '<h1>File uploader</h1><p>' . $safe . '</p><a href="index.html">Upload another image</a></body></html>';
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
