<?php

$items = scandir($_POST['dir']);
for ($i = 0; $i < count($items); $i++) {
    echo $items[$i];
    if(is_file($items[$i])) {
        echo $items[$i] . ' | file<br>';
    } else {
        echo $items[$i] . ' | directory<br>';
    }
}

?>
