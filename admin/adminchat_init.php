<?php

session_name('admin_session');
session_start();

ob_start();  

include './connect.php'; 

include './inc/functions/function.php';  


if (isset($_SESSION['username'])) {
    
    $adminUsername = $_SESSION['username'];
    $adminId = $_SESSION['id'];
} else {
    
    header('Location: index.php');
    exit();
}


if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    
    session_unset();
    session_destroy();
    
    
    if (isset($_COOKIE['login_credentials'])) {
        setcookie('login_credentials', '', time() - 3600, '/');  
    }

    
    header('Location: index.php');
    exit();
}


?>
