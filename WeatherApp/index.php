<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherApp</title>
    <link rel="icon" type="image/x-icon" href="static/icon.ico" />
    <link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body>
    <form method="post" class="container">
        <input type="text" name="city" minlength="1" maxlength="85" placeholder="City" required />
        <input type="submit" name="search" class="search" value="Search" />
    </form>
    <?php
    if (isset($_POST['search'])) {
        $url = 'https://visual-crossing-weather.p.rapidapi.com/forecast?aggregateHours=24&location=' . trim($_POST['city']) .'&contentType=csv&unitGroup=us&shortColumnNames=0';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "X-RapidAPI-Host: visual-crossing-weather.p.rapidapi.com",
                "X-RapidAPI-Key: <API_KEY>"
            ],
        ]);
        $dates_pattern = "/\b\d{2}\/\d{2}\/\d{4}\b/";
        $pattern = '/\"([^"]+)"$/m';
        $response = curl_exec($curl);
        $error = curl_error($curl);

        curl_close($curl);
        if ($error) {
            echo '<h1 style="color: red; text-align: center;">Error: Unable to fetch weather forecast</h1>';
        } else {
            if (preg_match_all($dates_pattern, $response, $dates) && preg_match_all($pattern, $response, $weather)) {
                echo '<div class="weather-container">';
                for ($i = 0; $i < count($dates[0]); $i++) {
                    echo '<div class="weather-box">';
                    echo '<div class="date">' . $dates[0][$i] . '</div>';
                    if ($weather[1][$i] == 'Clear') {
                        echo '<img src="static/sunny.png" alt="Sunny" />';
                        echo '<p>Sunny</p>';
                    } elseif ($weather[1][$i] == 'Rain, Partially cloudy') {
                        echo '<img src="static/rainy.png" alt="Rainy" />';
                        echo '<p>Rainy</p>';
                    } elseif ($weather[1][$i] == 'Overcast') {
                        echo '<img src="static/overcast.png" alt="Overcast" />';
                        echo '<p>Overcast</p>';
                    } elseif ($weather[1][$i] == 'Partially cloudy') {
                        echo '<img src="static/partly-cloudy.png" alt="Partially cloudy" />';
                        echo '<p>Partially cloudy</p>';
                    } elseif ($weather[1][$i] == 'Rain, Overcast') {
                        echo '<img src="static/rainy.png" alt="Rainy" />';
                        echo '<p>Rainy</p>';
                    }
                    echo '</div>';
                }
                echo '</div>';
            } else {
                echo '<h1 style="color: red; text-align: center;">Error: Unable to fetch weather forecast</h1>';
            }
        }
    }
    ?>
</body>
</html>
