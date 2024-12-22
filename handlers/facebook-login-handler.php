<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Facebook\Facebook;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Firebase\JWT\JWT;

session_start();

$fb = new Facebook([
    'app_id' => $config['facebook_app_id'],
    'app_secret' => $config['facebook_app_secret'],
    'default_graph_version' => 'v12.0'
]);

$helper = $fb->getRedirectLoginHelper();

if (isset($_GET['state'])) {
    $helper->getPersistentDataHandler()->set('state', $_GET['state']);
}

if (isset($_GET['facebook-login'])) {
    $permissions = ['email'];
    $loginUrl = $helper->getLoginUrl("http://localhost/DriveMarket/handlers/facebook-login-handler.php", $permissions);
    header("Location: " . $loginUrl);
    exit();
}

try {
    if (isset($_GET['code'])) {
        $accessToken = $helper->getAccessToken();
        
        if (isset($accessToken)) {
            $response = $fb->get('/me?fields=email,name', $accessToken);
            $user = $response->getGraphUser();
            
            $user_email = $user->getEmail();
            $user_name = $user->getName();

            // Connect to the database
            $connect = new PDO(
                "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
                $config['db_username'], 
                $config['db_password']
            );

            // Check if the user exists with Facebook authentication
            $query = "SELECT * FROM user WHERE user_email = ? AND user_password = 'FACEBOOK'";
            $statement = $connect->prepare($query);
            $statement->execute([$user_email]);
            $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$existing_user) {
                // If user doesn't exist with Facebook auth, insert new record
                $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, 'FACEBOOK')";
                $statement = $connect->prepare($insert_query);
                $statement->execute([$user_name, $user_email]);
                
                $user_id = $connect->lastInsertId();
            } else {
                $user_id = $existing_user['user_id'];
            }

            // Generate JWT token
            $jwt_token = JWT::encode(
                [
                    'iat' => time(),
                    'exp' => time() + 3600,
                    'data' => [
                        'user_id' => $user_id,
                        'user_name' => $user_name
                    ]
                ],
                $config['jwt_key'],
                'HS256'
            );

            setcookie("token", $jwt_token, time() + 3600, "/", "", true, true);
            header("Location: ../index.html");
            exit();
        }
    }
} catch(Exception $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
