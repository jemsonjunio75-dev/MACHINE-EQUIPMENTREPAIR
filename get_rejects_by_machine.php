<?php
include 'dbconnection.php';

header('Content-Type: application/json');

if (isset($_GET['machine_process_name'])) {
    $machine_process_name = $_GET['machine_process_name'];
    
    // Prepare the query to prevent SQL injection
    $stmt = $conn->prepare("SELECT reject_name FROM tbl_rejects WHERE machine_process_name = ? ORDER BY reject_name ASC");
    $stmt->bind_param("s", $machine_process_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rejects = array();
    while ($row = $result->fetch_assoc()) {
        $rejects[] = $row;
    }
    
    echo json_encode($rejects);
} else {
    echo json_encode(array());
}
?>
