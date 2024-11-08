<?php
ob_start();
session_start();
$pageTitle = 'Orders';
include './init.php';

if (isset($_SESSION['username'])) {
    $do = isset($_GET['do']) ? $_GET['do'] : 'dashboard';

    // Pagination settings
    $limit = 30;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $limit;

    // Prepare the base query
    $query = "SELECT 
        `orders`.`orders_number`,
        `customers`.`name_customer`,
        `customers`.`email_customer`,
        `customers`.`phone_customer`,
        `orders`.`order_date`,
        `orders`.`order_status`,
        GROUP_CONCAT(`orders`.`id`) as order_ids,
        GROUP_CONCAT(`orders`.`product_name` SEPARATOR '||') as products,
        GROUP_CONCAT(`orders`.`product_quantity` SEPARATOR '||') as quantities,
        GROUP_CONCAT(`orders`.`product_price` SEPARATOR '||') as prices,
        GROUP_CONCAT(`orders`.`subtotal` SEPARATOR '||') as subtotals,
        SUM(`orders`.`subtotal`) as total_amount,
        IF(sq.order_number IS NOT NULL, 'Verified', 'Not Verified') AS verified_status,
        c.symbol AS currency_symbol
        FROM `orders` 
        INNER JOIN `customers` ON `orders`.`customer_id` = `customers`.`id`
        LEFT JOIN `currencies` c ON `orders`.`currency` = c.currency
        LEFT JOIN `deltech_verify`.`successful_qr` AS sq ON `orders`.`orders_number` = sq.order_number";

    // Handle filters
    $conditions = [];
    if (isset($_POST['search_order_number']) && !empty($_POST['search_order_number'])) {
        $conditions[] = "`orders`.`orders_number` LIKE :order_number";
    }
    if (isset($_POST['filter_date']) && !empty($_POST['filter_date'])) {
        $conditions[] = "`orders`.`order_date` = :order_date";
    }
    if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
        $conditions[] = "`orders`.`order_status` = :order_status";
    }

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $query .= " GROUP BY `orders`.`orders_number`, `customers`.`name_customer`, `orders`.`order_date`, `orders`.`order_status`
                ORDER BY `orders`.`order_date` DESC
                LIMIT :limit OFFSET :offset";

    // Prepare and bind parameters
    $ListOrders = $con->prepare($query);
    if (isset($_POST['search_order_number']) && !empty($_POST['search_order_number'])) {
        $ListOrders->bindValue(':order_number', '%' . $_POST['search_order_number'] . '%', PDO::PARAM_STR);
    }
    if (isset($_POST['filter_date']) && !empty($_POST['filter_date'])) {
        $ListOrders->bindValue(':order_date', $_POST['filter_date']);
    }
    if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
        $ListOrders->bindValue(':order_status', $_POST['filter_status']);
    }
    $ListOrders->bindParam(':limit', $limit, PDO::PARAM_INT);
    $ListOrders->bindParam(':offset', $offset, PDO::PARAM_INT);
    $ListOrders->execute();
    $Orders = $ListOrders->fetchAll(PDO::FETCH_ASSOC);

    // Count total orders for pagination
    $countQuery = "SELECT COUNT(*) FROM `orders`";
    if ($conditions) {
        $countQuery .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $countStmt = $con->prepare($countQuery);
    if (isset($_POST['search_order_number']) && !empty($_POST['search_order_number'])) {
        $countStmt->bindValue(':order_number', '%' . $_POST['search_order_number'] . '%', PDO::PARAM_STR);
    }
    if (isset($_POST['filter_date']) && !empty($_POST['filter_date'])) {
        $countStmt->bindValue(':order_date', $_POST['filter_date']);
    }
    if (isset($_POST['filter_status']) && $_POST['filter_status'] !== '') {
        $countStmt->bindValue(':order_status', $_POST['filter_status']);
    }
    $countStmt->execute();
    $totalOrders = $countStmt->fetchColumn();
    $totalPages = ceil($totalOrders / $limit);

    if ($do == 'dashboard') {
?>
    <div class="orders">
        <div class="container">
            <h1>Orders&nbsp;<a class="btn btn-outline-primary" href="./edit-orders.php?do=add-new">Add New</a></h1>
            <form method="POST" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search_order_number" class="form-control" placeholder="Search Order Number" aria-label="Search Order Number">
                    <input type="date" name="filter_date" class="form-control" aria-label="Filter by Date">
                    <select name="filter_status" class="form-select">
                        <option value="">Filter by Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="processing">Processing</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>

            <div class="table-responsive">
                <?php if (isset($_SESSION['message'])) : ?>
                    <div id="message" class="alert alert-success">
                        <?php echo $_SESSION['message']; ?>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                <table class="table table-bordered" style="width: 100%; table-layout: auto;">
                    <thead>
                        <tr class="text-bg-light">
                            <th>Order/Tracking Number</th>
                            <th>Customer Name</th>
                            <th>Products</th>
                            <th>Total Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Verified Status</th> <!-- New Column -->
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Orders as $order) : 
                            // Split the concatenated strings into arrays
                            $products = explode('||', $order['products']);
                            $quantities = explode('||', $order['quantities']);
                            $prices = explode('||', $order['prices']);
                            $subtotals = explode('||', $order['subtotals']);
                            $order_ids = explode(',', $order['order_ids']);
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['orders_number']); ?></td>
                            <td><?php echo htmlspecialchars($order['name_customer']); ?></td>
                            <td>
                                <?php 
                                // Display all products with their quantities and prices
                                for ($i = 0; $i < count($products); $i++) {
                                    echo "<div class='product-item'>" . htmlspecialchars($products[$i]) . " <sup class='text-success fw-bold'>(Q: " . htmlspecialchars($quantities[$i]) . ")</sup> - " . htmlspecialchars($order['currency_symbol']) . number_format($prices[$i], 2) . " each = " . htmlspecialchars($order['currency_symbol']) . number_format($subtotals[$i], 2) . "</div>";
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($order['currency_symbol']) . number_format($order['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($order['order_status']); ?></td>
                            <td><?php echo htmlspecialchars($order['verified_status']); ?></td> <!-- Display Verified Status -->
                            <td>
                                <form action="./edit-orders.php?do=action" method="post" class="d-flex justify-content-between">
                                    <input type="hidden" name="orders_number" value="<?php echo htmlspecialchars($order['orders_number']); ?>">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($order_ids[0]); ?>">
                                    <button type="submit" class="btn btn-success me-1" name="btn_edit"><i class="fa-solid fa-pen-to-square"></i>&nbsp;Edit</button>
                                    <button type="submit" class="btn btn-info me-1" name="btn_view"><i class="fa-solid fa-eye"></i>&nbsp;View</button>
                                    <button type="submit" class="btn btn-danger" name="btn_delete"><i class="fa-solid fa-trash"></i>&nbsp;Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination links -->
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <style>
        .product-item {
            border-bottom: 1px solid #eee;
            padding: 5px 0;
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .table th, .table td {
            vertical-align: middle;
        }
    </style>

<?php
    } elseif ($do == 'action') {
        if (isset($_POST['btn_edit'])) {
            $orders_number = $_POST['orders_number'];
            $stmt = $con->prepare("SELECT 
                `orders`.*, 
                `customers`.`name_customer`,
                `customers`.`email_customer`,
                `customers`.`phone_customer`
                FROM `orders` 
                INNER JOIN `customers` ON `orders`.`customer_id` = `customers`.`id`
                WHERE `orders`.`orders_number` = ?");
            $stmt->execute([$orders_number]);
            $edit = $stmt->fetch(PDO::FETCH_ASSOC);
?>
            <div class="edit-order">
                <div class="container">
                    <a class="btn btn-light my-2" href="./edit-orders.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
                    <div class="col-md-6 mx-auto">
                        <h1>Edit Order : <?php echo htmlspecialchars($edit['orders_number'] . ' - ' . $edit['name_customer']); ?></h1>
                        <form method="POST" action="edit-orders.php?do=orders-update" enctype="multipart/form-data">
                            <input type="hidden" name="orders_number" value="<?php echo htmlspecialchars($edit['orders_number']); ?>">
                            <div class="form-group mb-3">
                                <select class="form-select text-capitalize" name="order_status">
                                    <option selected><?php echo htmlspecialchars($edit['order_status']); ?></option>
                                    <?php
                                    $isStatus = $con->prepare("SELECT * FROM `status`");
                                    $isStatus->execute();
                                    $Status = $isStatus->fetchAll(PDO::FETCH_ASSOC);
                                    if (!empty($Status)) {
                                        foreach ($Status as $status) {
                                            echo '<option value="' . htmlspecialchars($status['name']) . '">' . htmlspecialchars($status['name']) . '</option>';
                                        }
                                    } else {
                                        echo '<option value="...">...</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <span class="label">Note</span>
                                <textarea name="note_customer" class="form-control" rows="3"><?php echo htmlspecialchars($edit['note_customer']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mb-3" name="order_update">Update Order</button>
                        </form>
                    </div>
                </div>
            </div>
<?php
        } elseif (isset($_POST['btn_view'])) {
            $orders_number = $_POST['orders_number'];
            $stmt = $con->prepare("SELECT 
                `orders`.*, 
                `customers`.`name_customer`,
                `customers`.`email_customer`,
                `customers`.`phone_customer`
                FROM `orders` 
                INNER JOIN `customers` ON `orders`.`customer_id` = `customers`.`id`
                WHERE `orders`.`orders_number` = ?");
            $stmt->execute([$orders_number]);
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $view = $orders[0];
?>
            <div class="view-order">
                <div class="container">
                    <a class="btn btn-light my-2" href="./edit-orders.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
                    <div class="col-md-8 mx-auto">
                        <h1>Order <?php echo htmlspecialchars($view['orders_number']) ?> - <strong class="p-1 text-capitalize rounded <?php
                            $status = $view['order_status'];
                            if ($status == 'pending') :
                                echo 'text-bg-warning';
                            elseif ($status == 'cancelled') :
                                echo 'text-bg-danger';
                            elseif ($status == 'processing') :
                                echo 'text-bg-primary';
                            elseif ($status == 'pending payment') :
                                echo 'text-bg-info';
                            elseif ($status == 'completed') :
                                echo 'text-bg-success';
                            elseif ($status == 'failed') :
                                echo 'text-bg-danger';
                            endif;
                        ?>">
                            <?php echo htmlspecialchars($view['order_status']) ?>
                        </strong></h1>
                        <div class="view-content">
                            <h2>Billing details</h2>
                            <ul>
                                <li><strong>Full Name :</strong> <?php echo htmlspecialchars($view['name_customer']); ?></li>
                                <li><strong>Email :</strong> <?php echo htmlspecialchars($view['email_customer']); ?></li>
                                <li><strong>Phone :</strong> <?php echo htmlspecialchars($view['phone_customer']); ?></li>
                                <li><strong>Note :</strong> <?php echo htmlspecialchars($view['note_customer']); ?></li>
                            </ul>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr class="text-bg-dark">
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = 0;
                                        foreach($orders as $item): 
                                            $total += $item['subtotal'];
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td><?php echo htmlspecialchars($item['product_quantity']); ?></td>
                                                <td><?php echo htmlspecialchars($view['currency_symbol']) . number_format($item['product_price'], 2); ?></td>
                                                <td><?php echo htmlspecialchars($view['currency_symbol']) . number_format($item['subtotal'], 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-active">
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong><?php echo htmlspecialchars($view['currency_symbol']) . number_format($total, 2); ?></strong></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <form method="POST" action="edit-orders.php?do=receipt-orders">
                                    <input type="hidden" name="orders_number" value="<?php echo htmlspecialchars($view['orders_number']) ?>">
                                    <button type="submit" class="btn btn-light" name="order_receipt">
                                        <i class="fa-solid fa-file-invoice"></i>&nbsp;Receipt
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
        } elseif (isset($_POST['btn_delete'])) {
            $orders_number = $_POST['orders_number'];
            $stmt = $con->prepare("DELETE FROM orders WHERE `orders`.`orders_number` = ?");
            $stmt->execute([$orders_number]);
            show_message('Order deleted successfully', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            header('location: edit-orders.php');
            exit();
        }
    } elseif ($do == 'orders-update') {
        if (isset($_POST['order_update'])) {
            $orders_number = $_POST['orders_number'];
            $order_status = $_POST['order_status'];
            $note_customer = $_POST['note_customer'];
            $stmt = $con->prepare("UPDATE `orders` SET `note_customer` = ?, `order_status` = ? WHERE `orders_number` = ?");
            $stmt->execute([$note_customer, $order_status, $orders_number]);
            show_message('Order ' . $orders_number . ' updated successfully', 'success');
            header('location: ./edit-orders.php');
            exit();
        } else {
            header('location: edit-orders.php');
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
