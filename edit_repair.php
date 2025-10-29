<?php 
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';

$id = $_GET['id'] ?? '';
if (empty($id) || !is_numeric($id)) {
    die('Invalid record ID.');
}

// Fetch record data
$defaults = [];
$sql = "SELECT * FROM tbl_repair_records WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($record = $result->fetch_assoc()) {
    $defaults = $record;
} else {
    die('Record not found.');
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Repair Record | Machine & Equipment Repair Record</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="fontawesome/css/all.min.css">
    <script src="sweetalert/sweetalert.all.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        body { font-family: Arial, sans-serif; font-size: 30px; background: #f4f4f4; margin: 0; }
        .header { background: #2c3e50; color: #fff; padding: 5px 1; text-align: center; }
        h1 { margin: 0; font-size: 50px; font-weight: bold; }
        .sub-header {
            background: #2980b9;
            color: #fff;
            padding: 0px 0;
            font-size: 24px;
        }
        label { font-weight: 500; font-size: 16px;}
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-3 mb-3">
        <a href="index.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Back</a>
        <div class="card mx-auto" style="max-width:100%;">
            <div class="container-fluid mt-5 mb-5">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">Edit Repair Record</h4>
                </div>
                <div class="card-body">
                    <form id="editRepairForm">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($defaults['id']); ?>">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="section_name" class="form-label">Section Name</label>
                                <select class="form-control" id="section_name" name="section_name" required>
                                    <option value="">Select Section</option>
                                    <?php
                                    // Fetch all sections from tbl_section
                                    $sectionOptions = [];
                                    $sectionSql = "SELECT section_name FROM tbl_section ORDER BY section_name ASC";
                                    $sectionRes = $conn->query($sectionSql);
                                    while ($row = $sectionRes->fetch_assoc()) {
                                        $sectionOptions[] = $row['section_name'];
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['section_name']) && in_array($defaults['section_name'], $sectionOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['section_name']) . '" selected>' . htmlspecialchars($defaults['section_name']) . '</option>';
                                        // Remove from options to avoid duplicate
                                        $sectionOptions = array_diff($sectionOptions, [$defaults['section_name']]);
                                    }
                                    foreach ($sectionOptions as $section) {
                                        echo '<option value="' . htmlspecialchars($section) . '">' . htmlspecialchars($section) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="line_name" class="form-label">Line Name</label>
                                <select class="form-control" id="line_name" name="line_name" required>
                                    <option value="">Select Line</option>
                                    <?php
                                    // Fetch all lines for the current section
                                    $lineOptions = [];
                                    $currentSection = $defaults['section_name'] ?? '';
                                    if ($currentSection) {
                                        $lineSql = "SELECT line_name FROM tbl_lines WHERE section_name = ? ORDER BY line_name ASC";
                                        $lineStmt = $conn->prepare($lineSql);
                                        $lineStmt->bind_param('s', $currentSection);
                                        $lineStmt->execute();
                                        $lineRes = $lineStmt->get_result();
                                        while ($row = $lineRes->fetch_assoc()) {
                                            $lineOptions[] = $row['line_name'];
                                        }
                                        $lineStmt->close();
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['line_name']) && in_array($defaults['line_name'], $lineOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['line_name']) . '" selected>' . htmlspecialchars($defaults['line_name']) . '</option>';
                                        $lineOptions = array_diff($lineOptions, [$defaults['line_name']]);
                                    }
                                    foreach ($lineOptions as $line) {
                                        echo '<option value="' . htmlspecialchars($line) . '">' . htmlspecialchars($line) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="model_name" class="form-label">Model Name</label>
                                <select class="form-control" id="model_name" name="model_name" required>
                                    <option value="">Select Model</option>
                                    <?php
                                    // Fetch all models from tbl_model
                                    $modelOptions = [];
                                    $modelSql = "SELECT model_name FROM tbl_model ORDER BY model_name ASC";
                                    $modelRes = $conn->query($modelSql);
                                    while ($row = $modelRes->fetch_assoc()) {
                                        $modelOptions[] = $row['model_name'];
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['model_name']) && in_array($defaults['model_name'], $modelOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['model_name']) . '" selected>' . htmlspecialchars($defaults['model_name']) . '</option>';
                                        // Remove from options to avoid duplicate
                                        $modelOptions = array_diff($modelOptions, [$defaults['model_name']]);
                                    }
                                    foreach ($modelOptions as $model) {
                                        echo '<option value="' . htmlspecialchars($model) . '">' . htmlspecialchars($model) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="tool_name" class="form-label">Machine | Equipment | Jig Tool Name</label>
                                <select class="form-control" id="tool_name" name="tool_name" required onchange="filterRejects()">
                                    <option value="">Select Machine</option>
                                    <?php
                                    $machine_processes = $conn->query("SELECT id, machine_process_name FROM tbl_machine_process ORDER BY machine_process_name ASC");
                                    while($row = $machine_processes->fetch_assoc()) {
                                        $selected = ($defaults['tool_name'] == $row['machine_process_name']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($row['machine_process_name']) . '" ' . $selected . '>' . htmlspecialchars($row['machine_process_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="machine_serial" class="form-label">Machine Serial</label>
                                <input type="text" class="form-control" id="machine_serial" name="machine_serial" required value="<?php echo htmlspecialchars($defaults['machine_serial']); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="date_occured" class="form-label">Date Occurred</label>
                                <input type="date" class="form-control" id="date_occured" name="date_occured" required value="<?php echo htmlspecialchars($defaults['date_occured']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required value="<?php echo htmlspecialchars($defaults['start_time']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="date_ended" class="form-label">Date Ended</label>
                                <input type="date" class="form-control" id="date_ended" name="date_ended" value="<?php echo htmlspecialchars($defaults['date_ended']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required value="<?php echo htmlspecialchars($defaults['end_time']); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="loss_time" class="form-label">Loss Time</label>
                                <input type="text" class="form-control" id="loss_time" name="loss_time" readonly value="<?php echo htmlspecialchars($defaults['loss_time']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="reject_name" class="form-label">Reject Encountered / Abnormality</label>
                                <select class="form-control" id="reject_name" name="reject_name" required>
                                    <option value="<?php echo htmlspecialchars($defaults['reject_name']); ?>"><?php echo htmlspecialchars($defaults['reject_name']); ?></option>
                                    <?php
                                    // Fetch all rejects from tbl_rejects
                                    $rejectOptions = [];
                                    $rejectSql = "SELECT reject_name FROM tbl_rejects ORDER BY reject_name ASC";
                                    $rejectRes = $conn->query($rejectSql);
                                    while ($row = $rejectRes->fetch_assoc()) {
                                        $rejectOptions[] = $row['reject_name'];
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['reject_name']) && in_array($defaults['reject_name'], $rejectOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['reject_name']) . '" selected>' . htmlspecialchars($defaults['reject_name']) . '</option>';
                                        // Remove from options to avoid duplicate
                                        $rejectOptions = array_diff($rejectOptions, [$defaults['reject_name']]);
                                    }
                                    foreach ($rejectOptions as $reject) {
                                        echo '<option value="' . htmlspecialchars($reject) . '">' . htmlspecialchars($reject) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="analysis_cause" class="form-label">Analysis / Cause</label>
                                <select class="form-control" id="analysis_cause" name="analysis_cause" required>
                                    <option value="<?php echo htmlspecialchars($defaults['analysis_cause']); ?>"><?php echo htmlspecialchars($defaults['analysis_cause']); ?></option>
                                    <?php
                                    // Fetch all analysis causes from tbl_analysis_cause
                                    $analysisOptions = [];
                                    $analysisSql = "SELECT analysis_cause_name FROM tbl_analysis_cause ORDER BY analysis_cause_name ASC";
                                    $analysisRes = $conn->query($analysisSql);
                                    while ($row = $analysisRes->fetch_assoc()) {
                                        $analysisOptions[] = $row['analysis_cause_name'];
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['analysis_cause']) && in_array($defaults['analysis_cause'], $analysisOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['analysis_cause']) . '" selected>' . htmlspecialchars($defaults['analysis_cause']) . '</option>';
                                        // Remove from options to avoid duplicate
                                        $analysisOptions = array_diff($analysisOptions, [$defaults['analysis_cause']]);
                                    }
                                    foreach ($analysisOptions as $analysis) {
                                        echo '<option value="' . htmlspecialchars($analysis) . '">' . htmlspecialchars($analysis) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="action_taken" class="form-label">Action Taken</label>
                                <select class="form-control" id="action_taken" name="action_taken" required>
                                    <option value="<?php echo htmlspecialchars($defaults['action_taken']); ?>"><?php echo htmlspecialchars($defaults['action_taken']); ?></option>
                                    <?php
                                    // Fetch all actions taken from tbl_action_taken
                                    $actionOptions = [];
                                    $actionSql = "SELECT action_taken_name FROM tbl_action_taken ORDER BY action_taken_name ASC";
                                    $actionRes = $conn->query($actionSql);
                                    while ($row = $actionRes->fetch_assoc()) {
                                        $actionOptions[] = $row['action_taken_name'];
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['action_taken']) && in_array($defaults['action_taken'], $actionOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['action_taken']) . '" selected>' . htmlspecialchars($defaults['action_taken']) . '</option>';
                                        // Remove from options to avoid duplicate
                                        $actionOptions = array_diff($actionOptions, [$defaults['action_taken']]);
                                    }
                                    foreach ($actionOptions as $action) {
                                        echo '<option value="' . htmlspecialchars($action) . '">' . htmlspecialchars($action) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="attented_by" class="form-label">Attended By</label>
                                <select class="form-control" id="attented_by" name="attented_by" required>
                                    <option value="<?php echo htmlspecialchars($defaults['attented_by']); ?>"><?php echo htmlspecialchars($defaults['attented_by']); ?></option>
                                    <?php
                                    // Fetch all technicians from tbl_technicians
                                    $techOptions = [];
                                    $techSql = "SELECT tech_name FROM tbl_technicians ORDER BY tech_name ASC";
                                    $techRes = $conn->query($techSql);
                                    while ($row = $techRes->fetch_assoc()) {
                                        $techOptions[] = $row['tech_name'];
                                    }
                                    // Show current value first if set and not empty
                                    if (!empty($defaults['attented_by']) && in_array($defaults['attented_by'], $techOptions)) {
                                        echo '<option value="' . htmlspecialchars($defaults['attented_by']) . '" selected>' . htmlspecialchars($defaults['attented_by']) . '</option>';
                                        // Remove from options to avoid duplicate
                                        $techOptions = array_diff($techOptions, [$defaults['attented_by']]);
                                    }
                                    foreach ($techOptions as $tech) {
                                        echo '<option value="' . htmlspecialchars($tech) . '">' . htmlspecialchars($tech) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-4">
                            <h5 class="mb-3">CHECKING RESULT (Overall)</h5>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Appearance (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_appearance" placeholder="N" value="<?php echo htmlspecialchars($defaults['check_result_n_appearance']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Appearance (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_appearance" placeholder="R" value="<?php echo htmlspecialchars($defaults['check_result_r_appearance']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Appearance (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_appearance" placeholder="P" value="<?php echo htmlspecialchars($defaults['check_result_p_appearance']); ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Dimension (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_dimension" placeholder="N" value="<?php echo htmlspecialchars($defaults['check_result_n_dimension']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dimension (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_dimension" placeholder="R" value="<?php echo htmlspecialchars($defaults['check_result_r_dimension']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dimension (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_dimension" placeholder="P" value="<?php echo htmlspecialchars($defaults['check_result_p_dimension']); ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tensile (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_tensile" placeholder="N" value="<?php echo htmlspecialchars($defaults['check_result_n_tensile']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tensile (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_tensile" placeholder="R" value="<?php echo htmlspecialchars($defaults['check_result_r_tensile']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tensile (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_tensile" placeholder="P" value="<?php echo htmlspecialchars($defaults['check_result_p_tensile']); ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Electrical (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_electrical" placeholder="N" value="<?php echo htmlspecialchars($defaults['check_result_n_electrical']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Electrical (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_electrical" placeholder="R" value="<?php echo htmlspecialchars($defaults['check_result_r_electrical']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Electrical (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_electrical" placeholder="P" value="<?php echo htmlspecialchars($defaults['check_result_p_electrical']); ?>">
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-4">
                            <h5 class="mb-3">CHECKING METHOD</h5>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_appearance" checked onclick="toggleMethod('appearance')">
                                        <label class="form-check-label" for="chk_appearance">Appearance</label>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="checking_method_appearance" id="appearance_field" placeholder="Appearance" value="<?php echo htmlspecialchars($defaults['checking_method_appearance']); ?>">
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_dimension" checked onclick="toggleMethod('dimension')">
                                        <label class="form-check-label" for="chk_dimension">Dimension</label>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_a_1" id="dimension_a1" placeholder="Dim A (1)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_a_1']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_b_1" id="dimension_b1" placeholder="Dim B (1)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_b_1']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_c_1" id="dimension_c1" placeholder="Dim C (1)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_c_1']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_a_2" id="dimension_a2" placeholder="Dim A (2)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_a_2']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_b_2" id="dimension_b2" placeholder="Dim B (2)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_b_2']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_c_2" id="dimension_c2" placeholder="Dim C (2)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_c_2']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_a_3" id="dimension_a3" placeholder="Dim A (3)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_a_3']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_b_3" id="dimension_b3" placeholder="Dim B (3)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_b_3']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_c_3" id="dimension_c3" placeholder="Dim C (3)" value="<?php echo htmlspecialchars($defaults['checking_method_dim_c_3']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_tensile" checked onclick="toggleMethod('tensile')">
                                        <label class="form-check-label" for="chk_tensile">Tensile Strength</label>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_ten_1" id="tensile_1" placeholder="Tensile (1)" value="<?php echo htmlspecialchars($defaults['checking_method_ten_1']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_ten_2" id="tensile_2" placeholder="Tensile (2)" value="<?php echo htmlspecialchars($defaults['checking_method_ten_2']); ?>">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_ten_3" id="tensile_3" placeholder="Tensile (3)" value="<?php echo htmlspecialchars($defaults['checking_method_ten_3']); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_electrical" checked onclick="toggleMethod('electrical')">
                                        <label class="form-check-label" for="chk_electrical">Electrical</label>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="checking_method_electrical" id="electrical_field" placeholder="Electrical" value="<?php echo htmlspecialchars($defaults['checking_method_electrical']); ?>">
                                </div>
                            </div>
                        </div>
                        <div id="editAlert"></div>
                        <button type="submit" class="btn btn-primary" id="editSaveBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="editSpinner" role="status" aria-hidden="true"></span>
                            <span id="editBtnText">Save Changes</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    // Only allow numbers and one decimal point in checking result fields
    function restrictToNumberAndDecimal(e) {
        const input = e.target;
        let value = input.value;
        // Remove invalid characters
        value = value.replace(/[^0-9.]/g, '');
        // Only allow one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        input.value = value;
    }

    // Attach to all checking result fields
    const checkFields = [
        'check_result_n_appearance', 'check_result_r_appearance', 'check_result_p_appearance',
        'check_result_n_dimension', 'check_result_r_dimension', 'check_result_p_dimension',
        'check_result_n_tensile', 'check_result_r_tensile', 'check_result_p_tensile',
        'check_result_n_electrical', 'check_result_r_electrical', 'check_result_p_electrical'
    ];
    checkFields.forEach(function(name) {
        const els = document.getElementsByName(name);
        if (els.length > 0) {
            els[0].addEventListener('input', restrictToNumberAndDecimal);
        }
    });

    // Only allow numbers and one decimal point in dimension checking method fields
    function restrictToNumberAndDecimalDimension(e) {
        const input = e.target;
        let value = input.value;
        // Remove invalid characters
        value = value.replace(/[^0-9.]/g, '');
        // Only allow one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        input.value = value;
    }

    // Only allow numbers and one decimal point in tensile checking method fields
    function restrictToNumberAndDecimalTensile(e) {
        const input = e.target;
        let value = input.value;
        // Remove invalid characters
        value = value.replace(/[^0-9.]/g, '');
        // Only allow one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        input.value = value;
    }

    // Attach to dimension checking method fields
    const dimensionFields = [
        'dimension_a1', 'dimension_b1', 'dimension_c1',
        'dimension_a2', 'dimension_b2', 'dimension_c2',
        'dimension_a3', 'dimension_b3', 'dimension_c3'
    ];
    dimensionFields.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', restrictToNumberAndDecimalDimension);
        }
    });

    // Attach to tensile checking method fields
    const tensileFields = [
        'tensile_1', 'tensile_2', 'tensile_3'
    ];
    tensileFields.forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('input', restrictToNumberAndDecimalTensile);
        }
    });
    // AJAX to update line_name dropdown when section changes
    document.getElementById('section_name').addEventListener('change', function() {
        var section = this.value;
        var lineSelect = document.getElementById('line_name');
        lineSelect.innerHTML = '<option value="">Loading...</option>';
        if (!section) {
            lineSelect.innerHTML = '<option value="">Select Line</option>';
            return;
        }
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_lines_by_section.php?section_name=' + encodeURIComponent(section), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var res = JSON.parse(xhr.responseText);
                    var options = '<option value="">Select Line</option>';
                    if (Array.isArray(res) && res.length > 0) {
                        res.forEach(function(lineObj) {
                            options += '<option value="' + lineObj.line_name + '">' + lineObj.line_name + '</option>';
                        });
                        lineSelect.innerHTML = options;
                    } else {
                        lineSelect.innerHTML = '<option value="">No lines found</option>';
                    }
                } catch (e) {
                    lineSelect.innerHTML = '<option value="">Error loading lines</option>';
                }
            } else {
                lineSelect.innerHTML = '<option value="">Error loading lines</option>';
            }
        };
        xhr.send();
    });
    // Compute loss time
    function computeLossTime() {
        var dateOccured = document.getElementById('date_occured').value;
        var startTime = document.getElementById('start_time').value;
        var dateEnded = document.getElementById('date_ended').value;
        var endTime = document.getElementById('end_time').value;
        if(dateOccured && startTime && dateEnded && endTime) {
            var start = new Date(dateOccured + 'T' + startTime);
            var end = new Date(dateEnded + 'T' + endTime);
            var diffMs = end - start;
            if(diffMs > 0) {
                var diffMins = Math.floor(diffMs / 60000);
                document.getElementById('loss_time').value = diffMins + ' mins';
            } else {
                document.getElementById('loss_time').value = '00:00';
            }
        } else {
            document.getElementById('loss_time').value = '';
        }
    }
    document.getElementById('date_occured').addEventListener('change', computeLossTime);
    document.getElementById('start_time').addEventListener('change', computeLossTime);
    document.getElementById('date_ended').addEventListener('change', computeLossTime);
    document.getElementById('end_time').addEventListener('change', computeLossTime);

    // Filter rejects, analysis, and actions by selected machine process
    function filterRejects() {
        var machineProcess = document.getElementById('tool_name').value;
        var rejectSelect = document.getElementById('reject_name');
        var analysisSelect = document.getElementById('analysis_cause');
        var actionSelect = document.getElementById('action_taken');
        
        // Store current values before filtering
        var currentReject = rejectSelect.value;
        var currentAnalysis = analysisSelect.value;
        var currentAction = actionSelect.value;
        
        // Show loading state for all dropdowns
        rejectSelect.innerHTML = '<option value="">Loading...</option>';
        analysisSelect.innerHTML = '<option value="">Loading...</option>';
        actionSelect.innerHTML = '<option value="">Loading...</option>';
        
        if(machineProcess === '') {
            // Reset to show all options if no machine process selected
            Promise.all([
                fetch('get_all_rejects.php'),
                fetch('get_all_analysis.php'),
                fetch('get_all_actions.php')
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(([rejects, analysis, actions]) => {
                // Populate rejects
                let rejectOptions = '<option value="">Select Reject</option>';
                rejects.forEach(function(reject) {
                    var selected = (reject.reject_name === currentReject) ? ' selected' : '';
                    rejectOptions += '<option value="' + reject.reject_name + '"' + selected + '>' + reject.reject_name + '</option>';
                });
                rejectSelect.innerHTML = rejectOptions;
                
                // Populate analysis
                let analysisOptions = '<option value="">Select Analysis/Cause</option>';
                analysis.forEach(function(analysis) {
                    var selected = (analysis.analysis_cause_name === currentAnalysis) ? ' selected' : '';
                    analysisOptions += '<option value="' + analysis.analysis_cause_name + '"' + selected + '>' + analysis.analysis_cause_name + '</option>';
                });
                analysisSelect.innerHTML = analysisOptions;
                
                // Populate actions
                let actionOptions = '<option value="">Select Action Taken</option>';
                actions.forEach(function(action) {
                    var selected = (action.action_taken_name === currentAction) ? ' selected' : '';
                    actionOptions += '<option value="' + action.action_taken_name + '"' + selected + '>' + action.action_taken_name + '</option>';
                });
                actionSelect.innerHTML = actionOptions;
            })
            .catch(() => {
                rejectSelect.innerHTML = '<option value="">Select Reject</option>';
                analysisSelect.innerHTML = '<option value="">Select Analysis/Cause</option>';
                actionSelect.innerHTML = '<option value="">Select Action Taken</option>';
            });
            return;
        }
        
        // Fetch filtered data for selected machine process
        Promise.all([
            fetch('get_rejects_by_machine.php?machine_process_name=' + encodeURIComponent(machineProcess)),
            fetch('get_analysis_by_machine.php?machine_process_name=' + encodeURIComponent(machineProcess)),
            fetch('get_action_by_machine.php?machine_process_name=' + encodeURIComponent(machineProcess))
        ])
        .then(responses => Promise.all(responses.map(r => r.json())))
        .then(([rejects, analysis, actions]) => {
            // Populate rejects
            let rejectOptions = '<option value="">Select Reject</option>';
            rejects.forEach(function(reject) {
                var selected = (reject.reject_name === currentReject) ? ' selected' : '';
                rejectOptions += '<option value="' + reject.reject_name + '"' + selected + '>' + reject.reject_name + '</option>';
            });
            rejectSelect.innerHTML = rejectOptions;
            
            // Populate analysis
            let analysisOptions = '<option value="">Select Analysis/Cause</option>';
            analysis.forEach(function(analysis) {
                var selected = (analysis.analysis_cause_name === currentAnalysis) ? ' selected' : '';
                analysisOptions += '<option value="' + analysis.analysis_cause_name + '"' + selected + '>' + analysis.analysis_cause_name + '</option>';
            });
            analysisSelect.innerHTML = analysisOptions;
            
            // Populate actions
            let actionOptions = '<option value="">Select Action Taken</option>';
            actions.forEach(function(action) {
                var selected = (action.action_taken_name === currentAction) ? ' selected' : '';
                actionOptions += '<option value="' + action.action_taken_name + '"' + selected + '>' + action.action_taken_name + '</option>';
            });
            actionSelect.innerHTML = actionOptions;
        })
        .catch(() => {
            rejectSelect.innerHTML = '<option value="">Select Reject</option>';
            analysisSelect.innerHTML = '<option value="">Select Analysis/Cause</option>';
            actionSelect.innerHTML = '<option value="">Select Action Taken</option>';
        });
    }

    // Initialize filtering on page load
    window.onload = function() {
        filterRejects();
    };

    document.getElementById('editRepairForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const saveBtn = document.getElementById('editSaveBtn');
        const spinner = document.getElementById('editSpinner');
        const btnText = document.getElementById('editBtnText');
        const alertDiv = document.getElementById('editAlert');
        saveBtn.disabled = true;
        spinner.classList.remove('d-none');
        btnText.textContent = 'Saving...';
        alertDiv.innerHTML = '';
        try {
            const formData = new FormData(this);
            formData.append('action', 'edit_record');
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                alertDiv.innerHTML = `<div class="alert alert-danger"><b>SERVER RESPONSE:</b><br><pre>${text}</pre></div>`;
                saveBtn.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Save Changes';
                return;
            }
            if (result.success) {
                alertDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                setTimeout(() => { window.location.href = 'index.php'; }, 1200);
            } else {
                alertDiv.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
            }
        } catch (error) {
            alertDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
        } finally {
            saveBtn.disabled = false;
            spinner.classList.add('d-none');
            btnText.textContent = 'Save Changes';
        }
    });
    </script>
    <?php //include 'footer.php'; ?>
</body>
</html>

