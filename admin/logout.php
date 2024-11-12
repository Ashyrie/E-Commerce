<?php
session_name('admin_session');
session_start();
session_unset();
session_destroy();
header('location: index.php');
exit();
?>