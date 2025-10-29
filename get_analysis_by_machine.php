<?php
include 'dbconnection.php';

header('Content-Type: application/json');

if (isset($_GET['machine_process_name'])) {
    $machine_process_name = $_GET['machine_process_name'];
    
    // Prepare the query to prevent SQL injection
    $stmt = $conn->prepare("SELECT analysis_cause_name FROM tbl_analysis_cause WHERE machine_process_name = ? ORDER BY analysis_cause_name ASC");
    $stmt->bind_param("s", $machine_process_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $analysis_causes = array();
    while ($row = $result->fetch_assoc()) {
        $analysis_causes[] = $row;
    }
    
    echo json_encode($analysis_causes);
} else {
    echo json_encode(array());
}
?>
