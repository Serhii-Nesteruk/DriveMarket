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
    $client->setAccessToken($token['access_token']); // Set access token for the client

    // Get user information from Google API
    $google_service = new Google\Service\Oauth2($client);
    $data = $google_service->userinfo->get();

    $user_email = $data->email; // Extract user email
    $user_name = $data->name;  // Extract user name

    // Connect to the database
    $connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);

    // Check if the user already exists in the database
    $query = "SELECT * FROM user WHERE user_email = ?";
    $statement = $connect->prepare($query);
    $statement->execute([$user_email]);
    $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$existing_user) {
        // If user doesn't exist, insert their information into the database
        $insert_query = "INSERT INTO user (user_name, user_email) VALUES (?, ?)";
        $statement = $connect->prepare($insert_query);
        $statement->execute([$user_name, $user_email]);
    }

    // Generate a JWT token
    $key = 'jwt_key';
    $jwt_token = JWT::encode(
        [
            'iat'   => time(), // Issued at
            'nbf'   => time(), // Not before
            'exp'   => time() + 3600, // Expiration time (1 hour)
            'data'  => [
                'user_email' => $user_email, // Include user email in token
                'user_name'  => $user_name  // Include user name in token
            ]
        ],
        $key,
        'HS256' // Use HS256 algorithm
    );

    // Set the JWT token as a secure cookie
    setcookie("token", $jwt_token, time() + 3600, "/", "", true, true);

    // Redirect to the homepage
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