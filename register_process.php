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

require 'vendor/autoload.php';  // Make sure you have PHPMailer installed using Composer

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filterInput($_POST['username']); // Get username from form
    $name_customer = filterInput($_POST['name_customer']);
    $company_name = filterInput($_POST['company_name']);
    $company_address = filterInput($_POST['company_address']);
    $job_title = filterInput($_POST['job_title']);
    $email_customer = filterInput($_POST['email_customer']);
    $phone_customer = filterInput($_POST['phone_customer']);
    $address = filterInput($_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Check for duplicate username and email
    if (checkDuplicate($con, 'username', $username)) {
        $errors[] = 'Username is already taken';
    }
    if (checkDuplicate($con, 'email_customer', $email_customer)) {
        $errors[] = 'Email is already registered';
    }

    // Validate password match
    if (!validatePasswordMatch($password, $confirm_password)) {
        $errors[] = 'Passwords do not match';
    }

    if (empty($errors)) {
        // Generate random 6 digit code
        $verification_code = generateVerificationCode();

        // Insert customer details into the `customers` table
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $con->prepare("INSERT INTO customers (username, name_customer, email_customer, phone_customer, address, password, verification_code, date_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $name_customer, $email_customer, $phone_customer, $address, $hashedPassword, $verification_code]);

        // Get the last inserted customer ID
        $customer_id = $con->lastInsertId();

        // Insert company details into the `customer_companies` table
        if (!empty($company_name) && !empty($company_address) && !empty($job_title)) {
            $stmt_company = $con->prepare("INSERT INTO customer_companies (customer_id, company_name, company_address, job_title, business_document) VALUES (?, ?, ?, ?, ?)");
            $stmt_company->execute([$customer_id, $company_name, $company_address, $job_title, '']); // Assuming the business document is optional
        }

        // Store email in session for later use
        $_SESSION['email_customer'] = $email_customer;

        // Send verification code to email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.mailersend.net';  // Specify your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net';  // SMTP username
            $mail->Password = 'dueGu4EUSqlCTvI3';          // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net', 'Deltech Parking System');
            $mail->addAddress($email_customer); // Add recipient

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Your Registration Verification Code';
            $mail->Body    = 'Your verification code is: <strong>' . $verification_code . '</strong>';

            $mail->send();

            $_SESSION['success'] = 'A verification code has been sent to your email. Please check your inbox and verify your account.';
            header('Location: verify_code.php');  // Redirect to verification page
            exit();
        } catch (Exception $e) {
            $_SESSION['errors'][] = 'Mailer Error: ' . $mail->ErrorInfo;
            header('Location: register.php');
            exit();
        }
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: register.php');
        exit();
    }
}

ob_end_flush(); // End output buffering

// Function to generate a random 6-digit code
function generateVerificationCode() {
    return mt_rand(100000, 999999);
}
