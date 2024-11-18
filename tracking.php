<?php
session_name('client_session');
session_start();
$pageTitle = 'Tracking';
include './init.php';

ob_start(); // Start output buffering

$Orders = []; // Initialize $Orders
$error_message = ""; // Initialize error message

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['your_order'])) {
    $orders_number = $_POST['orders_number'];
    
    // Check if the order number does not start with '#'
    if ($orders_number[0] !== '#') {
        $error_message = "Please include '#' at the beginning of your order number.";
    } else {
        // Modified query to group order details
        $stmt = $con->prepare("
            SELECT 
                o.orders_number,
                o.customer_id,
                GROUP_CONCAT(o.product_name SEPARATOR '||') as products,
                GROUP_CONCAT(o.product_quantity SEPARATOR '||') as quantities,
                GROUP_CONCAT(o.product_price SEPARATOR '||') as prices,
                o.currency,
                GROUP_CONCAT(o.subtotal SEPARATOR '||') as subtotals,
                o.order_date,
                o.order_status,
                o.note_customer
            FROM orders o 
            WHERE o.orders_number = ?
            GROUP BY o.orders_number, o.customer_id, o.order_date, o.order_status
        ");
        $stmt->execute([$orders_number]);
        $Orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="track-order">
    <div class="container">
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger text-center mt-5" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (count($Orders) > 0) : ?>
            <a class="btn btn-light my-2" href="./tracking.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
            <h1 class="text-center"><i class="fa-solid fa-boxes-packing"></i>&nbsp;Your Order</h1>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th class="text-center">Order Number</th>
                            <th class="text-center">Product</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Order Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Orders as $order) : 
                            $products = explode('||', $order['products']);
                            $quantities = explode('||', $order['quantities']);
                            $prices = explode('||', $order['prices']);
                            $subtotals = explode('||', $order['subtotals']);
                            
                            // Count products for line divisions
                            $productCount = count($products);
                        ?>
                            <tr>
                                <td class="align-middle"><?php echo $order['orders_number']; ?></td>
                                <td class="align-middle">
                                    <?php 
                                    for ($i = 0; $i < $productCount; $i++) {
                                        echo $products[$i];
                                        if ($i < $productCount - 1) {
                                            echo '<hr class="my-2">';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <?php 
                                    for ($i = 0; $i < $productCount; $i++) {
                                        echo $quantities[$i] . ' (x' . $prices[$i] . $order['currency'] . ')';
                                        if ($i < $productCount - 1) {
                                            echo '<hr class="my-2">';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="align-middle">
                                    <?php 
                                    for ($i = 0; $i < $productCount; $i++) {
                                        echo $subtotals[$i] . '&nbsp;' . $order['currency'];
                                        if ($i < $productCount - 1) {
                                            echo '<hr class="my-2">';
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="align-middle"><?php echo $order['order_date']; ?></td>
                                <td class="align-middle text-capitalize <?php 
                                    $status = $order['order_status'];
                                    if ($status == 'pending') {
                                        echo 'text-bg-warning';
                                    } elseif ($status == 'cancelled') {
                                        echo 'text-bg-danger';
                                    } elseif ($status == 'processing') {
                                        echo 'text-bg-primary';
                                    } elseif ($status == 'pending payment') {
                                        echo 'text-bg-info';
                                    } elseif ($status == 'completed') {
                                        echo 'text-bg-success';
                                    } elseif ($status == 'failed') {
                                        echo 'text-bg-danger';
                                    } ?>">
                                    <?php echo $order['order_status']; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif (isset($_POST['your_order'])) : ?>
            <div class="alert alert-warning text-center mt-5" role="alert">
                No orders were found with the provided order number.
            </div>
            <?php 
                header('Refresh: 6; url=' . $_SERVER['HTTP_REFERER']);
                exit();
            ?>
        <?php else : ?>
            <h1 class="text-center">Track Your Order</h1>
            <form method="post" role="search" autocomplete="off" class="py-3 col-md-6 mx-auto">
                <div class="input-group mb-3">
                    <input class="form-control" name="orders_number" placeholder="Orders Number" aria-label="Orders Number" required="required" />
                    <button class="btn btn-dark" type="submit" name="your_order">Search</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
ob_end_flush();
include $tpl . 'footer.php';
?>