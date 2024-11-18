<?php
ob_start();
session_name('client_session'); // Start output buffering
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

    // Check if customer exists and if password matches
    if ($customer) {
        // Check if the account is verified
        if ($customer['is_verified'] == 0) {
            $_SESSION['error'] = 'Your account review is ongoing, please wait.';
            header('Location: login.php');
            exit();
        }

        // Check if the password is correct
        if (password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['username'] = $customer['name_customer']; // Store username for display
            header('Location: index.php'); // Redirect to index.php on success
            exit();
        } else {
            $_SESSION['error'] = 'Invalid username or password'; // Set error message
            header('Location: login.php'); // Redirect back to login page
            exit();
        }
    } else {
        $_SESSION['error'] = 'Invalid username or password'; // Set error message
        header('Location: login.php'); // Redirect back to login page
        exit();
    }
}

ob_end_flush(); // Flush the output buffer
?>
