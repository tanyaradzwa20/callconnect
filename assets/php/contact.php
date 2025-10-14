<?php

// Set a default response for errors
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

// Only process POST requests.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ===================================================================
    // CONFIGURE YOUR EMAIL ADDRESS HERE
    // ===================================================================
    $recipient_email = "your-email@your-domain.com"; // <-- 🚨 REPLACE THIS!

    // --- DATA SANITIZATION ---
    // Get data from the form and clean it to prevent security issues
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = filter_var(trim($_POST['subject']), FILTER_SANITIZE_STRING);
    $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_STRING);

    // --- VALIDATION ---
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response['message'] = 'Please fill in all the required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
    } else {
        // --- EMAIL CONSTRUCTION ---
        $email_subject = "New Website Contact Form: " . $subject;
        
        $email_body = "You have received a new message from your website contact form.\n\n";
        $email_body .= "Name: $name\n";
        $email_body .= "Email: $email\n\n";
        $email_body .= "Message:\n$message\n";

        // Set the email headers to ensure the 'From' field is the user's email
        $headers = "From: $name <$email>\r\n";
        $headers .= "Reply-To: $email\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        // --- SEND THE EMAIL ---
        if (mail($recipient_email, $email_subject, $email_body, $headers)) {
            // If the mail() function succeeds, update the response
            $response['success'] = true;
            $response['message'] = 'Email sent successfully!';
        } else {
            // If mail() fails, it's a server-side issue
            $response['message'] = 'Sorry, the email could not be sent. Please check server configurations.';
        }
    }
} else {
    // This handles cases where the script is accessed directly, not via POST
    $response['message'] = 'Invalid request method.';
}

// --- SEND JSON RESPONSE BACK TO JAVASCRIPT ---
// This is what the fetch() function in your JavaScript will receive
header('Content-Type: application/json');
echo json_encode($response);
?>