<?php

$items = scandir($_POST['dir']);
$files = 0;
$dirs = 0;
for ($i = 0; $i < count($items); $i++) {
    echo $items[$i];
    if(is_file($items[$i])) {
        echo $items[$i] . ' | file<br>';
        $files++;
    } else {
        echo $items[$i] . ' | directory<br>';
        $dirs++;
    }
}
echo "Files ==> $files<br><br>";
echo "Directories ==> $dirs<br><br>";

?>
