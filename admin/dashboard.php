<?php
ob_start(); 
session_name('admin_session');
session_start();
$pageTitle = 'Dashboard';
include './init.php';

if (isset($_SESSION['username'])) {
    $do = isset($_GET['do']) ? $_GET['do'] : 'dashboard';
    if ($do == 'dashboard') {

        // Fetch total subtotal for completed orders
        $totalSubtotal = $con->query("SELECT SUM(subtotal) AS total_subtotal FROM orders WHERE order_status = 'completed'")->fetchColumn();
        
        // Fetch total number of customers
        $customersCount = $con->query("SELECT COUNT(*) AS total_customers FROM customers")->fetchColumn();
        
        // Fetch total number of tickets
        $ticketsCount = $con->query("SELECT COUNT(*) AS total_tickets FROM support_tickets")->fetchColumn();
        
        // Removed customer inquiries count since we only need the link to open the inquiries

?>
        <div class="dashboard">
            <div class="container my-5"> <!-- Added vertical margin -->
                <h1 class="mb-4 text-center"><?php echo $pageTitle; ?></h1> <!-- Center title with margin -->
                
                <div class="dashboard-status py-3">
                    <div class="row">

                        <!-- Total Sell Card -->
                        <div class="col-md-3 mb-4">
                            <div class="card" style="background-color: #280068; color: white;"> <!-- Background and text color -->
                                <div class="card-body text-center"> <!-- Centered text -->
                                    <h4 class="card-title">Total Sell</h4>
                                    <p class="card-text"><?php echo number_format($totalSubtotal, 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Customers Card -->
                        <div class="col-md-3 mb-4">
                            <div class="card" style="background-color: #280068; color: white;"> <!-- Background and text color -->
                                <div class="card-body text-center"> <!-- Centered text -->
                                    <h4 class="card-title">Customers</h4>
                                    <p class="card-text"><?php echo $customersCount; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Tickets Card -->
                        <div class="col-md-3 mb-4">
                            <a href="admin_ticket_list.php" style="text-decoration: none;">
                                <div class="card" style="background-color: #280068; color: white;"> <!-- Simple background -->
                                    <div class="card-body text-center"> <!-- Centered text -->
                                        <h4 class="card-title">Tickets</h4>
                                        <p class="card-text"><?php echo $ticketsCount; ?></p> <!-- Display total number of tickets -->
                                    </div>
                                </div>
                            </a>
                        </div>

                        <!-- Customer Inquiries Card (Updated) -->
                        <div class="col-md-3 mb-4">
                            <a href="admin_chat_list.php" style="text-decoration: none;">
                                <div class="card" style="background-color: #280068; color: white;"> <!-- Simple background -->
                                    <div class="card-body text-center"> <!-- Centered text -->
                                        <h4 class="card-title">Customer Inquiries</h4>
                                        <p class="card-text">Click to Open</p> <!-- Display "Click to Open" text -->
                                    </div>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

<?php
    }
} else {
    // Redirect to login if not logged in
    header('location: index.php');
    exit();
}

ob_end_flush();
include $tpl . 'footer.php';
?>