<?php
date_default_timezone_set('Asia/Manila');
session_start();

// Clear the data management authentication
unset($_SESSION['data_management_auth']);

// Redirect to login page
header('Location: data_management_login.php');
exit;
?>
