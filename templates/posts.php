<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Produkty</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/posts.css">
</head>
<body>
    <header class="header">
        <div class="logo">DriveMarket</div>
        <nav class="menu">
            <a href="../index.html">Strona główna</a>
            <a href="posts.php">Produkty</a>
            <a href="#">Kontakt</a>
        </nav>
        <div class="auth-container">
            <div class="auth-buttons">
                <a href="login.php" class="auth-btn">Zaloguj się</a>
                <a href="register.php" class="auth-btn">Zarejestruj się</a>
            </div>
            <div class="user-menu">
                <div class="avatar-circle" onclick="toggleDropdown()">
                    <span id="userInitials">A</span>
                </div>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">Profil</a>
                    <button onclick="logout()">Wyloguj</button>
                </div>
            </div>
        </div>
    </header>

    <section class="posts">
        <h1>Nasze Posty</h1>
        <div class="post">
            <div class="post-box"></div>
            <p>Post 1</p>
        </div>
        <div class="post">
            <div class="post-box"></div>
            <p>Post 2</p>
        </div>
        <div class="post">
            <div class="post-box"></div>
            <p>Post 3</p>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>

    <script src="../scripts/auth.js"></script>
    <script src="../scripts/posts.js"></script>
</body>
</html> 