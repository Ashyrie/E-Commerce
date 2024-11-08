<?php

function filterInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function checkDuplicate($con, $field, $value) {
    $stmt = $con->prepare("SELECT * FROM customers WHERE $field = ?");
    $stmt->execute([$value]);
    return $stmt->rowCount() > 0;
}

function validatePasswordMatch($password, $confirm_password) {
    return $password === $confirm_password;
}
?>
