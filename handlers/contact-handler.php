<?php
session_start();
require_once '../config.php';
require_once '../utils/database.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $subject = isset($_POST['subject']) ? sanitizeInput($_POST['subject']) : '';
    $message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';

    // Validate inputs
    if (empty($name)) {
        $response['errors'][] = 'name_required';
    }

    if (empty($email)) {
        $response['errors'][] = 'email_required';
    } elseif (!isValidEmail($email)) {
        $response['errors'][] = 'invalid_email';
    }

    if (empty($subject)) {
        $response['errors'][] = 'subject_required';
    }

    if (empty($message)) {
        $response['errors'][] = 'message_required';
    }

    // If no errors, proceed with saving to database
    if (empty($response['errors'])) {
        try {
            $db = new Database();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("
                INSERT INTO contact_messages (name, email, subject, message)
                VALUES (:name, :email, :subject, :message)
            ");

            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'message_sent';

                // Send email notification
                $to = "admin@drivemarket.pl"; // Replace with your admin email
                $emailSubject = "Nowa wiadomość kontaktowa: " . $subject;
                $emailBody = "Otrzymałeś nową wiadomość kontaktową:\n\n" .
                            "Od: " . $name . " (" . $email . ")\n" .
                            "Temat: " . $subject . "\n\n" .
                            "Wiadomość:\n" . $message;
                $headers = "From: " . $email . "\r\n" .
                          "Reply-To: " . $email . "\r\n" .
                          "X-Mailer: PHP/" . phpversion();

                mail($to, $emailSubject, $emailBody, $headers);

            } else {
                $response['errors'][] = 'database_error';
            }
        } catch (PDOException $e) {
            $response['errors'][] = 'database_error';
            // Log the error for debugging
            error_log("Contact form error: " . $e->getMessage());
        }
    }
}

// Redirect back to contact page with appropriate message
$errorString = !empty($response['errors']) ? '?error=' . implode(',', $response['errors']) : '';
$successString = $response['success'] ? '?success=true' : '';

header('Location: ../templates/contact.php' . ($response['success'] ? $successString : $errorString));
exit();
