let currentTable = 'section';
let currentAction = 'add';
let currentId = null;
let machineProcesses = [];
let sections = [];
let pendingAction = null;
let pendingActionType = null;
let pendingRecordId = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
	loadTableData('section');
	loadMachineProcesses();
	loadSections();

	// Add event listeners for tab changes
	document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
		tab.addEventListener('shown.bs.tab', function(event) {
			const targetId = event.target.getAttribute('data-bs-target').substring(1);
			if(targetId === 'record-logs') {
				loadRecordLogs();
			} else {
				loadTableData(targetId);
			}
		});
	});
});

// Load machine processes for dropdowns
function loadMachineProcesses() {
	fetch('data_management_api.php?action=get_machine_processes')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				machineProcesses = data.data;
			}
		});
}

// Load sections for dropdowns
function loadSections() {
	fetch('data_management_api.php?action=get_sections')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				sections = data.data;
			}
		});
}

// Convert table name to table body ID
function getTableBodyId(table) {
	const idMap = {
		'section': 'sectionTableBody',
		'lines': 'linesTableBody',
		'models': 'modelsTableBody',
		'machine-process': 'machineProcessTableBody',
		'rejects': 'rejectsTableBody',
		'analysis': 'analysisTableBody',
		'action': 'actionTableBody',
		'technicians': 'techniciansTableBody',
		'users': 'usersTableBody',
		'repair-records': 'repairRecordsTableBody'
	};
	return idMap[table] || table + 'TableBody';
}

// Load table data
function loadTableData(table) {
	currentTable = table;
	const tableBody = document.getElementById(getTableBodyId(table));
	
	// Check if table body exists
	if(!tableBody) {
		console.warn(`Table body not found for: ${table}`);
		return;
	}
	
	fetch(`data_management_api.php?action=get&table=${table}`)
		.then(response => response.json())
		.then(data => {
			// Double check tableBody still exists
			if(!tableBody) {
				console.warn(`Table body disappeared for: ${table}`);
				return;
			}
			
			if(data.success) {
				tableBody.innerHTML = '';
				if(data.data.length === 0) {
					const colspan = getTableColspan(table);
					tableBody.innerHTML = `<tr><td colspan="${colspan}" class="text-center text-muted">No data available</td></tr>`;
				} else {
					data.data.forEach(row => {
						tableBody.innerHTML += generateTableRow(table, row);
					});
				}
			} else {
				console.error('API Error:', data.message);
				Swal.fire('Error', data.message || 'Failed to load data', 'error');
			}
		})
		.catch(error => {
			console.error('Fetch Error:', error);
			Swal.fire('Error', 'Failed to load data: ' + error.message, 'error');
		});
}

// Get table colspan
function getTableColspan(table) {
	const colspans = {
		'section': 3,
		'lines': 4,
		'models': 7,
		'machine-process': 3,
		'rejects': 4,
		'analysis': 4,
		'action': 4,
		'technicians': 3,
		'users': 5,
		'repair-records': 13
	};
	return colspans[table] || 3;
}

