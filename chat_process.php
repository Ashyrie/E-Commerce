<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_name('client_session');
session_start();

// session name ng lahat ng client session ay client session
include './connect.php';

// Ensure Content-Type is set to application/json before any output
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['customer_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'User is not logged in.'
    ]);
    exit; 
}

$customer_id = $_SESSION['customer_id']; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['product_id']) && isset($_POST['ticket_id'])) {
    $message = trim($_POST['message']);
    $product_id = (int)$_POST['product_id'];
    $ticket_id = $_POST['ticket_id'];

    if (!empty($message)) {
        try {
            $stmt = $con->prepare("INSERT INTO messages (customer_id, product_id, message, sender_type, sender_id, ticket_id) VALUES (?, ?, ?, 'customer', ?, ?)");
            $stmt->execute([$customer_id, $product_id, $message, $customer_id, $ticket_id]);

            // Return success response with ticket_id
            echo json_encode([
                'status' => 'success',
                'message' => $message,
                'ticket_id' => $ticket_id,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } catch (PDOException $e) {
            // Return error if there's an issue with the database operation
            echo json_encode([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    } else {
        // Return error if message is empty
        echo json_encode([
            'status' => 'error',
            'message' => 'Message cannot be empty.'
        ]);
    }
    exit; // Stop further execution
}

// GET request to fetch chat messages
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_id']) && isset($_GET['ticket_id'])) {
    $product_id = (int)$_GET['product_id'];
    $ticket_id = $_GET['ticket_id'];

    try {
        // Fetch messages for the given product_id and ticket_id
        $stmt = $con->prepare("
            SELECT m.id, m.message, m.timestamp, 
                   IF(m.sender_type = 'customer', 
                       CONCAT(c.name_customer, ' ', IFNULL(cc.company_name, '')), 
                       CONCAT(a.username, ' Administrator')) AS sender_name,
                   m.sender_type, m.ticket_id
            FROM messages m
            LEFT JOIN customers c ON m.customer_id = c.id
            LEFT JOIN customer_companies cc ON c.id = cc.customer_id  -- Join customer_companies for company_name
            LEFT JOIN admin a ON m.admin_id = a.id
            WHERE m.product_id = ? AND m.ticket_id = ?
            ORDER BY m.timestamp ASC
        ");
        $stmt->execute([$product_id, $ticket_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare messages with appropriate sender names and types
        $message_data = [];
        foreach ($messages as $msg) {
            // Format message to return in JSON
            $message_data[] = [
                'message' => $msg['message'],
                'timestamp' => $msg['timestamp'],
                'sender_name' => $msg['sender_name'],
                'sender_type' => $msg['sender_type'],
                'ticket_id' => $msg['ticket_id'],
            ];
        }

        if ($message_data) {
            echo json_encode([
                'status' => 'success',
                'messages' => $message_data
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No messages found for this product.'
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error fetching messages: ' . $e->getMessage()
        ]);
    }
    exit;
}
