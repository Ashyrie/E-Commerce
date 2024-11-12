<?php
ob_start();
session_name('admin_session');
session_start();
$pageTitle = 'Customers';
include './init.php';

if (isset($_SESSION['username'])) {
    $do = isset($_GET['do']) ? $_GET['do'] : 'dashboard';

    // Initialize search and filter variables
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $date_filter = isset($_POST['date_filter']) ? $_POST['date_filter'] : '';

    // Pagination variables
    $limit = 30; // Number of customers per page
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Prepare the SQL query with search and filter
    $sql = "SELECT * FROM customers WHERE 1=1";
    
    if (!empty($search)) {
        $sql .= " AND name_customer LIKE :search";
    }
    
    if (!empty($date_filter)) {
        $sql .= " AND date_at = :date_filter"; // Adjust date condition as necessary
    }
    
    $sql .= " ORDER BY date_at DESC LIMIT :offset, :limit";
    
    $ListCustomer = $con->prepare($sql);
    
    // Bind parameters
    if (!empty($search)) {
        $ListCustomer->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    }
    
    if (!empty($date_filter)) {
        $ListCustomer->bindValue(':date_filter', $date_filter);
    }

    $ListCustomer->bindValue(':offset', $offset, PDO::PARAM_INT);
    $ListCustomer->bindValue(':limit', $limit, PDO::PARAM_INT);
    
    $ListCustomer->execute();
    $customers = $ListCustomer->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total number of customers for pagination
    $totalCustomers = $con->query("SELECT COUNT(*) FROM customers WHERE 1=1" . 
        (!empty($search) ? " AND name_customer LIKE '%$search%'" : "") . 
        (!empty($date_filter) ? " AND date_at = '$date_filter'" : "")
    )->fetchColumn();

    $totalPages = ceil($totalCustomers / $limit);
    
    if ($do == 'dashboard') {
?>
        <div class="customers">
            <div class="container">
                <h1 class="">Customers</h1>
                
                <!-- Search and Filter Form -->
                <form method="post" class="mb-3 row align-items-end">
                    <div class="col-md-4">
                        <input type="text" name="search" placeholder="Search by name" class="form-control" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <input type="date" name="date_filter" class="form-control" value="<?php echo htmlspecialchars($date_filter); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <?php if (isset($_SESSION['message'])) : ?>
                        <div id="message" class="alert alert-success">
                            <?php echo $_SESSION['message']; ?>
                        </div>
                    <?php unset($_SESSION['message']);
                    endif; ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="text-bg-light">
                                <th>Name</th>
                                <th>Company</th>  <!-- New column for Company -->
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date</th>
                                <th style="min-width: 200px;">Action</th> <!-- Fixed width for Action column -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['name_customer']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['company_name']); ?></td>  <!-- Displaying the company name -->
                                    <td><?php echo htmlspecialchars($customer['email_customer']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone_customer']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['date_at']); ?></td>
                                    <td>
                                        <form action="./edit-customers.php?do=action" method="post">
                                            <input type="hidden" name="id" value="<?php echo $customer['id']; ?>">
                                            <input type="hidden" name="name_customer" value="<?php echo htmlspecialchars($customer['name_customer']); ?>">
                                            <div class="d-flex gap-2">
                                                <!-- Action buttons are now inside a flex container for better layout -->
                                                <button type="submit" class="btn btn-success btn-sm" name="btn_edit">
                                                    <i class="fa-solid fa-pen-to-square"></i>&nbsp;Edit
                                                </button>
                                                <button type="submit" class="btn btn-danger btn-sm" name="btn_delete">
                                                    <i class="fa-solid fa-trash"></i>&nbsp;Delete
                                                </button>
                                                <!-- Permit/Suspend Button -->
                                                <?php if ($customer['is_verified'] == 1) : ?>
                                                    <button type="submit" class="btn btn-warning btn-sm" name="btn_suspend">
                                                        <i class="fa-solid fa-ban"></i>&nbsp;Suspend
                                                    </button>
                                                <?php else : ?>
                                                    <button type="submit" class="btn btn-success btn-sm" name="btn_permit">
                                                        <i class="fa-solid fa-check"></i>&nbsp;Permit
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <!-- Document Button -->
                                                <?php if (!empty($customer['business_document'])) : ?>
                                                    <a href="<?php echo 'customerfileupload/' . basename(htmlspecialchars($customer['business_document'])); ?>" target="_blank" class="btn btn-info btn-sm">
                                                        <i class="fa-solid fa-file-alt"></i>&nbsp;View Document
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?do=dashboard&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&date_filter=<?php echo urlencode($date_filter); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

<?php
    } elseif ($do == 'action') {
        if (isset($_POST['btn_edit'])) {
            $id = $_POST['id'];
            $edit = $con->prepare("SELECT id, name_customer, email_customer, phone_customer, company_name, date_at, is_verified FROM customers WHERE id = ? LIMIT 1");
            $edit->execute([$id]);
            $row = $edit->fetch();
            $count = $edit->rowCount();
            if ($count > 0) {
?>
                <div class="edit-customer">
                    <div class="container">
                        <a class="btn btn-light my-2" href="./edit-customers.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
                        <div class="col-md-6 mx-auto">
                            <h1>Edit Customer: <?php echo htmlspecialchars($row['name_customer']); ?></h1>
                            <form action="./edit-customers.php?do=update-true" method="post">
                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                <div class="form-group">
                                    <label for="name_customer">Full Name *</label>
                                    <input type="text" name="name_customer" id="name_customer" value="<?php echo htmlspecialchars($row['name_customer']); ?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label for="company_name">Company Name</label> <!-- Company Name Input -->
                                    <input type="text" name="company_name" id="company_name" value="<?php echo htmlspecialchars($row['company_name']); ?>" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="phone_customer">Phone *</label>
                                    <input type="tel" name="phone_customer" id="phone_customer" value="<?php echo htmlspecialchars($row['phone_customer']); ?>" class="form-control" required="required">
                                </div>
                                <div class="form-group">
                                    <label for="email_customer">Email Address *</label>
                                    <input type="email" name="email_customer" id="email_customer" value="<?php echo htmlspecialchars($row['email_customer']); ?>" class="form-control" required="required">
                                </div>
                                <button type="submit" class="btn btn-primary my-3" name="customer_update">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
            } else {
            ?>
                <div class="container">
                    <div class="alert alert-warning text-center mt-5" role="alert">
                        There's no such customer
                    </div>
                </div>
            <?php
                header('Refresh: 6; url=./edit-customers.php');
            }
        } elseif (isset($_POST['btn_delete'])) {
            $id = $_POST['id'];
            $name_customer = $_POST['name_customer'];
            $stmt = $con->prepare("DELETE FROM customers WHERE customers.id = ?");
            $stmt->execute([$id]);
            show_message('Customer ' . $name_customer . ' deleted successfully', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } elseif (isset($_POST['btn_permit'])) {
            $id = $_POST['id'];
            $stmt = $con->prepare("UPDATE customers SET is_verified = 1 WHERE id = ?");
            $stmt->execute([$id]);
            show_message('Customer permitted successfully', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } elseif (isset($_POST['btn_suspend'])) {
            $id = $_POST['id'];
            $stmt = $con->prepare("UPDATE customers SET is_verified = 0 WHERE id = ?");
            $stmt->execute([$id]);
            show_message('Customer suspended successfully', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } elseif ($do == 'update-true') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'];
            $name_customer = $_POST['name_customer'];
            $email_customer = $_POST['email_customer'];
            $phone_customer = $_POST['phone_customer'];
            $company_name = $_POST['company_name']; // New variable to handle company name

            // Fetch the current data from the database for comparison
            $stmt = $con->prepare("SELECT name_customer, email_customer, phone_customer, company_name FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();

            // Initialize an array to store the update parameters
            $updateFields = [];
            $params = [];

            // Check if each field has changed and add to updateFields array
            if ($name_customer !== $row['name_customer']) {
                $updateFields[] = "name_customer = ?";
                $params[] = $name_customer;
            }

            if ($email_customer !== $row['email_customer']) {
                $updateFields[] = "email_customer = ?";
                $params[] = $email_customer;
            }

            if ($phone_customer !== $row['phone_customer']) {
                $updateFields[] = "phone_customer = ?";
                $params[] = $phone_customer;
            }

            if ($company_name !== $row['company_name']) {
                $updateFields[] = "company_name = ?"; // Update company name if changed
                $params[] = $company_name;
            }

            // Only proceed if any fields need to be updated
            if (count($updateFields) > 0) {
                // Add the ID to the parameters
                $params[] = $id;

                // Build the update query
                $updateQuery = "UPDATE customers SET " . implode(", ", $updateFields) . " WHERE id = ?";
                $stmt = $con->prepare($updateQuery);
                $stmt->execute($params);

                show_message('Customer ' . htmlspecialchars($name_customer) . ' updated successfully', 'success');
            } else {
                show_message('No changes detected. Customer info remains unchanged.', 'info');
            }

            // Redirect to the customer management page
            header('location: ./edit-customers.php');
            exit();
        }
    }
} else {
    header('location: index.php');
    exit();
}

include $tpl . 'footer.php';
ob_end_flush();
?>
