<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maskify • Output</title>
    <link rel="stylesheet" type="text/css" href="static/out.css" />
    <link rel="icon" type="image/x-icon" href="static/icon.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Honk&family=Protest+Strike&display=swap" rel="stylesheet">
</head>
<body>
<?php

function mask($domain, $url, $keywords) {
    function shortner($url) {
        $red = "\033[0;31m";
        $reset = "\033[0m";
        $url = "https://is.gd/create.php?format=json&url=$url";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($curl);
        $shortened = '';
        if ($resp === false) {
            echo $red . "Error: " . curl_error($curl) . $reset;
        } else {
            $decode = json_decode($resp, true);
            if ($decode === NULL) {
                echo $red . "Error: Unable to decode JSON response." . $reset;
            } else {
                if (!in_array('errorcode', array_keys($decode))) {
                    $shortened = $decode["shorturl"];
                }
            }
        }
        curl_close($curl);
        return $shortened;
        }
    $curl = shortner($url);
    if ($curl) {
        $parsed = parse_url($curl);
        return $domain . '-' . $keywords . '@' . $parsed['host'] . $parsed['path'];
    } else {
        return '';
    }
}

$result = mask($_POST['domain'], $_POST['url'], $_POST['keywords']);
?>
<center><h1><u>Masked URL:</u></h1>
<br /><br />
<?php
if ($result) {
?>
<div class="url-container">
    <a href="<?php echo $result; ?>"><?php echo $result; ?></a>
    <br /><br />
    <div class="popup" id="popup">Link copied to clipboard !</div>
</div></center>
<?php
} else {
?>
<div class="url-container">
    <div class="error-message" id="error-message" style="display: none;">Error generating mask for URL.</div>
</div>
<?php
}
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var urlLinks = document.querySelectorAll(".url-container a");
    urlLinks.forEach(function(link) {
        link.addEventListener("click", function(event) {
            event.preventDefault(); 
            var url = this.getAttribute("href");
            var tempInput = document.createElement("input");
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            var popup = this.parentNode.querySelector(".popup");
            popup.style.display = "block";
            setTimeout(function() {
                popup.style.display = "none";
            }, 2000); 
        });
    });
});
</script>
</body>
</html>