<?php
    require '../handlers/register-handler.php';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveMarket - Zarejestruj się</title>
    <link rel="stylesheet" href="../styles/common.css">
    <link rel="stylesheet" href="../styles/register.css">
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

    <!-- Register Section -->
    <section class="register">
        <h1>Zarejestruj się</h1>
        <?php
        if (isset($_GET['error'])) {
            switch($_GET['error']) {
                case 'email_exists':
                    echo '<p class="error-message">Користувач з такою електронною поштою вже існує!</p>';
                    break;
                case 'empty_username':
                    echo '<p class="error-message">Будь ласка, введіть ім\'я користувача!</p>';
                    break;
                case 'empty_email':
                    echo '<p class="error-message">Будь ласка, введіть email!</p>';
                    break;
                case 'empty_password':
                    echo '<p class="error-message">Будь ласка, введіть пароль!</p>';
                    break;
                case 'registration_failed':
                    echo '<p class="error-message">Помилка реєстрації. Спробуйте ще раз.</p>';
                    break;
                case 'database_error':
                    echo '<p class="error-message">Помилка бази даних. Спробуйте пізніше.</p>';
                    break;
            }
        }
        ?>
        <form action="#" method="POST">
            <label for="username">Nazwa użytkownika:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Hasło:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" class="auth-btn" name="register">Zarejestruj się</button>
        </form>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 DriveMarket. Wszystkie prawa zastrzeżone.</p>
    </footer>
</body>
</html>
