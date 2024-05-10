<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $target = 'uploads/' . basename($_FILES['file']['name']);
    $type = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    if (file_exists($target)) {
        die('❌ File already exists.');
    }
    if ($_FILES['file']['size'] > 500000) {
        die('❌ File size too large');
    }
    if ($type != 'jpg' && $type != 'png' && $type != 'jpeg' && $type != 'gif') {
        die('❌ Filetype not allowed');
    }
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        echo '✅ File ' . basename($_FILES['file']['name']) . ' has been uploaded successfully.';
    } else {
        die('❌ An error occured. File has not been uploaded !');
    }
}