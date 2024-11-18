<?php

session_name('client_session');
session_start();

ob_start(); 

include './connect.php'; // Connection file

include './inc/functions/function.php';  

$css = './assets/css/';
$img = './assets/img/';
$js = './assets/js/';


?>


