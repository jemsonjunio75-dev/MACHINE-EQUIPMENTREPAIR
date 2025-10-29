// Load and display record logs
let allLogs = [];

function loadRecordLogs() {
	console.log('Loading record logs...');
	fetch('data_management_api.php?action=get_logs')
		.then(response => response.json())
		.then(data => {
			console.log('Logs response:', data);
			if(data.success) {
				allLogs = data.data;
				console.log('Number of logs:', allLogs.length);
				displayLogs(allLogs);
			} else {
				console.error('Failed to load logs:', data.message);
				document.getElementById('recordLogsTableBody').innerHTML = 
					'<tr><td colspan="9" class="text-center text-danger">Failed to load logs: ' + (data.message || 'Unknown error') + '</td></tr>';
			}
		})
		.catch(error => {
			console.error('Error loading logs:', error);
			document.getElementById('recordLogsTableBody').innerHTML = 
				'<tr><td colspan="9" class="text-center text-danger">Error loading logs: ' + error.message + '</td></tr>';
		});
}

function displayLogs(logs) {
	const tbody = document.getElementById('recordLogsTableBody');
	
	if(!logs || logs.length === 0) {
		tbody.innerHTML = '<tr><td colspan="9" class="text-center">No logs found</td></tr>';
		return;
	}
	
	tbody.innerHTML = '';
	logs.forEach(log => {
		const row = document.createElement('tr');
		
		// Action badge color
		const actionBadge = log.action_type === 'EDIT' 
			? '<span class="badge bg-warning text-dark">EDIT</span>' 
			: '<span class="badge bg-danger">DELETE</span>';
		
		// Format timestamp
		const timestamp = new Date(log.action_timestamp).toLocaleString();
		
		// Truncate changes summary if too long
		let changesSummary = log.changes_summary || '-';
		if(changesSummary.length > 100) {
			changesSummary = changesSummary.substring(0, 100) + '...';
		}
		
		row.innerHTML = `
			<td>${log.log_id}</td>
			<td>${log.record_id}</td>
			<td>${actionBadge}</td>
			<td>${log.action_by}</td>
			<td>${log.action_by_name || '-'}</td>
			<td>${timestamp}</td>
			<td class="small">${changesSummary}</td>
			<td>${log.ip_address || '-'}</td>
			<td>
				<button class="btn btn-sm btn-info" onclick="viewLogDetails(${log.log_id})">
					<i class="fas fa-eye"></i>
				</button>
			</td>
		`;
		
		tbody.appendChild(row);
	});
}

function filterLogs() {
	const actionFilter = document.getElementById('logActionFilter').value;
	const userSearch = document.getElementById('logSearchUser').value.toLowerCase();
	
	let filteredLogs = allLogs;
	
	// Filter by action type
	if(actionFilter) {
		filteredLogs = filteredLogs.filter(log => log.action_type === actionFilter);
	}
	
	// Filter by user
	if(userSearch) {
		filteredLogs = filteredLogs.filter(log => 
			log.action_by.toLowerCase().includes(userSearch) || 
			(log.action_by_name && log.action_by_name.toLowerCase().includes(userSearch))
		);
	}
	
	displayLogs(filteredLogs);
}

function viewLogDetails(logId) {
	const log = allLogs.find(l => l.log_id == logId);
	
	if(!log) {
		Swal.fire('Error', 'Log not found', 'error');
		return;
	}
	
	let html = '<div class="log-details">';
	
	// Basic info
	html += '<div class="mb-3">';
	html += `<strong>Log ID:</strong> ${log.log_id}<br>`;
	html += `<strong>Record ID:</strong> ${log.record_id}<br>`;
	html += `<strong>Action:</strong> <span class="badge ${log.action_type === 'EDIT' ? 'bg-warning text-dark' : 'bg-danger'}">${log.action_type}</span><br>`;
	html += `<strong>User:</strong> ${log.action_by_name} (${log.action_by})<br>`;
	html += `<strong>Timestamp:</strong> ${new Date(log.action_timestamp).toLocaleString()}<br>`;
	html += `<strong>IP Address:</strong> ${log.ip_address || 'N/A'}<br>`;
	html += '</div>';
	
	// Changes summary
	if(log.changes_summary) {
		html += '<div class="mb-3">';
		html += '<strong>Changes Summary:</strong><br>';
		html += `<div class="alert alert-info small">${log.changes_summary}</div>`;
		html += '</div>';
	}
	
	// Old values (for EDIT and DELETE)
	if(log.old_values) {
		try {
			const oldValues = JSON.parse(log.old_values);
			html += '<div class="mb-3">';
			html += '<strong>Old Values:</strong><br>';
			html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
			html += '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';
			for(const [key, value] of Object.entries(oldValues)) {
				if(key !== 'id') {
					html += `<tr><td>${key}</td><td>${value || '<em>empty</em>'}</td></tr>`;
				}
			}
			html += '</tbody></table></div>';
			html += '</div>';
		} catch(e) {
			console.error('Error parsing old values:', e);
		}
	}
	
	// New values (for EDIT only)
	if(log.action_type === 'EDIT' && log.new_values) {
		try {
			const newValues = JSON.parse(log.new_values);
			html += '<div class="mb-3">';
			html += '<strong>New Values:</strong><br>';
			html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
			html += '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>';
			for(const [key, value] of Object.entries(newValues)) {
				html += `<tr><td>${key}</td><td>${value || '<em>empty</em>'}</td></tr>`;
			}
			html += '</tbody></table></div>';
			html += '</div>';
		} catch(e) {
			console.error('Error parsing new values:', e);
		}
	}
	
	// User agent
	if(log.user_agent) {
		html += '<div class="mb-3">';
		html += '<strong>User Agent:</strong><br>';
		html += `<small class="text-muted">${log.user_agent}</small>`;
		html += '</div>';
	}
	
	html += '</div>';
	
	Swal.fire({
		title: 'Log Details',
		html: html,
		width: '900px',
		showCloseButton: true,
		showConfirmButton: false,
		customClass: {
			popup: 'text-start'
		}
	});
}
