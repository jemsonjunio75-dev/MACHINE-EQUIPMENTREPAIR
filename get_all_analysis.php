<?php
include 'dbconnection.php';

header('Content-Type: application/json');

$analysis_causes = $conn->query("SELECT analysis_cause_name FROM tbl_analysis_cause ORDER BY analysis_cause_name ASC");

$result = array();
while ($row = $analysis_causes->fetch_assoc()) {
    $result[] = $row;
}

echo json_encode($result);
?>
