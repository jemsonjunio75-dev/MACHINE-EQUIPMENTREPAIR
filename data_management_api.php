<?php
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';
session_start();
header('Content-Type: application/json');

// Check if user is authenticated
if(!isset($_SESSION['data_management_auth']) || $_SESSION['data_management_auth'] !== true) {
	echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
	exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$table = isset($_GET['table']) ? $_GET['table'] : (isset($_POST['table']) ? $_POST['table'] : '');

// Map table names to actual database table names
$tableMap = [
    'section' => 'tbl_section',
    'lines' => 'tbl_lines',
    'models' => 'tbl_model',
    'machine-process' => 'tbl_machine_process',
    'rejects' => 'tbl_rejects',
    'analysis' => 'tbl_analysis_cause',
    'action' => 'tbl_action_taken',
    'technicians' => 'tbl_technicians',
    'users' => 'tbl_users',
    'repair-records' => 'tbl_repair_records'
];

// GET operations
if($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    // Get machine processes for dropdown
    if($action === 'get_machine_processes') {
        $sql = "SELECT * FROM tbl_machine_process ORDER BY machine_process_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get sections for dropdown
    if($action === 'get_sections') {
        $sql = "SELECT * FROM tbl_section ORDER BY section_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get lines for dropdown (optionally filtered by section)
    if($action === 'get_lines') {
        $section = isset($_GET['section']) ? $conn->real_escape_string($_GET['section']) : '';
        if($section) {
            $sql = "SELECT line_name FROM tbl_lines WHERE section_name = '$section' ORDER BY line_name";
        } else {
            $sql = "SELECT line_name FROM tbl_lines ORDER BY line_name";
        }
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get models for dropdown
    if($action === 'get_models') {
        $sql = "SELECT model_name FROM tbl_model ORDER BY model_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get rejects for dropdown
    if($action === 'get_rejects') {
        $sql = "SELECT reject_name FROM tbl_rejects ORDER BY reject_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get analysis causes for dropdown
    if($action === 'get_analysis') {
        $sql = "SELECT analysis_cause_name FROM tbl_analysis_cause ORDER BY analysis_cause_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get actions for dropdown
    if($action === 'get_actions') {
        $sql = "SELECT action_taken_name FROM tbl_action_taken ORDER BY action_taken_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get technicians for dropdown
    if($action === 'get_technicians') {
        $sql = "SELECT tech_name FROM tbl_technicians ORDER BY tech_name";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get record logs
    if($action === 'get_logs') {
        $sql = "SELECT * FROM tbl_repair_records_log ORDER BY action_timestamp DESC";
        $result = $conn->query($sql);
        $data = [];
        if($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get all records from a table
    if($action === 'get' && isset($tableMap[$table])) {
        $dbTable = $tableMap[$table];
        $sql = "SELECT * FROM $dbTable ORDER BY id DESC";   
        $result = $conn->query($sql);
        
        if($result === false) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        
        $data = [];
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    // Get single record
    if($action === 'get_single' && isset($tableMap[$table]) && isset($_GET['id'])) {
        $dbTable = $tableMap[$table];
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM $dbTable WHERE id = $id";
        $result = $conn->query($sql);
        
        if($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
        }
        exit;
    }
}

// POST operations (Add, Edit, Delete)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Authentication endpoint for repair records
    if($action === 'authenticate') {
        $user_id = $conn->real_escape_string($_POST['user_id']);
        $password = $conn->real_escape_string($_POST['password']);
        
        $sql = "SELECT * FROM tbl_users WHERE user_id = '$user_id' AND user_password = '$password'";
        $result = $conn->query($sql);
        
        if($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Store user information in session for audit logging
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_role'] = $user['user_role'];
            
            echo json_encode(['success' => true, 'message' => 'Authentication successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
        exit;
    }
    
    // ADD operation
    if($action === 'add' && isset($tableMap[$table])) {
        $dbTable = $tableMap[$table];
        
        switch($table) {
            case 'section':
                $section_name = $conn->real_escape_string($_POST['section_name']);
                $sql = "INSERT INTO $dbTable (section_name) VALUES ('$section_name')";
                break;
                
            case 'lines':
                $line_name = $conn->real_escape_string($_POST['line_name']);
                $section_name = $conn->real_escape_string($_POST['section_name']);
                $sql = "INSERT INTO $dbTable (line_name, section_name) VALUES ('$line_name', '$section_name')";
                break;
                
            case 'models':
                $model_name = $conn->real_escape_string($_POST['model_name']);
                $dimension_a = $conn->real_escape_string($_POST['dimension_a']);
                $dimension_b = $conn->real_escape_string($_POST['dimension_b']);
                $dimension_c = $conn->real_escape_string($_POST['dimension_c']);
                $tensile = $conn->real_escape_string($_POST['tensile']);
                $sql = "INSERT INTO $dbTable (model_name, dimension_a, dimension_b, dimension_c, tensile) 
                        VALUES ('$model_name', '$dimension_a', '$dimension_b', '$dimension_c', '$tensile')";
                break;
                
            case 'machine-process':
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "INSERT INTO $dbTable (machine_process_name) VALUES ('$machine_process_name')";
                break;
                
            case 'rejects':
                $reject_name = $conn->real_escape_string($_POST['reject_name']);
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "INSERT INTO $dbTable (reject_name, machine_process_name) VALUES ('$reject_name', '$machine_process_name')";
                break;
                
            case 'analysis':
                $analysis_cause_name = $conn->real_escape_string($_POST['analysis_cause_name']);
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "INSERT INTO $dbTable (analysis_cause_name, machine_process_name) VALUES ('$analysis_cause_name', '$machine_process_name')";
                break;
                
            case 'action':
                $action_taken_name = $conn->real_escape_string($_POST['action_taken_name']);
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "INSERT INTO $dbTable (action_taken_name, machine_process_name) VALUES ('$action_taken_name', '$machine_process_name')";
                break;
                
            case 'technicians':
                $tech_name = $conn->real_escape_string($_POST['tech_name']);
                $sql = "INSERT INTO $dbTable (tech_name) VALUES ('$tech_name')";
                break;
                
            case 'users':
                $user_id = $conn->real_escape_string($_POST['user_id']);
                $user_name = $conn->real_escape_string($_POST['user_name']);
                $user_password = $conn->real_escape_string($_POST['user_password']);
                $user_role = $conn->real_escape_string($_POST['user_role']);
                $sql = "INSERT INTO $dbTable (user_id, user_name, user_password, user_role) 
                        VALUES ('$user_id', '$user_name', '$user_password', '$user_role')";
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid table']);
                exit;
        }
        
        if($conn->query($sql)) {
            echo json_encode(['success' => true, 'message' => 'Data added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
    
    // EDIT operation
    if($action === 'edit' && isset($tableMap[$table]) && isset($_POST['id'])) {
        $dbTable = $tableMap[$table];
        $id = intval($_POST['id']);
        
        switch($table) {
            case 'section':
                $section_name = $conn->real_escape_string($_POST['section_name']);
                $sql = "UPDATE $dbTable SET section_name = '$section_name' WHERE id = $id";
                break;
                
            case 'lines':
                $line_name = $conn->real_escape_string($_POST['line_name']);
                $section_name = $conn->real_escape_string($_POST['section_name']);
                $sql = "UPDATE $dbTable SET line_name = '$line_name', section_name = '$section_name' WHERE id = $id";
                break;
                
            case 'models':
                $model_name = $conn->real_escape_string($_POST['model_name']);
                $dimension_a = $conn->real_escape_string($_POST['dimension_a']);
                $dimension_b = $conn->real_escape_string($_POST['dimension_b']);
                $dimension_c = $conn->real_escape_string($_POST['dimension_c']);
                $tensile = $conn->real_escape_string($_POST['tensile']);
                $sql = "UPDATE $dbTable SET model_name = '$model_name', dimension_a = '$dimension_a', 
                        dimension_b = '$dimension_b', dimension_c = '$dimension_c', tensile = '$tensile' WHERE id = $id";
                break;
                
            case 'machine-process':
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "UPDATE $dbTable SET machine_process_name = '$machine_process_name' WHERE id = $id";
                break;
                
            case 'rejects':
                $reject_name = $conn->real_escape_string($_POST['reject_name']);
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "UPDATE $dbTable SET reject_name = '$reject_name', machine_process_name = '$machine_process_name' WHERE id = $id";
                break;
                
            case 'analysis':
                $analysis_cause_name = $conn->real_escape_string($_POST['analysis_cause_name']);
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "UPDATE $dbTable SET analysis_cause_name = '$analysis_cause_name', machine_process_name = '$machine_process_name' WHERE id = $id";
                break;
                
            case 'action':
                $action_taken_name = $conn->real_escape_string($_POST['action_taken_name']);
                $machine_process_name = $conn->real_escape_string($_POST['machine_process_name']);
                $sql = "UPDATE $dbTable SET action_taken_name = '$action_taken_name', machine_process_name = '$machine_process_name' WHERE id = $id";
                break;
                
            case 'technicians':
                $tech_name = $conn->real_escape_string($_POST['tech_name']);
                $sql = "UPDATE $dbTable SET tech_name = '$tech_name' WHERE id = $id";
                break;
                
            case 'users':
                $user_id = $conn->real_escape_string($_POST['user_id']);
                $user_name = $conn->real_escape_string($_POST['user_name']);
                $user_password = $conn->real_escape_string($_POST['user_password']);
                $user_role = $conn->real_escape_string($_POST['user_role']);
                
                // Only update password if provided
                if(!empty($user_password)) {
                    $sql = "UPDATE $dbTable SET user_id = '$user_id', user_name = '$user_name', 
                            user_password = '$user_password', user_role = '$user_role' WHERE id = $id";
                } else {
                    $sql = "UPDATE $dbTable SET user_id = '$user_id', user_name = '$user_name', 
                            user_role = '$user_role' WHERE id = $id";
                }
                break;
                
            case 'repair-records':
                // Fetch old values for logging
                $oldDataSql = "SELECT * FROM $dbTable WHERE id = $id";
                $oldDataResult = $conn->query($oldDataSql);
                $oldData = $oldDataResult->fetch_assoc();
                
                $section_name = $conn->real_escape_string($_POST['section_name']);
                $line_name = $conn->real_escape_string($_POST['line_name']);
                $model_name = $conn->real_escape_string($_POST['model_name']);
                $tool_name = $conn->real_escape_string($_POST['tool_name']);
                $machine_serial = $conn->real_escape_string($_POST['machine_serial']);
                $date_occured = $conn->real_escape_string($_POST['date_occured']);
                $start_time = $conn->real_escape_string($_POST['start_time']);
                $date_ended = $conn->real_escape_string($_POST['date_ended']);
                $end_time = $conn->real_escape_string($_POST['end_time']);
                $loss_time = $conn->real_escape_string($_POST['loss_time']);
                $reject_name = $conn->real_escape_string($_POST['reject_name']);
                $analysis_cause = $conn->real_escape_string($_POST['analysis_cause']);
                $action_taken = $conn->real_escape_string($_POST['action_taken']);
                $attented_by = $conn->real_escape_string($_POST['attented_by']);
                
                // Checking Results
                $check_result_n_appearance = $conn->real_escape_string($_POST['check_result_n_appearance']);
                $check_result_r_appearance = $conn->real_escape_string($_POST['check_result_r_appearance']);
                $check_result_p_appearance = $conn->real_escape_string($_POST['check_result_p_appearance']);
                $check_result_n_dimension = $conn->real_escape_string($_POST['check_result_n_dimension']);
                $check_result_r_dimension = $conn->real_escape_string($_POST['check_result_r_dimension']);
                $check_result_p_dimension = $conn->real_escape_string($_POST['check_result_p_dimension']);
                $check_result_n_tensile = $conn->real_escape_string($_POST['check_result_n_tensile']);
                $check_result_r_tensile = $conn->real_escape_string($_POST['check_result_r_tensile']);
                $check_result_p_tensile = $conn->real_escape_string($_POST['check_result_p_tensile']);
                $check_result_n_electrical = $conn->real_escape_string($_POST['check_result_n_electrical']);
                $check_result_r_electrical = $conn->real_escape_string($_POST['check_result_r_electrical']);
                $check_result_p_electrical = $conn->real_escape_string($_POST['check_result_p_electrical']);
                
                // Checking Methods
                $checking_method_appearance = $conn->real_escape_string($_POST['checking_method_appearance']);
                $checking_method_dim_a_1 = $conn->real_escape_string($_POST['checking_method_dim_a_1']);
                $checking_method_dim_a_2 = $conn->real_escape_string($_POST['checking_method_dim_a_2']);
                $checking_method_dim_a_3 = $conn->real_escape_string($_POST['checking_method_dim_a_3']);
                $checking_method_dim_b_1 = $conn->real_escape_string($_POST['checking_method_dim_b_1']);
                $checking_method_dim_b_2 = $conn->real_escape_string($_POST['checking_method_dim_b_2']);
                $checking_method_dim_b_3 = $conn->real_escape_string($_POST['checking_method_dim_b_3']);
                $checking_method_dim_c_1 = $conn->real_escape_string($_POST['checking_method_dim_c_1']);
                $checking_method_dim_c_2 = $conn->real_escape_string($_POST['checking_method_dim_c_2']);
                $checking_method_dim_c_3 = $conn->real_escape_string($_POST['checking_method_dim_c_3']);
                $checking_method_ten_1 = $conn->real_escape_string($_POST['checking_method_ten_1']);
                $checking_method_ten_2 = $conn->real_escape_string($_POST['checking_method_ten_2']);
                $checking_method_ten_3 = $conn->real_escape_string($_POST['checking_method_ten_3']);
                $checking_method_electrical = $conn->real_escape_string($_POST['checking_method_electrical']);
                
                $sql = "UPDATE $dbTable SET 
                        section_name = '$section_name',
                        line_name = '$line_name',
                        model_name = '$model_name',
                        tool_name = '$tool_name',
                        machine_serial = '$machine_serial',
                        date_occured = '$date_occured',
                        start_time = '$start_time',
                        date_ended = '$date_ended',
                        end_time = '$end_time',
                        loss_time = '$loss_time',
                        reject_name = '$reject_name',
                        analysis_cause = '$analysis_cause',
                        action_taken = '$action_taken',
                        attented_by = '$attented_by',
                        check_result_n_appearance = '$check_result_n_appearance',
                        check_result_r_appearance = '$check_result_r_appearance',
                        check_result_p_appearance = '$check_result_p_appearance',
                        check_result_n_dimension = '$check_result_n_dimension',
                        check_result_r_dimension = '$check_result_r_dimension',
                        check_result_p_dimension = '$check_result_p_dimension',
                        check_result_n_tensile = '$check_result_n_tensile',
                        check_result_r_tensile = '$check_result_r_tensile',
                        check_result_p_tensile = '$check_result_p_tensile',
                        check_result_n_electrical = '$check_result_n_electrical',
                        check_result_r_electrical = '$check_result_r_electrical',
                        check_result_p_electrical = '$check_result_p_electrical',
                        checking_method_appearance = '$checking_method_appearance',
                        checking_method_dim_a_1 = '$checking_method_dim_a_1',
                        checking_method_dim_a_2 = '$checking_method_dim_a_2',
                        checking_method_dim_a_3 = '$checking_method_dim_a_3',
                        checking_method_dim_b_1 = '$checking_method_dim_b_1',
                        checking_method_dim_b_2 = '$checking_method_dim_b_2',
                        checking_method_dim_b_3 = '$checking_method_dim_b_3',
                        checking_method_dim_c_1 = '$checking_method_dim_c_1',
                        checking_method_dim_c_2 = '$checking_method_dim_c_2',
                        checking_method_dim_c_3 = '$checking_method_dim_c_3',
                        checking_method_ten_1 = '$checking_method_ten_1',
                        checking_method_ten_2 = '$checking_method_ten_2',
                        checking_method_ten_3 = '$checking_method_ten_3',
                        checking_method_electrical = '$checking_method_electrical'
                        WHERE id = $id";
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid table']);
                exit;
        }
        
        if($conn->query($sql)) {
            // Log the edit action for repair-records
            if($table === 'repair-records' && isset($oldData)) {
                $newData = $_POST;
                unset($newData['action'], $newData['table'], $newData['id']);
                
                // Build changes summary
                $changes = [];
                foreach($newData as $field => $newValue) {
                    $oldValue = $oldData[$field] ?? '';
                    if($oldValue != $newValue && $field != 'action' && $field != 'table') {
                        $fieldName = ucwords(str_replace('_', ' ', $field));
                        $changes[] = "$fieldName: '$oldValue' → '$newValue'";
                    }
                }
                $changesSummary = implode(', ', $changes);
                
                // Get user info from session
                $userId = $_SESSION['user_id'] ?? 'unknown';
                $userName = $_SESSION['user_name'] ?? 'Unknown User';
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $oldValuesJson = $conn->real_escape_string(json_encode($oldData));
                $newValuesJson = $conn->real_escape_string(json_encode($newData));
                $changesSummaryEsc = $conn->real_escape_string($changesSummary);
                $userIdEsc = $conn->real_escape_string($userId);
                $userNameEsc = $conn->real_escape_string($userName);
                $ipAddressEsc = $conn->real_escape_string($ipAddress);
                $userAgentEsc = $conn->real_escape_string(substr($userAgent, 0, 255));
                
                $logSql = "INSERT INTO tbl_repair_records_log 
                          (record_id, action_type, action_by, action_by_name, old_values, new_values, 
                           changes_summary, ip_address, user_agent) 
                          VALUES ($id, 'EDIT', '$userIdEsc', '$userNameEsc', '$oldValuesJson', 
                                  '$newValuesJson', '$changesSummaryEsc', '$ipAddressEsc', '$userAgentEsc')";
                $conn->query($logSql);
            }
            
            echo json_encode(['success' => true, 'message' => 'Data updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
    
    // DELETE operation
    if($action === 'delete' && isset($tableMap[$table]) && isset($_POST['id'])) {
        $dbTable = $tableMap[$table];
        $id = intval($_POST['id']);
        
        // Fetch record data before deletion for logging (repair-records only)
        $oldData = null;
        if($table === 'repair-records') {
            $oldDataSql = "SELECT * FROM $dbTable WHERE id = $id";
            $oldDataResult = $conn->query($oldDataSql);
            $oldData = $oldDataResult->fetch_assoc();
        }
        
        $sql = "DELETE FROM $dbTable WHERE id = $id";
        
        if($conn->query($sql)) {
            // Log the delete action for repair-records
            if($table === 'repair-records' && $oldData) {
                $userId = $_SESSION['user_id'] ?? 'unknown';
                $userName = $_SESSION['user_name'] ?? 'Unknown User';
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                
                $oldValuesJson = $conn->real_escape_string(json_encode($oldData));
                $changesSummary = "Deleted repair record ID: $id";
                $changesSummaryEsc = $conn->real_escape_string($changesSummary);
                $userIdEsc = $conn->real_escape_string($userId);
                $userNameEsc = $conn->real_escape_string($userName);
                $ipAddressEsc = $conn->real_escape_string($ipAddress);
                $userAgentEsc = $conn->real_escape_string(substr($userAgent, 0, 255));
                
                $logSql = "INSERT INTO tbl_repair_records_log 
                          (record_id, action_type, action_by, action_by_name, old_values, 
                           changes_summary, ip_address, user_agent) 
                          VALUES ($id, 'DELETE', '$userIdEsc', '$userNameEsc', '$oldValuesJson', 
                                  '$changesSummaryEsc', '$ipAddressEsc', '$userAgentEsc')";
                $conn->query($logSql);
            }
            
            echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        exit;
    }
}

// Invalid request
echo json_encode(['success' => false, 'message' => 'Invalid request']);
$conn->close();
?>
