<?php
ob_start(); // Start output buffering
session_name('client_session');
session_start(); // Start the session

require '../vendor/autoload.php'; // Ensure you load the Composer autoload file
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Include your database connection
include './init.php';

if (isset($_POST['order_number'])) {
    $orderNumber = $_POST['order_number'];

    // Fetch order details along with customer name based on order number
    $stmt = $con->prepare("SELECT o.*, c.name_customer, c.email_customer, c.phone_customer 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.id 
        WHERE o.orders_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();

    if ($order) {
        // Generate QR code for the order number
        $qrCode = new QrCode(htmlspecialchars($order['orders_number']));
        $qrCodeWriter = new PngWriter();
        $qrCodeData = $qrCodeWriter->write($qrCode)->getDataUri();

        // HTML content for the receipt
        $receiptContent = '
            <html>
            <head>
                <title>Receipt for Order ' . htmlspecialchars($orderNumber) . '</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 1rem;
                    }
                    .receipt {
                        border: 1px solid #000;
                        padding: 20px;
                        margin: 20px auto;
                        background-color: #fff;
                        width: 80%;
                        max-width: 600px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                        text-align: center;
                    }
                    .receipt h1 {
                        font-size: 2rem;
                    }
                    .receipt p {
                        font-size: 1.2rem;
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
                </style>
            </head>
            <body>
                <div class="receipt">
                    <h1>Order Receipt</h1>
                    <p><strong>Order Number:</strong> ' . htmlspecialchars($order['orders_number']) . '</p>
                    <p><strong>Customer Name:</strong> ' . htmlspecialchars($order['name_customer']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($order['email_customer']) . '</p>
                    <p><strong>Order Date:</strong> ' . date("F j, Y", strtotime($order['order_date'])) . '</p>
                    
                    <h3>Items:</h3>';
        
        // Fetch order items
        $stmt_items = $con->prepare("SELECT * FROM orders WHERE orders_number = ?");
        $stmt_items->execute([$orderNumber]);
        $items = $stmt_items->fetchAll();

        $total = 0;
        $receiptContent .= '<ul>';
        foreach ($items as $item) {
            $receiptContent .= '<li>' . htmlspecialchars($item['product_name']) . ' - Quantity: ' . htmlspecialchars($item['product_quantity']) . ' - Price: ' . number_format($item['product_price'], 2) . ' ' . htmlspecialchars($order['currency']) . '</li>';
            $total += $item['product_price'] * $item['product_quantity'];
        }
        $receiptContent .= '</ul>';

        $receiptContent .= '<p><strong>Total: ' . number_format($total, 2) . ' ' . htmlspecialchars($order['currency']) . '</strong></p>';

        // Output the QR code
        $receiptContent .= '<h3>QR Code:</h3>';
        $receiptContent .= '<img src="' . $qrCodeData . '" alt="QR Code" style="width: 200px; height: auto;" />';

        // Close the receipt content
        $receiptContent .= '</div>';

        // Print button for manual printing
        $receiptContent .= '<div class="print-button"><button onclick="window.print();">Print Receipt</button></div>';

        // Output the HTML content
        echo $receiptContent;

    } else {
        echo 'Order not found.';
    }
} else {
    echo 'No order number provided.';
}

ob_end_flush(); // Send the buffered output to the browser
?>
