<?php

$host = 'localhost';
$port = 8080;
$red = "\033[0;31m";
$green = "\033[0;32m";
$reset = "\033[0m";

echo "\n" . $green . "[RUNNING] Localhost is up and running on -> http://$host:$port" . $reset;
echo "\n[QUIT] Hit <Ctrl + C> to stop the server.\n";
echo $green . "[URL] To get started -> http://$host:$port/main.html\n" . $reset;
exec("php -S $host:$port");