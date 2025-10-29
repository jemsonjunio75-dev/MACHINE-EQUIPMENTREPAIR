<?php 
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';

// Handle status messages from form submission
$success = false;
$error = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $success = true;
    } elseif ($_GET['status'] === 'error') {
        $error = isset($_GET['message']) ? $_GET['message'] : 'An error occurred';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fill Up Form | Machine & Equipment Repair Record</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="fontawesome/css/all.min.css">
    <script src="sweetalert/sweetalert.all.min.js"></script>
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
    
    /* Banner styling */
    .banner-container {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 20px;
        margin: 20px 0;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .banner-image {
        max-height: 1000px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .banner-image:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.2);
    }
    
    @media (max-width: 768px) {
        .banner-image {
            max-height: 220px;
        }
        .banner-container {
            padding: 15px;
            margin: 15px 0;
        }
    }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container mt-3 mb-3">
        <a href="index.php" class="btn btn-secondary mb-4"><i class="fas fa-arrow-left"></i> Back</a>

    <div class="card mx-auto" style="max-width:100%;">
    <div class="container-fluid mt-5 mb-5">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0">Repair Record Fill Up Form</h4>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                        <div class="alert alert-success">Record saved successfully!</div>
                    <?php elseif($error): ?>
                        <div class="alert alert-danger">Error: <?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form id="repairForm" method="post" action="submit_repair_record.php">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="section_name" class="form-label">Section</label>
                                <select class="form-control" id="section_name" name="section_name" required onchange="filterLines()">
                                    <option value="">Select Section</option>
                                    <?php
                                    $sections = $conn->query("SELECT id, section_name FROM tbl_section ORDER BY id ASC");
                                    while($row = $sections->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['section_name']) . '">' . htmlspecialchars($row['section_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="line_name" class="form-label">Line Name</label>
                                <select class="form-control" id="line_name" name="line_name" required>
                                    <option value="">Select Line</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="model_name" class="form-label">Model Name</label>
                                <select class="form-control" id="model_name" name="model_id" required onchange="fetchReference()">
                                    <option value="">Select Model</option>
                                    <?php
                                    $models = $conn->query("SELECT id, model_name FROM tbl_model ORDER BY id ASC");
                                    while($row = $models->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['id']) . '" data-model-name="' . htmlspecialchars($row['model_name']) . '">' . htmlspecialchars($row['model_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                                <input type="hidden" id="model_name_hidden" name="model_name" value="">
                            </div>
                            
                        </div>
                        
                        <div class="row mb-3">
                        <div class="col-md-3">
                                <label for="tool_name" class="form-label">Machine | Equipment | Jig Tool Name</label>
                                <select class="form-control" id="tool_name" name="tool_name" required onchange="filterRejects()">
                                    <option value="">Select Machine</option>
                                    <?php
                                    $machine_processes = $conn->query("SELECT id, machine_process_name FROM tbl_machine_process ORDER BY machine_process_name ASC");
                                    while($row = $machine_processes->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['machine_process_name']) . '">' . htmlspecialchars($row['machine_process_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="machine_serial" class="form-label">Machine Serial Number</label>
                                <input type="text" class="form-control" id="machine_serial" name="machine_serial" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label for="date_occured" class="form-label">Date Occurred</label>
                                <input type="date" class="form-control" id="date_occured" name="date_occured" required>
                            </div>
                            <div class="col-md-2 me-5">
                                <label for="start_time" class="form-label">Time Occurred (From)</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-2">
                                <label for="date_ended" class="form-label">Date Ended</label>
                                <input type="date" class="form-control" id="date_ended" name="date_ended">
                            </div>
                            <div class="col-md-2 me-5">
                                <label for="end_time" class="form-label">Time Ended (To)</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                            <div class="col-md-2">
                                <label for="loss_time" class="form-label">Total Loss Time (Mins.)</label>
                                <input type="text" class="form-control" id="loss_time" name="loss_time" readonly>
                            </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="reject_name" class="form-label">Reject Encountered / Abnormality</label>
                                <select class="form-control" id="reject_name" name="reject_name_select" required onchange="toggleCustomInput('reject')">
                                    <option value="">Select Reject</option>
                                    <?php
                                    $rejects = $conn->query("SELECT reject_name FROM tbl_rejects ORDER BY reject_name ASC");
                                    while($row = $rejects->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['reject_name']) . '">' . htmlspecialchars($row['reject_name']) . '</option>';
                                    }
                                    ?>
                                    <option value="__CUSTOM__">Others (Custom Input)</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="reject_name_custom" name="reject_name_custom" placeholder="Enter custom reject" style="display:none;">
                                <input type="hidden" id="reject_name_final" name="reject_name">
                            </div>
                            <div class="col-md-4">
                                <label for="analysis_cause" class="form-label">Analysis / Cause</label>
                                <select class="form-control" id="analysis_cause" name="analysis_cause_select" required onchange="toggleCustomInput('analysis')">
                                    <option value="">Select Analysis/Cause</option>
                                    <?php
                                    $analysis_causes = $conn->query("SELECT analysis_cause_name FROM tbl_analysis_cause ORDER BY analysis_cause_name ASC");
                                    while($row = $analysis_causes->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['analysis_cause_name']) . '">' . htmlspecialchars($row['analysis_cause_name']) . '</option>';
                                    }
                                    ?>
                                    <option value="__CUSTOM__">Others (Custom Input)</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="analysis_cause_custom" name="analysis_cause_custom" placeholder="Enter custom analysis/cause" style="display:none;">
                                <input type="hidden" id="analysis_cause_final" name="analysis_cause">
                            </div>
                            <div class="col-md-4">
                                <label for="action_taken" class="form-label">Action Taken</label>
                                <select class="form-control" id="action_taken" name="action_taken_select" required onchange="toggleCustomInput('action')">
                                    <option value="">Select Action Taken</option>
                                    <?php
                                    $actions_taken = $conn->query("SELECT action_taken_name FROM tbl_action_taken ORDER BY action_taken_name ASC");
                                    while($row = $actions_taken->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['action_taken_name']) . '">' . htmlspecialchars($row['action_taken_name']) . '</option>';
                                    }
                                    ?>
                                    <option value="__CUSTOM__">Others (Custom Input)</option>
                                </select>
                                <input type="text" class="form-control mt-2" id="action_taken_custom" name="action_taken_custom" placeholder="Enter custom action taken" style="display:none;">
                                <input type="hidden" id="action_taken_final" name="action_taken">
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="attented_by" class="form-label">Attended By</label>
                                <select class="form-control" id="attented_by" name="attented_by" required>
                                    <option value="">Select Technician</option>
                                    <?php
                                    $techs = $conn->query("SELECT tech_name FROM tbl_technicians ORDER BY tech_name ASC");
                                    while($row = $techs->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['tech_name']) . '">' . htmlspecialchars($row['tech_name']) . '</option>';
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
                                    <input type="text" class="form-control" name="check_result_n_appearance" placeholder="N" value="30" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Appearance (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_appearance" placeholder="R" value="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Appearance (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_appearance" placeholder="P" value="0" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Dimension (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_dimension" placeholder="N" value="3" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dimension (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_dimension" placeholder="R" value="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Dimension (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_dimension" placeholder="P" value="0" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tensile (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_tensile" placeholder="N" value="3" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tensile (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_tensile" placeholder="R" value="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tensile (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_tensile" placeholder="P" value="0" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Electrical (N)</label>
                                    <input type="text" class="form-control" name="check_result_n_electrical" placeholder="N" value="30" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Electrical (R)</label>
                                    <input type="text" class="form-control" name="check_result_r_electrical" placeholder="R" value="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Electrical (P)</label>
                                    <input type="text" class="form-control" name="check_result_p_electrical" placeholder="P" value="0" required>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <!-- Banner Image Section -->
                        <div class="banner-container text-center">
                            <img src="images/dimensions_ref2.png" alt="Reference Banner" class="img-fluid banner-image">
                        </div>
                        <div class="mb-4">
                            <h5 class="mb-3">CHECKING METHOD</h5>
                            <div class="alert alert-warning mb-4" style="font-size:16px;">
                                <i class="fas fa-exclamation-circle"></i> Check &#9745; if <b>APPLICABLE</b>, or leave &#9744; unchecked if <b>NOT APPLICABLE</b>. Leave <b>BLANK</b> the input field if it does not apply.
                            </div>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="chk_appearance" checked onclick="toggleMethod('appearance')">
                                        <label class="form-check-label" for="chk_appearance">Appearance</label>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" name="checking_method_appearance" id="appearance_field" placeholder="Appearance" value="N/A" required>
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
                                    <label class="form-label">Reference: <span class="text-primary" id="dimension_ref">A = Please select a model, B = Please select a model, C = Please select a model</span></label>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_a_1" id="dimension_a1" placeholder="Dim A (1)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_b_1" id="dimension_b1" placeholder="Dim B (1)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_c_1" id="dimension_c1" placeholder="Dim C (1)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_a_2" id="dimension_a2" placeholder="Dim A (2)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_b_2" id="dimension_b2" placeholder="Dim B (2)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_c_2" id="dimension_c2" placeholder="Dim C (2)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_a_3" id="dimension_a3" placeholder="Dim A (3)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_b_3" id="dimension_b3" placeholder="Dim B (3)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_dim_c_3" id="dimension_c3" placeholder="Dim C (3)" value="N/A">
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
                                    <label class="form-label">Reference: <span class="text-primary" id="tensile_ref">Specs: Please select a model</span></label>
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_ten_1" id="tensile_1" placeholder="Tensile (1)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_ten_2" id="tensile_2" placeholder="Tensile (2)" value="N/A">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <input type="text" class="form-control" name="checking_method_ten_3" id="tensile_3" placeholder="Tensile (3)" value="N/A">
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
                                    <input type="text" class="form-control" name="checking_method_electrical" id="electrical_field" placeholder="Electrical" value="N/A" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status" aria-hidden="true"></span>
                            <span id="submitText">Submit</span>
                        </button>
                        <script>
                        // Only allow numbers and decimal point in checking result fields
                        function allowOnlyNumbersAndDot(e) {
                            let value = e.target.value;
                            // Remove any character that is not a digit or decimal point
                            value = value.replace(/[^\d.]/g, '');
                            // Prevent more than one decimal point
                            const parts = value.split('.');
                            if (parts.length > 2) {
                                value = parts[0] + '.' + parts.slice(1).join('');
                            }
                            if (e.target.value !== value) {
                                e.target.value = value;
                            }
                        }

                        // Attach to all checking result fields
                        [
                            'check_result_n_appearance', 'check_result_r_appearance', 'check_result_p_appearance',
                            'check_result_n_dimension', 'check_result_r_dimension', 'check_result_p_dimension',
                            'check_result_n_tensile', 'check_result_r_tensile', 'check_result_p_tensile',
                            'check_result_n_electrical', 'check_result_r_electrical', 'check_result_p_electrical'
                        ].forEach(function(name) {
                            var field = document.getElementsByName(name)[0];
                            if (field) {
                                field.addEventListener('input', allowOnlyNumbersAndDot);
                            }
                        });

                        // Attach to dimension and tensile fields in checking method section
                        [
                            'dimension_a1','dimension_a2','dimension_a3',
                            'dimension_b1','dimension_b2','dimension_b3',
                            'dimension_c1','dimension_c2','dimension_c3',
                            'tensile_1','tensile_2','tensile_3'
                        ].forEach(function(id) {
                            var field = document.getElementById(id);
                            if (field) {
                                field.addEventListener('input', allowOnlyNumbersAndDot);
                            }
                        });
                        // --- Dimension & Tensile Spec Validation ---
                        function parseSpec(text) {
                            // Example: 'A = 2.95~3.15 mm, B = 2.25~2.35 mm, C = 0.72~0.78 mm'
                            const spec = {};
                            const regex = /([ABC])\s*=\s*([\d.]+)~([\d.]+)\s*mm/g;
                            let match;
                            while ((match = regex.exec(text)) !== null) {
                                spec[match[1]] = { min: parseFloat(match[2]), max: parseFloat(match[3]) };
                            }
                            return spec;
                        }

                        function parseTensileSpec(text) {
                            // Example: 'Specs: ≥29.4N'
                            const regex = /≥\s*([\d.]+)N/;
                            const match = regex.exec(text);
                            if (match) {
                                return parseFloat(match[1]);
                            }
                            return null;
                        }

                        function validateDimensionFields() {
                            const refText = document.getElementById('dimension_ref').innerText;
                            const spec = parseSpec(refText);
                            let valid = true;
                            const fields = [
                                {id: 'dimension_a1', key: 'A'},
                                {id: 'dimension_a2', key: 'A'},
                                {id: 'dimension_a3', key: 'A'},
                                {id: 'dimension_b1', key: 'B'},
                                {id: 'dimension_b2', key: 'B'},
                                {id: 'dimension_b3', key: 'B'},
                                {id: 'dimension_c1', key: 'C'},
                                {id: 'dimension_c2', key: 'C'},
                                {id: 'dimension_c3', key: 'C'}
                            ];
                            fields.forEach(f => {
                                const field = document.getElementById(f.id);
                                field.classList.remove('is-invalid');
                                field.setCustomValidity('');
                                const val = parseFloat(field.value);
                                if (!isNaN(val) && spec[f.key]) {
                                    if (val < spec[f.key].min || val > spec[f.key].max) {
                                        field.classList.add('is-invalid');
                                        field.setCustomValidity('Out of specs');
                                        valid = false;
                                    }
                                }
                            });
                            return valid;
                        }

                        function validateTensileFields() {
                            const refText = document.getElementById('tensile_ref').innerText;
                            const minTensile = parseTensileSpec(refText);
                            let valid = true;
                            const ids = ['tensile_1','tensile_2','tensile_3'];
                            ids.forEach(id => {
                                const field = document.getElementById(id);
                                field.classList.remove('is-invalid');
                                field.setCustomValidity('');
                                const val = parseFloat(field.value);
                                if (!isNaN(val) && minTensile !== null) {
                                    if (val < minTensile) {
                                        field.classList.add('is-invalid');
                                        field.setCustomValidity('Out of specs');
                                        valid = false;
                                    }
                                }
                            });
                            return valid;
                        }

                        // Attach validation to dimension fields
                        [
                            'dimension_a1','dimension_a2','dimension_a3',
                            'dimension_b1','dimension_b2','dimension_b3',
                            'dimension_c1','dimension_c2','dimension_c3'
                        ].forEach(id => {
                            document.getElementById(id).addEventListener('input', function() {
                                validateDimensionFields();
                            });
                        });

                        // Attach validation to tensile fields
                        ['tensile_1','tensile_2','tensile_3'].forEach(id => {
                            document.getElementById(id).addEventListener('input', function() {
                                validateTensileFields();
                            });
                        });

                        // Prevent form submit if any dimension or tensile field is out of spec
                        document.getElementById('repairForm').addEventListener('submit', function(e) {
                            let validDim = validateDimensionFields();
                            let validTen = validateTensileFields();
                            if (!validDim || !validTen) {
                                let msg = '';
                                if (!validDim && !validTen) {
                                    msg = 'One or more dimension and tensile values are out of specs. Please correct them.';
                                } else if (!validDim) {
                                    msg = 'One or more dimension values are out of specs. Please correct them.';
                                } else {
                                    msg = 'One or more tensile values are out of specs. Please correct them.';
                                }
                                showAlert('danger', msg);
                                e.preventDefault();
                                return false;
                            }
                        }, true);
                        // Toggle custom input fields for reject, analysis, and action
                        function toggleCustomInput(type) {
                            if (type === 'reject') {
                                var select = document.getElementById('reject_name');
                                var customInput = document.getElementById('reject_name_custom');
                                var finalInput = document.getElementById('reject_name_final');
                                
                                if (select.value === '__CUSTOM__') {
                                    customInput.style.display = 'block';
                                    customInput.required = true;
                                    select.required = false;
                                    finalInput.value = '';
                                } else {
                                    customInput.style.display = 'none';
                                    customInput.required = false;
                                    customInput.value = '';
                                    select.required = true;
                                    finalInput.value = select.value;
                                }
                            } else if (type === 'analysis') {
                                var select = document.getElementById('analysis_cause');
                                var customInput = document.getElementById('analysis_cause_custom');
                                var finalInput = document.getElementById('analysis_cause_final');
                                
                                if (select.value === '__CUSTOM__') {
                                    customInput.style.display = 'block';
                                    customInput.required = true;
                                    select.required = false;
                                    finalInput.value = '';
                                } else {
                                    customInput.style.display = 'none';
                                    customInput.required = false;
                                    customInput.value = '';
                                    select.required = true;
                                    finalInput.value = select.value;
                                }
                            } else if (type === 'action') {
                                var select = document.getElementById('action_taken');
                                var customInput = document.getElementById('action_taken_custom');
                                var finalInput = document.getElementById('action_taken_final');
                                
                                if (select.value === '__CUSTOM__') {
                                    customInput.style.display = 'block';
                                    customInput.required = true;
                                    select.required = false;
                                    finalInput.value = '';
                                } else {
                                    customInput.style.display = 'none';
                                    customInput.required = false;
                                    customInput.value = '';
                                    select.required = true;
                                    finalInput.value = select.value;
                                }
                            }
                        }
                        
                        // Filter line names by selected section
                        function filterLines() {
                            var section = document.getElementById('section_name').value;
                            var lineSelect = document.getElementById('line_name');
                            lineSelect.innerHTML = '<option value="">Loading...</option>';
                            if(section === '') {
                                lineSelect.innerHTML = '<option value="">Select Line</option>';
                                return;
                            }
                            fetch('get_lines_by_section.php?section_name=' + encodeURIComponent(section))
                                .then(response => response.json())
                                .then(data => {
                                    let options = '<option value="">Select Line</option>';
                                    data.forEach(function(line) {
                                        options += '<option value="' + line.line_name + '">' + line.line_name + '</option>';
                                    });
                                    lineSelect.innerHTML = options;
                                })
                                .catch(() => {
                                    lineSelect.innerHTML = '<option value="">Select Line</option>';
                                });
                        }
                        
                        // Filter rejects, analysis, and actions by selected machine process
                        function filterRejects() {
                            var machineProcess = document.getElementById('tool_name').value;
                            var rejectSelect = document.getElementById('reject_name');
                            var analysisSelect = document.getElementById('analysis_cause');
                            var actionSelect = document.getElementById('action_taken');
                            
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
                                        rejectOptions += '<option value="' + reject.reject_name + '">' + reject.reject_name + '</option>';
                                    });
                                    rejectOptions += '<option value="__CUSTOM__">Others (Custom Input)</option>';
                                    rejectSelect.innerHTML = rejectOptions;
                                    
                                    // Populate analysis
                                    let analysisOptions = '<option value="">Select Analysis/Cause</option>';
                                    analysis.forEach(function(analysis) {
                                        analysisOptions += '<option value="' + analysis.analysis_cause_name + '">' + analysis.analysis_cause_name + '</option>';
                                    });
                                    analysisOptions += '<option value="__CUSTOM__">Others (Custom Input)</option>';
                                    analysisSelect.innerHTML = analysisOptions;
                                    
                                    // Populate actions
                                    let actionOptions = '<option value="">Select Action Taken</option>';
                                    actions.forEach(function(action) {
                                        actionOptions += '<option value="' + action.action_taken_name + '">' + action.action_taken_name + '</option>';
                                    });
                                    actionOptions += '<option value="__CUSTOM__">Others (Custom Input)</option>';
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
                                    rejectOptions += '<option value="' + reject.reject_name + '">' + reject.reject_name + '</option>';
                                });
                                rejectOptions += '<option value="__CUSTOM__">Others (Custom Input)</option>';
                                rejectSelect.innerHTML = rejectOptions;
                                
                                // Populate analysis
                                let analysisOptions = '<option value="">Select Analysis/Cause</option>';
                                analysis.forEach(function(analysis) {
                                    analysisOptions += '<option value="' + analysis.analysis_cause_name + '">' + analysis.analysis_cause_name + '</option>';
                                });
                                analysisOptions += '<option value="__CUSTOM__">Others (Custom Input)</option>';
                                analysisSelect.innerHTML = analysisOptions;
                                
                                // Populate actions
                                let actionOptions = '<option value="">Select Action Taken</option>';
                                actions.forEach(function(action) {
                                    actionOptions += '<option value="' + action.action_taken_name + '">' + action.action_taken_name + '</option>';
                                });
                                actionOptions += '<option value="__CUSTOM__">Others (Custom Input)</option>';
                                actionSelect.innerHTML = actionOptions;
                            })
                            .catch(() => {
                                rejectSelect.innerHTML = '<option value="">Select Reject</option>';
                                analysisSelect.innerHTML = '<option value="">Select Analysis/Cause</option>';
                                actionSelect.innerHTML = '<option value="">Select Action Taken</option>';
                            });
                        }
                        // Initialize line dropdown on page load if section is preselected
                        window.onload = function() {
                            toggleMethod('appearance');
                            toggleMethod('dimension');
                            toggleMethod('tensile');
                            toggleMethod('electrical');
                            filterLines();
                        }
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
                        // Fetches reference values for dimension and tensile from the database based on selected model
                        function fetchReference() {
                            var modelSelect = document.getElementById('model_name');
                            var modelId = modelSelect.value;
                            
                            // Update hidden model name field
                            var selectedOption = modelSelect.options[modelSelect.selectedIndex];
                            var modelName = selectedOption.getAttribute('data-model-name') || '';
                            document.getElementById('model_name_hidden').value = modelName;
                            
                            // Clear dimension and tensile input fields
                            [
                                'dimension_a1','dimension_a2','dimension_a3',
                                'dimension_b1','dimension_b2','dimension_b3',
                                'dimension_c1','dimension_c2','dimension_c3'
                            ].forEach(function(id) {
                                var field = document.getElementById(id);
                                if (field.disabled) {
                                    field.value = 'N/A';
                                } else {
                                    field.value = '';
                                }
                            });
                            ['tensile_1','tensile_2','tensile_3'].forEach(function(id) {
                                var field = document.getElementById(id);
                                if (field.disabled) {
                                    field.value = 'N/A';
                                } else {
                                    field.value = '';
                                }
                            });

                            if(modelId === '') {
                                document.getElementById('dimension_ref').innerText = 'Please select a model';
                                document.getElementById('tensile_ref').innerText = 'Please select a model';
                                return;
                            }
                            fetch('get_model_ref.php?id=' + encodeURIComponent(modelId))
                                .then(response => response.json())
                                .then(data => {
                                    // Update dimension reference display
                                    var dimensionText = 'A = ' + data.dimension_a + ' mm, B = ' + data.dimension_b + ' mm, C = ' + data.dimension_c + ' mm';
                                    document.getElementById('dimension_ref').innerText = dimensionText;
                                    
                                    // Update tensile reference display
                                    var tensileText = 'Specs: ≥' + data.tensile + 'N';
                                    document.getElementById('tensile_ref').innerText = tensileText;
                                    
                                    // Handle dimension fields based on N/A values
                                    var dimensionA = data.dimension_a;
                                    var dimensionB = data.dimension_b;
                                    var dimensionC = data.dimension_c;
                                    
                                    // Handle dimension A fields
                                    if (dimensionA === 'N/A' || dimensionA === null || dimensionA === '') {
                                        ['dimension_a1','dimension_a2','dimension_a3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            field.disabled = true;
                                            field.value = 'N/A';
                                        });
                                    } else {
                                        ['dimension_a1','dimension_a2','dimension_a3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            // Only enable if the checkbox is checked
                                            var checkbox = document.getElementById('chk_dimension');
                                            field.disabled = !checkbox.checked;
                                            if (!checkbox.checked) {
                                                field.value = 'N/A';
                                            } else {
                                                field.value = '';
                                            }
                                        });
                                    }
                                    
                                    // Handle dimension B fields
                                    if (dimensionB === 'N/A' || dimensionB === null || dimensionB === '') {
                                        ['dimension_b1','dimension_b2','dimension_b3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            field.disabled = true;
                                            field.value = 'N/A';
                                        });
                                    } else {
                                        ['dimension_b1','dimension_b2','dimension_b3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            // Only enable if the checkbox is checked
                                            var checkbox = document.getElementById('chk_dimension');
                                            field.disabled = !checkbox.checked;
                                            if (!checkbox.checked) {
                                                field.value = 'N/A';
                                            } else {
                                                field.value = '';
                                            }
                                        });
                                    }
                                    
                                    // Handle dimension C fields
                                    if (dimensionC === 'N/A' || dimensionC === null || dimensionC === '') {
                                        ['dimension_c1','dimension_c2','dimension_c3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            field.disabled = true;
                                            field.value = 'N/A';
                                        });
                                    } else {
                                        ['dimension_c1','dimension_c2','dimension_c3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            // Only enable if the checkbox is checked
                                            var checkbox = document.getElementById('chk_dimension');
                                            field.disabled = !checkbox.checked;
                                            if (!checkbox.checked) {
                                                field.value = 'N/A';
                                            } else {
                                                field.value = '';
                                            }
                                        });
                                    }
                                    
                                    // Handle tensile fields
                                    var tensileValue = data.tensile;
                                    if (tensileValue === 'N/A' || tensileValue === null || tensileValue === '') {
                                        ['tensile_1','tensile_2','tensile_3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            field.disabled = true;
                                            field.value = 'N/A';
                                        });
                                    } else {
                                        ['tensile_1','tensile_2','tensile_3'].forEach(function(id) {
                                            var field = document.getElementById(id);
                                            // Only enable if the checkbox is checked
                                            var checkbox = document.getElementById('chk_tensile');
                                            field.disabled = !checkbox.checked;
                                            if (!checkbox.checked) {
                                                field.value = 'N/A';
                                            } else {
                                                field.value = '';
                                            }
                                        });
                                    }
                                })
                                .catch(() => {
                                    document.getElementById('dimension_ref').innerText = 'Please select a model';
                                    document.getElementById('tensile_ref').innerText = 'Please select a model';
                                });
                        }
                        // Enables/disables and sets value for Checking Method fields based on checkbox state
                        function toggleMethod(method) {
                            if(method === 'appearance') {
                                var checked = document.getElementById('chk_appearance').checked;
                                var field = document.getElementById('appearance_field');
                                field.disabled = !checked;
                                field.value = checked ? '' : 'N/A';

                                // Also control the Checking Result Appearance fields
                                var resultFields = [
                                    document.getElementsByName('check_result_n_appearance')[0],
                                    document.getElementsByName('check_result_r_appearance')[0],
                                    document.getElementsByName('check_result_p_appearance')[0]
                                ];
                                resultFields.forEach(function(f) {
                                    f.disabled = !checked;
                                    if (checked) {
                                        f.value = f.getAttribute('value');
                                    } else {
                                        f.value = 'N/A';
                                    }
                                });
                            }
                            if(method === 'dimension') {
                                var checked = document.getElementById('chk_dimension').checked;
                                var ids = ['dimension_a1','dimension_b1','dimension_c1','dimension_a2','dimension_b2','dimension_c2','dimension_a3','dimension_b3','dimension_c3'];
                                ids.forEach(function(id){
                                    var field = document.getElementById(id);
                                    field.disabled = !checked;
                                    field.value = checked ? '' : 'N/A';
                                });
                                // Also control the Checking Result Dimension fields
                                var resultFields = [
                                    document.getElementsByName('check_result_n_dimension')[0],
                                    document.getElementsByName('check_result_r_dimension')[0],
                                    document.getElementsByName('check_result_p_dimension')[0]
                                ];
                                resultFields.forEach(function(f) {
                                    f.disabled = !checked;
                                    if (checked) {
                                        f.value = f.getAttribute('value');
                                    } else {
                                        f.value = 'N/A';
                                    }
                                });
                            }
                            if(method === 'tensile') {
                                var checked = document.getElementById('chk_tensile').checked;
                                var ids = ['tensile_1','tensile_2','tensile_3'];
                                ids.forEach(function(id){
                                    var field = document.getElementById(id);
                                    field.disabled = !checked;
                                    field.value = checked ? '' : 'N/A';
                                });
                                // Also control the Checking Result Tensile fields
                                var resultFields = [
                                    document.getElementsByName('check_result_n_tensile')[0],
                                    document.getElementsByName('check_result_r_tensile')[0],
                                    document.getElementsByName('check_result_p_tensile')[0]
                                ];
                                resultFields.forEach(function(f) {
                                    f.disabled = !checked;
                                    if (checked) {
                                        f.value = f.getAttribute('value');
                                    } else {
                                        f.value = 'N/A';
                                    }
                                });
                            }
                            if(method === 'electrical') {
                                var checked = document.getElementById('chk_electrical').checked;
                                var field = document.getElementById('electrical_field');
                                field.disabled = !checked;
                                field.value = checked ? '' : 'N/A';
                                // Also control the Checking Result Electrical fields
                                var resultFields = [
                                    document.getElementsByName('check_result_n_electrical')[0],
                                    document.getElementsByName('check_result_r_electrical')[0],
                                    document.getElementsByName('check_result_p_electrical')[0]
                                ];
                                resultFields.forEach(function(f) {
                                    f.disabled = !checked;
                                    if (checked) {
                                        f.value = f.getAttribute('value');
                                    } else {
                                        f.value = 'N/A';
                                    }
                                });
                            }
                            if(method === 'dimension') {
                                var checked = document.getElementById('chk_dimension').checked;
                                var ids = ['dimension_a1','dimension_b1','dimension_c1','dimension_a2','dimension_b2','dimension_c2','dimension_a3','dimension_b3','dimension_c3'];
                                ids.forEach(function(id){
                                    var field = document.getElementById(id);
                                    field.disabled = !checked;
                                    field.value = checked ? '' : 'N/A';
                                });
                            }
                            if(method === 'tensile') {
                                var checked = document.getElementById('chk_tensile').checked;
                                var ids = ['tensile_1','tensile_2','tensile_3'];
                                ids.forEach(function(id){
                                    var field = document.getElementById(id);
                                    field.disabled = !checked;
                                    field.value = checked ? '' : 'N/A';
                                });
                            }
                            if(method === 'electrical') {
                                var checked = document.getElementById('chk_electrical').checked;
                                var field = document.getElementById('electrical_field');
                                field.disabled = !checked;
                                field.value = checked ? '' : 'N/A';
                            }
                        }
                        // Initializes the state of Checking Method fields on page load
                        window.onload = function() {
                            toggleMethod('appearance');
                            toggleMethod('dimension');
                            toggleMethod('tensile');
                            toggleMethod('electrical');
                        }
                        
                        // Fetch API form submission
                        document.getElementById('repairForm').addEventListener('submit', async function(e) {
                            e.preventDefault();
                            
                            // Update final hidden fields with custom input values if "Others" is selected
                            var rejectSelect = document.getElementById('reject_name');
                            var rejectCustom = document.getElementById('reject_name_custom');
                            var rejectFinal = document.getElementById('reject_name_final');
                            if (rejectSelect.value === '__CUSTOM__') {
                                rejectFinal.value = rejectCustom.value;
                            } else {
                                rejectFinal.value = rejectSelect.value;
                            }
                            
                            var analysisSelect = document.getElementById('analysis_cause');
                            var analysisCustom = document.getElementById('analysis_cause_custom');
                            var analysisFinal = document.getElementById('analysis_cause_final');
                            if (analysisSelect.value === '__CUSTOM__') {
                                analysisFinal.value = analysisCustom.value;
                            } else {
                                analysisFinal.value = analysisSelect.value;
                            }
                            
                            var actionSelect = document.getElementById('action_taken');
                            var actionCustom = document.getElementById('action_taken_custom');
                            var actionFinal = document.getElementById('action_taken_final');
                            if (actionSelect.value === '__CUSTOM__') {
                                actionFinal.value = actionCustom.value;
                            } else {
                                actionFinal.value = actionSelect.value;
                            }
                            
                            const submitBtn = document.getElementById('submitBtn');
                            const submitSpinner = document.getElementById('submitSpinner');
                            const submitText = document.getElementById('submitText');
                            
                            // Show loading state
                            submitBtn.disabled = true;
                            submitSpinner.classList.remove('d-none');
                            submitText.textContent = 'Submitting...';
                            
                            try {
                                const formData = new FormData(this);
                                
                                const response = await fetch('submit_repair_record.php', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                                
                                const result = await response.json();
                                
                                if (result.success) {
                                    // Show success message
                                    showAlert('success', result.message);
                                    
                                    // Reset form
                                    this.reset();
                                    document.getElementById('loss_time').value = '';
                                    toggleMethod('appearance');
                                    toggleMethod('dimension');
                                    toggleMethod('tensile');
                                    toggleMethod('electrical');
                                    
                                    // Reset custom input fields
                                    document.getElementById('reject_name_custom').style.display = 'none';
                                    document.getElementById('reject_name_custom').required = false;
                                    document.getElementById('analysis_cause_custom').style.display = 'none';
                                    document.getElementById('analysis_cause_custom').required = false;
                                    document.getElementById('action_taken_custom').style.display = 'none';
                                    document.getElementById('action_taken_custom').required = false;
                                    
                                    // Update dimension and tensile references
                                    document.getElementById('dimension_ref').innerText = 'A = Please select a model, B = Please select a model, C = Please select a model';
                                    document.getElementById('tensile_ref').innerText = 'Please select a model';
                                    
                                } else {
                                    showAlert('danger', result.message);
                                }
                                
                            } catch (error) {
                                console.error('Error:', error);
                                showAlert('danger', 'An error occurred while submitting the form. Please try again.');
                            } finally {
                                // Reset button state
                                submitBtn.disabled = false;
                                submitSpinner.classList.add('d-none');
                                submitText.textContent = 'Submit';
                            }
                        });
                        
                        // Function to show alert messages
                        function showAlert(type, message) {
                            // Remove existing alerts
                            const existingAlerts = document.querySelectorAll('.alert');
                            existingAlerts.forEach(alert => alert.remove());
                            
                            // Create new alert
                            const alertDiv = document.createElement('div');
                            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
                            alertDiv.innerHTML = `
                                ${message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            `;
                            
                            // Insert alert before the form
                            const form = document.getElementById('repairForm');
                            form.parentNode.insertBefore(alertDiv, form);
                            
                            // Auto-scroll to alert on success
                            if (type === 'success') {
                                setTimeout(() => {
                                    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }, 100); // slight delay to ensure DOM update
                            }
                            
                            // Auto-hide success messages after 5 seconds
                            if (type === 'success') {
                                setTimeout(() => {
                                    alertDiv.remove();
                                }, 10000);
                            }
                        }
                        </script>
                    </form>
                </div>
            </div>
    </div>
    <?php //include 'footer.php'; ?>
</body>
</html>
