<?php
// bulk_sv_approval.php
// Supervisor Bulk Approval Page
session_start();
include 'dbconnection.php';

// Only allow access for supervisor role (add your own logic)
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'supervisor') {
//     header('Location: index.php');
//     exit;
// }

// Fetch records eligible for supervisor approval (verified by tech and line leader, not yet approved)
$sql = "SELECT * FROM tbl_repair_records WHERE verified_tech_status = 'verified' AND verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status != 'approved') ORDER BY date_occured DESC";
$result = $conn->query($sql);
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor Bulk Approval | Machine & Equipment Repair Record</title>
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
    <style>
        body { font-size: 18px; background: #f4f4f4; }
        .table-container { max-height: 70vh; overflow-y: auto; }
        table th { font-size: 15px !important; }
        table td { font-size: 14px !important; }
    </style>
    <?php include 'header.php'; ?>
    <div class="container mt-4 mb-4">
        <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left me-1"></i>Back to Main Page</a>
        <h3 class="mb-3 text-primary"><i class="fas fa-user-shield me-2"></i>Supervisor Bulk Approval</h3>
        <form id="bulkApprovalForm" method="post" action="api.php?action=bulk_sv_approve">
            <div class="table-responsive table-container mb-3">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ID</th>
                            <th>Line Name</th>
                            <th>Model</th>
                            <th>Tool Name</th>
                            <th>Date Occurred</th>
                            <th>Reject</th>
                            <th>Tech Verified</th>
                            <th>Line Verified</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $rec): ?>
                        <tr>
                            <td><input type="checkbox" name="record_ids[]" value="<?= htmlspecialchars($rec['id']) ?>"></td>
                            <td><?= htmlspecialchars($rec['id']) ?></td>
                            <td><?= htmlspecialchars($rec['line_name']) ?></td>
                            <td><?= htmlspecialchars($rec['model_name']) ?></td>
                            <td><?= htmlspecialchars($rec['tool_name']) ?></td>
                            <td><?= htmlspecialchars($rec['date_occured']) ?></td>
                            <td><?= htmlspecialchars($rec['reject_name']) ?></td>
                            <td><span class="badge bg-warning text-dark">Verified</span></td>
                            <td><span class="badge bg-info">Verified</span></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-light btn-sm" onclick="viewRecord(<?= htmlspecialchars($rec['id']) ?>)" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($records)): ?>
                        <tr><td colspan="9" class="text-center text-muted">No records available for bulk approval.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mb-3">
                <label for="sv_userid" class="form-label">Supervisor User ID</label>
                <input type="text" class="form-control" id="sv_userid" name="sv_userid" required>
            </div>
            <div class="mb-3">
                <label for="sv_password" class="form-label">Supervisor Password</label>
                <input type="password" class="form-control" id="sv_password" name="sv_password" required>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-check-double me-1"></i>Approve Selected</button>
        </form>
        <div id="bulkApprovalAlert" class="mt-3"></div>
    </div>
    <!-- Modal for viewing record details -->
    <div class="modal fade" id="recordModal" tabindex="-1" aria-labelledby="recordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="recordModalLabel">Repair Record Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="recordDetails">
                    <!-- Record details will be loaded here -->
                </div>
            </div>
        </div>
    </div>
    <script>
        // Select/unselect all checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checked = this.checked;
            document.querySelectorAll('input[name="record_ids[]"]').forEach(cb => cb.checked = checked);
        });
        // Handle form submission via AJAX
        document.getElementById('bulkApprovalForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const alertDiv = document.getElementById('bulkApprovalAlert');
            alertDiv.innerHTML = '';
            // Show loading
            const btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Approving...';
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alertDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
                    setTimeout(() => { location.reload(); }, 1200);
                } else {
                    alertDiv.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
                }
            } catch (error) {
                alertDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-double me-1"></i>Approve Selected';
            }
        });

        // View record details in modal (reference from index.php)
        async function viewRecord(id) {
            try {
                document.getElementById('recordDetails').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
                const response = await fetch(`api.php?action=get_record&id=${id}`);
                const result = await response.json();
                if (result.success) {
                    displayRecordDetails(result.data);
                } else {
                    document.getElementById('recordDetails').innerHTML = '<div class="alert alert-danger">Error: ' + result.message + '</div>';
                }
            } catch (error) {
                document.getElementById('recordDetails').innerHTML = '<div class="alert alert-danger">Failed to load record details</div>';
            }
            var modal = new bootstrap.Modal(document.getElementById('recordModal'));
            modal.show();
        }

        function displayRecordDetails(record) {
            function na(val) {
                return (val === undefined || val === null || val === '') ? 'N/A' : val;
            }
            const details = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>ID:</strong></td><td>${na(record.id)}</td></tr>
                            <tr><td><strong>Line Name:</strong></td><td>${na(record.line_name)}</td></tr>
                            <tr><td><strong>Model:</strong></td><td>${na(record.model_name)}</td></tr>
                            <tr><td><strong>Machine/Equipment/Jig Tool:</strong></td><td>${na(record.tool_name)}</td></tr>
                            <tr><td><strong>Machine Serial No.:</strong></td><td>${na(record.machine_serial)}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Timing Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Date Occurred:</strong></td><td>${na(record.date_occured)}</td></tr>
                            <tr><td><strong>Start Time:</strong></td><td>${na(record.start_time)}</td></tr>
                            <tr><td><strong>Date Ended:</strong></td><td>${na(record.date_ended)}</td></tr>
                            <tr><td><strong>End Time:</strong></td><td>${na(record.end_time)}</td></tr>
                            <tr><td><strong>Loss Time:</strong></td><td>${na(record.loss_time)}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Issue Details</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Reject / Abnormality:</strong></td><td>${na(record.reject_name)}</td></tr>
                            <tr><td><strong>Analysis/Cause:</strong></td><td>${na(record.analysis_cause)}</td></tr>
                            <tr><td><strong>Action Taken:</strong></td><td>${na(record.action_taken)}</td></tr>
                            <tr><td><strong>Attended By:</strong></td><td>${na(record.attented_by)}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6><i class="fas fa-clipboard-check me-1 text-success"></i>Checking Result</h6>
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th></th>
                                    <th class="text-center text-white">N</th>
                                    <th class="text-center text-white">r</th>
                                    <th class="text-center text-white">p</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Appearance</strong></td>
                                    <td class="text-center">${na(record.check_result_n_appearance)}</td>
                                    <td class="text-center">${na(record.check_result_r_appearance)}</td>
                                    <td class="text-center">${na(record.check_result_p_appearance)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dimension</strong></td>
                                    <td class="text-center">${na(record.check_result_n_dimension)}</td>
                                    <td class="text-center">${na(record.check_result_r_dimension)}</td>
                                    <td class="text-center">${na(record.check_result_p_dimension)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tensile</strong></td>
                                    <td class="text-center">${na(record.check_result_n_tensile)}</td>
                                    <td class="text-center">${na(record.check_result_r_tensile)}</td>
                                    <td class="text-center">${na(record.check_result_p_tensile)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Electrical</strong></td>
                                    <td class="text-center">${na(record.check_result_n_electrical)}</td>
                                    <td class="text-center">${na(record.check_result_r_electrical)}</td>
                                    <td class="text-center">${na(record.check_result_p_electrical)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6><i class="fas fa-tools me-1 text-primary"></i>Checking Method</h6>
                        <table class="table table-sm table-bordered align-middle">
                            <tbody>
                                <tr>
                                    <td><strong>Appearance</strong></td>
                                    <td colspan="9">${na(record.checking_method_appearance)}</td>
                                </tr>
                                <tr>
                                    <td rowspan="3"><strong>Dimension</strong></td>
                                    <td>A1: ${na(record.checking_method_dim_a_1)}mm</td>
                                    <td>B1: ${na(record.checking_method_dim_b_1)}mm</td>
                                    <td>C1: ${na(record.checking_method_dim_c_1)}mm</td>
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td>A2: ${na(record.checking_method_dim_a_2)}mm</td>
                                    <td>B2: ${na(record.checking_method_dim_b_2)}mm</td>
                                    <td>C2: ${na(record.checking_method_dim_c_2)}mm</td>
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td>A3: ${na(record.checking_method_dim_a_3)}mm</td>
                                    <td>B3: ${na(record.checking_method_dim_b_3)}mm</td>
                                    <td>C3: ${na(record.checking_method_dim_c_3)}mm</td>
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td><strong>Tensile</strong></td>
                                    <td>1: &nbsp;≥${na(record.checking_method_ten_1)}N</td>
                                    <td>2: &nbsp;≥${na(record.checking_method_ten_2)}N</td>
                                    <td>3: &nbsp;≥${na(record.checking_method_ten_3)}N</td>
                                    <td colspan="6"></td>
                                </tr>
                                <tr>
                                    <td><strong>Electrical</strong></td>
                                    <td colspan="9">${na(record.checking_method_electrical)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Status</h6>
                        <div class="d-flex gap-2 mb-3">
                            ${record.verified_tech_status === 'verified' ? 
                                `<span class="badge bg-warning text-dark" title="Verified by ${record.verified_tech_by || 'Unknown'} on ${record.verified_tech_date ? new Date(record.verified_tech_date).toLocaleString() : 'Unknown date'}">Tech Verified</span>` : 
                                record.verified_tech_status === 'ng' ?
                                `<span class="badge bg-danger" title="NG by ${record.verified_tech_by || 'Unknown'} on ${record.verified_tech_date ? new Date(record.verified_tech_date).toLocaleString() : 'Unknown date'}">Tech NG</span>` :
                                '<span class="badge bg-secondary">Tech Pending</span>'
                            }
                            ${record.verified_lineleader_status === 'verified' ? 
                                `<span class="badge bg-info" title="Verified by ${record.verified_lineleader_by || 'Unknown'} on ${record.verified_lineleader_date ? new Date(record.verified_lineleader_date).toLocaleString() : 'Unknown date'}">Line Leader Verified</span>` : 
                                record.verified_lineleader_status === 'ng' ?
                                `<span class="badge bg-danger" title="NG by ${record.verified_lineleader_by || 'Unknown'} on ${record.verified_lineleader_date ? new Date(record.verified_lineleader_date).toLocaleString() : 'Unknown date'}">Line Leader NG</span>` :
                                '<span class="badge bg-secondary">Line Leader Pending</span>'
                            }
                            ${record.approved_sv_status === 'approved' ? 
                                `<span class="badge bg-success" title="Approved by ${record.approved_sv_by || 'Unknown'} on ${record.approved_sv_date ? new Date(record.approved_sv_date).toLocaleString() : 'Unknown date'}">Approved</span>` : 
                                record.approved_sv_status === 'ng' ?
                                `<span class="badge bg-danger" title="NG by ${record.approved_sv_by || 'Unknown'} on ${record.approved_sv_date ? new Date(record.approved_sv_date).toLocaleString() : 'Unknown date'}">Supervisor NG</span>` :
                                '<span class="badge bg-secondary">Approval Pending</span>'
                            }
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('recordDetails').innerHTML = details;
        }
    </script>
</body>
</html>
