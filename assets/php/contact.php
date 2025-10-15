<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if you're using it) or load files manually
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Set a default response for errors
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize user input
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

    // Validate input
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = 'Please fill in all the required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        
        $mail = new PHPMailer(true);

        try {
            // ===================================================================
            // SMTP CONFIGURATION - GET THIS FROM YOUR HOSTING PROVIDER
            // ===================================================================
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; //mail.callconnect.co.zw        // Your SMTP server (e.g., smtp.gmail.com or your host's SMTP server)
            $mail->SMTPAuth   = true;
            $mail->Username   = '';         // Your SMTP username (your full email address)
            $mail->Password   = '';     // Your SMTP password (or app-specific password for Gmail)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use 'tls' or 'ssl' (PHPMailer::ENCRYPTION_STARTTLS)
            $mail->Port       = 587;                       // Port for SSL is 465, for TLS is 587
            // ===================================================================

            // RECIPIENTS
            $mail->setFrom('ngonimabasa1964@gmail.com', 'CallConnect Website'); // This can be the same as your Username
            $mail->addAddress('ngonimabasa1964@gmail.com', 'Ngoni');      // Add the main recipient
            $mail->addAddress('ngoni7596@gmail.com','Ngo');           // Add another recipient
            $mail->addReplyTo($email, $name);                         // Set the reply-to address to the user who filled the form

            // CONTENT
            $mail->isHTML(false); // Set email format to plain text
            $mail->Subject = 'New Website Contact: ' . $subject;
            $mail->Body    = "You have received a new message from your website contact form.\n\n" .
                           "Name: " . $name . "\n" .
                           "Email: " . $email . "\n\n" .
                           "Message:\n" . $message;

            $mail->send();
            $response['success'] = true;
            $response['message'] = 'Your message has been sent successfully!';

        } catch (Exception $e) {
            // If PHPMailer fails, provide a detailed error for debugging
            $response['message'] = "Oops! An error occurred and your message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

// Send the JSON response back to the JavaScript
header('Content-Type: application/json');
echo json_encode($response);
?>