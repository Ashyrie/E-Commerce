<?php
session_name('client_session');
session_start();
$pageTitle = 'Reset Password';
include './init.php';

if (!isset($_GET['token'])) {
    header('Location: login.php'); 
    exit();
}

$token = $_GET['token'];
$stmt = $con->prepare("SELECT * FROM customers WHERE reset_token = ?");
$stmt->execute([$token]);
$customer = $stmt->fetch();

if (!$customer) {
    $_SESSION['error'] = 'Invalid token.';
    header('Location: login.php');
    exit();
}
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
    <h2>Reset Your Password</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <p class="message"><?php echo $_SESSION['success']; ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form action="reset_pass_process.php" method="POST">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button class="btn btn-primary" type="submit">Reset Password</button>
    </form>
</div>
</body>
</html>
