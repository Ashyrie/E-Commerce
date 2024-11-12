<?php
ob_start(); 
session_name('admin_session');
session_start();
include './init.php';

// Check if token exists
if (isset($_POST['token'])) {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($newPassword === $confirmPassword) {
        $hashedPass = sha1($newPassword);

        // Update the admin password
        $stmt = $con->prepare("UPDATE admin SET password = ?, reset_token = NULL WHERE reset_token = ?");
        $stmt->execute([$hashedPass, $token]);

        $_SESSION['success'] = 'Password successfully reset. You can now log in.';
        header('Location: index.php');
    } else {
        $_SESSION['error'] = 'Passwords do not match!';
        header('Location: reset_pass.php?token=' . $token);
    }
} else {
    header('Location: index.php');
    exit();
}

ob_end_flush();
?>