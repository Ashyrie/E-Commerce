<?php
session_start();
ob_start(); // Start output buffering
include './init.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filterInput($_POST['username']); // Get username from form
    $name_customer = filterInput($_POST['name_customer']);
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

    // Insert into the database if there are no errors
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $con->prepare("INSERT INTO customers (username, name_customer, email_customer, phone_customer, address, password, date_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $name_customer, $email_customer, $phone_customer, $address, $hashedPassword]);
        
        // Set a success message and redirect to the login page
        $_SESSION['success'] = 'Account created successfully. Please login.';
        header('Location: login.php'); // Redirect to login
        exit();
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: register.php'); // Redirect back to registration
        exit();
    }
}

ob_end_flush(); // End output buffering
?>
