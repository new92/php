<?php

$dir = new DirectoryIterator($_POST['dir']);
while ($dir -> valid()) {
    if (!$dir -> isDot()) {
        echo $dir -> getFilename() . '<br>';
    }
    $dir -> next();
}

unset($dir);

?>
