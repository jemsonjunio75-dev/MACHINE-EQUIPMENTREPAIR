<?php
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';

/**
 * Handles form submission for repair records
 * Returns an array with 'success' boolean and 'message' string
 */
function handleRepairRecordSubmission() {
    global $conn;
    
    $success = false;
    $message = '';
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['success' => false, 'message' => 'Invalid request method'];
    }
    
    // Define all required fields
    $fields = [
        'section_name',
        'line_name','model_name','tool_name','machine_serial','date_occured','start_time','date_ended','end_time','loss_time',
        'reject_name','analysis_cause','action_taken','attented_by',
        'check_result_n_appearance','check_result_r_appearance','check_result_p_appearance',
        'check_result_n_dimension','check_result_r_dimension','check_result_p_dimension',
        'check_result_n_tensile','check_result_r_tensile','check_result_p_tensile',
        'check_result_n_electrical','check_result_r_electrical','check_result_p_electrical',
        'checking_method_appearance','checking_method_dim_a_1','checking_method_dim_b_1','checking_method_dim_c_1',
        'checking_method_dim_a_2','checking_method_dim_b_2','checking_method_dim_c_2',
        'checking_method_dim_a_3','checking_method_dim_b_3','checking_method_dim_c_3',
        'checking_method_ten_1','checking_method_ten_2','checking_method_ten_3','checking_method_electrical'
    ];
    
    // Sanitize and collect form data
    $data = [];
    foreach($fields as $field) {
        $data[$field] = isset($_POST[$field]) ? $conn->real_escape_string($_POST[$field]) : '';
    }
    
    // Build the SQL query
    $sql = "INSERT INTO tbl_repair_records (
        section_name,
        line_name, model_name, tool_name, machine_serial, date_occured, start_time, date_ended, end_time, loss_time,
        reject_name, analysis_cause, action_taken, attented_by,
        check_result_n_appearance, check_result_r_appearance, check_result_p_appearance,
        check_result_n_dimension, check_result_r_dimension, check_result_p_dimension,
        check_result_n_tensile, check_result_r_tensile, check_result_p_tensile,
        check_result_n_electrical, check_result_r_electrical, check_result_p_electrical,
        checking_method_appearance, checking_method_dim_a_1, checking_method_dim_b_1, checking_method_dim_c_1,
        checking_method_dim_a_2, checking_method_dim_b_2, checking_method_dim_c_2,
        checking_method_dim_a_3, checking_method_dim_b_3, checking_method_dim_c_3,
        checking_method_ten_1, checking_method_ten_2, checking_method_ten_3, checking_method_electrical
    ) VALUES (
        '{$data['section_name']}',
        '{$data['line_name']}', '{$data['model_name']}', '{$data['tool_name']}', '{$data['machine_serial']}', '{$data['date_occured']}', '{$data['start_time']}', '{$data['date_ended']}', '{$data['end_time']}', '{$data['loss_time']}',
        '{$data['reject_name']}', '{$data['analysis_cause']}', '{$data['action_taken']}', '{$data['attented_by']}',
        '{$data['check_result_n_appearance']}', '{$data['check_result_r_appearance']}', '{$data['check_result_p_appearance']}',
        '{$data['check_result_n_dimension']}', '{$data['check_result_r_dimension']}', '{$data['check_result_p_dimension']}',
        '{$data['check_result_n_tensile']}', '{$data['check_result_r_tensile']}', '{$data['check_result_p_tensile']}',
        '{$data['check_result_n_electrical']}', '{$data['check_result_r_electrical']}', '{$data['check_result_p_electrical']}',
        '{$data['checking_method_appearance']}', '{$data['checking_method_dim_a_1']}', '{$data['checking_method_dim_b_1']}', '{$data['checking_method_dim_c_1']}',
        '{$data['checking_method_dim_a_2']}', '{$data['checking_method_dim_b_2']}', '{$data['checking_method_dim_c_2']}',
        '{$data['checking_method_dim_a_3']}', '{$data['checking_method_dim_b_3']}', '{$data['checking_method_dim_c_3']}',
        '{$data['checking_method_ten_1']}', '{$data['checking_method_ten_2']}', '{$data['checking_method_ten_3']}', '{$data['checking_method_electrical']}'
    )";
    
    // Execute the query
    if ($conn->query($sql)) {
        $success = true;
        $message = 'Record saved successfully!';
    } else {
        $success = false;
        $message = 'Error: ' . $conn->error;
    }
    
    return ['success' => $success, 'message' => $message];
}

// If this file is accessed directly via POST, handle the submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = handleRepairRecordSubmission();
    
    // Return JSON response for AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    // For regular form submissions, redirect back to the form with status
    $redirect_url = 'fillup.php?status=' . ($result['success'] ? 'success' : 'error') . '&message=' . urlencode($result['message']);
    header('Location: ' . $redirect_url);
    exit;
}
?>
