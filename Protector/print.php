<?php

$file = './static/headers.txt';
if (file_exists($file)) {
    header('Content-type: text/plain');
    header('Content-Disposition: inline; filename="headers.txt"');
    readfile($file);
} else {
    die('Error: File not found.');
}
