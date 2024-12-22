<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Google\Client;

// Setting up Google OAuth Client
$client = new Client();
$client->setClientId($config['google_client_id']); // Google client ID
$client->setClientSecret($config['google_client_secret']); // Google client secret
$client->setRedirectUri("http://localhost/DriveMarket/handlers/login-handler.php"); // Redirect URI
$client->addScope("email"); // Request email scope
$client->addScope("profile"); // Request profile scope

// If the user clicks the "Google Login" button
if (isset($_GET['google-login'])) {
    // Redirect user to Google's authentication URL
    header("Location: " . $client->createAuthUrl());
    exit();
}

// If Google redirects back with an authorization code
if (isset($_GET['code'])) {
    // Exchange authorization code for access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // Get user information from Google API
    $google_service = new Google\Service\Oauth2($client);
    $data = $google_service->userinfo->get();

    $user_email = $data->email;
    $user_name = $data->name;

    // Connect to the database
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );

    // Check if the user exists with Google authentication
    $query = "SELECT * FROM user WHERE user_email = ? AND user_password = 'GOOGLE'";
    $statement = $connect->prepare($query);
    $statement->execute([$user_email]);
    $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$existing_user) {
        // If user doesn't exist with Google auth, insert new record
        $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, 'GOOGLE')";
        $statement = $connect->prepare($insert_query);
        $statement->execute([$user_name, $user_email]);
        
        // Get the ID of the newly inserted user
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

// Standard login logic using email and password
if (isset($_POST["login"])) {
    // Connect to the database
    $connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);

    // Check if email and password are provided
    if (empty($_POST["email"])) {
        $error = 'Please Enter Email Details';
    } elseif (empty($_POST["password"])) {
        $error = 'Please Enter Password Details';
    } else {
        // Query to fetch the user by email
        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$_POST["email"]]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // Verify the password
            if (password_verify($_POST['password'], $data['user_password'])) {
                $key = 'jwt_key'; // Replace with your secret key
                $token = JWT::encode(
                    array(
                        'iat'   => time(), // Issued at
                        'nbf'   => time(), // Not before
                        'exp'   => time() + 3600, // Expiration time (1 hour)
                        'data'  => array(
                            'user_id'   => $data['user_id'], // Include user ID in token
                            'user_name' => $data['user_name'] // Include user name in token
                        )
                    ),
                    $key,
                    'HS256' // Use HS256 algorithm
                );

                // Set the JWT token as a secure cookie
                setcookie("token", $token, time() + 3600, "/", "", true, true);

                // Redirect to the homepage
                header('location:../index.html');
                exit();
            } else {
                $error = 'Wrong Password'; // Handle incorrect password
            }
        } else {
            $error = 'Wrong Email Address'; // Handle incorrect email
        }
    }
}
?>