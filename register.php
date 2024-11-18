<?php
session_name('client_session');
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
            max-width: 800px; 
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

        .form-container form {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr; 
            gap: 20px; 
            align-items: center; 
        }

        .form-container .input-group {
            grid-column: span 2; 
            display: flex;
            gap: 20px; 
        }

        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }

        .form-container .btn {
            width: auto; 
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 20px auto;
            display: block;
        }

        .form-container .btn:hover {
            background-color: #45a049;
        }

        .message,
        .error {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }

        .error {
            color: red;
        }

        @media (max-width: 768px) {
            .form-container {
                max-width: 100%;
                padding: 20px;
            }

            .form-container form {
                grid-template-columns: 1fr 1fr; 
            }

            .form-container .input-group {
                grid-column: span 2; 
            }

            .form-container .btn {
                grid-column: span 2; 
            }
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
        <input type="text" name="company_name" placeholder="Company Name" required>
        <input type="text" name="company_address" placeholder="Company Address" required>
        <input type="text" name="job_title" placeholder="Job Title" required>
        <input type="email" name="email_customer" placeholder="Email" required>
        <input type="text" name="phone_customer" placeholder="Contact Number" required>
        <input type="text" name="address" placeholder="Address" required>

        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        
        <button class="btn" type="submit">Register</button>
    </form>
</div>
</body>
</html>
