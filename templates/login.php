<?php
session_start();
if (isset($_COOKIE['token'])) {
    header('Location: ../index.html');
    exit();
}
require '../handlers/login-handler.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Zaloguj się</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/login.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="logo">DriveMarket</div>
        <nav class="menu">
            <a href="../index.html">Strona główna</a>
            <a href="#">Produkty</a>
            <a href="#">Kontakt</a>
        </nav>
    </header>

    <!-- Login Section -->
    <section class="login">
        <h1>Zaloguj się</h1>
        <form action="#" method="POST">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="email" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="auth-btn" name="login">Zaloguj się</button>
            <h2>Or login with:</h2>
            <a href="../handlers/login-handler.php?google-login=true" class="google-login-btn">Google</a>
            <a href="../handlers/facebook-login-handler.php?facebook-login=true" class="facebook-login-btn">Facebook</a>
        </form>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>

    <script src="../scripts/auth.js"></script>
</body>
</html>
