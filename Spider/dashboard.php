<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spider • Dashboard</title>
    <link rel="stylesheet" type="text/css" href="static/dash.css" />
    <link rel="icon" type="image/x-icon" href="static/icon.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Honk&family=Protest+Strike&display=swap" rel="stylesheet">
</head>
<body>
    <?php

    
    require_once "vendor/autoload.php";
    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Component\DomCrawler\Crawler;

    function categorize(array $social, array $urls, string $host): array {
        $socials = [];
        $mails = [];
        $externals = [];
        $host = parse_url($host, PHP_URL_HOST);
        $host = explode('.', $host)[0];
        for ($i = 0; $i < count($urls); $i++) {
            for ($j = 0; $j < count($social); $j++) {
                if (str_contains($urls[$i], $social[$j]) && !(in_array($urls[$i], $socials))) {
                    array_push($socials, $urls[$i]);
                    continue;
                }
            }
            if (str_contains($urls[$i], 'mailto')) {
                preg_match('/mailto:(.*)/', $urls[$i], $email);
                $email = $email[1];
                if (!(in_array($email, $mails))) {
                    array_push($mails, $email);
                    continue;
                }
            } elseif (!str_contains($urls[$i], $host) && !(in_array($urls[$i], $externals))) {
                array_push($externals, $urls[$i]);
            }
        }
        return array($socials, $mails, $externals);
    }

    $browser = new HttpBrowser(HttpClient::create());
    $host = $_POST['url'];
    $resp = $browser->request('GET', $host);
    $html = $browser->getResponse()->getContent();
    $crawler = new Crawler($html, $host);
    $urls = $crawler->filter('a')->each(function ($node) {
        return $node->link()->getUri();
    });
    $socials = array(
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'youtube',
        'snapchat',
        'pinterest',
        'reddit',
        'tumblr',
        'whatsapp',
        'telegram',
        'tiktok',
        'wechat',
        'vkontakte',
        'discord',
        'medium',
        'quora',
        'flickr',
        'meetup',
        'goodreads'
    );

    $cats = categorize($socials, $urls, $host);
    $socs = $cats[0];
    $mails = $cats[1];
    $externals = $cats[2];
    for ($i = 0; $i < count($urls); $i++) {
        array_push($socs, NULL);
        array_push($mails, NULL);
        array_push($externals, NULL);
    }
    ?>
    <center>
        <div class="widget">
            <h2>Extracted URLS</h2>
            <table>
                <tr>
                    <th>Exctracted URLS</th>
                    <th>Socials</th>
                    <th>Mails</th>
                    <th>External URLS</th>
                </tr>
                <?php
                    for ($i = 0; $i < count($urls); $i++) {
                        echo '<tr><td><a href="' . $urls[$i] . '" target="_blank">' . $urls[$i] . '</a></td><td><a href="' . $socs[$i] . '" target="_blank">' . $socs[$i] . '</a></td><td><a href="' . $mails[$i] . '" target="_blank">' . $mails[$i] . '</a></td><td><a href="' . $externals[$i] . '" target="_blank">' . $externals[$i] . '</a></td></tr>';
                    }
                ?>
            </table>
        </div>
    </center>
</body>
</html>