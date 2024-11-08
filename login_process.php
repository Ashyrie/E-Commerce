<?php
ob_start(); // Start output buffering
session_start();
include './init.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = filterInput($_POST['username']); // Use username for login
    $password = $_POST['password'];

    // Check for the customer by username
    $stmt = $con->prepare("SELECT * FROM customers WHERE username = ?");
    $stmt->execute([$username]);
    $customer = $stmt->fetch();

    if ($customer && password_verify($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['id'];
        $_SESSION['username'] = $customer['name_customer']; // Store username for display
        header('Location: index.php'); // Redirect to index.php on success
        exit();
    } else {
        $_SESSION['error'] = 'Invalid username or password'; // Set error message
        header('Location: login.php'); // Redirect back to login page
        exit();
    }
}

ob_end_flush(); // Flush the output buffer
?>
