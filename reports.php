<?php
date_default_timezone_set('Asia/Manila');
include 'dbconnection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repair Records - Reports & Analytics</title>
                    <!-- Avg Loss Time stat card moved to summaryStats row -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" type="text/css" href="fontawesome/css/all.min.css">
    <script src="sweetalert/sweetalert.all.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <style>
        .report-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .stat-card, .stat-card-success, .stat-card-warning, .stat-card-info {
            background: #6c757d !important; /* dark gray */
            color: #fff !important;
        }
        .stat-card .card-body h3,
        .stat-card-success .card-body h3,
        .stat-card-warning .card-body h3,
        .stat-card-info .card-body h3 {
            font-size: 1.5rem !important;
        }
        .stat-card .card-body p,
        .stat-card-success .card-body p,
        .stat-card-warning .card-body p,
        .stat-card-info .card-body p {
            font-size: 0.9rem !important;
        }
        .stat-card .card-body,
        .stat-card-success .card-body,
        .stat-card-warning .card-body,
        .stat-card-info .card-body {
            font-size: 0.9rem !important;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin: 20px 0;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .export-buttons {
            gap: 10px;
        }
        .table-analytics {
            font-size: 0.9rem;
        }
        .loss-time-cell {
            font-weight: bold;
            color: #dc3545;
        }
        .summary-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!--HEADER-->
    <?php include 'header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Page Header -->
                <div class="card report-card mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h4>
                            <a href="index.php" class="btn btn-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Records
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card report-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Report Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="reportFilters" class="row g-3">
                            <div class="col-md-3">
                                <label for="dateFrom" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="dateFrom" name="dateFrom" 
                                       value="<?php echo date('Y-m-01'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="dateTo" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="dateTo" name="dateTo" 
                                       value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="timePeriod" class="form-label">Time Period</label>
                                <select class="form-select" id="timePeriod" name="timePeriod">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="rejectFilter" class="form-label">Reject/Issue Filter</label>
                                <select class="form-select" id="rejectFilter" name="rejectFilter">
                                    <option value="">All Issues</option>
                                    <?php
                                    $rejects = $conn->query("SELECT reject_name FROM tbl_rejects ORDER BY reject_name ASC");
                                    while($row = $rejects->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['reject_name']) . '">' . htmlspecialchars($row['reject_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="sectionFilter" class="form-label">Section Filter</label>
                                <select class="form-select" id="sectionFilter" name="sectionFilter">
                                    <option value="">All Sections</option>
                                    <?php
                                    $sections = $conn->query("SELECT id, section_name FROM tbl_section ORDER BY section_name ASC");
                                    while($row = $sections->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($row['section_name']) . '">' . htmlspecialchars($row['section_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="lineFilter" class="form-label">Line Filter</label>
                                <select class="form-select" id="lineFilter" name="lineFilter">
                                    <option value="">All Lines</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select class="form-select" id="statusFilter" name="statusFilter">
                                    <option value="">All status</option>
                                    <option value="pending">Pending</option>
                                    <option value="ng">NG</option>
                                    <option value="tech_verified">Tech verified</option>
                                    <option value="line_verified">Line verified</option>
                                    <option value="approved">Approved</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Generate Report
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Summary Statistics -->
                <div class="row mb-4" id="summaryStats">
                    <div class="col-md-3">
                        <div class="card stat-card text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <h3 id="totalRecords">0</h3>
                                <p class="mb-0">Total Records</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-clock fa-2x mb-2"></i>
                                <h3 id="totalLossTime">0</h3>
                                <p class="mb-0">Total Loss Time</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                <h3 id="totalNG">0</h3>
                                <p class="mb-0">NG Records</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-chart-line fa-2x mb-2"></i>
                                <h3 id="avgLossTime">0</h3>
                                <p class="mb-0">Avg Loss Time</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="card report-card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-download me-2"></i>Export Options</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex export-buttons">
                            <button class="btn btn-success" onclick="exportToExcel()">
                                <i class="fas fa-file-excel me-1"></i>Export to Excel
                            </button>
                            <button class="btn btn-primary" onclick="exportToPDF()">
                                <i class="fas fa-file-pdf me-1"></i>Export to PDF
                            </button>
                            <button class="btn btn-info" onclick="exportSummary()">
                                <i class="fas fa-chart-pie me-1"></i>Export Summary
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Excel export with professional formatting, multiple sheets, and analytics is now available!
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tables -->
                <div class="row">
                    <!-- Loss Time by Issue -->
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Loss Time by Issue</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-analytics table-hover" id="lossTimeByIssue">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Issue/Reject</th>
                                                <th class="text-center">Count</th>
                                                <th class="text-center">Total Loss Time</th>
                                                <th class="text-center">Avg Loss Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loss Time by Time Period -->
                    <div class="col-md-6">
                        <div class="card report-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Loss Time by Time Period</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-analytics table-hover" id="lossTimeByPeriod">
                                        <thead class="table-dark">
                                            <tr>
                                                <th id="periodHeader">Date</th>
                                                <th class="text-center">Records</th>
                                                <th class="text-center">Total Loss Time</th>
                                                <th class="text-center">Avg Loss Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Records -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card report-card">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detailed Records</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover table-analytics" id="detailedRecords">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Section</th>
                                                <th>Line Name</th>
                                                <th>Model</th>
                                                <th>Tool/Machine</th>
                                                <th>Reject/Issue</th>
                                                <th class="text-center">Loss Time</th>
                                                <th class="text-center">Status</th>
                                                <th>Attended By</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Set default date range (last 30 days)
            const today = new Date();
            const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
            
            document.getElementById('dateTo').value = today.toISOString().split('T')[0];
            document.getElementById('dateFrom').value = thirtyDaysAgo.toISOString().split('T')[0];
            
            // Reject filter now uses database data directly, no special handling needed
            
            // Handle section filter change to populate lines
            document.getElementById('sectionFilter').addEventListener('change', function() {
                filterLines();
            });
            
            // Generate initial report
            generateReport();
        });

        // Handle form submission
        document.getElementById('reportFilters').addEventListener('submit', function(e) {
            e.preventDefault();
            generateReport();
        });

        // Generate report function
        async function generateReport() {
            const formData = new FormData(document.getElementById('reportFilters'));
            
            // Show simple loading message
            showAlert('info', 'Generating report...');
            
            try {
                const params = new URLSearchParams();
                for (let [key, value] of formData.entries()) {
                    params.append(key, value);
                }
                
                const response = await fetch(`api.php?action=get_report_data&${params}`);
                const result = await response.json();
                
                if (result.success) {
                    updateSummaryStats(result.data.summary);
                    updateLossTimeByIssue(result.data.lossTimeByIssue);
                    updateLossTimeByPeriod(result.data.lossTimeByPeriod);
                    updateDetailedRecords(result.data.detailedRecords);
                    showAlert('success', 'Report generated successfully!');
                } else {
                    showAlert('danger', result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred while generating the report.');
            }
        }

        // Update summary statistics
        function updateSummaryStats(summary) {
            document.getElementById('totalRecords').textContent = summary.totalRecords || 0;
            document.getElementById('totalLossTime').textContent = formatTotalLossTime(summary.totalLossTime || 0);
            document.getElementById('totalNG').textContent = summary.totalNG || 0;
            document.getElementById('avgLossTime').textContent = formatLossTime(summary.avgLossTime || 0);
        }

        // Update loss time by issue table
        function updateLossTimeByIssue(data) {
            const tbody = document.querySelector('#lossTimeByIssue tbody');
            tbody.innerHTML = '';
            
            if (data && data.length > 0) {
                data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.reject_name}</td>
                        <td class="text-center">${item.count}</td>
                        <td class="text-center loss-time-cell">${formatTotalLossTime(item.totalLossTime)}</td>
                        <td class="text-center">${formatLossTime(item.avgLossTime)}</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
            }
        }

        // Update loss time by period table
        function updateLossTimeByPeriod(data) {
            const tbody = document.querySelector('#lossTimeByPeriod tbody');
            const header = document.getElementById('periodHeader');
            const timePeriod = document.getElementById('timePeriod').value;
            
            // Update header based on time period
            switch(timePeriod) {
                case 'daily':
                    header.textContent = 'Date';
                    break;
                case 'weekly':
                    header.textContent = 'Week';
                    break;
                case 'monthly':
                    header.textContent = 'Month';
                    break;
                case 'yearly':
                    header.textContent = 'Year';
                    break;
            }
            
            tbody.innerHTML = '';
            
            if (data && data.length > 0) {
                data.forEach(item => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${item.period}</td>
                        <td class="text-center">${item.count}</td>
                        <td class="text-center loss-time-cell">${formatTotalLossTime(item.totalLossTime)}</td>
                        <td class="text-center">${formatLossTime(item.avgLossTime)}</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No data available</td></tr>';
            }
        }

        // Update detailed records table
        function updateDetailedRecords(data) {
            const tbody = document.querySelector('#detailedRecords tbody');
            tbody.innerHTML = '';
            
            if (data && data.length > 0) {
                data.forEach(record => {
                    const statusBadge = getStatusBadge(record);
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="fw-bold text-primary">${record.id}</td>
                        <td>${record.date_occured}</td>
                        <td>${record.section_name || ''}</td>
                        <td>${record.line_name || ''}</td>
                        <td>${record.model_name || ''}</td>
                        <td>${record.tool_name || ''}</td>
                        <td>${record.reject_name || ''}</td>
                        <td class="text-center loss-time-cell">${record.loss_time || ''}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td>${record.attented_by || ''}</td>
                    `;
                    tbody.appendChild(row);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No records found</td></tr>';
            }
        }

        // Get status badge HTML
        function getStatusBadge(record) {
            if (record.approved_sv_status === 'approved') {
                return '<span class="badge bg-success">Approved</span>';
            } else if (record.approved_sv_status === 'ng') {
                return '<span class="badge bg-danger">NG - Supervisor</span>';
            } else if (record.verified_lineleader_status === 'verified') {
                return '<span class="badge bg-info">Line Verified</span>';
            } else if (record.verified_lineleader_status === 'ng') {
                return '<span class="badge bg-danger">NG - Line Leader</span>';
            } else if (record.verified_tech_status === 'verified') {
                return '<span class="badge bg-warning text-dark">Tech Verified</span>';
            } else if (record.verified_tech_status === 'ng') {
                return '<span class="badge bg-danger">NG - Tech</span>';
            } else {
                return '<span class="badge bg-secondary">Pending</span>';
            }
        }

        // Format loss time (for averages - shows hours and minutes)
        function formatLossTime(minutes) {
            if (!minutes || minutes === 0) return '0 mins';
            
            // Round to whole number to remove decimal points
            const roundedMinutes = Math.round(minutes);
            const hours = Math.floor(roundedMinutes / 60);
            const mins = roundedMinutes % 60;
            
            if (hours > 0) {
                return `${hours}h ${mins}m`;
            } else {
                return `${mins} mins`;
            }
        }

        // Format total loss time (shows only minutes for consistency)
        function formatTotalLossTime(minutes) {
            if (!minutes || minutes === 0) return '0 mins';
            
            // Round to whole number to remove decimal points
            const roundedMinutes = Math.round(minutes);
            return `${roundedMinutes} mins`;
        }

        // Export functions
        async function exportToExcel() {
            const formData = new FormData(document.getElementById('reportFilters'));
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            // Show simple message
            showAlert('info', 'Preparing Excel export...');
            
            try {
                const response = await fetch(`api.php?action=export_excel&${params}`, {
                    method: 'GET'
                });
                
                if (response.ok) {
                    const blob = await response.blob();
                    
                    // Check if it's actually an Excel file
                    if (blob.type.includes('spreadsheet') || blob.size > 1000) {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `repair_report_${new Date().toISOString().split('T')[0]}.xlsx`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        showAlert('success', 'Excel report downloaded successfully!');
                    } else {
                        // If it's not an Excel file, it might be an error message
                        const text = await blob.text();
                        console.error('Unexpected response:', text);
                        showAlert('danger', 'Export failed. Please check console for details.');
                    }
                } else {
                    showAlert('danger', 'Export failed. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred during export: ' + error.message);
            }
        }

        async function exportToPDF() {
            const formData = new FormData(document.getElementById('reportFilters'));
            const params = new URLSearchParams();
            for (let [key, value] of formData.entries()) {
                params.append(key, value);
            }
            showAlert('info', 'Preparing PDF export...');
            try {
                const response = await fetch(`api.php?action=export_pdf&${params}`, {
                    method: 'GET'
                });
                if (response.ok) {
                    const blob = await response.blob();
                    if (blob.type === 'application/pdf' && blob.size > 1000) {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `repair_report_${new Date().toISOString().split('T')[0]}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        showAlert('success', 'PDF report downloaded successfully!');
                    } else {
                        const text = await blob.text();
                        console.error('Unexpected response:', text);
                        showAlert('danger', 'PDF export failed. Please check console for details.');
                    }
                } else {
                    showAlert('danger', 'PDF export failed. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                showAlert('danger', 'An error occurred during PDF export: ' + error.message);
            }
        }

        function exportSummary() {
            showAlert('info', 'Summary export functionality will be implemented soon!');
        }

        // Filter line names by selected section
        function filterLines() {
            var section = document.getElementById('sectionFilter').value;
            var lineSelect = document.getElementById('lineFilter');
            lineSelect.innerHTML = '<option value="">Loading...</option>';
            if(section === '') {
                lineSelect.innerHTML = '<option value="">All Lines</option>';
                return;
            }
            fetch('get_lines_by_section.php?section_name=' + encodeURIComponent(section))
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">All Lines</option>';
                    data.forEach(function(line) {
                        options += '<option value="' + line.line_name + '">' + line.line_name + '</option>';
                    });
                    lineSelect.innerHTML = options;
                })
                .catch(() => {
                    lineSelect.innerHTML = '<option value="">All Lines</option>';
                });
        }

        // Show alert function
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
    </script>

    <!--FOOTER-->
    <?php //include 'footer.php'; ?>
</body>
</html>
