<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data • Investigator</title>
    <link rel="icon" type="image/x-icon" href="./static/icon.png" />
    <link rel="stylesheet" type="text/css" href="./static/out.css" />
</head>
<body>
    <?php
        function investigate(string $ip) {
            $url = "http://ip-api.com/json/$ip";
            $options = [
                'http' => [
                    'method' => 'GET',
                    'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
                ]
            ];
            $context = stream_context_create($options);
            $response = file_get_contents($url, false, $context);
            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data !== null) {
                    return $data;
                } else {
                    return 'Error decoding JSON response';
                }
            } else {
                return 'Error fetching data from API';
            }
        }
    ?>
    <div class="container">
        <h2><?php echo $_POST['ip']; ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php $invest = investigate($_POST['ip']);
                if (is_array($invest)) {
                    foreach ($invest as $key=>$value) {
                 ?>
                    <tr>
                        <td><?php echo $key; ?></td>
                        <td><?php echo $value; ?></td>
                    </tr>
                <?php } } else {
                    die($invest);
                } ?>
            </tbody>
        </table>
    </div>
</body>
</html>