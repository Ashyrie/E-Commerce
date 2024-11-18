<?php
session_name('client_session');
session_start();
ob_start(); // Start output buffering
$pageTitle = 'Verify Code';
include './init.php';
include 'functions.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $verification_code = filterInput($_POST['verification_code']);
    $email_customer = $_SESSION['email_customer']; // Save email in session when sending email

    // Validate the code
    $stmt = $con->prepare("SELECT * FROM customers WHERE email_customer = ? AND verification_code = ?");
    $stmt->execute([$email_customer, $verification_code]);
    $customer = $stmt->fetch();

    if ($customer) {
        // Clear the verification code once it's verified
        $stmt = $con->prepare("UPDATE customers SET verification_code = NULL WHERE id = ?");
        $stmt->execute([$customer['id']]);

        $_SESSION['success'] = 'Your account has been successfully verified. Proceeding to business document upload.';
        header('Location: verify_code.php?verified=true'); // Redirect to the same page with a query parameter
        exit();
    } else {
        $_SESSION['errors'][] = 'Invalid verification code.';
        header('Location: verify_code.php');
        exit();
    }
}

ob_end_flush(); // End output buffering
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
            grid-template-columns: 1fr;
            gap: 20px;
            align-items: center;
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

        /* Modal (Pop-up) Style */
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 30px;
            background-color: #fff;
            color: #555;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.2);
            display: none; /* Hide by default */
            z-index: 9999;
        }

        .popup.show {
            display: block;
        }

        .popup p {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .popup .btn {
            background-color: #4CAF50; /* Green */
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .popup .btn:hover {
            background-color: #45a049;
        }

        @media (max-width: 768px) {
            .form-container {
                max-width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Verify Your Account</h2>

    <?php if (isset($_SESSION['success']) && isset($_GET['verified'])): ?>
        <div class="popup show" id="popup">
            <p><?php echo $_SESSION['success']; ?></p>
            <button class="btn" id="closePopup">OK</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <p class="error"><?php echo implode('<br>', $_SESSION['errors']); ?></p>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form action="verify_code.php" method="POST">
        <input type="text" name="verification_code" placeholder="Enter the 6-digit code" required>
        <button class="btn" type="submit">Verify</button>
    </form>
</div>

<script>
    // Check if the popup needs to be shown
    const popup = document.getElementById('popup');
    const closeButton = document.getElementById('closePopup');

    if (popup) {
        setTimeout(function() {
            // After 3 seconds, automatically redirect to the upload document page
            window.location.href = 'upload_document.php';
        }, 3000);
    }

    // Close the popup manually if the user clicks the OK button
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            window.location.href = 'upload_document.php';
        });
    }
</script>
</body>
</html>
