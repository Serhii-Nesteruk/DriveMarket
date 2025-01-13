<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';
$config = include(__DIR__ . '/../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $message = htmlspecialchars($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        http_response_code(400);
        echo "Wypełnij wszystkie pola.";
        exit;
    }

    try {
        $emailToSend = new \SendGrid\Mail\Mail(); 
        $emailToSend->setFrom("serhiinesteruk0@gmail.com", $name); 
        $emailToSend->setSubject("Ta wiadomość z formularza opinii");
        $emailToSend->addTo("serhiinesteruk0@gmail.com", "Serhii Nesteruk"); 
        $emailToSend->addContent(
            "text/plain", 
            "Ім'я: $name\nEmail: $email\nWiadomość:\n$message"
        );
        $emailToSend->addContent(
            "text/html", 
            "<strong>Ім'я:</strong> $name<br><strong>Email:</strong> $email<br><br><strong>Powiadomienie:</strong><br>$message"
        );

        $apiKey = $config['sendgrid_api_key']; 
        $sendgrid = new \SendGrid($apiKey);

        $response = $sendgrid->send($emailToSend);
        if ($response->statusCode() === 202) {
            echo "Dziekujemy, $name! Twoja wiadomość została pomyślnie wysłana.";
        } else {
            http_response_code(500);
            echo "Niestety, nie można wysłać wiadomości. Kod stanu: " . $response->statusCode();
            echo "\nOdpowiedź serwera: " . $response->body();
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo 'Błąd: ' . $e->getMessage();
    }
} else {
    http_response_code(405);
    echo "Ta metoda jest niedozwolona.";
}
?>
