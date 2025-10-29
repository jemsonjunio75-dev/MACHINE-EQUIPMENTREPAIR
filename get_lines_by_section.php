<?php
include 'dbconnection.php';
header('Content-Type: application/json');
$section = isset($_GET['section_name']) ? $_GET['section_name'] : '';
$result = [];
if ($section !== '') {
    $stmt = $conn->prepare('SELECT line_name FROM tbl_lines WHERE section_name = ? ORDER BY id ASC');
    $stmt->bind_param('s', $section);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }
    $stmt->close();
}
echo json_encode($result);
?>