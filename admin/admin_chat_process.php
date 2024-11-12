<?php
session_name('admin_session');
session_start();
include './connect.php';  


header('Content-Type: application/json');


if (!isset($_SESSION['id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Admin is not logged in.'
    ]);
    exit; 
}

$admin_id = $_SESSION['id'];  // Get the admin ID from session

// POST request to send a new message (Admin sends a message to a customer)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['product_id']) && isset($_POST['ticket_id'])) {
    $message = trim($_POST['message']);
    $product_id = (int)$_POST['product_id'];
    $ticket_id = $_POST['ticket_id'];  // Ensure ticket_id is passed from the client-side

    // Ensure the message is not empty
    if (!empty($message)) {
        try {
            // Insert the new message into the database (Admin's message)
            // Insert with the same ticket_id as the customer's message
            $stmt = $con->prepare("INSERT INTO messages (product_id, ticket_id, message, sender_type, sender_id, admin_id) 
                                    VALUES (?, ?, ?, 'admin', ?, ?)");
            $stmt->execute([$product_id, $ticket_id, $message, $admin_id, $admin_id]);  // Pass ticket_id along with admin info

            // Return success response with the message details
            echo json_encode([
                'status' => 'success',
                'message' => $message,
                'timestamp' => date('Y-m-d H:i:s')  // Optionally return the timestamp
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
        // SQL Query to get messages from both customer and admin, correctly joining the necessary tables
        $stmt = $con->prepare("
            SELECT m.id, m.message, m.timestamp, m.ticket_id,
                   IF(m.sender_type = 'customer', 
                       CONCAT(c.name_customer, ' ', IFNULL(c.company_name, '')) , 
                       CONCAT(a.username, ' Administrator')) AS sender_name,
                   m.sender_type
            FROM messages m
            LEFT JOIN customers c ON m.sender_id = c.id AND m.sender_type = 'customer'
            LEFT JOIN admin a ON m.sender_id = a.id AND m.sender_type = 'admin' 
            WHERE m.product_id = ? AND m.ticket_id = ?
            ORDER BY m.timestamp ASC
        ");
        $stmt->execute([$product_id, $ticket_id]);  // Ensure that $product_id and $ticket_id are passed properly
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Prepare messages with appropriate sender names and types
        $message_data = [];
        foreach ($messages as $msg) {
            // Check if sender_name is empty, and set it to 'Unknown' if it is
            $sender_name = $msg['sender_name'] ?: 'Unknown';  // Default to 'Unknown' if sender_name is empty
        
            $message_data[] = [
                'message' => $msg['message'],
                'timestamp' => $msg['timestamp'],
                'sender_name' => $sender_name,
                'sender_type' => $msg['sender_type'],
                'ticket_id' => $msg['ticket_id'],  // Include ticket_id in the response
            ];
        }
        
        // Return the messages as a response
        if ($message_data) {
            echo json_encode([
                'status' => 'success',
                'messages' => $message_data
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'No messages found for this product and ticket.'
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
