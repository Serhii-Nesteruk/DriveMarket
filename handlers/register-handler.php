<?php

use Firebase\JWT\JWT;

require '../vendor/autoload.php';
$config = include('../config.php');

if (isset($_POST["register"])) {
    try {
        // Database connection
        $connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validate input fields
        if (empty($_POST["username"])) {
            header("Location: ../templates/register.php?error=empty_username");
            exit();
        } elseif (empty($_POST["email"])) {
            header("Location: ../templates/register.php?error=empty_email");
            exit();
        } elseif (empty($_POST["password"])) {
            header("Location: ../templates/register.php?error=empty_password");
            exit();
        }

        // Check if the email is already registered
        $query = "SELECT * FROM user WHERE user_email = ?";
        $statement = $connect->prepare($query);
        $statement->execute([$_POST["email"]]);
        $data = $statement->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            header("Location: ../templates/register.php?error=email_exists");
            exit();
        }

        // Hash the password for secure storage
        $hashed_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

        // Insert the new user into the database
        $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, ?)";
        $statement = $connect->prepare($insert_query);
        
        if ($statement->execute([$_POST["username"], $_POST["email"], $hashed_password])) {
            // Generate JWT token
            $user_id = $connect->lastInsertId();
            $key = $config['jwt_key'];
            $token = JWT::encode(
                array(
                    'iat' => time(),
                    'nbf' => time(),
                    'exp' => time() + 3600,
                    'data' => array(
                        'user_id' => $user_id,
                        'user_name' => $_POST["username"]
                    )
                ),
                $key,
                'HS256'
            );

            // Set the JWT token as a secure cookie
            setcookie("token", $token, time() + 3600, "/", "", true, true);

            // Redirect to the homepage
            header('location:../index.html');
            exit();
        } else {
            header("Location: ../templates/register.php?error=registration_failed");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: ../templates/register.php?error=database_error");
        exit();
    }
}
?>