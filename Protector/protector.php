<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protector • Review</title>
    <link rel="icon" type="image/x-icon" href="static/icon.png" />
    <link rel="stylesheet" type="text/css" href="static/dash.css" />
</head>
<body>
    <?php
        $url = strtolower($_POST['url']);
        $host = strtolower($_POST['host']);
        function inspect() {
            global $url, $host;
            $data = file('./static/legitimate.txt', FILE_IGNORE_NEW_LINES);
            $target = '';
            foreach ($data as $line) {
                $line = trim($line);
                if (str_contains($line, $host)) {
                    $target = $line;
                    break;
                }
            }
            if (isset($target)) {
                $curl = curl_init($target);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_exec($curl);
                $legit_host = curl_getinfo($curl)['primary_ip'];
                curl_close($curl);
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_exec($curl);
                $server = curl_getinfo($curl)['primary_ip'];
                curl_close($curl);
                return $server == $legit_host;
            }
            return '';
        }
        $context = stream_context_create( [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);
        $headers = get_headers($url, true, $context);
        $domain = parse_url($url, PHP_URL_HOST);
        $serverInfo = $_SERVER['SERVER_SOFTWARE'];
        $ip = gethostbyname($domain);
    ?>
    <div class="container">
        <div class="info-table">
            <?php
            if ($domain) {
                echo "<h2 style='color: #333;'>Domain Information</h2>";
                echo "<table>";
                echo "<tr><th style='background-color: #f2f2f2;'>Domain</th><td>$domain</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>IP Address</th><td>$ip</td></tr>";
                echo "</table>";
            }
            if ($serverInfo) {
                echo "<h2 style='color: #333;'>Server Information</h2>";
                echo "<table>";
                echo "<tr><th style='background-color: #f2f2f2;'>Server</th><td>$serverInfo</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>IP Address</th><td>{$_SERVER['SERVER_ADDR']}</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>Port</th><td>{$_SERVER['SERVER_PORT']}</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>Name</th><td>{$_SERVER['SERVER_NAME']}</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>Request Method</th><td>{$_SERVER['REQUEST_METHOD']}</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>Protocol</th><td>{$_SERVER['SERVER_PROTOCOL']}</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>Remote IP</th><td>{$_SERVER['REMOTE_ADDR']}</td></tr>";
                echo "<tr><th style='background-color: #f2f2f2;'>Remote port</th><td>{$_SERVER['REMOTE_PORT']}</td></tr>";
                echo "</table>";
            }
            ?>
        </div>
        <div class="info-table">
            <?php
                $result = inspect();
                if (is_bool($result)) {
                    $color = ($result == true) ? '#4CAF50' : '#f00';
                    echo "<h2>Safety Status</h2>";
                    echo "<p style='color: #8080ff'>Is this <a href='$url'>website</a> safe? </p><strong style='color: $color;'>" . var_export($result == true, true) . "</strong>";
                } else {
                    echo "<center><h1 style='color: red;'><u>unable to detect host</u></h1></center>";
                }
            ?>
        </div>
        <div class="info-table">
            <?php
            echo "<h2 style='color: #033;'><a href='print.php'>Headers</a></h2>";
            echo "<table>";
            foreach ($headers as $key => $value) {
                echo "<tr><th>$key</th><td>$value</td></tr>";
            }
            $fp = fopen('./static/headers.txt', 'w');
            foreach ($headers as $key => $value) {
                if (is_array($value)) {
                    $value = implode(',', $value) . PHP_EOL;
                }
                fwrite($fp, "$key | $value\n");
            }
            fclose($fp);
            echo "</table>";
            ?>
        </div>
    </div>
    <footer>
        <?php
        echo "<h2 style='color: #333;'>Preview</h2>";
        echo "<iframe class='preview' src='$url'></iframe>";
        ?>
    </footer>
</body>
</html>