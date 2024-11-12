<?php
session_name('admin_session');
session_start();
include './connect.php';  // Database connection
include './inc/functions/function.php';  // Additional functions if needed

// Ensure admin is logged in
if (!isset($_SESSION['id'])) {
    // Redirect to login page if not logged in as admin
    header('Location: index.php');
    exit;
}

// Get admin's username
$admin_id = $_SESSION['id']; // The logged-in admin's username

// Get product ID and other details from the URL
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$ticket_id = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : ''; 
$customer_name = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';

// Fetch the product details for display
$product_details = null;
$product = $con->prepare("SELECT * FROM products WHERE id = ?");
$product->execute(array($product_id));
$product_details = $product->fetch(PDO::FETCH_ASSOC);

// Ensure the product exists before showing chat
if (!$product_details) {
    echo "Product not found.";
    exit;
}

// Fetch messages related to this product and ticket_id
$chat_messages = [];
$messages = $con->prepare("
    SELECT m.*, c.name_customer, a.username AS admin_username 
    FROM messages m
    LEFT JOIN customers c ON m.sender_id = c.id AND m.sender_type = 'customer'
    LEFT JOIN admin a ON m.sender_id = a.id AND m.sender_type = 'admin' 
    WHERE m.product_id = ? AND m.ticket_id = ? ORDER BY m.timestamp ASC
");
$messages->execute([$product_id, $ticket_id]);
$chat_messages = $messages->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Chat with Customers - <?php echo htmlspecialchars($product_details['name_product']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            background-color: #f7f7f7;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            overflow: hidden;
        }

        .chat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #280068; /* Deep Purple */
            color: white; /* White text */
            padding: 6px;
            border-radius: 10px 10px 0 0;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .chat-messages {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
            font-size: 14px;
            background-color: #fafafa;
            max-height: calc(100vh - 170px);
            padding-right: 10px;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            padding: 10px;
            border-radius: 25px;
            max-width: 75%;
        }

        .message.sent {
            justify-content: flex-end;
            background-color: #d1f1d1;
            align-self: flex-end;
            border-bottom-right-radius: 0;
        }

        .message.received {
            justify-content: flex-start;
            background-color: #dcc6ff;
            align-self: flex-start;
            border-bottom-left-radius: 0;
        }

        .sender {
            font-weight: bold;
            font-size: 12px;
            color: #333;
        }

        .text {
            margin-top: 5px;
            font-size: 14px;
            line-height: 1.4;
        }

        .chat-input {
            display: flex;
            justify-content: space-between;
            padding-top: 10px;
            gap: 10px;
            padding: 10px;
            background-color: #fff;
            border-top: 1px solid #e0e0e0;
        }

        .chat-input textarea {
            width: 75%;
            resize: none;
            border-radius: 20px;
            padding: 8px;
            font-size: 14px;
            height: 40px;
        }

        .chat-input button {
            width: 20%;
            background-color: #310080;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .chat-input button:hover {
            background-color: #310080;
        }

        .back-to-product-btn {
            background-color: #310080;
            color: white;
            padding: 8px 15px;
            border-radius: 10px;
            display: inline-block;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
        }

        .back-to-product-btn:hover {
            background-color: #380091;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_chat_list.php?id=<?php echo $product_id; ?>" class="back-to-product-btn">
            <i class="fa fa-backward" aria-hidden="true"></i> Back
        </a>

        <div class="chat-container">
            <div class="chat-header">
                <h3>Admin - Chat with Customers about <?php echo htmlspecialchars($product_details['name_product']); ?></h3>
                <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($ticket_id); ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <?php
                if (!empty($chat_messages)) {
                    foreach ($chat_messages as $msg) : ?>
                        <div class="message <?php echo ($msg['sender_type'] === 'admin') ? 'sent' : 'received'; ?>">
                            <div class="sender"><?php echo htmlspecialchars($msg['name_customer'] ?: $msg['admin_username']); ?> <small>(<?php echo $msg['timestamp']; ?>)</small></div>
                            <div class="text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                    <?php endforeach;
                } else {
                    echo "<p>No messages yet.</p>";
                }
                ?>
            </div>

            
            <form id="messageForm" method="POST">
                <div class="chat-input">
                    <textarea class="form-control" id="message" name="message" rows="2" required placeholder="Type your response..."></textarea>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        // Handle message form submission using AJAX
        $('#messageForm').submit(function(e) {
            e.preventDefault();  

            var message = $('#message').val().trim();
            if (message) {
                $.ajax({
                    url: 'admin_chat_process.php',  
                    method: 'POST',
                    data: {
                        message: message,
                        product_id: <?php echo $product_id; ?>,  
                        ticket_id: '<?php echo $ticket_id; ?>'  
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#message').val('');  

                            
                            var messageHtml = ` 
                                <div class="message sent">
                                    <div class="sender">You (Admin) <small>(${response.timestamp})</small></div>
                                    <div class="text">${response.message.replace(/\n/g, '<br>')}</div>
                                </div>
                            `;
                            $('#chatMessages').append(messageHtml);  

                          
                            loadMessages();
                            scrollToBottom(); 
                        } else {
                            alert(response.message);  
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error: " + status + ": " + error);  
                        alert("An error occurred. Please try again.");
                    }
                });
            }
        });

        
        function loadMessages() {
            $.ajax({
                url: 'admin_chat_process.php',
                method: 'GET',
                data: { 
                    product_id: <?php echo $product_id; ?>,
                    ticket_id: '<?php echo $ticket_id; ?>'
                },
                success: function(response) {
                    if (response.status === 'success') {
                        var chatMessagesHtml = '';
                        response.messages.forEach(function(msg) {
                            var messageClass = msg.sender_type === 'admin' ? 'sent' : 'received';
                            chatMessagesHtml += `
                                <div class="message ${messageClass}">
                                    <div class="sender">${msg.sender_name} <small>(${msg.timestamp})</small></div>
                                    <div class="text">${msg.message.replace(/\n/g, '<br>')}</div>
                                </div>
                            `;
                        });

                        
                        $('#chatMessages').html(chatMessagesHtml);
                        scrollToBottom();  
                    } else {
                        alert(response.message);  
                    }
                },
                error: function(xhr, status, error) {
                    console.log("AJAX Error: " + status + ": " + error);  
                }
            });
        }

        
        function scrollToBottom() {
            var chatMessagesDiv = $('#chatMessages');
            chatMessagesDiv.scrollTop(chatMessagesDiv[0].scrollHeight);
        }

        
        setInterval(function() {
            loadMessages();
        }, 2000);

        
        loadMessages();
    </script>
</body>
</html>
