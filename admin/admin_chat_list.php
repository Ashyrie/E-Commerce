<?php
include './adminchat_init.php'; 


if (!isset($_SESSION['username'])) {
    
    header('Location: index.php');
    exit();
}


$customer_filter = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';


$chats = [];
$chat_query_str = "
    SELECT p.id AS product_id, p.name_product, 
           MAX(m.timestamp) AS last_message_time, 
           c.name_customer, c.username, 
           m.ticket_id, t.resolved
    FROM messages m
    JOIN products p ON m.product_id = p.id
    JOIN customers c ON m.customer_id = c.id
    LEFT JOIN support_tickets t ON m.ticket_id = t.ticket_id
";


if ($customer_filter) {
    $chat_query_str .= " WHERE c.name_customer LIKE :customer_filter ";
}

$chat_query_str .= "
    GROUP BY p.id, p.name_product, c.id, c.name_customer, c.username, m.ticket_id, t.resolved
    ORDER BY last_message_time DESC
";

$chat_query = $con->prepare($chat_query_str);


if ($customer_filter) {
    $chat_query->bindValue(':customer_filter', "%$customer_filter%");
}

$chat_query->execute();
$chats = $chat_query->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_chat'])) {
    
    ob_clean();
    $product_id_to_delete = (int)$_POST['delete_chat']['product_id']; // Ensure to cast as integer
    $ticket_id_to_delete = $_POST['delete_chat']['ticket_id']; // Ticket ID is a string

    // Prepare and execute the delete statement
    $delete_stmt = $con->prepare("DELETE FROM messages WHERE product_id = ? AND ticket_id = ?");
    $delete_stmt->execute([$product_id_to_delete, $ticket_id_to_delete]);

    // Respond with success or error message
    echo json_encode([
        'status' => 'success',
        'message' => 'Chat deleted successfully'
    ]);
    exit; 
}


$customers_query = $con->query("SELECT name_customer FROM customers ORDER BY name_customer ASC");
$customers = $customers_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Chat List</title>
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
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 15px; 
            font-size: 14px; 
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: none;  
            width: auto;
            text-align: center;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .chat-item:hover .delete-btn {
            display: block;  
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
        .admin-info {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Customer Inquiries</h2>
            
            <?php if (isset($_SESSION['username'])): ?>
                <span class="admin-info">Logged in as: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php endif; ?>
        </div>

       
        <form method="get" class="mb-4">
            <div class="row">
                <div class="col">
                    <select name="customer_name" class="form-control">
                        <option value="">Filter by Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo htmlspecialchars($customer['name_customer']); ?>" <?php echo $customer_filter == $customer['name_customer'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['name_customer']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </div>
        </form>

        <div class="chat-list-container">
            <?php if (!empty($chats)): ?>
                <div class="list-group">
                    <?php foreach ($chats as $chat): ?>
                        <a href="admin_chat.php?product_id=<?php echo $chat['product_id']; ?>&ticket_id=<?php echo $chat['ticket_id']; ?>&customer_name=<?php echo urlencode($chat['name_customer']); ?>" class="chat-item <?php echo $chat['resolved'] === 'yes' ? 'resolved' : ''; ?>">
                            
                            
                            <?php if ($chat['resolved'] === 'yes'): ?>
                                <div class="resolved-tag">
                                    Resolved
                                </div>
                            <?php endif; ?>

                            <div class="chat-info">
                                <strong><?php echo htmlspecialchars($chat['name_product']); ?></strong>
                            </div>

                           
                            <div class="chat-info">
                                <strong>Ticket ID: <?php echo htmlspecialchars($chat['ticket_id']); ?></strong>
                            </div>

                            <div class="chat-timestamp">
                                Last message at: <?php echo date('Y-m-d H:i:s', strtotime($chat['last_message_time'])); ?>
                            </div>

                            <div class="chat-info">
                                <strong>Customer: <?php echo htmlspecialchars($chat['name_customer']); ?></strong><br>
                                <small>Username: <?php echo htmlspecialchars($chat['username']); ?></small>
                            </div>

                            
                            <button class="delete-btn" data-product-id="<?php echo $chat['product_id']; ?>" data-ticket-id="<?php echo $chat['ticket_id']; ?>">Delete Entire Chat</button>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-chats-message">
                    No chats found yet. All inquiries are listed here.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var ticketId = $(this).data('ticket-id');

            if (confirm('Are you sure you want to delete all messages for this product and ticket?')) {
                $.ajax({
                    url: 'admin_chat_list.php',
                    method: 'POST',
                    data: {
                        delete_chat: {
                            product_id: productId,
                            ticket_id: ticketId
                        }
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            alert(response.message);
                            location.reload();
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred. Please try again.');
                    }
                });
            }
        });
    </script>
</body>
</html>