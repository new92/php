<?php

$host = 'localhost';
$port = 8080;
$green = "\033[0;32m";
$reset = "\033[0m";

echo "\n" . $green . "[RUNNING] Localhost is up and running on -> http://$host:$port" . $reset;
echo "\n[QUIT] Hit <Ctrl + C> to stop the server.\n";
echo $green . "[URL] To get started -> http://$host:$port/index.html\n" . $reset;
exec("php -S $host:$port");
