<?php
session_name('admin_session');
session_start();
include './connect.php';  
include './inc/functions/function.php';  


if (!isset($_SESSION['id'])) {
    
    header('Location: index.php');
    exit;
}


$admin_id = $_SESSION['id']; 


$ticket_id = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : ''; 

// Fetch ticket details for display (ticket_id, product_id, customer_name)
$ticket_details = null;
$ticket_query = $con->prepare("SELECT st.ticket_id, st.customer_id, c.name_customer AS customer_name, p.id AS product_id, p.name_product 
                              FROM support_tickets st
                              JOIN customers c ON st.customer_id = c.id
                              JOIN products p ON st.product_id = p.id
                              WHERE st.ticket_id = ?");
$ticket_query->execute([$ticket_id]);
$ticket_details = $ticket_query->fetch(PDO::FETCH_ASSOC);


if (!$ticket_details) {
    echo "Ticket not found.";
    exit;
}

// Fetch messages related to this ticket
$chat_messages = [];
$messages = $con->prepare("
    SELECT m.*, c.name_customer AS customer_name, a.username AS admin_username 
    FROM messages m
    LEFT JOIN customers c ON m.sender_id = c.id AND m.sender_type = 'customer'
    LEFT JOIN admin a ON m.sender_id = a.id AND m.sender_type = 'admin' 
    WHERE m.ticket_id = ? ORDER BY m.timestamp ASC
");
$messages->execute([$ticket_id]);
$chat_messages = $messages->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Ticket Details - <?php echo htmlspecialchars($ticket_details['name_product']); ?></title>
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

        .back-to-ticket-list-btn {
            background-color: #310080;
            color: white;
            padding: 8px 15px;
            border-radius: 10px;
            display: inline-block;
            text-decoration: none;
            font-size: 14px;
            margin-top: 10px;
        }

        .back-to-ticket-list-btn:hover {
            background-color: #380091;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_ticket_list.php" class="back-to-ticket-list-btn">
            <i class="fa fa-backward" aria-hidden="true"></i> Back to Ticket List
        </a>

        <div class="chat-container">
            <div class="chat-header">
                <h3>Admin - Ticket Details - <?php echo htmlspecialchars($ticket_details['name_product']); ?></h3>
                <p><strong>Ticket ID:</strong> <?php echo htmlspecialchars($ticket_details['ticket_id']); ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($ticket_details['customer_name']); ?></p>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <?php
                if (!empty($chat_messages)) {
                    foreach ($chat_messages as $msg) : ?>
                        <div class="message <?php echo ($msg['sender_type'] === 'admin') ? 'sent' : 'received'; ?>">
                            <div class="sender"><?php echo htmlspecialchars($msg['customer_name'] ?: $msg['admin_username']); ?> <small>(<?php echo $msg['timestamp']; ?>)</small></div>
                            <div class="text"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
                        </div>
                    <?php endforeach;
                } else {
                    echo "<p>No messages yet.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        
        function scrollToBottom() {
            var chatMessagesDiv = $('#chatMessages');
            chatMessagesDiv.scrollTop(chatMessagesDiv[0].scrollHeight);
        }

       
        setInterval(function() {
            loadMessages();
        }, 2000);

       
        function loadMessages() {
            $.ajax({
                url: 'admin_chat_process.php',
                method: 'GET',
                data: { 
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

        // Load messages initially when page loads
        loadMessages();
    </script>
</body>
</html>