<?php

$host = '127.0.0.1';
$port = 8080;

echo "\n[RUNNING] Localhost is up and running on -> http://$host:$port";
echo "\n[QUIT] Hit <Ctrl + C> to stop the server.\n";
echo "[URL] To get started -> http://$host:$port/Trooper/form.html\n";
exec('php -S localhost:' . $port);