<?php
include 'dbconnection.php';

header('Content-Type: application/json');

$rejects = $conn->query("SELECT reject_name FROM tbl_rejects ORDER BY reject_name ASC");

$result = array();
while ($row = $rejects->fetch_assoc()) {
    $result[] = $row;
}

echo json_encode($result);
?>
