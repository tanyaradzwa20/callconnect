<?php
// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load environment variables from .env file (no vendor folder needed)
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
        } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
            $value = $matches[1];
        }
        
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
    return true;
}

// Load .env file from project root
loadEnv(__DIR__ . '/../../.env');

// Load PHPMailer files manually
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// Set a default response for errors
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize user input - ADD PHONE NUMBER HERE
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING); // ADDED PHONE NUMBER
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

    // Validate input - UPDATE VALIDATION TO INCLUDE PHONE
    if (empty($name) || empty($email) || empty($phone) || empty($subject) || empty($message)) {
        $response['message'] = 'Please fill in all the required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        
        $mail = new PHPMailer(true);

        try {
            // ===================================================================
            // 2. SMTP CONFIGURATION (READING SECURELY FROM .env) ✅
            // ===================================================================
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];      // Should be 'smtp.gmail.com' in your .env file
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USER'];      // Your full Gmail address
            $mail->Password   = $_ENV['SMTP_PASS'];      // Your 16-digit Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['SMTP_PORT'];      // Should be 587
            // ===================================================================

            // RECIPIENTS
            // The 'From' address should match your Username for best results
            $mail->setFrom($_ENV['SMTP_USER'], 'CallConnect Website');

            // Set the addresses where you want to RECEIVE the emails
            //$mail->addAddress('shaneez@callconnect.co.zw', 'Shaneez');
            $mail->addAddress('ngonimabasa1964@gmail.com', 'CallConnect');
            
            // Set the reply-to address to the user who filled the form
            $mail->addReplyTo($email, $name);                         

            // CONTENT
            $mail->isHTML(false); // Set email format to plain text
            $mail->Subject = 'New Website Contact: ' . $subject;
            $mail->Body    = "You have received a new message from your website contact form.\n\n" .
                           "Name: " . $name . "\n" .
                           "Email: " . $email . "\n" .
                           "Phone: " . $phone . "\n\n" .  // ADDED PHONE NUMBER HERE
                           "Message:\n" . $message;

            $mail->send();
            $response['success'] = true;
            $response['message'] = 'Your message has been sent successfully!';

        } catch (Exception $e) {
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
