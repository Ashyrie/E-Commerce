<?php
include './chat_init.php';  // Include necessary files and initialize the session

// Check if the customer is logged in
if (!isset($_SESSION['customer_id'])) {
    // Redirect to the login page if the customer is not logged in
    header('Location: login.php');
    exit;
}

$customer_id = $_SESSION['customer_id']; // Get the logged-in customer ID

// Fetch the latest message and ticket_id for each product the logged-in customer has chatted about
$chats = [];
$chat_query = $con->prepare("SELECT p.id AS product_id, p.name_product, 
                            MAX(m.timestamp) AS last_message_time,
                            m.ticket_id, t.resolved
                             FROM messages m
                             JOIN products p ON m.product_id = p.id
                             LEFT JOIN support_tickets t ON m.ticket_id = t.ticket_id
                             WHERE m.customer_id = ?  -- Only fetch chats belonging to the logged-in customer
                             GROUP BY p.id, p.name_product, m.ticket_id, t.resolved
                             ORDER BY last_message_time DESC");

// Execute the query with the logged-in customer's ID
$chat_query->execute([$customer_id]);

// Fetch the results
$chats = $chat_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Past Inquiries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f6f7;
        }

        .back-button {
            margin: 20px 0;
            text-decoration: none;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #218838;
            color: white;
        }

        .chat-list-container {
            margin-top: 30px;
            padding: 0 15px;
        }

        .chat-item {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease, background-color 0.3s ease;
            text-decoration: none;
        }

        .chat-item:hover {
            background-color: #f1f1f1;
            transform: scale(1.02);
        }

        .chat-info {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            text-align: center;
        }

        .chat-timestamp {
            font-size: 14px;
            color: #888;
            margin-top: 8px;
        }

        
        .resolved-tag {
            position: absolute;
            top: -10px; 
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745; 
            color: white;
            padding: 5px 15px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: bold;
            display: none;
        }

        
        .chat-item.resolved:hover .resolved-tag {
            display: block;
        }

        .no-chats-message {
            padding: 30px;
            text-align: center;
            font-size: 18px;
            color: #999;
            font-style: italic;
        }

        .chat-list-container .list-group {
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button">Back to Home</a>
        <h2>Your Inquiries</h2>

        <div class="chat-list-container">
            <?php if (!empty($chats)): ?>
                <div class="list-group">
                    <?php foreach ($chats as $chat): ?>
                        <a href="chat.php?product_id=<?php echo $chat['product_id']; ?>&ticket_id=<?php echo $chat['ticket_id']; ?>" 
                           class="chat-item <?php echo $chat['resolved'] === 'yes' ? 'resolved' : ''; ?>">

                            <!-- Resolved Tag -->
                            <?php if ($chat['resolved'] === 'yes'): ?>
                                <div class="resolved-tag">
                                    Resolved
                                </div>
                            <?php endif; ?>

                            <div class="chat-info">
                                <strong><?php echo htmlspecialchars($chat['name_product']); ?></strong>
                            </div>

                            <!-- Display Ticket ID before the timestamp -->
                            <?php if (!empty($chat['ticket_id'])): ?>
                                <div class="chat-timestamp">
                                    Ticket ID: <strong><?php echo htmlspecialchars($chat['ticket_id']); ?></strong>
                                </div>
                            <?php endif; ?>

                            <div class="chat-timestamp">
                                Last message at: <?php echo date('Y-m-d H:i:s', strtotime($chat['last_message_time'])); ?>
                            </div>

                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-chats-message">
                    You haven't had any chats yet. Start a conversation with a product!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
</body>
</html>
