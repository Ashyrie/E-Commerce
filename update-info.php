<?php 
session_name('client_session');
session_start();
include './init.php';

// Check if the customer is logged in
if (isset($_SESSION['customer_id'])) {
    $customer_id = $_SESSION['customer_id'];

    // Retrieve existing customer data
    $stmt = $con->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customer_id]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $contact_number = $_POST['contact_number'];
        $address = $_POST['address'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if any information was changed
        $isChanged = (
            $username !== $customer['username'] ||
            $full_name !== $customer['name_customer'] ||
            $email !== $customer['email_customer'] ||
            $contact_number !== $customer['phone_customer'] ||
            $address !== $customer['address'] ||
            (!empty($password) && password_verify($password, $customer['password']) === false)
        );

        // Display a message if no changes were made
        if (!$isChanged) {
            echo "<script>alert('Edit any of the information to proceed');</script>";
        } 
        // Basic form validation
        elseif (empty($username) || empty($full_name) || empty($email) || empty($contact_number) || empty($address)) {
            echo "<div class='alert alert-danger'>Please fill in all required fields.</div>";
        } elseif ($password !== $confirm_password) {
            echo "<div class='alert alert-danger'>Passwords do not match.</div>";
        } else {
            // If password is changed, hash it; otherwise, keep the old password
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            } else {
                $hashed_password = $customer['password'];
            }

            // Update customer information in the database
            $stmt = $con->prepare("UPDATE customers SET username = ?, name_customer = ?, email_customer = ?, phone_customer = ?, address = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $full_name, $email, $contact_number, $address, $hashed_password, $customer_id]);

            echo "<div class='alert alert-success'>Information updated successfully!</div>";
            echo "<script>
                    alert('Information updated successfully!');
                    setTimeout(function() {
                        window.location.href = 'homepage.php';
                    }, 1500); // Redirect after 1.5 seconds
                  </script>";
        }
    }
} else {
    echo "<div class='alert alert-danger'>Please log in to update your information.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Information</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .btn-update {
            border-radius: 20px;
            background-color: #007bff;
            color: #fff;
            width: 50%; 
            margin-top: 10px;
            font-weight: bold;
        }
        .btn-update:hover {
            background-color: #0056b3; 
        }

        .btn-back {
            border-radius: 20px;
            background-color: #add8e6; 
            color: #000; 
            width: 50%; 
            margin-top: 10px;
            font-weight: bold;
        }
        .btn-back:hover {
            background-color: #0056b3; 
          
        }

        .button-container {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="col-md-6 mx-auto">
            <h2 class="text-center mt-4">UPDATE YOUR INFORMATION <br></h2>
            <form action="update-info.php" method="post" class="p-4 border rounded shadow-sm bg-light">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($customer['username']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($customer['name_customer']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($customer['email_customer']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="tel" name="contact_number" class="form-control" value="<?php echo htmlspecialchars($customer['phone_customer']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($customer['address']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password or leave blank">
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                </div>
                
                <div class="button-container">
                    <button type="submit" class="btn btn-update">UPDATE INFORMATION</button>
                    <a href="homepage.php" class="btn btn-back">BACK</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
