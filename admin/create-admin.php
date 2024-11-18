<?php
session_name('admin_session');
session_start();
$noNavbar = '';
$pageTitle = 'Create Admin Account';
include './init.php';

$do = isset($_GET['do']) ? $_GET['do'] : 'view';

if ($do == 'view') {
?>
    <div class="login py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <form action="./create-admin.php?do=create" autocomplete="off" method="post" enctype="multipart/form-data">
                        <h1>Create New Admin Account - Deltech | Store</h1>
                        <?php if (isset($_SESSION['message'])) : ?>
                            <div id="message">
                                <?php echo $_SESSION['message']; ?>
                            </div>
                        <?php unset($_SESSION['message']);
                        endif; ?>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="floatingUsername" placeholder="Username" name="username" required="required">
                            <label for="floatingUsername">Username</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="floatingFullname" placeholder="Full Name" name="fullname" required="required">
                            <label for="floatingFullname">Full Name</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="floatingEmail" placeholder="name@example.com" name="email" required="required">
                            <label for="floatingEmail">Email</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <textarea class="form-control" id="floatingBiographical" placeholder="Biographical" name="biographical" style="height: 100px" required="required"></textarea>
                            <label for="floatingBiographical">Biographical</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="floatingPhone" placeholder="Phone" name="phone" required="required">
                            <label for="floatingPhone">Phone</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password" required="required">
                            <label for="floatingPassword">Password</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="floatingRole" placeholder="Role" name="role" required="required">
                            <label for="floatingRole">Role</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" name="create">Create Account</button>
                            <a href="index.php" class="btn btn-secondary">Back to Login</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php
} elseif ($do == 'create') {
    if (isset($_POST['create'])) {
        $username = $_POST['username'];
        $password = sha1($_POST['password']);
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $biographical = $_POST['biographical'];
        $phone = $_POST['phone'];
        $role = $_POST['role'];
        $status = $_POST['status'] ?? 'active'; // Default to 'active' if not set
        
        // Check if username already exists
        $stmt = $con->prepare("SELECT `id` FROM `admin` WHERE `username` = ?");
        $stmt->execute(array($username));
        if ($stmt->rowCount() > 0) {
            show_message('Username already exists. Please choose another username.', 'danger');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        
        // Check if email already exists
        $stmt = $con->prepare("SELECT `id` FROM `admin` WHERE `email` = ?");
        $stmt->execute(array($email));
        if ($stmt->rowCount() > 0) {
            show_message('Email already exists. Please use another email address.', 'danger');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
        
        // Insert new admin
        $stmt = $con->prepare("INSERT INTO `admin` (`username`, `password`, `fullname`, `email`, `biographical`, `phone`, `role`, `status`, `created`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute(array($username, $password, $fullname, $email, $biographical, $phone, $role, $status));
        
        if ($stmt->rowCount() > 0) {
            show_message('New admin account created successfully', 'success');
            header('location: index.php');
            exit();
        } else {
            show_message('Error creating admin account. Please try again.', 'danger');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else {
        header('location: create-admin.php');
        exit();
    }
}

include $tpl . 'footer.php';