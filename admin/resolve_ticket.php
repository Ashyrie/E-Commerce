<?php
include './adminchat_init.php';


if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    
    
    $update_query = $con->prepare("UPDATE support_tickets SET resolved = 'yes' WHERE ticket_id = ?");
    $update_query->execute([$ticket_id]);
    
    
    header('Location: admin_ticket_list.php');
    exit();
}