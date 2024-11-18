<?php
session_name('client_session');
session_start();
ob_start(); // Start output buffering

include './init.php';
include 'functions.php';

// Ensure the user is logged in before uploading
if (!isset($_SESSION['email_customer'])) {
    $_SESSION['errors'][] = 'You must be logged in to upload documents.';
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_customer = $_SESSION['email_customer']; // Get the user's email from session

    // Validate file upload
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $file_name = $_FILES['document']['name'];
        $file_tmp = $_FILES['document']['tmp_name'];
        $file_size = $_FILES['document']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Define allowed file types (e.g., .jpg, .jpeg, .png, .pdf, .docx)
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'doc'];
        
        // Check if file type is allowed
        if (!in_array($file_ext, $allowed_extensions)) {
            $_SESSION['errors'][] = 'Invalid file type. Only JPG, JPEG, PNG, PDF, DOCX are allowed.';
            header('Location: upload_document.php');
            exit();
        }

        // Check if file size is less than 5MB (adjust as needed)
        if ($file_size > 5 * 1024 * 1024) {
            $_SESSION['errors'][] = 'File size must be less than 5MB.';
            header('Location: upload_document.php');
            exit();
        }

        // Define upload directory and create folder if not exists
        $upload_dir = 'admin/customerfileupload/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique file name to avoid overwriting
        $unique_file_name = uniqid() . '.' . $file_ext;

        // Move file to upload directory
        if (move_uploaded_file($file_tmp, $upload_dir . $unique_file_name)) {
            // First, check if the customer has a company record in the customer_companies table
            $stmt = $con->prepare("SELECT * FROM customer_companies WHERE customer_id = (SELECT id FROM customers WHERE email_customer = ?)");
            $stmt->execute([$email_customer]);
            $company = $stmt->fetch();

            if ($company) {
                // If company exists, update the business document in the customer_companies table
                $stmt = $con->prepare("UPDATE customer_companies SET business_document = ? WHERE customer_id = (SELECT id FROM customers WHERE email_customer = ?)");
                $stmt->execute([$upload_dir . $unique_file_name, $email_customer]);
                
                $_SESSION['success'] = 'Your business document has been successfully uploaded.';
                header('Location: login.php'); // Redirect to a dashboard or profile page after successful upload
                exit();
            } else {
                $_SESSION['errors'][] = 'No company record found for this customer. Please contact support.';
                header('Location: upload_document.php');
                exit();
            }
        } else {
            $_SESSION['errors'][] = 'Failed to upload the document. Please try again.';
            header('Location: upload_document.php');
            exit();
        }
    } else {
        $_SESSION['errors'][] = 'No file uploaded or an error occurred during upload.';
        header('Location: upload_document.php');
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
    <title>Upload Business Document</title>
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
        .form-container input[type="file"] {
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
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Upload Your Business Document</h2>

    <!-- Display success or error messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success">
            <?php echo $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['errors'])): ?>
        <div class="error">
            <?php echo implode('<br>', $_SESSION['errors']); ?>
            <?php unset($_SESSION['errors']); ?>
        </div>
    <?php endif; ?>

    <form action="upload_document.php" method="POST" enctype="multipart/form-data">
        <label for="document">Upload Document</label>
        <input type="file" name="document" id="document" accept=".jpg, .jpeg, .png, .pdf, .docx" required>

        <button class="btn" type="submit">Upload</button>
    </form>
</div>

</body>
</html>
