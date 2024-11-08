<?php
session_start();
$pageTitle = 'Register';
include './init.php';
include 'functions.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <style>
        .form-container {
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            color: #555;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .form-container .btn {
            width: 100%;
        }
        .message {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Create a New Account</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="message"><?php echo $_SESSION['success']; ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <p class="error"><?php echo implode('<br>', $_SESSION['errors']); ?></p>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="register_process.php" method="POST">
        <input type="text" name="username" placeholder="Username" required> 
        <input type="text" name="name_customer" placeholder="Full Name" required>
        <input type="email" name="email_customer" placeholder="Email" required>
        <input type="text" name="phone_customer" placeholder="Contact Number" required>
        <input type="text" name="address" placeholder="Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button class="btn btn-primary" type="submit">Register</button>
    </form>
</div>
</body>
</html>
