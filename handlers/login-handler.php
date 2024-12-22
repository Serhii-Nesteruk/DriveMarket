<?php
require "../vendor/autoload.php";
$config = include('../config.php');

use Firebase\JWT\JWT;
use Google\Client;

// Setting up Google OAuth Client
$client = new Client();
$client->setClientId($config['google_client_id']);
$client->setClientSecret($config['google_client_secret']);
$client->setRedirectUri("http://localhost/DriveMarket/handlers/login-handler.php");
$client->addScope("email");
$client->addScope("profile");

try {
    // Connect to the database
    $connect = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
        $config['db_username'], 
        $config['db_password']
    );
    $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Створюємо таблицю, якщо вона не існує
    createUserTableIfNotExists($connect);

    // If the user clicks the "Google Login" button
    if (isset($_GET['google-login'])) {
        header("Location: " . $client->createAuthUrl());
        exit();
    }

    // If Google redirects back with an authorization code
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token['access_token']);

        $google_service = new Google\Service\Oauth2($client);
        $data = $google_service->userinfo->get();

        $user_email = $data->email;
        $user_name = $data->name;

        // Check if user exists with Google authentication
        $query = "SELECT * FROM user WHERE user_email = ? AND user_password = 'GOOGLE'";
        $statement = $connect->prepare($query);
        $statement->execute([$user_email]);
        $existing_user = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$existing_user) {
            $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, 'GOOGLE')";
            $statement = $connect->prepare($insert_query);
            $statement->execute([$user_name, $user_email]);
            $user_id = $connect->lastInsertId();
        } else {
            $user_id = $existing_user['user_id'];
        }

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

    // Standard login logic
    if (isset($_POST["login"])) {
        $error = '';
        
        if (empty($_POST["email"])) {
            $error = 'Будь ласка, введіть email';
        } elseif (empty($_POST["password"])) {
            $error = 'Будь ласка, введіть пароль';
        } else {
            $query = "SELECT * FROM user WHERE user_email = ?";
            $statement = $connect->prepare($query);
            $statement->execute([$_POST["email"]]);
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                if (password_verify($_POST['password'], $data['user_password'])) {
                    $token = JWT::encode(
                        [
                            'iat' => time(),
                            'exp' => time() + 3600,
                            'data' => [
                                'user_id' => $data['user_id'],
                                'user_name' => $data['user_name']
                            ]
                        ],
                        $config['jwt_key'],
                        'HS256'
                    );

                    setcookie("token", $token, time() + 3600, "/", "", true, true);
                    header('location:../index.html');
                    exit();
                } else {
                    $error = 'Невірний пароль';
                }
            } else {
                $error = 'Невірна email адреса';
            }
        }
        
        if (!empty($error)) {
            header("Location: ../templates/login.php?error=" . urlencode($error));
            exit();
        }
    }

} catch (PDOException $e) {
    header("Location: ../templates/login.php?error=database_error");
    exit();
} catch (Exception $e) {
    header("Location: ../templates/login.php?error=system_error");
    exit();
}
?>