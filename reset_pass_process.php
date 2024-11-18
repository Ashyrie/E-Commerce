<?php
session_name('client_session');
session_start();
ob_start(); // Start output buffering
include './init.php';
include 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the new passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: reset_pass.php?token=' . $token);
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password and clear the reset token
    $stmt = $con->prepare("UPDATE customers SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->execute([$hashedPassword, $token]);

    $_SESSION['success'] = 'Your password has been reset successfully. You can now log in.';
    header('Location: login.php'); // Redirect to login
    exit();
}

ob_end_flush(); // End output buffering
?>
