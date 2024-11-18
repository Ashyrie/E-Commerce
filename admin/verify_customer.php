<?php

include './init.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $customerId = $_POST['id'];

    
    $stmt = $con->prepare("SELECT * FROM customers WHERE id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($customer) {
        
        $newStatus = $customer['verified'] == 1 ? 0 : 1;
        $updateStmt = $con->prepare("UPDATE customers SET is_verified = ? WHERE id = ?");
        $updateStmt->execute([$newStatus, $customerId]);

        
        echo json_encode(['status' => 'success', 'message' => $newStatus == 1 ? 'Customer verified successfully' : 'Customer suspended successfully']);
    } else {
        
        echo json_encode(['status' => 'error', 'message' => 'Customer not found']);
    }
} else {
    
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>