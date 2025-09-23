<?php
// Include PHPMailer classes
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send account credentials email to new user
 * @param string $toEmail  Recipient email
 * @param string $userName Recipient name
 * @param string $password Plain password
 * @return bool
 */
function sendUserCredentials($toEmail, $userName, $password)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';         // Your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'nishantkanada9@gmail.com';  // Your email
        $mail->Password   = 'ymhx jyzn uwol jyau';     // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Admin');
        $mail->addAddress($toEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Account Details';
        $mail->Body    = "
            <h3>Welcome to the System</h3>
            <p>Your account has been created successfully.</p>
            <p><strong>Email:</strong> $toEmail</p>
            <p><strong>Password:</strong> $password</p>
            <p>Please login and change your password after first login.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
