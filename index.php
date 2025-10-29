<?php
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Machine & Equipment Repair Record</title>
    <script src="js/chart.min.js"></script>
    <script src="js/chartjs-plugin-datalabels.min.js"></script>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="fontawesome/css/all.min.css">
    <script src="sweetalert/sweetalert.all.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        /* Custom table improvements */
        .table-container {
            border-radius: 0.375rem;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Maximized table container */
        .table-container-maximized {
            border-radius: 0.375rem;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
            width: 100%;
        }
        
        .table-container::-webkit-scrollbar,
        .table-container-maximized::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        .table-container::-webkit-scrollbar-track,
        .table-container-maximized::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .table-container::-webkit-scrollbar-thumb,
        .table-container-maximized::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .table-container::-webkit-scrollbar-thumb:hover,
        .table-container-maximized::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        /* Sticky header improvements */
        .table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #212529 !important;
            border-bottom: 2px solid #dee2e6;
        }
        
        /* Table row improvements */
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Button group improvements */
        .btn-group-vertical .btn-group {
            margin-bottom: 2px;
        }
        
        /* Badge improvements */
        .badge {
            font-size: 0.75em;
            padding: 0.5em 0.75em;
        }
        
        /* Card improvements */
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .card-header {
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .table-container {
                max-height: 60vh;
            }
            
            .btn-group-vertical {
                flex-direction: row;
                flex-wrap: wrap;
            }
            
            .btn-group-vertical .btn-group {
                margin-right: 2px;
                margin-bottom: 2px;
            }
        }
        
        /* Fix for menu conflicts */
        .table-container {
            margin-top: 0;
            z-index: 1;
        }
        
        /* Ensure proper spacing from header */
        .container {
            margin-top: 2rem;
        }

        /* Compact table font size and padding */
        .table thead th, .table tbody td {
            font-size: 0.85rem;
            padding-top: 0.35rem;
            padding-bottom: 0.35rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        /* Action button improvements */
        .btn-table-action {
            padding: 0.4rem 0.5rem;
            font-size: 1.1rem;
            border-radius: 50%;
            margin-right: 0.25rem;
            margin-bottom: 0.15rem;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-table-action:last-child { margin-right: 0; }
        .btn-table-action .fa-eye { color: #0d6efd; }
        .btn-table-action .fa-user-cog { color:rgb(241, 206, 49); }
        .btn-table-action .fa-user-tie { color:rgb(20, 245, 253); }
        .btn-table-action .fa-user-shield, .text-purple { color:rgb(11, 138, 68); }
        .btn-table-action .fa-thumbs-up { color: #6f42c1; }
        .btn-table-action:hover, .btn-table-action:focus {
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .btn-table-action:hover .fa-eye { color: #084298; }
        .btn-table-action:hover .fa-user-cog { color: rgb(241, 206, 49); }
        .btn-table-action:hover .fa-user-tie { color: rgb(20, 245, 253); }
        .btn-table-action:hover .fa-user-shield, .btn-table-action:hover .fa-thumbs-up { color: rgb(11, 138, 68); }
        .btn-table-action[disabled] { opacity: 0.5; pointer-events: none; }
        .action-divider {
            display: inline-block;
            width: 1px;
            height: 28px;
            background: #e0e0e0;
            margin: 0 0.5rem;
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .table-actions-flex { flex-direction: column !important; align-items: flex-start !important; }
            .action-divider { display: none; }
            .btn-table-action { margin-bottom: 0.3rem; margin-right: 0; }
        }
        
        /* Auto-update styles */
        .btn-success .fa-sync-alt {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .auto-update-indicator {
            position: relative;
        }
        
        .auto-update-indicator::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .update-status {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        .update-status.active {
            color: #28a745;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!--HEADER-->
	<?php include 'header.php'; ?>
	<!-- Remove the navigation cards section -->
	<!-- (No code here; section removed) -->
	
	
	<!-- Full-Width Table Section -->
	<!-- Charts and Filter Section -->
	<div class="container mb-4" id="chartsContainer" style="display: none;">
	  <!-- Filter Controls -->
	  <div class="row mb-3">
	    <div class="col-12">
	      <div class="card shadow-sm">
	        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
	          <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Charts</h5>
	        </div>
	        <div class="card-body d-flex flex-wrap align-items-end gap-3">
	          <div>
	            <label for="filterSection" class="form-label mb-0">Section</label>
	            <select class="form-select" id="filterSection" style="min-width: 140px;">
	              <option value="">All</option>
	              <option value="CABLE">CABLE</option>
	              <option value="FEEDER">FEEDER</option>
	              <option value="USB">USB</option>
	              <option value="JLR">JLR</option>
	            </select>
	          </div>
	          <div>
	            <label for="filterDateFrom" class="form-label mb-0">From</label>
	            <input type="date" class="form-control" id="filterDateFrom" style="min-width: 140px;">
	          </div>
	          <div>
	            <label for="filterDateTo" class="form-label mb-0">To</label>
	            <input type="date" class="form-control" id="filterDateTo" style="min-width: 140px;">
	          </div>
	          <div>
	            <button class="btn btn-primary" id="filterChartsBtn"><i class="fas fa-filter me-1"></i>Filter</button>
	          </div>
	        </div>
	      </div>
	    </div>
	  </div>
	  
	  <!-- Charts -->
	  <div class="row">
	    <div class="col-12 mb-4">
	      <div class="card shadow-sm">
	        <div class="card-header bg-info text-white">
	          <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Loss Time by Line</h5>
	        </div>
	        <div class="card-body">
	          <canvas id="lossTimeByLineChart" height="80"></canvas>
	        </div>
	      </div>
	    </div>
	    <div class="col-12 mb-4">
	      <div class="card shadow-sm">
	        <div class="card-header bg-secondary text-white">
	          <h5 class="mb-0"><i class="fas fa-stopwatch me-2"></i>Chokotei</h5>
	        </div>
	        <div class="card-body">
	          <canvas id="chokoteiChart" height="80"></canvas>
	        </div>
	      </div>
	    </div>
	    <div class="col-12 mb-4">
	      <div class="card shadow-sm">
	        <div class="card-header bg-warning text-dark">
	          <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Top 10 Reject/Issue by Count & Loss Time</h5>
	        </div>
	        <div class="card-body">
	          <canvas id="topRejectsChart" height="80"></canvas>
	        </div>
	      </div>
	    </div>
	  </div>
	</div>
	<!-- Repair Records Table Section -->
	<div class="container-fluid px-0 mt-4">
		<div class="row g-0">
			<div class="col-12">
				<div class="card shadow-sm border-0 mx-3">
					<div class="card-header bg-gradient bg-primary text-white">
						<div class="d-flex justify-content-between align-items-center mb-3">
							<h4 class="mb-0"><i class="fas fa-list me-2"></i> Repair Records</h4>
							<div class="d-flex gap-2">
								<a href="fillup.php" class="btn btn-light btn-sm d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Fill Up Form" aria-label="Fill Up Form">
									<i class="fa-regular fa-clipboard"></i>
								</a>
								<button id="showChartsBtn" class="btn btn-light btn-sm d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Chart" type="button" style="margin-left: 4px;">
									<i class="fas fa-chart-bar me-1"></i>
								</button>
								<!--<button class="btn btn-light btn-sm" onclick="refreshTable()" title="Refresh">
									<i class="fas fa-sync-alt"></i>
								</button>-->
								<button class="btn btn-light btn-sm" onclick="exportTable()" data-bs-toggle="tooltip" data-bs-placement="top" title="Export">
									<i class="fas fa-download"></i>
								</button>
				
								<!--<button id="manualRefresh" class="btn btn-light btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Refresh Now" onclick="manualRefresh()">
									<i class="fas fa-refresh"></i>
								</button>-->
								<a href="bulk_sv_approval.php" class="btn btn-light btn-sm d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Supervisor Bulk Approval" aria-label="Supervisor Bulk Approval">
									<i class="fas fa-user-shield me-1"></i>
								</a>
								<a href="data_management_login.php" class="btn btn-light btn-sm d-flex align-items-center" data-bs-toggle="tooltip" data-bs-placement="top" title="Data Management" aria-label="Data Management">
									<i class="fas fa-database me-1"></i>
								</a>
							</div>
						</div>
						<!-- Search and Filter Controls -->
						<div class="row g-2">
							<div class="col-md-6">
								<div class="input-group">
									<span class="input-group-text"><i class="fas fa-search"></i></span>
									<input type="text" class="form-control" id="searchInput" placeholder="Search records..." autocomplete="off">
									<button class="btn btn-outline-light" type="button" onclick="clearSearch()" title="Clear Search">
										<i class="fas fa-times"></i>
									</button>
								</div>
							</div>
							<div class="col-md-3">
								<select class="form-select" id="statusFilter">
									<option value="">All Status</option>
									<option value="pending">Pending</option>
									<option value="ng">NG</option>
									<option value="tech">Tech Verified</option>
									<option value="line">Line Verified</option>
									<option value="approved">Approved</option>
								</select>
							</div>
							<div class="col-md-3">
								<select class="form-select" id="sortBy">
									<option value="priority">Priority (Pending First)</option>
									<option value="date_desc">Newest First</option>
									<option value="date_asc">Oldest First</option>
									<option value="id_desc">ID Descending</option>
									<option value="id_asc">ID Ascending</option>
								</select>
							</div>
						</div>
					</div>
					<div class="card-body p-0">
						<!-- Loading Spinner -->
						<div id="tableLoading" class="text-center py-5" style="display: none;">
							<div class="spinner-border text-primary" role="status">
								<span class="visually-hidden">Loading...</span>
							</div>
							<p class="mt-2 text-muted">Loading records...</p>
						</div>
						
						<!-- Table Container -->
						<div id="tableContainer" class="table-container-maximized" style="max-height: 75vh; overflow-y: auto; overflow-x: auto;">
							<table class="table table-hover mb-0" id="repairTable">
								<thead class="table-dark sticky-top">
									<tr>
										<th class="text-center" style="min-width: 60px;">ID</th>
										<th style="min-width: 100px;">Section</th>
										<th style="min-width: 120px;">Line Name</th>
										<th style="min-width: 100px;">Model</th>
										<th style="min-width: 150px;">Tool/Machine</th>
										<th style="min-width: 120px;">Serial</th>
										<th style="min-width: 120px;">Date Occurred</th>
										<th style="min-width: 120px;">Date Ended</th>
										<th style="min-width: 100px;">Time</th>
										<th class="text-center" style="min-width: 100px;">Loss Time</th>
										<th style="min-width: 150px;">Reject/Issue</th>
										<th style="min-width: 120px;">Attended By</th>
										<th class="text-center" style="min-width: 120px;">Status</th>
										<th class="text-center" style="min-width: 200px;">Actions</th>
									</tr>
								</thead>
								<tbody id="tableBody">
									<!-- Records will be loaded here via AJAX -->
								</tbody>
							</table>
						</div>
						
						<!-- Pagination Controls -->
						<div id="paginationContainer" class="card-footer bg-light">
							<div class="row align-items-center">
							<div class="col-md-6">
								<span id="recordCount" class="text-muted fs-6">
									<i class="fas fa-info-circle me-1"></i>
									Loading records...
								</span>
								<span id="autoUpdateStatus" class="update-status ms-3" style="display: none;">
									<i class="fas fa-clock me-1"></i>
									Auto-update: <span id="updateStatus">Off</span>
								</span>
							</div>
								<div class="col-md-6">
									<nav aria-label="Table pagination">
										<ul id="pagination" class="pagination pagination-sm justify-content-end mb-0">
											<!-- Pagination will be generated here -->
										</ul>
									</nav>
								</div>
							</div>
						</div>
						
						<!-- No Records Message -->
						<div id="noRecordsMessage" class="text-center py-5" style="display: none;">
							<i class="fas fa-inbox fa-3x text-muted mb-3"></i>
							<h5 class="text-muted">No repair records found</h5>
							<p class="text-muted">Submit your first repair record using the form above.</p>
							<a href="fillup.php" class="btn btn-primary">
								<i class="fas fa-plus me-2"></i>Create New Record
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
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

	<!-- Password Verification Modal -->
	<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="verifyModalLabel"><i class="fas fa-key me-2"></i>Verification Required</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="verifyForm">
						<input type="hidden" id="verify_record_id">
						<input type="hidden" id="verify_action_type">
						<div class="mb-3">
							<label for="verify_user_id" class="form-label">User ID</label>
							<input type="text" class="form-control" id="verify_user_id" required autocomplete="username">
						</div>
						<div class="mb-3">
							<label for="verify_user_password" class="form-label">Password</label>
							<input type="password" class="form-control" id="verify_user_password" required autocomplete="current-password">
						</div>
						<div class="mb-3">
							<label class="form-label">Verification Result</label>
							<div class="d-flex gap-3">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="verification_result" id="verify_ok" value="ok" required>
									<label class="form-check-label text-success fw-bold" for="verify_ok">
										<i class="fas fa-check-circle me-1"></i>OK
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="radio" name="verification_result" id="verify_ng" value="ng" required>
									<label class="form-check-label text-danger fw-bold" for="verify_ng">
										<i class="fas fa-times-circle me-1"></i>NG
									</label>
								</div>
							</div>
						</div>
						<div class="mb-3" id="ngRemarksGroup" style="display:none;">
							<label for="ng_remarks" class="form-label text-danger">Remarks</label>
							<textarea class="form-control" id="ng_remarks" rows="3" placeholder="Provide reason/details for NG"></textarea>
						</div>
					</form>
					<div id="verifyModalAlert"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="verifyConfirmBtn">Confirm</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Edit Record Modal -->
	<div class="modal fade" id="editRecordModal" tabindex="-1" aria-labelledby="editRecordModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editRecordModalLabel"><i class="fas fa-edit me-2"></i>Edit Record (NG)</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="editRecordForm">
						<input type="hidden" id="edit_record_id" name="id">
						<div class="row mb-3">
							<div class="col-md-3">
								<label for="edit_line_name" class="form-label">Line Name</label>
								<input type="text" class="form-control" id="edit_line_name" name="line_name" required>
							</div>
							<div class="col-md-3">
								<label for="edit_model_name" class="form-label">Model Name</label>
								<input type="text" class="form-control" id="edit_model_name" name="model_name" required>
							</div>
							<div class="col-md-3">
								<label for="edit_tool_name" class="form-label">Tool Name</label>
								<input type="text" class="form-control" id="edit_tool_name" name="tool_name" required>
							</div>
							<div class="col-md-3">
								<label for="edit_machine_serial" class="form-label">Machine Serial</label>
								<input type="text" class="form-control" id="edit_machine_serial" name="machine_serial" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-3">
								<label for="edit_date_occured" class="form-label">Date Occurred</label>
								<input type="date" class="form-control" id="edit_date_occured" name="date_occured" required>
							</div>
							<div class="col-md-3">
								<label for="edit_start_time" class="form-label">Start Time</label>
								<input type="time" class="form-control" id="edit_start_time" name="start_time" required>
							</div>
							<div class="col-md-3">
								<label for="edit_date_ended" class="form-label">Date Ended</label>
								<input type="date" class="form-control" id="edit_date_ended" name="date_ended">
							</div>
							<div class="col-md-3">
								<label for="edit_end_time" class="form-label">End Time</label>
								<input type="time" class="form-control" id="edit_end_time" name="end_time" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-3">
								<label for="edit_loss_time" class="form-label">Loss Time</label>
								<input type="text" class="form-control" id="edit_loss_time" name="loss_time" readonly>
							</div>
							<div class="col-md-3">
								<label for="edit_reject_name" class="form-label">Reject/Issue</label>
								<input type="text" class="form-control" id="edit_reject_name" name="reject_name" required>
							</div>
							<div class="col-md-3">
								<label for="edit_analysis_cause" class="form-label">Analysis/Cause</label>
								<input type="text" class="form-control" id="edit_analysis_cause" name="analysis_cause" required>
							</div>
							<div class="col-md-3">
								<label for="edit_action_taken" class="form-label">Action Taken</label>
								<input type="text" class="form-control" id="edit_action_taken" name="action_taken" required>
							</div>
						</div>
						<div class="row mb-3">
							<div class="col-md-3">
								<label for="edit_attented_by" class="form-label">Attended By</label>
								<input type="text" class="form-control" id="edit_attented_by" name="attented_by" required>
							</div>
						</div>
						<div id="editRecordAlert"></div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="editRecordSaveBtn">Save Changes</button>
				</div>
			</div>
		</div>
	</div>

	<script>
		// Fetch API functions for table actions
		async function viewRecord(id) {
			try {
				// Show loading state
				document.getElementById('recordDetails').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
				
				const response = await fetch(`api.php?action=get_record&id=${id}`);
				const result = await response.json();
				
				if (result.success) {
					displayRecordDetails(result.data);
				} else {
					document.getElementById('recordDetails').innerHTML = '<div class="alert alert-danger">Error: ' + result.message + '</div>';
				}
			} catch (error) {
				console.error('Error:', error);
				document.getElementById('recordDetails').innerHTML = '<div class="alert alert-danger">Failed to load record details</div>';
			}
			
			var modal = new bootstrap.Modal(document.getElementById('recordModal'));
			modal.show();
		}
		
		function displayRecordDetails(record) {
			// Helper to show N/A if value is empty/null/undefined
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
							<thead class="table-light">
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
						<div class="audit-trail">
							<h6 class="text-muted mb-2"><i class="fas fa-history me-1"></i>Verification/Approval History</h6>
							<div class="timeline">
								${record.verified_tech_status ? 
									`<div class="timeline-item mb-2">
										<i class="fas fa-tools ${record.verified_tech_status === 'ng' ? 'text-danger' : 'text-warning'} me-2"></i>
										<span class="text-dark fs-6">Tech Verification:</span> 
										<span class="text-dark fs-6 fw-bold">${record.verified_tech_by || 'Unknown'}</span>
										<span class="badge ${record.verified_tech_status === 'ng' ? 'bg-danger' : 'bg-warning text-dark'} ms-2">${record.verified_tech_status === 'ng' ? 'NG' : 'OK'}</span>
										${record.verified_tech_status === 'ng' && record.verified_tech_ng_remarks ? `<div class="text-danger small ms-4 mt-1">Reason: ${record.verified_tech_ng_remarks}</div>` : ''}
										<small class="text-muted ms-2 fs-6">${record.verified_tech_date ? new Date(record.verified_tech_date).toLocaleString() : 'Unknown date'}</small>
									</div>` : ''
								}
								${record.verified_lineleader_status ? 
									`<div class="timeline-item mb-2">
										<i class="fas fa-user-check ${record.verified_lineleader_status === 'ng' ? 'text-danger' : 'text-info'} me-2"></i>
										<span class="text-dark fs-6">Line Leader Verification:</span> 
										<span class="text-dark fs-6 fw-bold">${record.verified_lineleader_by || 'Unknown'}</span>
										<span class="badge ${record.verified_lineleader_status === 'ng' ? 'bg-danger' : 'bg-info'} ms-2">${record.verified_lineleader_status === 'ng' ? 'NG' : 'OK'}</span>
										${record.verified_lineleader_status === 'ng' && record.verified_lineleader_ng_remarks ? `<div class="text-danger small ms-4 mt-1">Reason: ${record.verified_lineleader_ng_remarks}</div>` : ''}
										<small class="text-muted ms-2 fs-6">${record.verified_lineleader_date ? new Date(record.verified_lineleader_date).toLocaleString() : 'Unknown date'}</small>
									</div>` : ''
								}
								${record.approved_sv_status ? 
									`<div class="timeline-item mb-2">
										<i class="fas fa-check-circle ${record.approved_sv_status === 'ng' ? 'text-danger' : 'text-success'} me-2"></i>
										<span class="text-dark fs-6">Supervisor Approval:</span> 
										<span class="text-dark fs-6 fw-bold">${record.approved_sv_by || 'Unknown'}</span>
										<span class="badge ${record.approved_sv_status === 'ng' ? 'bg-danger' : 'bg-success'} ms-2">${record.approved_sv_status === 'ng' ? 'NG' : 'OK'}</span>
										${record.approved_sv_status === 'ng' && record.approved_sv_ng_remarks ? `<div class="text-danger small ms-4 mt-1">Reason: ${record.approved_sv_ng_remarks}</div>` : ''}
										<small class="text-muted ms-2 fs-6">${record.approved_sv_date ? new Date(record.approved_sv_date).toLocaleString() : 'Unknown date'}</small>
									</div>` : ''
								}
							</div>
						</div>
					</div>
				</div>
			`;
			document.getElementById('recordDetails').innerHTML = details;
		}
		
		// Store the current verification context
		let currentVerifyContext = { recordId: null, actionType: null };

		function verifyRecord(id, type) {
			// Open modal and set context
			currentVerifyContext = { recordId: id, actionType: type };
			document.getElementById('verify_record_id').value = id;
			document.getElementById('verify_action_type').value = type;
			document.getElementById('verify_user_id').value = '';
			document.getElementById('verify_user_password').value = '';
			document.getElementById('ngRemarksGroup').style.display = 'none'; // Hide NG remarks group
			document.getElementById('ng_remarks').value = ''; // Clear remarks
			document.getElementById('verifyModalAlert').innerHTML = '';
			// Clear radio buttons
			document.querySelectorAll('input[name="verification_result"]').forEach(radio => radio.checked = false);
			var modal = new bootstrap.Modal(document.getElementById('verifyModal'));
			modal.show();
		}

		function approveRecord(id) {
			// Open modal and set context for supervisor approval
			currentVerifyContext = { recordId: id, actionType: 'sv' };
			document.getElementById('verify_record_id').value = id;
			document.getElementById('verify_action_type').value = 'sv';
			document.getElementById('verify_user_id').value = '';
			document.getElementById('verify_user_password').value = '';
			document.getElementById('ngRemarksGroup').style.display = 'none'; // Hide NG remarks group
			document.getElementById('ng_remarks').value = ''; // Clear remarks
			document.getElementById('verifyModalAlert').innerHTML = '';
			// Clear radio buttons
			document.querySelectorAll('input[name="verification_result"]').forEach(radio => radio.checked = false);
			var modal = new bootstrap.Modal(document.getElementById('verifyModal'));
			modal.show();
		}

		// Handle modal confirm
		const verifyConfirmBtn = document.getElementById('verifyConfirmBtn');
		verifyConfirmBtn.addEventListener('click', async function() {
			const recordId = document.getElementById('verify_record_id').value;
			const actionType = document.getElementById('verify_action_type').value;
			const userId = document.getElementById('verify_user_id').value.trim();
			const userPassword = document.getElementById('verify_user_password').value;
			const verificationResult = document.querySelector('input[name="verification_result"]:checked');
			const ngRemarks = (document.getElementById('ng_remarks')?.value || '').trim();
			
			if (!verificationResult) {
				document.getElementById('verifyModalAlert').innerHTML = '<div class="alert alert-warning">Please select OK or NG</div>';
				return;
			}

			if (verificationResult.value === 'ng' && !ngRemarks) {
				document.getElementById('verifyModalAlert').innerHTML = '<div class="alert alert-warning">NG remarks are required.</div>';
				return;
			}
			
			const alertDiv = document.getElementById('verifyModalAlert');
			alertDiv.innerHTML = '';
			verifyConfirmBtn.disabled = true;
			verifyConfirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Confirm';
			try {
				const formData = new FormData();
				formData.append('id', recordId);
				formData.append('action_type', actionType);
				formData.append('user_id', userId);
				formData.append('user_password', userPassword);
				formData.append('verification_result', verificationResult.value);
				if (verificationResult.value === 'ng') {
					formData.append('ng_remarks', ngRemarks);
				}
				const response = await fetch('api.php?action=verify_with_password', {
					method: 'POST',
					body: formData
				});
				const result = await response.json();
				if (result.success) {
					alertDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
					setTimeout(() => { location.reload(); }, 1000);
				} else {
					alertDiv.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
				}
			} catch (error) {
				alertDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
			} finally {
				verifyConfirmBtn.disabled = false;
				verifyConfirmBtn.innerHTML = 'Confirm';
			}
		});

		// Toggle NG remarks visibility
		document.querySelectorAll('input[name="verification_result"]').forEach(r => {
			r.addEventListener('change', function() {
				const group = document.getElementById('ngRemarksGroup');
				if (!group) return;
				group.style.display = (this.value === 'ng') ? 'block' : 'none';
				if (this.value !== 'ng') {
					document.getElementById('ng_remarks').value = '';
				}
			});
		});
		
		// Function to show alert messages
		function showAlert(type, message) {
			// Remove existing alerts
			const existingAlerts = document.querySelectorAll('.alert');
			existingAlerts.forEach(alert => alert.remove());
			
			// Create new alert
			const alertDiv = document.createElement('div');
			alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
			alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
			alertDiv.innerHTML = `
				${message}
				<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
			`;
			
			// Add alert to body
			document.body.appendChild(alertDiv);
			
			// Auto-hide after 5 seconds
			setTimeout(() => {
				if (alertDiv.parentNode) {
					alertDiv.remove();
				}
			}, 5000);
		}
		
		// Refresh table function
		function refreshTable() {
			const refreshBtn = event.target.closest('button');
			const originalContent = refreshBtn.innerHTML;
			
			// Show loading state
			refreshBtn.disabled = true;
			refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
			
			// Reload page after a short delay
			setTimeout(() => {
				location.reload();
			}, 1000);
		}
		
		// Export table function
		function exportTable() {
			window.location.href = 'reports.php';
		}
		
		// Global variables for table management
		let currentPage = 1;
		let totalPages = 1;
		let searchTimeout;
		
		// Auto-update variables
		let autoUpdateInterval = null;
		let autoUpdateEnabled = false;
		let autoUpdateIntervalMs = 2000; // 2 seconds default
		let lastUpdateTime = null;
		let isUpdating = false;
		
		// Initialize table enhancements
		// Show chartsContainer when button is clicked
		const showChartsBtn = document.getElementById('showChartsBtn');
		const chartsContainer = document.getElementById('chartsContainer');
		showChartsBtn.addEventListener('click', function() {
			if (chartsContainer.style.display === 'none' || chartsContainer.style.display === '') {
				chartsContainer.style.display = 'block';
				showChartsBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i>'; //Hide Charts & Filters
			} else {
				chartsContainer.style.display = 'none';
				showChartsBtn.innerHTML = '<i class="fas fa-chart-bar me-1"></i>'; //Show Charts & Filters
			}
		});

		document.addEventListener('DOMContentLoaded', function() {
			// Register Chart.js datalabels plugin
			Chart.register(ChartDataLabels);
			
			// Load initial data
			loadTableData();
			
			// Setup search functionality
			const searchInput = document.getElementById('searchInput');
			if (searchInput) {
				searchInput.addEventListener('input', function() {
					clearTimeout(searchTimeout);
					searchTimeout = setTimeout(() => {
						currentPage = 1;
						loadTableData();
					}, 300);
				});
			}
			
			// Setup filter functionality
			const statusFilter = document.getElementById('statusFilter');
			if (statusFilter) {
				statusFilter.addEventListener('change', function() {
					currentPage = 1;
					loadTableData();
				});
			}
			
			// Setup sort functionality
			const sortBy = document.getElementById('sortBy');
			if (sortBy) {
				sortBy.addEventListener('change', function() {
					currentPage = 1;
					loadTableData();
				});
			}

			// Enable Bootstrap tooltips
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
			tooltipTriggerList.forEach(function (tooltipTriggerEl) {
				new bootstrap.Tooltip(tooltipTriggerEl);
			});

			// --- CHARTS ---
			let chartFilters = { section: '', dateFrom: '', dateTo: '' };
			
			// Initial chart rendering
			renderLossTimeByLineChart();
			renderChokoteiChart();
			renderTopRejectsChart();

			document.getElementById('filterChartsBtn').addEventListener('click', function(e) {
				e.preventDefault();
				chartFilters.section = document.getElementById('filterSection').value;
				chartFilters.dateFrom = document.getElementById('filterDateFrom').value;
				chartFilters.dateTo = document.getElementById('filterDateTo').value;
				renderLossTimeByLineChart();
				renderChokoteiChart();
				renderTopRejectsChart();
			});

			// Fetch and render Loss Time by Line (Bar Chart)
			async function renderLossTimeByLineChart() {
				const canvas = document.getElementById('lossTimeByLineChart');
				if (!canvas) { console.error('Canvas not found: lossTimeByLineChart'); return; }
				const ctx = canvas.getContext('2d');
				try {
					let url = 'api.php?action=loss_time_by_line';
					const params = [];
					if (chartFilters.section) params.push('section_name=' + encodeURIComponent(chartFilters.section));
					if (chartFilters.dateFrom) params.push('date_from=' + encodeURIComponent(chartFilters.dateFrom));
					if (chartFilters.dateTo) params.push('date_to=' + encodeURIComponent(chartFilters.dateTo));
					if (params.length) url += '&' + params.join('&');
					const res = await fetch(url);
					const json = await res.json();
					console.log('Loss time data:', json);
					if (!json.success || !json.data.length) {
						ctx.clearRect(0, 0, canvas.width, canvas.height);
						ctx.font = '16px Arial';
						ctx.fillText('No data available', 10, 50);
						return;
					}
					const labels = json.data.map(item => item.line_name);
					const data = json.data.map(item => item.total_loss_time_minutes);
					if (lossTimeByLineChartInstance) lossTimeByLineChartInstance.destroy();
					lossTimeByLineChartInstance = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: labels,
							datasets: [{
								label: 'Total Loss Time (mins)',
								data: data,
								backgroundColor: 'rgba(54, 162, 235, 0.7)',
								borderColor: 'rgba(54, 162, 235, 1)',
								borderWidth: 1
							}]
						},
						options: {
							responsive: true,
							plugins: {
								legend: { display: false },
								title: { display: false },
								datalabels: {
									display: true,
									anchor: 'end',
									align: 'top',
									color: '#000',
									font: {
										weight: 'bold',
										size: 12
									},
									formatter: function(value) {
										return value + ' mins';
									}
								}
							},
							scales: {
								x: { title: { display: true, text: 'Line' } },
								y: { title: { display: true, text: 'Total Loss Time (mins)' }, beginAtZero: true }
							}
						}
					});
				} catch (e) {
					console.error('Chart error:', e);
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					ctx.font = '16px Arial';
					ctx.fillText('Failed to load chart', 10, 50);
				}
			}

			// Fetch and render Chokotei (Loss Time >= 5 mins) by Line
			async function renderChokoteiChart() {
				const canvas = document.getElementById('chokoteiChart');
				if (!canvas) { console.error('Canvas not found: chokoteiChart'); return; }
				const ctx = canvas.getContext('2d');
				try {
					let url = 'api.php?action=loss_time_by_line';
					const params = ['min_minutes=5'];
					if (chartFilters.section) params.push('section_name=' + encodeURIComponent(chartFilters.section));
					if (chartFilters.dateFrom) params.push('date_from=' + encodeURIComponent(chartFilters.dateFrom));
					if (chartFilters.dateTo) params.push('date_to=' + encodeURIComponent(chartFilters.dateTo));
					if (params.length) url += '&' + params.join('&');
					const res = await fetch(url);
					const json = await res.json();
					console.log('Chokotei data:', json);
					if (!json.success || !json.data.length) {
						ctx.clearRect(0, 0, canvas.width, canvas.height);
						ctx.font = '16px Arial';
						ctx.fillText('No data available', 10, 50);
						return;
					}
					const labels = json.data.map(item => item.line_name);
					const data = json.data.map(item => item.total_loss_time_minutes);
					if (chokoteiChartInstance) chokoteiChartInstance.destroy();
					chokoteiChartInstance = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: labels,
							datasets: [{
								label: 'Chokotei Loss Time: ',
								data: data,
								backgroundColor: 'rgba(102, 16, 242, 0.7)',
								borderColor: 'rgba(102, 16, 242, 1)',
								borderWidth: 1
							}]
						},
						options: {
							responsive: true,
							plugins: {
								legend: { display: false },
								title: { display: false },
								datalabels: {
									display: true,
									anchor: 'end',
									align: 'top',
									color: '#000',
									font: {
										weight: 'bold',
										size: 12
									},
									formatter: function(value) {
										return value + ' mins';
									}
								}
							},
							scales: {
								x: { title: { display: true, text: 'Line' } },
								y: { title: { display: true, text: 'Total Loss Time (mins)' }, beginAtZero: true }
							}
						}
					});
				} catch (e) {
					console.error('Chart error:', e);
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					ctx.font = '16px Arial';
					ctx.fillText('Failed to load chart', 10, 50);
				}
			}

			// Fetch and render Top 10 Reject/Issue (Horizontal Bar Chart with Count and Loss Time)
			async function renderTopRejectsChart() {
				const canvas = document.getElementById('topRejectsChart');
				if (!canvas) { console.error('Canvas not found: topRejectsChart'); return; }
				const ctx = canvas.getContext('2d');
				try {
					let url = 'api.php?action=top_rejects_by_line';
					const params = [];
					if (chartFilters.section) params.push('section_name=' + encodeURIComponent(chartFilters.section));
					if (chartFilters.dateFrom) params.push('date_from=' + encodeURIComponent(chartFilters.dateFrom));
					if (chartFilters.dateTo) params.push('date_to=' + encodeURIComponent(chartFilters.dateTo));
					if (params.length) url += '&' + params.join('&');
					const res = await fetch(url);
					const json = await res.json();
					console.log('Top rejects data:', json);
					if (!json.success || !json.data.length) {
						ctx.clearRect(0, 0, canvas.width, canvas.height);
						ctx.font = '16px Arial';
						ctx.fillText('No data available', 10, 50);
						return;
					}
					const labels = json.data.map(item => item.reject_name);
					const countData = json.data.map(item => item.count);
					const lossTimeData = json.data.map(item => item.total_loss_time_minutes);
					if (topRejectsChartInstance) topRejectsChartInstance.destroy();
					topRejectsChartInstance = new Chart(ctx, {
						type: 'bar',
						data: {
							labels: labels,
							datasets: [{
								label: 'Count',
								data: countData,
								backgroundColor: 'rgba(255, 193, 7, 0.7)',
								borderColor: 'rgba(255, 193, 7, 1)',
								borderWidth: 1,
								yAxisID: 'y'
							}, {
								label: 'Total Loss Time (mins)',
								data: lossTimeData,
								backgroundColor: 'rgba(220, 53, 69, 0.7)',
								borderColor: 'rgba(220, 53, 69, 1)',
								borderWidth: 1,
								yAxisID: 'y'
							}]
						},
						options: {
							indexAxis: 'y', // horizontal bar
							responsive: true,
							plugins: {
								legend: { 
									display: true,
									position: 'top',
									labels: {
										usePointStyle: true,
										padding: 20
									}
								},
								title: { display: false },
								datalabels: {
									display: true,
									anchor: 'end',
									align: 'right',
									color: '#000',
									font: {
										weight: 'bold',
										size: 10
									},
									formatter: function(value, context) {
										// Show different labels for different datasets
										if (context.datasetIndex === 0) {
											return value; // Count
										} else {
											return value + ' mins'; // Loss time in minutes
										}
									}
								}
							},
							scales: {
								x: { 
									title: { display: true, text: 'Value' }, 
									beginAtZero: true 
								},
								y: { 
									title: { display: true, text: 'Reject/Issue' } 
								}
							}
						}
					});
				} catch (e) {
					console.error('Chart error:', e);
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					ctx.font = '16px Arial';
					ctx.fillText('Failed to load chart', 10, 50);
				}
			}
		});

		// Chart.js chart instances (for future updates)
		let lossTimeByLineChartInstance = null;
		let chokoteiChartInstance = null;
		let topRejectsChartInstance = null;

		// Load table data via AJAX
		async function loadTableData(showLoadingIndicator = true) {
			const searchTerm = document.getElementById('searchInput')?.value || '';
			const statusFilter = document.getElementById('statusFilter')?.value || '';
			const sortBy = document.getElementById('sortBy')?.value || 'priority';
			
			if (showLoadingIndicator) {
				showLoading(true);
			}
			
			try {
				const params = new URLSearchParams({
					action: 'get_records',
					page: currentPage,
					search: searchTerm,
					status: statusFilter,
					sort: sortBy
				});
				
				const response = await fetch(`api.php?${params}`);
				const result = await response.json();
				
				if (result.success) {
					displayTableData(result.data);
					updatePagination(result.pagination);
					updateRecordCount(result.total);
					lastUpdateTime = new Date();
					updateLastUpdateTime();
				} else {
					showNoRecords();
				}
			} catch (error) {
				console.error('Error loading table data:', error);
				showNoRecords();
			} finally {
				if (showLoadingIndicator) {
					showLoading(false);
				}
				isUpdating = false;
			}
		}
		
		// Display table data
		function displayTableData(records) {
			const tbody = document.getElementById('tableBody');
			if (!tbody) return;
			
			if (records.length === 0) {
				showNoRecords();
				return;
			}
			
			let html = '';
			records.forEach(record => {
				const tech_status = record.verified_tech_status;
				const line_status = record.verified_lineleader_status;
				const sv_status = record.approved_sv_status;
				
				let statusBadge = '';
				if (sv_status === 'approved') {
					const approver = record.approved_sv_by || 'Unknown';
					const approveDate = record.approved_sv_date ? new Date(record.approved_sv_date).toLocaleDateString() : '';
					statusBadge = `<span class="badge bg-success fs-6" title="Approved by ${approver} on ${approveDate}"><i class="fas fa-check-circle me-1"></i>Approved</span>`;
				} else if (sv_status === 'ng') {
					const approver = record.approved_sv_by || 'Unknown';
					const approveDate = record.approved_sv_date ? new Date(record.approved_sv_date).toLocaleDateString() : '';
					const remarks = record.approved_sv_ng_remarks || '';
					statusBadge = `<span class="badge bg-danger fs-6" title="NG by ${approver} on ${approveDate}${remarks ? ` — ${remarks.replace(/'|"/g, "&#39;")}` : ''}"><i class="fas fa-times-circle me-1"></i>NG - SV</span>`;
				} else if (line_status === 'verified') {
					const verifier = record.verified_lineleader_by || 'Unknown';
					const verifyDate = record.verified_lineleader_date ? new Date(record.verified_lineleader_date).toLocaleDateString() : '';
					statusBadge = `<span class="badge bg-info fs-6" title="Verified by ${verifier} on ${verifyDate}"><i class="fas fa-user-check me-1"></i>Line Verified</span>`;
				} else if (line_status === 'ng') {
					const verifier = record.verified_lineleader_by || 'Unknown';
					const verifyDate = record.verified_lineleader_date ? new Date(record.verified_lineleader_date).toLocaleDateString() : '';
					const remarks = record.verified_lineleader_ng_remarks || '';
					statusBadge = `<span class="badge bg-danger fs-6" title="NG by ${verifier} on ${verifyDate}${remarks ? ` — ${remarks.replace(/'|"/g, "&#39;")}` : ''}"><i class="fas fa-times-circle me-1"></i>NG - Line</span>`;
				} else if (tech_status === 'verified') {
					const verifier = record.verified_tech_by || 'Unknown';
					const verifyDate = record.verified_tech_date ? new Date(record.verified_tech_date).toLocaleDateString() : '';
					statusBadge = `<span class="badge bg-warning text-dark fs-6" title="Verified by ${verifier} on ${verifyDate}"><i class="fas fa-tools me-1"></i>Tech Verified</span>`;
				} else if (tech_status === 'ng') {
					const verifier = record.verified_tech_by || 'Unknown';
					const verifyDate = record.verified_tech_date ? new Date(record.verified_tech_date).toLocaleDateString() : '';
					const remarks = record.verified_tech_ng_remarks || '';
					statusBadge = `<span class="badge bg-danger fs-6" title="NG by ${verifier} on ${verifyDate}${remarks ? ` — ${remarks.replace(/'|"/g, "&#39;")}` : ''}"><i class="fas fa-times-circle me-1"></i>NG - Tech</span>`;
				} else {
					statusBadge = '<span class="badge bg-secondary fs-6"><i class="fas fa-clock me-1"></i>Pending</span>';
				}
				
				html += `
					<tr class="align-middle">
						<td class="text-center fw-bold text-primary">${record.id}</td>
						<td><span class="text-dark">${record.section_name || ''}</span></td>
						<td><span class="text-dark">${record.line_name || ''}</span></td>
						<td>${record.model_name || ''}</td>
						<td>
							<div class="text-truncate" style="max-width: 150px;" title="${record.tool_name || ''}">
								${record.tool_name || ''}
							</div>
						</td>
						<td><span class="text-dark fs-10">${record.machine_serial || ''}</span></td>
						<td><span class="text-dark fs-10">${record.date_occured || ''}</span></td>
						<td><span class="text-dark fs-10">${record.date_ended || ''}</span></td>
						<td>
							<small class="text-muted">
								${record.start_time || ''}${record.end_time ? ' → ' + record.end_time : ''}
							</small>
						</td>
						<td class="text-center"><span class="text-dark fs-10">${record.loss_time || ''}</span></td>
						<td>
							<div class="text-truncate text-dark fw-medium" style="max-width: 150px;" 
								 title="${record.reject_name || ''} - ${record.analysis_cause || ''}">
								${(record.reject_name || '').substring(0, 25)}${(record.reject_name || '').length > 25 ? '...' : ''}
							</div>
						</td>
						<td><span class="text-dark fs-10">${record.attented_by || ''}</span></td>
						<td class="text-center">${statusBadge}</td>
						<td class="text-center">
							<div class="d-flex table-actions-flex align-items-center justify-content-center flex-wrap">
								<button type="button" class="btn btn-light btn-table-action" 
									onclick="viewRecord(${record.id})"
									data-bs-toggle="tooltip" data-bs-placement="top" title="View Details" aria-label="View Details">
									<i class="fas fa-eye"></i>
								</button>
								<span class="action-divider"></span>
								<button type="button" class="btn btn-light btn-table-action" 
									onclick="verifyRecord(${record.id}, 'tech')"
									data-bs-toggle="tooltip" data-bs-placement="top" title="Tech Verify" aria-label="Tech Verify" ${tech_status ? 'disabled' : ''}>
									<i class="fas fa-user-cog"></i>
								</button>
								<button type="button" class="btn btn-light btn-table-action" 
									onclick="verifyRecord(${record.id}, 'line')"
									data-bs-toggle="tooltip" data-bs-placement="top" title="Line Leader Verify" aria-label="Line Leader Verify" ${(line_status || tech_status !== 'verified') ? 'disabled' : ''}>
									<i class="fas fa-user-tie"></i>
								</button>
								<button type="button" class="btn btn-light btn-table-action" 
									onclick="approveRecord(${record.id})"
									data-bs-toggle="tooltip" data-bs-placement="top" title="Supervisor Approve" aria-label="Supervisor Approve" ${(sv_status || line_status !== 'verified') ? 'disabled' : ''}>
									<i class="fas fa-user-shield"></i>
								</button>
								${(tech_status === 'ng' || line_status === 'ng' || sv_status === 'ng') ? `
								<button type="button" class="btn btn-warning btn-table-action ms-1" 
									onclick="window.location.href='edit_repair.php?id=${record.id}'"
									data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Record (NG)" aria-label="Edit Record">
									<i class="fas fa-edit"></i>
								</button>` : ''}
							</div>
						</td>
					</tr>
				`;
			});
			
			tbody.innerHTML = html;
			document.getElementById('tableContainer').style.display = 'block';
			document.getElementById('noRecordsMessage').style.display = 'none';
			
			// Re-enable tooltips for new content
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
			tooltipTriggerList.forEach(function (tooltipTriggerEl) {
				new bootstrap.Tooltip(tooltipTriggerEl);
			});
		}
		
		// Update pagination
		function updatePagination(pagination) {
			const paginationContainer = document.getElementById('pagination');
			if (!paginationContainer) return;
			
			totalPages = pagination.total_pages;
			currentPage = pagination.current_page;
			
			let html = '';
			
			// Previous button
			html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
				<a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
			</li>`;
			
			// Page numbers
			const startPage = Math.max(1, currentPage - 2);
			const endPage = Math.min(totalPages, currentPage + 2);
			
			for (let i = startPage; i <= endPage; i++) {
				html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
					<a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
				</li>`;
			}
			
			// Next button
			html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
				<a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
			</li>`;
			
			paginationContainer.innerHTML = html;
		}
		
		// Change page
		function changePage(page) {
			if (page < 1 || page > totalPages) return;
			currentPage = page;
			loadTableData();
		}
		
		// Update record count
		function updateRecordCount(total) {
			const recordCount = document.getElementById('recordCount');
			if (recordCount) {
				recordCount.innerHTML = `<i class="fas fa-info-circle me-1"></i>Showing ${total} record(s)`;
			}
		}
		
		// Show loading state
		function showLoading(show) {
			const loading = document.getElementById('tableLoading');
			const container = document.getElementById('tableContainer');
			const pagination = document.getElementById('paginationContainer');
			
			if (loading) loading.style.display = show ? 'block' : 'none';
			if (container) container.style.display = show ? 'none' : 'block';
			if (pagination) pagination.style.display = show ? 'none' : 'block';
		}
		
		// Show no records message
		function showNoRecords() {
			document.getElementById('tableContainer').style.display = 'none';
			document.getElementById('paginationContainer').style.display = 'none';
			document.getElementById('noRecordsMessage').style.display = 'block';
		}
		
		// Clear search
		function clearSearch() {
			document.getElementById('searchInput').value = '';
			currentPage = 1;
			loadTableData();
		}

		async function editRecord(id) {
			// Fetch record data
			const response = await fetch(`api.php?action=get_record&id=${id}`);
			const result = await response.json();
			if (!result.success) {
				alert('Failed to load record for editing.');
				return;
			}
			const record = result.data;
			// Fill modal fields
			document.getElementById('edit_record_id').value = record.id;
			document.getElementById('edit_line_name').value = record.line_name || '';
			document.getElementById('edit_model_name').value = record.model_name || '';
			document.getElementById('edit_tool_name').value = record.tool_name || '';
			document.getElementById('edit_machine_serial').value = record.machine_serial || '';
			document.getElementById('edit_date_occured').value = record.date_occured || '';
			document.getElementById('edit_start_time').value = record.start_time || '';
			document.getElementById('edit_date_ended').value = record.date_ended || '';
			document.getElementById('edit_end_time').value = record.end_time || '';
			document.getElementById('edit_loss_time').value = record.loss_time || '';
			document.getElementById('edit_reject_name').value = record.reject_name || '';
			document.getElementById('edit_analysis_cause').value = record.analysis_cause || '';
			document.getElementById('edit_action_taken').value = record.action_taken || '';
			document.getElementById('edit_attented_by').value = record.attented_by || '';
			document.getElementById('editRecordAlert').innerHTML = '';
			var modal = new bootstrap.Modal(document.getElementById('editRecordModal'));
			modal.show();
		}

		// Compute loss time in modal
		['edit_date_occured','edit_start_time','edit_date_ended','edit_end_time'].forEach(id => {
			document.getElementById(id).addEventListener('change', function() {
				const dateOccured = document.getElementById('edit_date_occured').value;
				const startTime = document.getElementById('edit_start_time').value;
				const dateEnded = document.getElementById('edit_date_ended').value;
				const endTime = document.getElementById('edit_end_time').value;
				if(dateOccured && startTime && dateEnded && endTime) {
					const start = new Date(dateOccured + 'T' + startTime);
					const end = new Date(dateEnded + 'T' + endTime);
					const diffMs = end - start;
					if(diffMs > 0) {
						const diffMins = Math.floor(diffMs / 60000);
						document.getElementById('edit_loss_time').value = diffMins + ' mins';
					} else {
						document.getElementById('edit_loss_time').value = '00:00';
					}
				} else {
					document.getElementById('edit_loss_time').value = '';
				}
			});
		});

		document.getElementById('editRecordSaveBtn').addEventListener('click', async function() {
			const form = document.getElementById('editRecordForm');
			const formData = new FormData(form);
			formData.append('action', 'edit_record');
			// Show loading
			const alertDiv = document.getElementById('editRecordAlert');
			alertDiv.innerHTML = '';
			this.disabled = true;
			this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
			try {
				const response = await fetch('api.php', {
					method: 'POST',
					body: formData
				});
				const result = await response.json();
				if (result.success) {
					alertDiv.innerHTML = '<div class="alert alert-success">' + result.message + '</div>';
					setTimeout(() => {
						loadTableData();
						// Close the modal
						var modal = bootstrap.Modal.getInstance(document.getElementById('editRecordModal'));
						if (modal) modal.hide();
					}, 1000);
				} else {
					alertDiv.innerHTML = '<div class="alert alert-danger">' + result.message + '</div>';
				}
			} catch (error) {
				alertDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
			} finally {
				this.disabled = false;
				this.innerHTML = 'Save Changes';
			}
		});

		// Auto-update functionality
		function startAutoUpdate() {
			autoUpdateEnabled = true;
			const statusSpan = document.getElementById('updateStatus');
			const statusContainer = document.getElementById('autoUpdateStatus');
			
			// Update status display
			if (statusSpan) {
				statusSpan.textContent = 'On';
			}
			if (statusContainer) {
				statusContainer.style.display = 'inline';
				statusContainer.classList.add('active');
			}
			
			// Start the interval
			autoUpdateInterval = setInterval(() => {
				if (!isUpdating) {
					isUpdating = true;
					loadTableData(false); // Don't show loading indicator for auto-updates
				}
			}, autoUpdateIntervalMs);
		}

		function manualRefresh() {
			const refreshBtn = document.getElementById('manualRefresh');
			const originalContent = refreshBtn.innerHTML;
			
			// Show loading state
			refreshBtn.disabled = true;
			refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
			
			// Force refresh
			isUpdating = true;
			loadTableData(true).then(() => {
				refreshBtn.disabled = false;
				refreshBtn.innerHTML = originalContent;
				showAlert('success', 'Table refreshed successfully');
			}).catch(() => {
				refreshBtn.disabled = false;
				refreshBtn.innerHTML = originalContent;
				showAlert('danger', 'Failed to refresh table');
			});
		}

		function updateLastUpdateTime() {
			if (lastUpdateTime) {
				const timeString = lastUpdateTime.toLocaleTimeString();
				const statusSpan = document.getElementById('updateStatus');
				if (statusSpan) {
					statusSpan.textContent = `On (Last: ${timeString})`;
				}
			}
		}

		// Auto-update settings
		function setAutoUpdateInterval(seconds) {
			autoUpdateIntervalMs = seconds * 1000;
			if (autoUpdateEnabled) {
				clearInterval(autoUpdateInterval);
				startAutoUpdate();
			}
		}

		// Initialize auto-update on page load
		document.addEventListener('DOMContentLoaded', function() {
			// Auto-start auto-update on page load
			startAutoUpdate();
		});
	</script>

<!--FOOTER-->
<?php //include 'footer.php'; ?>
</body>
</html>