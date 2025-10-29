// Populate repair record dropdowns with database data
function populateRepairRecordDropdowns(recordData) {
	// Fetch sections
	fetch('data_management_api.php?action=get_sections')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const sectionSelect = document.getElementById('section_name');
				sectionSelect.innerHTML = '<option value="">Select Section</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.section_name === item.section_name ? 'selected' : '';
					sectionSelect.innerHTML += `<option value="${item.section_name}" ${selected}>${item.section_name}</option>`;
				});
				
				// Load lines after section is set
				if(recordData && recordData.section_name) {
					loadLines(recordData.section_name, recordData.line_name);
				}
			}
		});
	
	// Fetch models
	fetch('data_management_api.php?action=get_models')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const modelSelect = document.getElementById('model_name');
				modelSelect.innerHTML = '<option value="">Select Model</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.model_name === item.model_name ? 'selected' : '';
					modelSelect.innerHTML += `<option value="${item.model_name}" ${selected}>${item.model_name}</option>`;
				});
			}
		});
	
	// Fetch machine processes (tool_name)
	fetch('data_management_api.php?action=get_machine_processes')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const toolSelect = document.getElementById('tool_name');
				toolSelect.innerHTML = '<option value="">Select Machine</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.tool_name === item.machine_process_name ? 'selected' : '';
					toolSelect.innerHTML += `<option value="${item.machine_process_name}" ${selected}>${item.machine_process_name}</option>`;
				});
			}
		});
	
	// Fetch rejects
	fetch('data_management_api.php?action=get_rejects')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const rejectSelect = document.getElementById('reject_name');
				rejectSelect.innerHTML = '<option value="">Select Reject</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.reject_name === item.reject_name ? 'selected' : '';
					rejectSelect.innerHTML += `<option value="${item.reject_name}" ${selected}>${item.reject_name}</option>`;
				});
			}
		});
	
	// Fetch analysis causes
	fetch('data_management_api.php?action=get_analysis')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const analysisSelect = document.getElementById('analysis_cause');
				analysisSelect.innerHTML = '<option value="">Select Analysis/Cause</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.analysis_cause === item.analysis_cause_name ? 'selected' : '';
					analysisSelect.innerHTML += `<option value="${item.analysis_cause_name}" ${selected}>${item.analysis_cause_name}</option>`;
				});
			}
		});
	
	// Fetch actions
	fetch('data_management_api.php?action=get_actions')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const actionSelect = document.getElementById('action_taken');
				actionSelect.innerHTML = '<option value="">Select Action Taken</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.action_taken === item.action_taken_name ? 'selected' : '';
					actionSelect.innerHTML += `<option value="${item.action_taken_name}" ${selected}>${item.action_taken_name}</option>`;
				});
			}
		});
	
	// Fetch technicians
	fetch('data_management_api.php?action=get_technicians')
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				const techSelect = document.getElementById('attented_by');
				techSelect.innerHTML = '<option value="">Select Technician</option>';
				data.data.forEach(item => {
					const selected = recordData && recordData.attented_by === item.tech_name ? 'selected' : '';
					techSelect.innerHTML += `<option value="${item.tech_name}" ${selected}>${item.tech_name}</option>`;
				});
			}
		});
	
	// Add event listener for section change to update lines
	document.getElementById('section_name').addEventListener('change', function() {
		loadLines(this.value, null);
	});
}

// Load lines based on selected section
function loadLines(sectionName, selectedLine) {
	const lineSelect = document.getElementById('line_name');
	lineSelect.innerHTML = '<option value="">Select Line</option>';
	
	if(!sectionName) return;
	
	fetch(`data_management_api.php?action=get_lines&section=${encodeURIComponent(sectionName)}`)
		.then(response => response.json())
		.then(data => {
			if(data.success) {
				data.data.forEach(item => {
					const selected = selectedLine && selectedLine === item.line_name ? 'selected' : '';
					lineSelect.innerHTML += `<option value="${item.line_name}" ${selected}>${item.line_name}</option>`;
				});
			}
		});
}
