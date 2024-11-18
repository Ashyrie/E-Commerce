<?php
session_name('client_session');
session_start();
ob_start(); // Start output buffering
include './init.php';
include 'functions.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_customer = filterInput($_POST['email_customer']);

    // Check if the email exists in the database
    $stmt = $con->prepare("SELECT * FROM customers WHERE email_customer = ?");
    $stmt->execute([$email_customer]);
    $customer = $stmt->fetch();

    if ($customer) {
        // Generate a unique token for password reset
        $token = bin2hex(random_bytes(50));
        $stmt = $con->prepare("UPDATE customers SET reset_token = ? WHERE email_customer = ?");
        $stmt->execute([$token, $email_customer]);

        // Send email with PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.mailersend.net';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net'; //  SMTP username
            $mail->Password   = 'dueGu4EUSqlCTvI3'; //  SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net', 'Deltech Parking System');
            $mail->addAddress($email_customer);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = 'To reset your password, please click the link below: 
            http://localhost/E-Commerce/reset_pass.php?token=' . $token;

            $mail->send();
            $_SESSION['success'] = 'A reset link has been sent to your email.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Mailer Error: ' . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = 'No account found with that email address.';
    }

    header('Location: forgot_pass.php'); // Redirect back to forgot password page
    exit();
}

ob_end_flush(); // End output buffering
?>
