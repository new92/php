<?php
http_response_code(410);
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store');
echo "Shared header export has been removed. Inspect headers on the response page.\n";
