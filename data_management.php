<?php
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';
session_start();

// Check if user is authenticated for data management
if(!isset($_SESSION['data_management_auth']) || $_SESSION['data_management_auth'] !== true) {
	header('Location: data_management_login.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Data Management - Machine & Equipment Repair</title>
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<script src="bootstrap/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="fontawesome/css/all.min.css">
	<script src="sweetalert/sweetalert.all.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<link rel="stylesheet" type="text/css" href="css/data_management.css">
</head>
<body>
	<div class="container-fluid">
		<div class="main-card">
			<div class="card-header bg-gradient text-white p-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
				<div class="d-flex justify-content-between align-items-center">
					<h3 class="mb-0"><i class="fas fa-database me-2"></i>Data Management</h3>
					<div class="d-flex gap-2">
						<a href="data_management_logout.php" class="btn btn-danger">
							<i class="fas fa-sign-out-alt me-2"></i>Logout
						</a>
						<a href="index.php" class="btn btn-light">
							<i class="fas fa-arrow-left me-2"></i>Back to Dashboard
						</a>
					</div>
				</div>
			</div>
			<div class="card-body p-0">
				<!-- Navigation Tabs -->
				<ul class="nav nav-tabs pt-3" id="dataManagementTabs" role="tablist">
					<li class="nav-item" role="presentation">
						<button class="nav-link active" id="section-tab" data-bs-toggle="tab" data-bs-target="#section" type="button" role="tab">
							<i class="fas fa-building me-1"></i>Section
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="lines-tab" data-bs-toggle="tab" data-bs-target="#lines" type="button" role="tab">
							<i class="fas fa-stream me-1"></i>Lines
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="models-tab" data-bs-toggle="tab" data-bs-target="#models" type="button" role="tab">
							<i class="fas fa-cube me-1"></i>Models
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="machine-process-tab" data-bs-toggle="tab" data-bs-target="#machine-process" type="button" role="tab">
							<i class="fas fa-cogs me-1"></i>Machine Process
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="rejects-tab" data-bs-toggle="tab" data-bs-target="#rejects" type="button" role="tab">
							<i class="fas fa-times-circle me-1"></i>Rejects
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="analysis-tab" data-bs-toggle="tab" data-bs-target="#analysis" type="button" role="tab">
							<i class="fas fa-search me-1"></i>Analysis/Cause
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="action-tab" data-bs-toggle="tab" data-bs-target="#action" type="button" role="tab">
							<i class="fas fa-wrench me-1"></i>Action Taken
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="technicians-tab" data-bs-toggle="tab" data-bs-target="#technicians" type="button" role="tab">
							<i class="fas fa-user-cog me-1"></i>Technicians
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
							<i class="fas fa-users me-1"></i>Users
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="repair-records-tab" data-bs-toggle="tab" data-bs-target="#repair-records" type="button" role="tab">
							<i class="fas fa-clipboard-list me-1"></i>Repair Records
						</button>
					</li>
					<li class="nav-item" role="presentation">
						<button class="nav-link" id="record-logs-tab" data-bs-toggle="tab" data-bs-target="#record-logs" type="button" role="tab">
							<i class="fas fa-history me-1"></i>Record Logs
						</button>
					</li>
				</ul>

				<!-- Tab Content -->
				<div class="tab-content" id="dataManagementTabsContent">
					<!-- Include tab content from separate file -->
					<?php include 'data_management_tabs.php'; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Universal Modal for Add/Edit -->
	<div class="modal fade" id="dataModal" tabindex="-1">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="modalTitle">Add Data</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" id="modalBody">
					<!-- Form will be dynamically generated here -->
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="saveBtn" onclick="saveData()">Save</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Authentication Modal for Repair Records Edit/Delete -->
	<div class="modal fade" id="authModal" tabindex="-1">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header bg-warning">
					<h5 class="modal-title"><i class="fas fa-lock me-2"></i>Authentication Required</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<p class="text-muted mb-3">Please enter your credentials to proceed with this action.</p>
					<form id="authForm">
						<div class="mb-3">
							<label class="form-label">User ID *</label>
							<input type="text" class="form-control" id="auth_user_id" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Password *</label>
							<input type="password" class="form-control" id="auth_password" required>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-primary" id="authBtn" onclick="authenticateAndProceed()">
						<i class="fas fa-check me-2"></i>Authenticate
					</button>
				</div>
			</div>
		</div>
	</div>

	<script src="js/data_management.js"></script>
	<script src="js/repair_record_dropdowns.js"></script>
	<script src="js/record_logs.js"></script>
</body>
</html>
