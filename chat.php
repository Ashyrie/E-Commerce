    <?php
    include './chat_init.php';

    if (!isset($_SESSION['customer_id'])) {
        header('Location: login.php');
        exit;
    }

    $customer_id = isset($_SESSION['customer_id']) ? (int)$_SESSION['customer_id'] : 0;
    $product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0; 

    // Fetch the product details
    $product_details = null;
    $product = $con->prepare("SELECT * FROM products WHERE id = ?");
    $product->execute(array($product_id));
    $product_details = $product->fetch(PDO::FETCH_ASSOC);

    if (!$product_details) {
        echo "Product not found.";
        exit;
    }

    // Fetch the ticket_id for this product and customer combination
    $ticket_id = null;
    $ticket_query = $con->prepare("SELECT ticket_id FROM messages WHERE product_id = ? AND customer_id = ? LIMIT 1");
    $ticket_query->execute([$product_id, $customer_id]);
    $ticket_data = $ticket_query->fetch(PDO::FETCH_ASSOC);
    if ($ticket_data) {
        $ticket_id = $ticket_data['ticket_id'];
    } else {
        // Generate a new ticket_id if none exists
        // Combine random_bytes with a timestamp and uniqid for more entropy
        $random_bytes = bin2hex(random_bytes(16)); // 16 bytes = 128 bits of entropy
        $timestamp = microtime(true); // Get current time with microseconds
        $uniqid = uniqid('', true); // Unique ID with more entropy
        
        
        $ticket_id = "TICKET-" . strtoupper(uniqid($random_bytes . $timestamp . $uniqid, true));

       
        $ticket_insert = $con->prepare("INSERT INTO support_tickets (ticket_id, customer_id, product_id) VALUES (?, ?, ?)");
        $ticket_insert->execute([$ticket_id, $customer_id, $product_id]);

        
        $message_insert = $con->prepare("INSERT INTO messages (customer_id, product_id, message, sender_type, sender_id, ticket_id) VALUES (?, ?, ?, 'customer', ?, ?)");
        $message_insert->execute([$customer_id, $product_id, 'Inquiry started.', $customer_id, $ticket_id]);
    }

    
    $chat_messages = [];
    $messages = $con->prepare("SELECT m.*, c.name_customer
                            FROM messages m
                            JOIN customers c ON m.customer_id = c.id
                            WHERE m.product_id = ? AND m.ticket_id = ?
                            ORDER BY m.timestamp ASC");
    $messages->execute([$product_id, $ticket_id]);
    $chat_messages = $messages->fetchAll(PDO::FETCH_ASSOC);

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inquire Before Buying - <?php echo htmlspecialchars($product_details['name_product']); ?></title>
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
                background-color: #280068; 
                color: white; 
                padding: 10px;
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
            <a href="chat_list.php?id=<?php echo $product_id; ?>" class="back-to-product-btn">
                <i class="fa fa-backward" aria-hidden="true"></i> Back
            </a>

            <div class="chat-container">
                <div class="chat-header">
                    <h3>Inquire about <?php echo htmlspecialchars($product_details['name_product']); ?></h3>
                    <?php if ($ticket_id): ?>
                        <small><strong>Ticket ID: <?php echo htmlspecialchars($ticket_id); ?></strong></small>
                    <?php endif; ?>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <?php
                    if (!empty($chat_messages)) {
                        foreach ($chat_messages as $msg) : ?>
                            <div class="message <?php echo ($msg['customer_id'] == $customer_id) ? 'sent' : 'received'; ?>">
                                <div class="sender"><?php echo htmlspecialchars($msg['name_customer']); ?> <small>(<?php echo $msg['timestamp']; ?>)</small></div>
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
                        <textarea class="form-control" id="message" name="message" rows="2" required placeholder="Type your message..."></textarea>
                        <button type="submit" class="btn btn-primary">Send</button>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script>
            $('#messageForm').submit(function(e) {
                e.preventDefault();

                var message = $('#message').val().trim();
                if (message) {
                    $.ajax({
                        url: 'chat_process.php',
                        method: 'POST',
                        data: {
                            message: message,
                            product_id: <?php echo $product_id; ?>,
                            ticket_id: '<?php echo $ticket_id; ?>' // Ensure ticket_id is passed
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                $('#message').val(''); // Clear yung message
                                var messageHtml = `
                                    <div class="message sent">
                                        <div class="sender">You <small>(${response.timestamp})</small></div>
                                        <div class="text">${response.message.replace(/\n/g, '<br>')}</div>
                                    </div>
                                `;
                                $('#chatMessages').append(messageHtml);
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
                    url: 'chat_process.php',
                    method: 'GET',
                    data: { 
                        product_id: <?php echo $product_id; ?>,
                        ticket_id: '<?php echo $ticket_id; ?>' // Pass yung ticket_id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var messageHtml = '';
                            response.messages.forEach(function(msg) {
                                var message_class = msg.sender_type === 'admin' ? 'sent' : 'received';
                                var timestamp = new Date(msg.timestamp).toLocaleString();
                                
                                messageHtml += `
                                    <div class="message ${message_class}">
                                        <div class="sender">${msg.sender_name} <small>(${timestamp})</small></div>
                                        <div class="text">${msg.message.replace(/\n/g, '<br>')}</div>
                                    </div>
                                `;
                            });

                            $('#chatMessages').html(messageHtml);
                            scrollToBottom();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error fetching messages: " + status + ": " + error);
                    }
                });
            }

            function scrollToBottom() {
                var chatMessages = document.getElementById("chatMessages");
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            setInterval(function() {
                loadMessages();
            }, 2000);  // Refresh every 2 seconds

            loadMessages(); // Initial load of messages
        </script>

<script>
        // wag tanggalin i2 need to para magreload yung ticket id
        if (window.location.search.includes('reload=true')) {
            
            window.history.replaceState({}, document.title, window.location.pathname);
        } else {
            
            setTimeout(function() {
                window.location.href = window.location.href + '&reload=true';
            }, 500); 
        }
    </script>
    </body>
    </html>
