<?php
// buffer need para hindi mag error
ob_start();
session_start();
include './init.php';  
include '../functions.php'; 



// PHPMailer
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filterInput($_POST['email']); 

    // Check if the email exists in the admin table
    $stmt = $con->prepare("SELECT id, username FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin) {
        // Email exists, so generate a reset token
        $resetToken = bin2hex(random_bytes(16));  // Generate a secure token
        $stmt = $con->prepare("UPDATE admin SET reset_token = ? WHERE email = ?");
        $stmt->execute([$resetToken, $email]);

        // d2 reset link
        $resetLink = "http://localhost/E-Commerce/admin/reset_pass.php?token=" . $resetToken;

        // Set PHPMailer 
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.mailersend.net';   
            $mail->SMTPAuth = true;
            $mail->Username = 'MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net'; 
            $mail->Password = 'dueGu4EUSqlCTvI3'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  
            $mail->Port = 587;  

            // Recipients
            $mail->setFrom('MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net', 'Admin Reset Password');
            $mail->addAddress($email);  // Add recipient email

            // Content d2
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "<p>To reset your password, click the link below:</p>
                             <p><a href='$resetLink'>$resetLink</a></p>";

            // Send the email
            $mail->send();

            
            $_SESSION['success'] = 'Password reset link sent to your email address.';
            header('Location: forgot_pass.php');
            exit();
        } catch (Exception $e) {
           
            $_SESSION['error'] = 'Mailer Error: ' . $mail->ErrorInfo;
            header('Location: forgot_pass.php');
            exit();
        }
    } else {
        
        $_SESSION['error'] = 'No account found with that email address.';
        header('Location: forgot_pass.php');
        exit();
    }
}
// End output buffering and flush
ob_end_flush();
?>
