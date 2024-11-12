<?php
include './adminchat_init.php'; 


if (!isset($_SESSION['username'])) {
   
    header('Location: index.php');
    exit();
}


$customer_filter = isset($_GET['customer_name']) ? $_GET['customer_name'] : '';
$product_filter = isset($_GET['product_name']) ? $_GET['product_name'] : '';
$ticket_filter = isset($_GET['ticket_id']) ? $_GET['ticket_id'] : '';
$resolved_filter = isset($_GET['resolved']) ? $_GET['resolved'] : '';


$tickets = [];
$ticket_query_str = "
    SELECT t.ticket_id, t.created_at AS ticket_created_at, 
           c.name_customer, c.username, 
           p.name_product, p.id AS product_id, t.resolved
    FROM support_tickets t
    JOIN customers c ON t.customer_id = c.id
    JOIN products p ON t.product_id = p.id
";


$conditions = [];
if ($customer_filter) {
    $conditions[] = "c.name_customer LIKE :customer_filter";
}
if ($product_filter) {
    $conditions[] = "p.name_product LIKE :product_filter";
}
if ($ticket_filter) {
    $conditions[] = "t.ticket_id LIKE :ticket_filter";
}
if ($resolved_filter !== '') {
    
    $conditions[] = "t.resolved = :resolved_filter";
}


if (!empty($conditions)) {
    $ticket_query_str .= " WHERE " . implode(' AND ', $conditions);
}

$ticket_query_str .= " ORDER BY t.created_at DESC";

$ticket_query = $con->prepare($ticket_query_str);


if ($customer_filter) {
    $ticket_query->bindValue(':customer_filter', "%$customer_filter%");
}
if ($product_filter) {
    $ticket_query->bindValue(':product_filter', "%$product_filter%");
}
if ($ticket_filter) {
    $ticket_query->bindValue(':ticket_filter', "%$ticket_filter%");
}
if ($resolved_filter !== '') {
    $ticket_query->bindValue(':resolved_filter', $resolved_filter);
}

$ticket_query->execute();
$tickets = $ticket_query->fetchAll(PDO::FETCH_ASSOC);


$customers_query = $con->query("SELECT name_customer FROM customers ORDER BY name_customer ASC");
$customers = $customers_query->fetchAll(PDO::FETCH_ASSOC);

$products_query = $con->query("SELECT name_product FROM products ORDER BY name_product ASC");
$products = $products_query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Ticket List</title>
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
        .ticket-list-container {
            margin-top: 30px;
            padding: 0 15px;
        }
        .ticket-item {
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
        .ticket-item:hover {
            background-color: #f1f1f1;
            transform: scale(1.02);
        }
        .ticket-info {
            font-size: 18px; 
            font-weight: 600;
            color: #333;
            text-align: center;
        }
        .ticket-timestamp {
            font-size: 14px; 
            color: #888;
            margin-top: 8px;
        }
        .ticket-list-container .list-group {
            padding: 0;
        }
        .no-tickets-message {
            padding: 30px;
            text-align: center;
            font-size: 18px;
            color: #999;
            font-style: italic;
        }
        .admin-info {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-left: 10px;
        }

        
        .ticket-item .ticket-actions {
            display: none;
        }

        
        .ticket-item:hover .ticket-actions {
            display: block;
        }

        
        .ticket-actions button {
            margin-left: 10px;
            font-size: 14px;
        }

        .resolved-btn {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }

        .resolved-btn:hover {
            background-color: #218838;
        }

        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        .undo-resolved-btn {
            background-color: #ffc107; 
            color: white;
            border: none;
            cursor: pointer;
        }

        .undo-resolved-btn:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Customer Support Tickets</h2>
            
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
                    <select name="product_name" class="form-control">
                        <option value="">Filter by Product</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo htmlspecialchars($product['name_product']); ?>" <?php echo $product_filter == $product['name_product'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['name_product']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <input type="text" name="ticket_id" class="form-control" placeholder="Search by Ticket ID" value="<?php echo htmlspecialchars($ticket_filter); ?>" />
                </div>
                <div class="col">
                    <select name="resolved" class="form-control">
                        <option value="">Filter by Status</option>
                        <option value="yes" <?php echo $resolved_filter === 'yes' ? 'selected' : ''; ?>>Resolved</option>
                        <option value="no" <?php echo $resolved_filter === 'no' ? 'selected' : ''; ?>>Unresolved</option>
                    </select>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </div>
        </form>

        <div class="ticket-list-container">
            <?php if (!empty($tickets)): ?>
                <div class="list-group">
                    <?php foreach ($tickets as $ticket): ?>
                        <a href="admin_ticket_details.php?ticket_id=<?php echo htmlspecialchars($ticket['ticket_id']); ?>" class="ticket-item">
                            <div class="ticket-info">
                                <strong>Ticket ID: <?php echo htmlspecialchars($ticket['ticket_id']); ?></strong>
                            </div>

                            <div class="ticket-info">
                                <strong>Customer: <?php echo htmlspecialchars($ticket['name_customer']); ?></strong><br>
                                <small>Username: <?php echo htmlspecialchars($ticket['username']); ?></small>
                            </div>

                            <div class="ticket-info">
                                <strong>Product: <?php echo htmlspecialchars($ticket['name_product']); ?></strong>
                            </div>

                            <div class="ticket-timestamp">
                                Created at: <?php echo date('Y-m-d H:i', strtotime($ticket['ticket_created_at'])); ?>
                            </div>

                            
                            <div class="ticket-actions">
                               
                                <?php if ($ticket['resolved'] === 'no'): ?>
                                    <form method="POST" action="resolve_ticket.php">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>" />
                                        <button type="submit" class="resolved-btn">Mark as Resolved</button>
                                    </form>
                                <?php elseif ($ticket['resolved'] === 'yes'): ?>
                                    <form method="POST" action="undo_resolve_ticket.php">
                                        <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>" />
                                        <button type="submit" class="undo-resolved-btn">Undo Resolved</button>
                                    </form>
                                <?php endif; ?>

                                <form method="POST" action="delete_ticket.php">
                                    <input type="hidden" name="ticket_id" value="<?php echo $ticket['ticket_id']; ?>" />
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-tickets-message">No tickets found. Try adjusting the filters.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
