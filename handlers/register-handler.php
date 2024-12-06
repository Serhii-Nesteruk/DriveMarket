<?php

use Firebase\JWT\JWT;

require '../vendor/autoload.php';
$config = include('../config.php');

$error = ''; // Initialize error message variable

if (isset($_POST["register"])) {
    try {
        // Database connection
        $connect = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']}", $config['db_username'], $config['db_password']);
        $connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Validate input fields
        if (empty($_POST["username"])) {
            $error = 'Please Enter Username'; // Error for empty username
        } elseif (empty($_POST["email"])) {
            $error = 'Please Enter Email'; // Error for empty email
        } elseif (empty($_POST["password"])) {
            $error = 'Please Enter Password'; // Error for empty password
        } else {
            // Check if the email is already registered
            $query = "SELECT * FROM user WHERE user_email = ?";
            $statement = $connect->prepare($query);
            $statement->execute([$_POST["email"]]);
            $data = $statement->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $error = 'Email is already registered'; // Error for duplicate email
            } else {
                // Hash the password for secure storage
                $hashed_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

                // Insert the new user into the database
                $insert_query = "INSERT INTO user (user_name, user_email, user_password) VALUES (?, ?, ?)";
                $statement = $connect->prepare($insert_query);
                $statement->execute([$_POST["username"], $_POST["email"], $hashed_password]);

                // Generate JWT token
                $user_id = $connect->lastInsertId(); // Retrieve the ID of the newly inserted user
                $key = $config['jwt_key']; // Secret key
                $token = JWT::encode(
                    array(
                        'iat'   => time(), // Issued at
                        'nbf'   => time(), // Not before
                        'exp'   => time() + 3600, // Expiration time (1 hour)
                        'data'  => array(
                            'user_id'   => $user_id, // Include user ID in the token
                            'user_name' => $_POST["username"] // Include username in the token
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
            }
        }
    } catch (PDOException $e) {
        $error = 'Database error: ' . $e->getMessage(); // Handle database connection or query errors
    }
}
?>