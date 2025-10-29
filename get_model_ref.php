<?php
include 'dbconnection.php';
header('Content-Type: application/json');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT dimension_a, dimension_b, dimension_c, tensile FROM tbl_model WHERE id = $id LIMIT 1");
if($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode([
        'dimension_a' => '0.00~0.00',
        'dimension_b' => '0.00~0.00',
        'dimension_c' => '0.00~0.00',
        'tensile' => '≥0.00'
    ]);
}
?>
