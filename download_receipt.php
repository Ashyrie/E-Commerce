<?php
ob_start(); // Start output buffering
session_name('client_session');
session_start(); // Start the session
require 'vendor/autoload.php'; // Ensure you load the Composer autoload file

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

include './init.php'; // Include your database connection

if (isset($_POST['order_number'])) {
    $orderNumber = $_POST['order_number'];

    // Fetch order details along with customer name based on order number
    $stmt = $con->prepare("
        SELECT o.*, c.name_customer 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.orders_number = ?
    ");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if ($order) {
        // Set headers for a proper file display
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: inline; filename="receipt.html"');

        // Generate QR code
        $qrCode = new QrCode(htmlspecialchars($order['orders_number']));
        $qrCodeWriter = new PngWriter();
        $qrCodeData = $qrCodeWriter->write($qrCode)->getDataUri();

        // Output your receipt HTML with a printable div
        echo '<html>';
        echo '<head>';
        echo '<title>Receipt for Order ' . htmlspecialchars($orderNumber) . '</title>';
        echo '<style>
                body {
                    font-family: Arial, sans-serif;
                    font-size: 1rem;
                }
                .printable-div {
                    border: 1px solid #000;
                    padding: 20px;
                    margin: 20px auto 0; /* Added top margin */
                    background-color: #fff;
                    width: 80%; /* Keep this width for regular view */
                    max-width: 600px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    text-align: center;
                }
                @media print {
                    body {
                        margin: 0;
                    }
                    .printable-div {
                        width: 600px; /* Fixed width for print */
                        margin: 20px auto; /* Center it with top margin */
                        border: none; /* Optional: remove border for print */
                        box-shadow: none; /* Optional: remove shadow for print */
                        padding: 40px; /* Increase padding for print */
                    }
                    body * {
                        visibility: hidden;
                    }
                    .printable-div, .printable-div * {
                        visibility: visible;
                    }
                    .printable-div {
                        position: relative;
                        left: 0;
                        top: 0;
                    }
                    h1, h2, p {
                        font-size: 1.5rem; /* Increase font size */
                    }
                    img {
                        width: 300px; /* Increased QR code size */
                        height: auto;
                    }
                }
                .print-button {
                    display: flex;
                    justify-content: center;
                    margin-top: 20px;
                }
                .print-button button {
                    padding: 10px 20px;
                    font-size: 1rem;
                }
              </style>';
        echo '</head>';
        echo '<body>';
        echo '<script>document.title = "Receipt for Order ' . htmlspecialchars($orderNumber) . '";</script>';
        echo '<div class="printable-div">';
        echo '<h1>Reservation Acknowledgement</h1>';
        echo '<p>Order Number: ' . htmlspecialchars($order['orders_number']) . '</p>';
        echo '<p>Customer Name: ' . htmlspecialchars($order['name_customer']) . '</p>';
        echo '<p>Order Date: ' . date("F j, Y", strtotime($order['order_date'])) . '</p>';
        
        echo '<h2>Items:</h2>';
        echo '<ul>';
        echo '<li>' . htmlspecialchars($order['product_name']) . ' - Quantity: ' . htmlspecialchars($order['product_quantity']) . ' - Price: ' . number_format($order['product_price'], 2) . '</li>';
        echo '</ul>';
        echo '<p>Total: ' . number_format($order['subtotal'], 2) . ' ' . htmlspecialchars($order['currency']) . '</p>';
        
        // Output the QR code
        echo '<h2>QR Code:</h2>';
        echo '<img src="' . $qrCodeData . '" alt="QR Code" />';
        echo '</div>'; // Close the printable div

        // Center the print button
        echo '<div class="print-button">';
        echo '<button onclick="window.print();">Print Receipt</button>';
        echo '</div>';
        
        echo '</body>';
        echo '</html>';
    } else {
        echo 'Order not found.';
    }
} else {
    echo 'No order number provided.';
}

ob_end_flush(); // Send the buffered output to the browser
?>
