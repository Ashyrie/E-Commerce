<?php
include './adminchat_init.php';


if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $ticket_id = $_POST['ticket_id'];

    try {
        
        $con->beginTransaction();

        
        $delete_messages_query = $con->prepare("DELETE FROM messages WHERE ticket_id = ?");
        $delete_messages_query->execute([$ticket_id]);

        
        $delete_ticket_query = $con->prepare("DELETE FROM support_tickets WHERE ticket_id = ?");
        $delete_ticket_query->execute([$ticket_id]);

        
        $con->commit();

        
        header('Location: admin_ticket_list.php');
        exit();

    } catch (Exception $e) {
        
        $con->rollBack();
        
        echo "Error: " . $e->getMessage();
        exit();
    }
}
