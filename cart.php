<?php
ob_start(); // Start output buffering
session_name('client_session');
session_start();
$pageTitle = 'Cart';
include './init.php';
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$do = isset($_GET['do']) ? $_GET['do'] : 'cart';
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];




// Initialize customer variables
$fullName = '';
$email = '';
$phone = '';

// Fetch user information if logged in
if (isset($_SESSION['customer_id'])) {
    $stmt = $con->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();

    // Store customer details for use in forms
    if ($customer) {
        $fullName = htmlspecialchars($customer['name_customer']);
        $email = htmlspecialchars($customer['email_customer']);
        $phone = htmlspecialchars($customer['phone_customer']);
    }
}

if ($do == 'cart') {
    ?>
    <div class="cart">
        <div class="container">
            <a class="btn btn-light my-2" href="./index.php"><i class="fa fa-backward" aria-hidden="true"></i>&nbsp;Back</a>
            <h1>Reserved items list</h1>
            <?php if (isset($_SESSION['message'])) : ?>
                <div id="message">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']); endif; ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr class="text-bg-light">
                            <th>Product Details</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($cartItems)) : ?>
                            <?php foreach ($cartItems as $item) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($item['product_price']); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity'] * $item['product_price']); ?></td>
                                    <td>
                                        <a href="cart.php?do=remove-product&id=<?php echo $item['id']; ?>" class="btn btn-danger" style="color: white; text-decoration: none;">
                                            <i class="fa-solid fa-trash"></i>&nbsp;Remove
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5">Your cart is empty.<br /><a class="btn btn-outline-dark" href="./index.php">Return to shop</a></td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                    <tfoot>
                        <?php
                        $subtotal = 0;
                        $currency = null;
                        if (!empty($cartItems)) {
                            foreach ($cartItems as $item) {
                                $subtotal += $item['quantity'] * $item['product_price'];
                                if ($currency === null) {
                                    $currency = $item['currency'];
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td colspan="3"><strong>Subtotal:</strong></td>
                            <td><strong><?php echo number_format($subtotal, 2) . ' ' . htmlspecialchars($currency); ?></strong></td>
                            <td>
                                <a href="cart.php?do=checkout" class="btn btn-primary" style="color: white; text-decoration: none;">
                                    <i class="fa-solid fa-check-to-slot"></i>&nbsp;Reserve now
                                </a>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php
} elseif ($do == 'checkout') {
    ?>
    <div class="checkout">
        <div class="container">
            <h1>Checkout</h1>
            <form method="post" action="cart.php?do=place-order" autocomplete="off" class="py-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <h2>Billing Details</h2>
                        <?php if (isset($_SESSION['message'])) : ?>
                            <div id="message">
                                <?php echo $_SESSION['message']; ?>
                            </div>
                            <?php unset($_SESSION['message']); endif; ?>
                        <div class="form-group">
                            <label for="name_customer">Full Name *</label>
                            <input type="text" name="name_customer" id="name_customer" class="form-control" value="<?php echo $fullName; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="phone_customer">Phone *</label>
                            <input type="tel" name="phone_customer" id="phone_customer" class="form-control" value="<?php echo $phone; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="email_customer">Email Address *</label>
                            <input type="email" name="email_customer" id="email_customer" class="form-control" value="<?php echo $email; ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label for="note_customer">Notes for this reservation</label>
                            <textarea name="note_customer" id="note_customer" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2>Your Order</h2>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($item['product_price']); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantity'] * $item['product_price']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <?php
                                    $subtotal = 0;
                                    $currency = null;
                                    if (!empty($cartItems)) {
                                        foreach ($cartItems as $item) {
                                            $subtotal += $item['quantity'] * $item['product_price'];
                                            if ($currency === null) {
                                                $currency = $item['currency'];
                                            }
                                        }
                                    }
                                    ?>
                                    <tr>
                                        <td colspan="3"><strong>Subtotal:</strong></td>
                                        <td><strong><?php echo number_format($subtotal, 2) . ' ' . htmlspecialchars($currency); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="submit" name="place_order" class="btn btn-primary"><i class="fa-solid fa-check-double"></i>&nbsp;Place reservation</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
} elseif ($do == 'place-order') {
    if (isset($_POST['place_order'])) {
        $note_customer = $_POST['note_customer'];
        $orders_number = generateOrderNumber($con);

        if (empty($fullName) || empty($phone) || empty($email)) {
            show_message('Please fill in all required fields.', 'danger');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }

        // Use the existing customer_id from the session
        $customer_id = $_SESSION['customer_id'];

        foreach ($_SESSION['cart'] as $item) {
            $product_name = $item['product_name'];
            $product_quantity = $item['quantity'];
            $product_price = $item['product_price'];
            $subtotal = $product_quantity * $product_price;
            $currency = $item['currency'];

            if ($currency === null) {
                show_message('Currency not found for the product.', 'danger');
                header('location: cart.php');
                exit();
            }

            $orders = $con->prepare("INSERT INTO orders(orders_number, customer_id, product_name, product_quantity, product_price, subtotal, note_customer, currency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $orders->execute([$orders_number, $customer_id, $product_name, $product_quantity, $product_price, $subtotal, $note_customer, $currency]);
        }

        // Send confirmation email
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();                                            // SMTP
            $mail->Host       = 'smtp.mailersend.net';                  //  SMTP server natin
            $mail->SMTPAuth   = true;                               
            $mail->Username   = 'MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net';          // SMTP username natin
            $mail->Password   = 'dueGu4EUSqlCTvI3';                    // SMTP password natin
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 587;                                // TCP port to connect to

            //Recipients
            $mail->setFrom('MS_EOosuM@trial-3vz9dlez0d7lkj50.mlsender.net', 'Deltech Parking System');
            $mail->addAddress($email, $fullName);                  // recipient natin yung user

            // Content
            $mail->isHTML(true);                                  // format nung email na sesend
            $mail->Subject = 'Order Confirmation - ' . $orders_number;
            $mail->Body    = '<h1>Thank you for your order!</h1>
                              <p>Your order number is <strong>' . htmlspecialchars($orders_number) . '</strong>.</p>
                              <p>Details:</p>
                              <ul>';
            foreach ($_SESSION['cart'] as $item) {
                $mail->Body .= '<li>' . htmlspecialchars($item['product_name']) . ' x ' . htmlspecialchars($item['quantity']) . ' - ' . htmlspecialchars($item['product_price']) . '</li>';
            }
            $mail->Body .= '</ul>
                            <p>We will notify you once your order is processed.</p>';

            $mail->send();
        } catch (Exception $e) {
            // Handle error if needed
            error_log("Mailer Error: {$mail->ErrorInfo}");
        }

        unset($_SESSION['cart']);
        header('Location: cart.php?do=order-received');
        exit();
    } else {
        header('location: cart.php');
        exit();
    }
} elseif ($do == 'order-received') {
    ?>
    <div class="order-received">
        <div class="container">
            <?php if (isset($_SESSION['customer_id'])) {
                $stmt = $con->prepare("SELECT * FROM customers WHERE id = ?");
                $stmt->execute([$_SESSION['customer_id']]);
                $customer = $stmt->fetch();
                
                // Get the most recent order for this customer
                $orderStmt = $con->prepare("SELECT orders_number FROM orders WHERE customer_id = ? ORDER BY id DESC LIMIT 1");
                $orderStmt->execute([$customer['id']]);
                $recentOrder = $orderStmt->fetch();
                
                if ($recentOrder) {
                    ?>
                    <div class="alert alert-success text-center mt-4 mb-4" role="alert">
                    <h4 class="alert-heading mb-3">Thank you for trusting Deltech Parking System and Solutions Inc.</h4>
                      <p class="mb-2">Your order has been successfully placed!</p>
                      <hr>
    
                     <p class="mb-0">Your Order/Tracking Number is: 
                    <strong id="order-number"><?php echo htmlspecialchars($recentOrder['orders_number']); ?></strong>
        
                    <button onclick="copyOrderNumber()" class="btn btn btn-success btn-sm ml-2">Copy</button>
        
                 <form action="download_receipt.php" method="POST" class="d-inline" target="_blank">
                  <input type="hidden" name="order_number" value="<?php echo htmlspecialchars($recentOrder['orders_number']); ?>">
                   <button type="submit" class="btn btn btn-success btn-sm ml-2">Print/Download</button>
                    </form>
                     </p>

                     <p class="small text-muted mt-2">Please save this number for future reference.</p>

            <div class="cta-buttons mt-4">
            <a href="chat_list.php" class="btn  bt-btn-custom btn-sm ml-2">Do you have more in mind? Check your Inquiries!</a>
            <a href="tracking.php" class="btn  bt-btn-custom btn-sm ml-2">Track Your Order</a>
           </div>
         </div>
    <style>
   
    .btn-custom {
        background-color: #4a69bd; 
        border-color: #4a69bd;
        color: white; 
        border-radius: 5px;
        font-weight: 600;
        text-align: center;
        padding: 8px 20px;
        margin: 5px;
        text-decoration: none;
        font-size: 0.875rem;
        display: inline-block;
        cursor: pointer;
        transition: background-color 0.3s ease; 
    }

    .btn-custom:hover {
        background-color: #60a3bc; 
        border-color: #60a3bc;
        color: white; 
    }

    
    .btn-sm {
        font-size: 0.85rem; 
    }

    .ml-2 {
        margin-left: 10px;
    }

    .cta-buttons {
        margin-top: 20px;
        text-align: center;
    }
    </style>
                    <?php
                }
                
                $order = $con->prepare("SELECT o.*, c.symbol FROM orders o JOIN currencies c ON o.currency = c.id WHERE customer_id = ? ORDER BY id DESC");
                $order->execute([$customer['id']]);
                $orderCount = $order->rowCount();
                if ($orderCount > 0) {
                ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Order number</th>
                                    <th>Date</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                while ($row = $order->fetch()) {
                                    $orders_number = $row['orders_number'];
                                    $order_date = date("F j, Y", strtotime($row['order_date']));
                                    $total_price = $row['subtotal'];
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($orders_number); ?></td>
                                        <td><?php echo htmlspecialchars($order_date); ?></td>
                                        <td><?php echo number_format($total_price, 2) . '&nbsp;' . htmlspecialchars($row['symbol']); ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="text-bg-light">
                                <tr>
                                    <th>Order details</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $order->execute([$customer['id']]);
                                while ($row = $order->fetch()) {
                                ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['product_name'] . ' x ' . $row['product_quantity']); ?></td>
                                        <td><?php echo number_format($row['subtotal'], 2) . '&nbsp;' . htmlspecialchars($row['symbol']); ?></td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                }
            } else {
                header('location: cart.php');
                exit();
            }
            ?>
        </div>
    </div>

    <script>
    function copyOrderNumber() {
        const orderNumber = document.getElementById("order-number").innerText;
        navigator.clipboard.writeText(orderNumber).then(() => {
            alert("Order number copied to clipboard!");
        }, (err) => {
            console.error("Could not copy text: ", err);
        });
    }
    </script>

    <?php
} elseif ($do == 'add-cart') {
    if (isset($_POST['add_to_cart'])) {
        $id = $_POST['id'];
        $quantity = $_POST['quantity'];
        $Details = $con->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $Details->execute([$id]);
        $cart = $Details->fetch(PDO::FETCH_ASSOC);
        
        if ($cart) {
            $product_id = $cart['id'];
            $product_name = $cart['name_product'];
            $product_price = $cart['price_product'];
            $currency = $cart['currency']; 

            $cart_item = array(
                'id' => $product_id,
                'product_name' => $product_name,
                'quantity' => $quantity,
                'product_price' => $product_price,
                'currency' => $currency
            );
            $_SESSION['cart'][] = $cart_item;
            show_message('Add ' . htmlspecialchars($product_name) . ' reserved successfully.', 'success');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            show_message('Product not found.', 'danger');
            header('location: ' . $_SERVER['HTTP_REFERER']);
            exit();
        }
    } else {
        header('location: index.php');
        exit();
    }
} elseif ($do == 'remove-product') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $name = $con->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $name->execute([$id]);
        $cart = $name->fetch(PDO::FETCH_ASSOC);
        $products = $cart['name_product'];
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $id) {
                unset($_SESSION['cart'][$key]);
                break;
            }
        }
        show_message('Remove ' . htmlspecialchars($products) . ' from cart successfully.', 'success');
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    } else {
        header('location: index.php');
        exit();
    }
}

include $tpl . 'footer.php';
ob_end_flush(); // End output buffering
?>