// Generate table row based on table type
function generateTableRow(table, row) {
	let html = '<tr>';
	
	switch(table) {
		case 'section':
			html += `<td>${row.id}</td>
					<td>${row.section_name}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('section', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('section', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'lines':
			html += `<td>${row.id}</td>
					<td>${row.line_name}</td>
					<td>${row.section_name || '-'}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('lines', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('lines', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'models':
			html += `<td>${row.id}</td>
					<td>${row.model_name}</td>
					<td>${row.dimension_a || '-'}</td>
					<td>${row.dimension_b || '-'}</td>
					<td>${row.dimension_c || '-'}</td>
					<td>${row.tensile || '-'}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('models', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('models', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'machine-process':
			html += `<td>${row.id}</td>
					<td>${row.machine_process_name}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('machine-process', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('machine-process', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'rejects':
			html += `<td>${row.id}</td>
					<td>${row.reject_name}</td>
					<td>${row.machine_process_name || '-'}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('rejects', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('rejects', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'analysis':
			html += `<td>${row.id}</td>
					<td>${row.analysis_cause_name}</td>
					<td>${row.machine_process_name || '-'}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('analysis', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('analysis', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'action':
			html += `<td>${row.id}</td>
					<td>${row.action_taken_name}</td>
					<td>${row.machine_process_name || '-'}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('action', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('action', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'technicians':
			html += `<td>${row.id}</td>
					<td>${row.tech_name}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('technicians', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('technicians', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'users':
			html += `<td>${row.id}</td>
					<td>${row.user_id}</td>
					<td>${row.user_name}</td>
					<td>${row.user_role}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="openEditModal('users', ${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="deleteData('users', ${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
		case 'repair-records':
			html += `<td>${row.id}</td>
					<td>${row.section_name || '-'}</td>
					<td>${row.line_name || '-'}</td>
					<td>${row.model_name || '-'}</td>
					<td>${row.machine_serial || '-'}</td>
					<td>${row.date_occured || '-'}</td>
					<td>${row.date_ended || '-'}</td>
					<td>${row.loss_time || '-'}</td>
					<td>${row.reject_name || '-'}</td>
					<td>${row.analysis_cause || '-'}</td>
					<td>${row.action_taken || '-'}</td>
					<td>${row.attented_by || '-'}</td>
					<td class="table-actions">
						<button class="btn btn-sm btn-warning btn-action" onclick="requestAuthForEdit(${row.id})">
							<i class="fas fa-edit"></i> Edit
						</button>
						<button class="btn btn-sm btn-danger btn-action" onclick="requestAuthForDelete(${row.id})">
							<i class="fas fa-trash"></i> Delete
						</button>
					</td>`;
			break;
	}
	
	html += '</tr>';
	return html;
}

// Open Add Modal
function openAddModal(table) {
	currentTable = table;
	currentAction = 'add';
	currentId = null;
	
	document.getElementById('modalTitle').textContent = 'Add ' + getTableTitle(table);
	document.getElementById('modalBody').innerHTML = generateForm(table, null);
	
	const modal = new bootstrap.Modal(document.getElementById('dataModal'));
	modal.show();
}

// Open Edit Modal
function openEditModal(table, id) {
	currentTable = table;
	currentAction = 'edit';
	currentId = id;
	
	fetch(`data_management_api.php?action=get_single&table=${table}&id=${id}`)
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				document.getElementById('modalTitle').textContent = 'Edit ' + getTableTitle(table);
				document.getElementById('modalBody').innerHTML = generateForm(table, data.data);
				
				// Populate dropdowns for repair-records
				if(table === 'repair-records') {
					populateRepairRecordDropdowns(data.data);
				}
				
				const modal = new bootstrap.Modal(document.getElementById('dataModal'));
				modal.show();
			} else {
				Swal.fire('Error', data.message, 'error');
			}
		});
}

// Get table title
function getTableTitle(table) {
	const titles = {
		'section': 'Section',
		'lines': 'Line',
		'models': 'Model',
		'machine-process': 'Machine Process',
		'rejects': 'Reject',
		'analysis': 'Analysis/Cause',
		'action': 'Action Taken',
		'technicians': 'Technician',
		'users': 'User',
		'repair-records': 'Repair Record',
		'record-logs': 'Record Log'
	};
	return titles[table] || 'Data';
}

// Generate form based on table type
function generateForm(table, data) {
	let html = '<form id="dataForm">';
	
	switch(table) {
		case 'section':
			html += `
				<div class="mb-3">
					<label class="form-label">Section Name *</label>
					<input type="text" class="form-control" id="section_name" value="${data ? data.section_name : ''}" required>
				</div>`;
			break;
		case 'lines':
			html += `
				<div class="mb-3">
					<label class="form-label">Line Name *</label>
					<input type="text" class="form-control" id="line_name" value="${data ? data.line_name : ''}" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Section Name</label>
					<select class="form-select" id="section_name">
						<option value="">Select Section</option>
						${sections.map(s => `<option value="${s.section_name}" ${data && data.section_name === s.section_name ? 'selected' : ''}>${s.section_name}</option>`).join('')}
					</select>
				</div>`;
			break;
		case 'models':
			html += `
				<div class="mb-3">
					<label class="form-label">Model Name *</label>
					<input type="text" class="form-control" id="model_name" value="${data ? data.model_name : ''}" required>
				</div>
				<div class="row">
					<div class="col-md-4 mb-3">
						<label class="form-label">Dimension A</label>
						<input type="text" class="form-control" id="dimension_a" value="${data ? data.dimension_a || '' : ''}">
					</div>
					<div class="col-md-4 mb-3">
						<label class="form-label">Dimension B</label>
						<input type="text" class="form-control" id="dimension_b" value="${data ? data.dimension_b || '' : ''}">
					</div>
					<div class="col-md-4 mb-3">
						<label class="form-label">Dimension C</label>
						<input type="text" class="form-control" id="dimension_c" value="${data ? data.dimension_c || '' : ''}">
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label">Tensile</label>
					<input type="text" class="form-control" id="tensile" value="${data ? data.tensile || '' : ''}">
				</div>`;
			break;
		case 'machine-process':
			html += `
				<div class="mb-3">
					<label class="form-label">Machine Process Name *</label>
					<input type="text" class="form-control" id="machine_process_name" value="${data ? data.machine_process_name : ''}" required>
				</div>`;
			break;
		case 'rejects':
			html += `
				<div class="mb-3">
					<label class="form-label">Reject Name *</label>
					<input type="text" class="form-control" id="reject_name" value="${data ? data.reject_name : ''}" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Machine Process</label>
					<select class="form-select" id="machine_process_name">
						<option value="">Select Machine Process</option>
						${machineProcesses.map(mp => `<option value="${mp.machine_process_name}" ${data && data.machine_process_name === mp.machine_process_name ? 'selected' : ''}>${mp.machine_process_name}</option>`).join('')}
					</select>
				</div>`;
			break;
		case 'analysis':
			html += `
				<div class="mb-3">
					<label class="form-label">Analysis/Cause Name *</label>
					<input type="text" class="form-control" id="analysis_cause_name" value="${data ? data.analysis_cause_name : ''}" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Machine Process</label>
					<select class="form-select" id="machine_process_name">
						<option value="">Select Machine Process</option>
						${machineProcesses.map(mp => `<option value="${mp.machine_process_name}" ${data && data.machine_process_name === mp.machine_process_name ? 'selected' : ''}>${mp.machine_process_name}</option>`).join('')}
					</select>
				</div>`;
			break;
		case 'action':
			html += `
				<div class="mb-3">
					<label class="form-label">Action Taken Name *</label>
					<input type="text" class="form-control" id="action_taken_name" value="${data ? data.action_taken_name : ''}" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Machine Process</label>
					<select class="form-select" id="machine_process_name">
						<option value="">Select Machine Process</option>
						${machineProcesses.map(mp => `<option value="${mp.machine_process_name}" ${data && data.machine_process_name === mp.machine_process_name ? 'selected' : ''}>${mp.machine_process_name}</option>`).join('')}
					</select>
				</div>`;
			break;
		case 'technicians':
			html += `
				<div class="mb-3">
					<label class="form-label">Technician Name *</label>
					<input type="text" class="form-control" id="tech_name" value="${data ? data.tech_name : ''}" required>
				</div>`;
			break;
		case 'users':
			html += `
				<div class="mb-3">
					<label class="form-label">User ID *</label>
					<input type="text" class="form-control" id="user_id" value="${data ? data.user_id : ''}" required>
				</div>
				<div class="mb-3">
					<label class="form-label">User Name *</label>
					<input type="text" class="form-control" id="user_name" value="${data ? data.user_name : ''}" required>
				</div>
				<div class="mb-3">
					<label class="form-label">Password ${currentAction === 'add' ? '*' : '(leave blank to keep current)'}</label>
					<input type="password" class="form-control" id="user_password" ${currentAction === 'add' ? 'required' : ''}>
				</div>
				<div class="mb-3">
					<label class="form-label">User Role *</label>
					<select class="form-select" id="user_role" required>
						<option value="">Select Role</option>
						<option value="tech" ${data && data.user_role === 'tech' ? 'selected' : ''}>Technician</option>
						<option value="line" ${data && data.user_role === 'line' ? 'selected' : ''}>Line Leader</option>
						<option value="sv" ${data && data.user_role === 'sv' ? 'selected' : ''}>Supervisor</option>
					</select>
				</div>`;
			break;
		case 'repair-records':
			html += `
				<div style="max-height: 70vh; overflow-y: auto;">
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Section Name</label>
							<select class="form-control" id="section_name">
								<option value="">Select Section</option>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Line Name</label>
							<select class="form-control" id="line_name">
								<option value="">Select Line</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Model Name</label>
							<select class="form-control" id="model_name">
								<option value="">Select Model</option>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Tool Name</label>
							<select class="form-control" id="tool_name">
								<option value="">Select Machine</option>
							</select>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Machine Serial</label>
						<input type="text" class="form-control" id="machine_serial" value="${data ? data.machine_serial || '' : ''}">
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Date Occurred</label>
							<input type="date" class="form-control" id="date_occured" value="${data ? data.date_occured || '' : ''}">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">Start Time</label>
							<input type="time" class="form-control" id="start_time" value="${data ? data.start_time || '' : ''}">
						</div>
					</div>
					<div class="row">
						<div class="col-md-6 mb-3">
							<label class="form-label">Date Ended</label>
							<input type="date" class="form-control" id="date_ended" value="${data ? data.date_ended || '' : ''}">
						</div>
						<div class="col-md-6 mb-3">
							<label class="form-label">End Time</label>
							<input type="time" class="form-control" id="end_time" value="${data ? data.end_time || '' : ''}">
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label">Loss Time</label>
						<input type="text" class="form-control" id="loss_time" value="${data ? data.loss_time || '' : ''}">
					</div>
					<div class="mb-3">
						<label class="form-label">Reject Name</label>
						<select class="form-control" id="reject_name">
							<option value="">Select Reject</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Analysis/Cause</label>
						<select class="form-control" id="analysis_cause">
							<option value="">Select Analysis/Cause</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Action Taken</label>
						<select class="form-control" id="action_taken">
							<option value="">Select Action Taken</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Attended By</label>
						<select class="form-control" id="attented_by">
							<option value="">Select Technician</option>
						</select>
					</div>
					<hr>
					<h6 class="mb-3">CHECKING RESULT (Overall)</h6>
					<div class="row mb-2">
						<div class="col-md-4">
							<label class="form-label small">Appearance (N)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_n_appearance" value="${data ? data.check_result_n_appearance || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Appearance (R)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_r_appearance" value="${data ? data.check_result_r_appearance || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Appearance (P)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_p_appearance" value="${data ? data.check_result_p_appearance || '' : ''}">
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-md-4">
							<label class="form-label small">Dimension (N)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_n_dimension" value="${data ? data.check_result_n_dimension || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Dimension (R)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_r_dimension" value="${data ? data.check_result_r_dimension || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Dimension (P)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_p_dimension" value="${data ? data.check_result_p_dimension || '' : ''}">
						</div>
					</div>
					<div class="row mb-2">
						<div class="col-md-4">
							<label class="form-label small">Tensile (N)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_n_tensile" value="${data ? data.check_result_n_tensile || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Tensile (R)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_r_tensile" value="${data ? data.check_result_r_tensile || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Tensile (P)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_p_tensile" value="${data ? data.check_result_p_tensile || '' : ''}">
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-4">
							<label class="form-label small">Electrical (N)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_n_electrical" value="${data ? data.check_result_n_electrical || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Electrical (R)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_r_electrical" value="${data ? data.check_result_r_electrical || '' : ''}">
						</div>
						<div class="col-md-4">
							<label class="form-label small">Electrical (P)</label>
							<input type="text" class="form-control form-control-sm" id="check_result_p_electrical" value="${data ? data.check_result_p_electrical || '' : ''}">
						</div>
					</div>
					<hr>
					<h6 class="mb-3">CHECKING METHOD</h6>
					<div class="mb-3">
						<label class="form-label small">Appearance</label>
						<input type="text" class="form-control form-control-sm" id="checking_method_appearance" value="${data ? data.checking_method_appearance || '' : ''}">
					</div>
					<div class="mb-3">
						<label class="form-label small">Dimension</label>
						<div class="row">
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_a_1" placeholder="Dim A (1)" value="${data ? data.checking_method_dim_a_1 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_b_1" placeholder="Dim B (1)" value="${data ? data.checking_method_dim_b_1 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_c_1" placeholder="Dim C (1)" value="${data ? data.checking_method_dim_c_1 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_a_2" placeholder="Dim A (2)" value="${data ? data.checking_method_dim_a_2 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_b_2" placeholder="Dim B (2)" value="${data ? data.checking_method_dim_b_2 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_c_2" placeholder="Dim C (2)" value="${data ? data.checking_method_dim_c_2 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_a_3" placeholder="Dim A (3)" value="${data ? data.checking_method_dim_a_3 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_b_3" placeholder="Dim B (3)" value="${data ? data.checking_method_dim_b_3 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_dim_c_3" placeholder="Dim C (3)" value="${data ? data.checking_method_dim_c_3 || '' : ''}">
							</div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label small">Tensile Strength</label>
						<div class="row">
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_ten_1" placeholder="Tensile (1)" value="${data ? data.checking_method_ten_1 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_ten_2" placeholder="Tensile (2)" value="${data ? data.checking_method_ten_2 || '' : ''}">
							</div>
							<div class="col-md-4 mb-2">
								<input type="text" class="form-control form-control-sm" id="checking_method_ten_3" placeholder="Tensile (3)" value="${data ? data.checking_method_ten_3 || '' : ''}">
							</div>
						</div>
					</div>
					<div class="mb-3">
						<label class="form-label small">Electrical</label>
						<input type="text" class="form-control form-control-sm" id="checking_method_electrical" value="${data ? data.checking_method_electrical || '' : ''}">
					</div>
				</div>`;
			break;
	}
	
	html += '</form>';
	return html;
}

// Save data (Add or Edit)
function saveData() {
	const formData = new FormData();
	formData.append('action', currentAction);
	formData.append('table', currentTable);
	if(currentId) formData.append('id', currentId);
	
	// Collect form data based on table type
	const fields = getTableFields(currentTable);
	fields.forEach(field => {
		const element = document.getElementById(field);
		if(element) {
			formData.append(field, element.value);
		}
	});
	
	fetch('data_management_api.php', {
		method: 'POST',
		body: formData
	})
	.then(response => response.json())
	.then(data => {
		if(data.success) {
			Swal.fire('Success', data.message, 'success');
			bootstrap.Modal.getInstance(document.getElementById('dataModal')).hide();
			loadTableData(currentTable);
			if(currentTable === 'machine-process') loadMachineProcesses();
			if(currentTable === 'section') loadSections();
		} else {
			Swal.fire('Error', data.message, 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		Swal.fire('Error', 'Failed to save data', 'error');
	});
}

// Get table fields
function getTableFields(table) {
	const fields = {
		'section': ['section_name'],
		'lines': ['line_name', 'section_name'],
		'models': ['model_name', 'dimension_a', 'dimension_b', 'dimension_c', 'tensile'],
		'machine-process': ['machine_process_name'],
		'rejects': ['reject_name', 'machine_process_name'],
		'analysis': ['analysis_cause_name', 'machine_process_name'],
		'action': ['action_taken_name', 'machine_process_name'],
		'technicians': ['tech_name'],
		'users': ['user_id', 'user_name', 'user_password', 'user_role'],
		'repair-records': ['section_name', 'line_name', 'model_name', 'tool_name', 'machine_serial', 
						   'date_occured', 'start_time', 'date_ended', 'end_time', 'loss_time', 
						   'reject_name', 'analysis_cause', 'action_taken', 'attented_by',
						   'check_result_n_appearance', 'check_result_r_appearance', 'check_result_p_appearance',
						   'check_result_n_dimension', 'check_result_r_dimension', 'check_result_p_dimension',
						   'check_result_n_tensile', 'check_result_r_tensile', 'check_result_p_tensile',
						   'check_result_n_electrical', 'check_result_r_electrical', 'check_result_p_electrical',
						   'checking_method_appearance', 'checking_method_dim_a_1', 'checking_method_dim_a_2', 'checking_method_dim_a_3',
						   'checking_method_dim_b_1', 'checking_method_dim_b_2', 'checking_method_dim_b_3',
						   'checking_method_dim_c_1', 'checking_method_dim_c_2', 'checking_method_dim_c_3',
						   'checking_method_ten_1', 'checking_method_ten_2', 'checking_method_ten_3',
						   'checking_method_electrical']
	};
	return fields[table] || [];
}

// Delete data
function deleteData(table, id) {
	Swal.fire({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Yes, delete it!'
	}).then((result) => {
		if (result.isConfirmed) {
			const formData = new FormData();
			formData.append('action', 'delete');
			formData.append('table', table);
			formData.append('id', id);
			
			fetch('data_management_api.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if(data.success) {
					Swal.fire('Deleted!', data.message, 'success');
					loadTableData(table);
					if(table === 'machine-process') loadMachineProcesses();
					if(table === 'section') loadSections();
				} else {
					Swal.fire('Error', data.message, 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				Swal.fire('Error', 'Failed to delete data', 'error');
			});
		}
	});
}

// Authentication functions for repair records
function requestAuthForEdit(id) {
	pendingActionType = 'edit';
	pendingRecordId = id;
	document.getElementById('auth_user_id').value = '';
	document.getElementById('auth_password').value = '';
	const modal = new bootstrap.Modal(document.getElementById('authModal'));
	modal.show();
}

function requestAuthForDelete(id) {
	pendingActionType = 'delete';
	pendingRecordId = id;
	document.getElementById('auth_user_id').value = '';
	document.getElementById('auth_password').value = '';
	const modal = new bootstrap.Modal(document.getElementById('authModal'));
	modal.show();
}

function authenticateAndProceed() {
	const userId = document.getElementById('auth_user_id').value;
	const password = document.getElementById('auth_password').value;
	
	if(!userId || !password) {
		Swal.fire('Error', 'Please enter both User ID and Password', 'error');
		return;
	}
	
	const formData = new FormData();
	formData.append('action', 'authenticate');
	formData.append('user_id', userId);
	formData.append('password', password);
	
	fetch('data_management_api.php', {
		method: 'POST',
		body: formData
	})
	.then(response => response.json())
	.then(data => {
		if(data.success) {
			bootstrap.Modal.getInstance(document.getElementById('authModal')).hide();
			
			if(pendingActionType === 'edit') {
				openEditModal('repair-records', pendingRecordId);
			} else if(pendingActionType === 'delete') {
				deleteRepairRecord(pendingRecordId);
			}
			
			pendingActionType = null;
			pendingRecordId = null;
		} else {
			Swal.fire('Authentication Failed', data.message, 'error');
		}
	})
	.catch(error => {
		console.error('Error:', error);
		Swal.fire('Error', 'Authentication failed', 'error');
	});
}

function deleteRepairRecord(id) {
	Swal.fire({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#d33',
		cancelButtonColor: '#3085d6',
		confirmButtonText: 'Yes, delete it!'
	}).then((result) => {
		if (result.isConfirmed) {
			const formData = new FormData();
			formData.append('action', 'delete');
			formData.append('table', 'repair-records');
			formData.append('id', id);
			
			fetch('data_management_api.php', {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if(data.success) {
					Swal.fire('Deleted!', data.message, 'success');
					loadTableData('repair-records');
				} else {
					Swal.fire('Error', data.message, 'error');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				Swal.fire('Error', 'Failed to delete record', 'error');
			});
		}
	});
}
