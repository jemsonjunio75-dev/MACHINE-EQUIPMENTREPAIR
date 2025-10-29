<?php
include 'dbconnection.php';

header('Content-Type: application/json');

$actions_taken = $conn->query("SELECT action_taken_name FROM tbl_action_taken ORDER BY action_taken_name ASC");

$result = array();
while ($row = $actions_taken->fetch_assoc()) {
    $result[] = $row;
}

echo json_encode($result);
?>
