<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trooper • Info</title>
    <link rel="icon" type="image/x-icon" href="static/icon.png" />
    <link rel="stylesheet" type="text/css" href="static/dash.css" />
</head>
<?php
function main($username) {
    if ($username === null) {
        die('Error: No username provided.');
    }
    $url = 'https://www.instagram.com/api/v1/users/web_profile_info/?username=' . $username;
    $headers = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: Instagram 64.0.0.14.96'
        ]
    ];
    $context = stream_context_create($headers);
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        die('Error: Unable to fetch info !');
    }
    $data = json_decode($response, true);
    if ($data === null) {
        die('Error: Error decoding JSON !');
    }
    $links = [];
    $tagged = [];
    $hashes = [];
    if ($data['data']['user']['bio_links'] != []) {
        foreach ($data['data']['user']['bio_links'] as $link) {
            array_push($links, $link['url']);
        }
    }
    if ($data['data']['user']['biography_with_entities']['entities'] != []) {
        for ($i = 0; $i < count($data['data']['user']['biography_with_entities']['entities']); $i++) {
            if ($data['data']['user']['biography_with_entities']['entities'][$i]['user'] != null) {
                array_push($tagged, $data['data']['user']['biography_with_entities']['entities'][$i]['user']['username']);
            }
            if ($data['data']['user']['biography_with_entities']['entities'][$i]['hashtag'] != null) {
                array_push($hashes, $data['data']['user']['biography_with_entities']['entities'][$i]['hashtag']['name']);
            }
        }
    }
    return array(
        'username' => $username,
        'full_name' => $data['data']['user']['full_name'],
        'bio' => $data['data']['user']['biography'],
        'posts' => $data['data']['user']['edge_owner_to_timeline_media']['count'],
        'followers' => $data['data']['user']['edge_followed_by']['count'],
        'following' => $data['data']['user']['edge_follow']['count'],
        'id' => $data['data']['user']['id'],
        'bio_links' => implode(', ', $links),
        'tagged_users' => implode(', ', $tagged),
        'hashtags_in_bio' => implode(', ', $hashes),
        'fbid' => $data['data']['user']['fbid'],
        'email' => $data['data']['user']['business_email'],
        'address' => $data['data']['user']['business_address_json'],
        'tel' => $data['data']['user']['business_phone_number'],
        'category' => $data['data']['user']['category_name'],
        'business' => var_export($data['data']['user']['is_business_account'], true),
        'professional' => var_export($data['data']['user']['is_professional_account'], true),
        'supervised' => var_export($data['data']['user']['is_supervision_enabled'], true),
        'joined_recently' => var_export($data['data']['user']['is_joined_recently'], true),
        'private' => var_export($data['data']['user']['is_private'], true),
        'verified' => var_export($data['data']['user']['is_verified'], true),
        'profile_pic' => $data['data']['user']['profile_pic_url_hd']
    );
}

?>
<body>
<?php

$data = main($_POST['username']);
?>
<center><div class="dashboard">
    <div class="widget">
        <h2><?php echo $_POST['username'] ?>'s Info</h2>
        <table>
            <?php
                $keys = array_keys($data);
                foreach ($keys as $key) {
                    echo '<tr><td>' . $key . '</td><td>' . $data[$key] . '</td></tr>';
                }
            ?>
        </table>
    </div>
</div></center>
</body>
</html>