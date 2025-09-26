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
        $mail->Host = 'smtp.gmail.com';         // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'nishantkanada9@gmail.com';  // Your email
        $mail->Password = 'ymhx jyzn uwol jyau';     // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($toEmail, 'Admin');
        $mail->addAddress($toEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Account Details';
        $mail->Body = "
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
/**
 * Send task assignment email
 * @param string $toEmail Recipient email
 * @param string $toName  Recipient name
 * @param string $title   Task title
 * @param string $description Task description
 * @param string $start_date
 * @param string $due_date
 * @return bool
 */
function sendTaskAssignedEmail($toEmail, $toName, $title, $description, $start_date, $due_date)
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'nishantkanada9@gmail.com';   // your Gmail
        $mail->Password = 'ymhx jyzn uwol jyau';       // app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Task Manager');
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Task Assigned: $title";
        $mail->Body = "
            <h3>Hello $toName,</h3>
            <p>You have been assigned a new task. Here are the details:</p>
            <ul>
                <li><b>Task:</b> $title</li>
                <li><b>Description:</b> $description</li>
                <li><b>Start Date:</b> $start_date</li>
                <li><b>Due Date:</b> $due_date</li>
            </ul>
            <p>Please login to the system to update your progress.</p>
        ";

        return $mail->send();
    } catch (Exception $e) {
        error_log("Task email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}