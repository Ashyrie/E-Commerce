<?php
session_name('admin_session');
session_start();

// PHPMailer
require '../vendor/phpmailer/phpmailer/src/Exception.php';
require '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/phpmailer/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';  //  PHPMailer 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contact_id = $_POST['contact_id'];
    $recipient_email = $_POST['recipient_email'];
    $reply_message = $_POST['reply_message'];

    // PHPMailer configuration
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.mailersend.net';  
        $mail->SMTPAuth = true;
        $mail->Username = 'MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net';  // SMTP username
        $mail->Password = 'dueGu4EUSqlCTvI3';  // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net', 'Your Name');
        $mail->addAddress($recipient_email);  // Recipient's email

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Reply to you Message on Deltech';
        $mail->Body    = nl2br($reply_message);  // yung message 

        $mail->send();

        $_SESSION['message'] = 'Reply sent successfully';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}
?>