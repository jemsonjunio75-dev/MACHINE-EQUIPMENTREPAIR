<!-- SECTION TAB -->
<div class="tab-pane fade show active" id="section" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Section Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('section')">
			<i class="fas fa-plus me-2"></i>Add Section
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="sectionTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="70%">Section Name</th>
					<th width="20%">Actions</th>
				</tr>
			</thead>
			<tbody id="sectionTableBody">
				<tr><td colspan="3" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- LINES TAB -->
<div class="tab-pane fade" id="lines" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Lines Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('lines')">
			<i class="fas fa-plus me-2"></i>Add Line
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="linesTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="40%">Line Name</th>
					<th width="30%">Section Name</th>
					<th width="20%">Actions</th>
				</tr>
			</thead>
			<tbody id="linesTableBody">
				<tr><td colspan="4" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- MODELS TAB -->
<div class="tab-pane fade" id="models" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Models Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('models')">
			<i class="fas fa-plus me-2"></i>Add Model
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="modelsTable">
			<thead>
				<tr>
					<th width="8%">ID</th>
					<th width="25%">Model Name</th>
					<th width="12%">Dimension A</th>
					<th width="12%">Dimension B</th>
					<th width="12%">Dimension C</th>
					<th width="15%">Tensile</th>
					<th width="16%">Actions</th>
				</tr>
			</thead>
			<tbody id="modelsTableBody">
				<tr><td colspan="7" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- MACHINE PROCESS TAB -->
<div class="tab-pane fade" id="machine-process" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Machine Process Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('machine-process')">
			<i class="fas fa-plus me-2"></i>Add Machine Process
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="machineProcessTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="70%">Machine Process Name</th>
					<th width="20%">Actions</th>
				</tr>
			</thead>
			<tbody id="machineProcessTableBody">
				<tr><td colspan="3" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- REJECTS TAB -->
<div class="tab-pane fade" id="rejects" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Rejects Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('rejects')">
			<i class="fas fa-plus me-2"></i>Add Reject
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="rejectsTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="50%">Reject Name</th>
					<th width="25%">Machine Process</th>
					<th width="15%">Actions</th>
				</tr>
			</thead>
			<tbody id="rejectsTableBody">
				<tr><td colspan="4" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- ANALYSIS/CAUSE TAB -->
<div class="tab-pane fade" id="analysis" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Analysis/Cause Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('analysis')">
			<i class="fas fa-plus me-2"></i>Add Analysis/Cause
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="analysisTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="50%">Analysis/Cause Name</th>
					<th width="25%">Machine Process</th>
					<th width="15%">Actions</th>
				</tr>
			</thead>
			<tbody id="analysisTableBody">
				<tr><td colspan="4" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- ACTION TAKEN TAB -->
<div class="tab-pane fade" id="action" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Action Taken Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('action')">
			<i class="fas fa-plus me-2"></i>Add Action Taken
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="actionTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="50%">Action Taken Name</th>
					<th width="25%">Machine Process</th>
					<th width="15%">Actions</th>
				</tr>
			</thead>
			<tbody id="actionTableBody">
				<tr><td colspan="4" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- TECHNICIANS TAB -->
<div class="tab-pane fade" id="technicians" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Technicians Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('technicians')">
			<i class="fas fa-plus me-2"></i>Add Technician
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="techniciansTable">
			<thead>
				<tr>
					<th width="10%">ID</th>
					<th width="70%">Technician Name</th>
					<th width="20%">Actions</th>
				</tr>
			</thead>
			<tbody id="techniciansTableBody">
				<tr><td colspan="3" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- USERS TAB -->
<div class="tab-pane fade" id="users" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Users Management</h5>
		<button class="btn btn-primary" onclick="openAddModal('users')">
			<i class="fas fa-plus me-2"></i>Add User
		</button>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table" id="usersTable">
			<thead>
				<tr>
					<th width="8%">ID</th>
					<th width="20%">User ID</th>
					<th width="25%">User Name</th>
					<th width="20%">User Role</th>
					<th width="12%">Actions</th>
				</tr>
			</thead>
			<tbody id="usersTableBody">
				<tr><td colspan="5" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- REPAIR RECORDS TAB -->
<div class="tab-pane fade" id="repair-records" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Repair Records Management</h5>
		<div class="text-muted small">
			<i class="fas fa-info-circle me-1"></i>Authentication required for Edit/Delete operations
		</div>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table table-sm" id="repairRecordsTable">
			<thead>
				<tr>
					<th width="3%">ID</th>
					<th width="7%">Section</th>
					<th width="7%">Line</th>
					<th width="8%">Model</th>
					<th width="8%">Machine Serial</th>
					<th width="7%">Date Occurred</th>
					<th width="7%">Date Ended</th>
					<th width="6%">Loss Time</th>
					<th width="8%">Reject</th>
					<th width="10%">Analysis/Cause</th>
					<th width="10%">Action Taken</th>
					<th width="8%">Attended By</th>
					<th width="11%">Actions</th>
				</tr>
			</thead>
			<tbody id="repairRecordsTableBody">
				<tr><td colspan="13" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>

<!-- RECORD LOGS TAB -->
<div class="tab-pane fade" id="record-logs" role="tabpanel">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<h5 class="mb-0">Repair Records Audit Log</h5>
		<div class="d-flex gap-2">
			<select class="form-select form-select-sm" id="logActionFilter" style="width: 150px;" onchange="filterLogs()">
				<option value="">All Actions</option>
				<option value="EDIT">Edit Only</option>
				<option value="DELETE">Delete Only</option>
			</select>
			<input type="text" class="form-control form-control-sm" id="logSearchUser" placeholder="Search by user..." style="width: 200px;" onkeyup="filterLogs()">
		</div>
	</div>
	<div class="table-responsive">
		<table class="table table-hover data-table table-sm" id="recordLogsTable">
			<thead>
				<tr>
					<th width="5%">Log ID</th>
					<th width="6%">Record ID</th>
					<th width="7%">Action</th>
					<th width="10%">User ID</th>
					<th width="12%">User Name</th>
					<th width="12%">Timestamp</th>
					<th width="35%">Changes Summary</th>
					<th width="8%">IP Address</th>
					<th width="5%">Details</th>
				</tr>
			</thead>
			<tbody id="recordLogsTableBody">
				<tr><td colspan="9" class="text-center">Loading...</td></tr>
			</tbody>
		</table>
	</div>
</div>
