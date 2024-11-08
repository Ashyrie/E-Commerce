<?php
ob_start(); 
session_start();
$pageTitle = 'Dashboard';
include './init.php';
if (isset($_SESSION['username'])) {
    $do = isset($_GET['do']) ? $_GET['do'] : 'dashboard';
    if ($do == 'dashboard') {

        $totalSubtotal = $con->query("SELECT SUM(subtotal) AS total_subtotal FROM orders WHERE order_status = 'completed'")->fetchColumn();
        $customersCount = $con->query("SELECT COUNT(*) AS total_customers FROM customers")->fetchColumn();
?>
        <div class="dashboard">
            <div class="container my-5"> <!-- Added vertical margin -->
                <h1 class="mb-4 text-center"><?php echo $pageTitle; ?></h1> <!-- Center title with margin -->
                <div class="dashboard-status py-3">
                    <div class="row">

                        <div class="col-md-4 mb-4">
                            <div class="card" style="background-color: #280068; color: white;"> <!-- Background and text color -->
                                <div class="card-body text-center"> <!-- Centered text -->
                                    <h4 class="card-title">Total Sell</h4>
                                    <p class="card-text"><?php echo number_format($totalSubtotal, 2); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4">
                            <div class="card" style="background-color: #280068; color: white;"> <!-- Background and text color -->
                                <div class="card-body text-center"> <!-- Centered text -->
                                    <h4 class="card-title">Customers</h4>
                                    <p class="card-text"><?php echo $customersCount; ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
<?php
    }
} else {
    header('location: index.php');
    exit();
}
ob_end_flush();
include $tpl . 'footer.php';
?>
