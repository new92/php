<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User info • Gitspy</title>
    <link rel="stylesheet" type="text/css" href="./static/styles.css" />
    <link rel="icon" type="image/x-icon" href="./static/icon.png" />
</head>
<body>
    <?php
    /*

    Gitspy 🕵️ by @new92

    */
    function fetch($username) {
        $url = "https://api.github.com/users/$username";
        $options = [
            "http" => [
                "method" => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            ]
        ];
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $json = json_decode($response, true);
            
            if ($json !== NULL) {
                return $json;
            } else {
                return 'Error decoding JSON response.';
            }
        } else {
            return 'Error fetching data from API.';
        }
    }
    $username = $_POST['username'];
    if (!empty($username)) {
        $json = fetch($username);
        $options = [
            "http" => [
                "method" => 'GET',
                'header' => 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36'
            ]
        ];
        $context = stream_context_create($options);
        $url = $json['starred_url'];
        $response = file_get_contents(substr($url, 0, strpos($url, '{')), false, $context);
        
        if ($response !== false) {
            $starred_repos = json_decode($response, true);
            $likes = count($starred_repos);
        } else {
            $likes = 0;
        }

        $response = file_get_contents($json['followers_url'], false, $context);
        if ($response !== false) {
            $followers = json_decode($response, true);
        } else {
            $followers = [];
        }

        $response = file_get_contents($json['repos_url'], false, $context);
        if ($response !== false) {
            $repos = json_decode($response, true);
        } else {
            $repos = [];
        }

        $url = $json['following_url'];
        $response = file_get_contents(substr($url, 0, strpos($url, '{')), false, $context);
        if ($response !== false) {
            $followings = json_decode($response, true);
        } else {
            $followings = [];
        }
        if (!is_string($json)) {
    ?>
            <div class="container">
                <div class="left-panel">
                    <h2>Followers</h2>
                    <table class="followers-table">
                        <?php foreach ($followers as $follower) : ?>
                            <tr>
                                <td><a href="<?php echo $follower['html_url']; ?>" target="_blank"><?php echo $follower['login']; ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                
                <div class="profile-main">
                    <div class="profile-container">
                        <div class="profile-header">
                            <img src="<?php echo $json['avatar_url']; ?>" alt="User Profile Picture" class="profile-picture">
                            <div class="user-stats">
                                <h1 class="user-name"><?php echo $json['name']; ?> <a href="<?php echo $json['html_url']; ?>" target="_blank"><span class="user-username">@<?php echo $json['login']; ?></span></a></h1>
                                <p class="user-bio"><?php echo empty($json['bio']) ? 'Not set' : $json['bio']; ?></p>
                                <div class="stats-container">
                                    <div class="stat type"><strong>Type:</strong> <?php echo $json['type']; ?></div>
                                    <div class="stat followers"><strong>Followers:</strong> <?php echo $json['followers']; ?></div>
                                    <div class="stat following"><strong>Following:</strong> <?php echo $json['following']; ?></div>
                                    <div class="stat likes"><strong>Stars:</strong> <?php echo $likes; ?></div>
                                    <div class="stat location"><strong>Location:</strong> <?php echo empty($json['location']) ? 'Not specified' : $json['location']; ?></div>
                                    <div class="stat email"><strong>Email:</strong> <?php echo empty($json['email']) ? 'Not specified' : $json['email']; ?></div>
                                    <div class="stat hireable"><strong>Hireable:</strong> <?php echo empty($json['hireable']) ? 'Not specified' : $json['hireable']; ?></div>
                                    <div class="stat company"><strong>Company:</strong> <?php echo empty($json['company']) ? 'Not specified' : $json['company']; ?></div>
                                    <div class="stat twitter"><strong>Twitter:</strong> <a href="https://twitter.com/<?php echo $json['twitter_username']; ?>" target="_blank">@<?php echo $json['twitter_username']; ?></a></div>
                                    <div class="stat blog"><strong>Blog:</strong> <a href="<?php echo $json['blog']; ?>" target="_blank"><?php echo $json['blog']; ?></a></div>
                                    <div class="stat created-at"><strong>Account Created:</strong> <?php echo str_replace('Z', '', str_replace('T', ' ', $json['created_at'])); ?></div>
                                    <div class="stat updated-at"><strong>Last Updated:</strong> <?php echo str_replace('Z', '', str_replace('T', ' ', $json['updated_at'])); ?></div>
                                    <div class="stat public-repos"><strong>Public Repositories:</strong> <?php echo $json['public_repos']; ?></div>
                                    <div class="stat public-gists"><strong>Public Gists:</strong> <?php echo $json['public_gists']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="repos-container">
                            <h2>Repositories</h2>
                            <?php foreach ($repos as $repo) : ?>
                                <div class="repo-card">
                                    <div class="repo-header">
                                        <a href="<?php echo $repo['html_url']; ?>" class="repo-name" target="_blank"><?php echo $repo['name']; ?></a>
                                        <div class="repo-stats">
                                            <span>⭐ <?php echo $repo['stargazers_count']; ?></span>
                                            <span>🍴 <?php echo $repo['forks_count']; ?></span>
                                            <span>👁️ <?php echo $repo['watchers_count']; ?></span>
                                        </div>
                                    </div>
                                    <p class="repo-description"><?php echo empty($repo['description']) ? 'No description' : $repo['description']; ?></p>
                                    <div class="repo-meta">
                                        <span>Language: <?php echo $repo['language']; ?></span>
                                        <span>Open Issues: <?php echo $repo['open_issues_count']; ?></span>
                                        <span>License: <?php echo $repo['license']['spdx_id']; ?></span>
                                        <span>Forking Allowed: <?php echo $repo['fork']; ?></span>
                                        <span>Created At: <?php echo str_replace('Z', '', str_replace('T', ' ', $repo['created_at'])); ?></span>
                                        <span>Updated At: <?php echo str_replace('Z', '', str_replace('T', ' ', $repo['updated_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="right-panel">
                    <h2>Followings</h2>
                    <table class="followings-table">
                        <?php foreach ($followings as $following) : ?>
                            <tr>
                                <td><a href="<?php echo $following['html_url']; ?>" target="_blank"><?php echo $following['login']; ?></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        <?php
        } else {
            echo '<p class="error">' . $json . '</p>';
        }
    } else {
        header('Location: index.html');
        exit();
    }
    ?>
</body>
</html>
