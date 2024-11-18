<?php
session_name('client_session');
session_start();

// Check if the logout action is confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset(); // Unset all session variables
    session_destroy(); // Destroy the session
    header('Location: homepage.php'); // Redirect to homepage or login page
    exit();
}
?>
