<?php
$dsn = "mysql:host=localhost;dbname=deltechecom";
$user = "root";
$pass = "";
$option = array(
  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
);
try {
  $con = new PDO($dsn, $user, $pass, $option);
  $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  echo 'Failed To Connect ' . $e->getMessage();
}
// Connect to the verification database
$dsn_verify = "mysql:host=localhost;dbname=deltech_verify";
try {
    $con_verify = new PDO($dsn_verify, $user, $pass, $option);
    $con_verify->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Failed To Connect to deltech_verify database: ' . $e->getMessage();
}