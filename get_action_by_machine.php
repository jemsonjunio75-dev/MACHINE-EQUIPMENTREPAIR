<?php
include 'dbconnection.php';

header('Content-Type: application/json');

if (isset($_GET['machine_process_name'])) {
    $machine_process_name = $_GET['machine_process_name'];
    
    // Prepare the query to prevent SQL injection
    $stmt = $conn->prepare("SELECT action_taken_name FROM tbl_action_taken WHERE machine_process_name = ? ORDER BY action_taken_name ASC");
    $stmt->bind_param("s", $machine_process_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $actions_taken = array();
    while ($row = $result->fetch_assoc()) {
        $actions_taken[] = $row;
    }
    
    echo json_encode($actions_taken);
} else {
    echo json_encode(array());
}
?>
