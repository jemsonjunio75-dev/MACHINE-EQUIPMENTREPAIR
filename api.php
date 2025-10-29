
<?php
date_default_timezone_set('Asia/Manila');

include 'dbconnection.php';

// PHPSpreadsheet imports
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;

// Set JSON header for all responses
header('Content-Type: application/json');

// Get the action from the request
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_records':
        getRecords();
        break;
    case 'get_record':
        getRecord();
        break;
    case 'verify_tech':
        verifyRecord('tech');
        break;
    case 'verify_line':
        verifyRecord('line');
        break;
    case 'approve':
        approveRecord();
        break;
    case 'verify_with_password':
        // Secure verification/approval with password
        $id = $_POST['id'] ?? '';
        $action_type = $_POST['action_type'] ?? '';
        $user_id = $_POST['user_id'] ?? '';
        $user_password = $_POST['user_password'] ?? '';
        $verification_result = $_POST['verification_result'] ?? '';
        $ng_remarks = $_POST['ng_remarks'] ?? '';
        
        if (!$id || !$action_type || !$user_id || !$user_password || !$verification_result) {
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            exit;
        }
        
        if ($verification_result === 'ng' && !$ng_remarks) {
            echo json_encode(['success' => false, 'message' => 'NG remarks are required.']);
            exit;
        }
        
        // Map action_type to user_role and db field
        $role_map = [
            'tech' => ['role' => 'tech', 'field' => 'verified_tech_status'],
            'line' => ['role' => 'line', 'field' => 'verified_lineleader_status'],
            'sv'   => ['role' => 'sv',   'field' => 'approved_sv_status'],
        ];
        if (!isset($role_map[$action_type])) {
            echo json_encode(['success' => false, 'message' => 'Invalid action type.']);
            exit;
        }
        $expected_role = $role_map[$action_type]['role'];
        $update_field = $role_map[$action_type]['field'];
        
        // Set status value based on verification result
        $update_value = ($verification_result === 'ok') ? 
            ($action_type === 'sv' ? 'approved' : 'verified') : 
            'ng';
        
        // Check user credentials and role
        $stmt = $conn->prepare("SELECT id, user_name FROM tbl_users WHERE user_id = ? AND user_password = ? AND user_role = ? LIMIT 1");
        $stmt->bind_param('sss', $user_id, $user_password, $expected_role);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials or role for this action.']);
            exit;
        }
        $user_data = $result->fetch_assoc();
        $user_name = $user_data['user_name'];
        $stmt->close();
        
        // Prepare update query with verifier/approver name and timestamp
        $current_time = date('Y-m-d H:i:s');
        $by_field = '';
        $date_field = '';
        $remarks_field = '';
        
        switch ($action_type) {
            case 'tech':
                $by_field = 'verified_tech_by';
                $date_field = 'verified_tech_date';
                $remarks_field = 'verified_tech_ng_remarks';
                break;
            case 'line':
                $by_field = 'verified_lineleader_by';
                $date_field = 'verified_lineleader_date';
                $remarks_field = 'verified_lineleader_ng_remarks';
                break;
            case 'sv':
                $by_field = 'approved_sv_by';
                $date_field = 'approved_sv_date';
                $remarks_field = 'approved_sv_ng_remarks';
                break;
        }
        
        // Update the record with verifier/approver name, timestamp, and optional NG remarks
        if ($verification_result === 'ng') {
            $stmt2 = $conn->prepare("UPDATE tbl_repair_records SET $update_field = ?, $by_field = ?, $date_field = ?, $remarks_field = ? WHERE id = ?");
            $stmt2->bind_param('ssssi', $update_value, $user_name, $current_time, $ng_remarks, $id);
        } else {
            $stmt2 = $conn->prepare("UPDATE tbl_repair_records SET $update_field = ?, $by_field = ?, $date_field = ? WHERE id = ?");
            $stmt2->bind_param('sssi', $update_value, $user_name, $current_time, $id);
        }
        if ($stmt2->execute()) {
            $result_text = ($verification_result === 'ok') ? 'verified/approved' : 'marked as NG (No Good)';
            echo json_encode(['success' => true, 'message' => ucfirst($expected_role) . ' ' . $result_text . ' successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update record.']);
        }
        $stmt2->close();
        exit;
    case 'bulk_sv_approve':
        // Bulk supervisor approval
        $record_ids = $_POST['record_ids'] ?? [];
        $sv_userid = $_POST['sv_userid'] ?? '';
        $sv_password = $_POST['sv_password'] ?? '';
        if (empty($record_ids) || !is_array($record_ids) || !$sv_userid || !$sv_password) {
            echo json_encode(['success' => false, 'message' => 'Please select records and enter supervisor credentials.']);
            exit;
        }
        // Validate supervisor credentials
        $stmt = $conn->prepare("SELECT user_name FROM tbl_users WHERE user_id = ? AND user_password = ? AND user_role = 'sv' LIMIT 1");
        $stmt->bind_param('ss', $sv_userid, $sv_password);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid supervisor credentials.']);
            exit;
        }
        $user_name = $result->fetch_assoc()['user_name'];
        $stmt->close();
        // Approve each selected record
        $success_count = 0;
        $fail_count = 0;
        $current_time = date('Y-m-d H:i:s');
        foreach ($record_ids as $rid) {
            if (!is_numeric($rid)) continue;
            $stmt2 = $conn->prepare("UPDATE tbl_repair_records SET approved_sv_status = 'approved', approved_sv_by = ?, approved_sv_date = ? WHERE id = ? AND verified_tech_status = 'verified' AND verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status != 'approved')");
            $stmt2->bind_param('ssi', $user_name, $current_time, $rid);
            if ($stmt2->execute() && $stmt2->affected_rows > 0) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $stmt2->close();
        }
        $msg = "$success_count record(s) approved.";
        if ($fail_count > 0) $msg .= " $fail_count record(s) failed or already approved.";
        echo json_encode(['success' => true, 'message' => $msg]);
        exit;

    case 'get_report_data':
        $dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
        $dateTo = $_GET['dateTo'] ?? date('Y-m-d');
        $timePeriod = $_GET['timePeriod'] ?? 'daily';
        $rejectFilter = $_GET['rejectFilter'] ?? '';
        $lineFilter = $_GET['lineFilter'] ?? '';
        $sectionFilter = $_GET['sectionFilter'] ?? '';
        $statusFilter = $_GET['statusFilter'] ?? '';

        // Build base query
        $where_conditions = ["date_occured BETWEEN ? AND ?"];
        $params = [$dateFrom, $dateTo];
        $param_types = 'ss';

        // Add reject/issue filter (now handles database-driven reject options)
        if (!empty($rejectFilter)) {
            $where_conditions[] = "reject_name = ?";
            $params[] = $rejectFilter;
            $param_types .= 's';
        }

        // Add section filter
        if (!empty($sectionFilter)) {
            $where_conditions[] = "section_name = ?";
            $params[] = $sectionFilter;
            $param_types .= 's';
        }

        // Add line filter
        if (!empty($lineFilter)) {
            $where_conditions[] = "line_name = ?";
            $params[] = $lineFilter;
            $param_types .= 's';
        }

        // Add status filter
        if (!empty($statusFilter)) {
            switch ($statusFilter) {
                case 'approved':
                    $where_conditions[] = "approved_sv_status = 'approved'";
                    break;
                case 'line_verified':
                    $where_conditions[] = "verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
                case 'tech_verified':
                    $where_conditions[] = "verified_tech_status = 'verified' AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
                case 'ng':
                    $where_conditions[] = "(verified_tech_status = 'ng' OR verified_lineleader_status = 'ng' OR approved_sv_status = 'ng')";
                    break;
                case 'pending':
                    $where_conditions[] = "(verified_tech_status IS NULL OR verified_tech_status = '') AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
            }
        }

        // Add status filter
        if (!empty($statusFilter)) {
            switch ($statusFilter) {
                case 'approved':
                    $where_conditions[] = "approved_sv_status = 'approved'";
                    break;
                case 'line_verified':
                    $where_conditions[] = "verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
                case 'tech_verified':
                    $where_conditions[] = "verified_tech_status = 'verified' AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
                case 'ng':
                    $where_conditions[] = "(verified_tech_status = 'ng' OR verified_lineleader_status = 'ng' OR approved_sv_status = 'ng')";
                    break;
                case 'pending':
                    $where_conditions[] = "(verified_tech_status IS NULL OR verified_tech_status = '') AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
            }
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Get detailed records
        $detailed_sql = "SELECT * FROM tbl_repair_records WHERE $where_clause ORDER BY date_occured DESC, id DESC";
        $stmt = $conn->prepare($detailed_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $detailed_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Calculate summary statistics
        $total_records = count($detailed_records);
        $total_ng = 0;
        $total_loss_time = 0;
        $valid_loss_times = 0;

        foreach ($detailed_records as $record) {
            if ($record['verified_tech_status'] === 'ng' || 
                $record['verified_lineleader_status'] === 'ng' || 
                $record['approved_sv_status'] === 'ng') {
                $total_ng++;
            }

            // Parse loss time
            $loss_time = $record['loss_time'];
            if (!empty($loss_time)) {
                $minutes = parseLossTimeToMinutes($loss_time);
                if ($minutes > 0) {
                    $total_loss_time += $minutes;
                    $valid_loss_times++;
                }
            }
        }

        $avg_loss_time = $valid_loss_times > 0 ? round($total_loss_time / $valid_loss_times) : 0;

        // Get loss time by issue
        $loss_time_by_issue = [];
        $issue_stats = [];

        foreach ($detailed_records as $record) {
            $reject_name = $record['reject_name'];
            $minutes = parseLossTimeToMinutes($record['loss_time']);

            if (!isset($issue_stats[$reject_name])) {
                $issue_stats[$reject_name] = [
                    'count' => 0,
                    'totalLossTime' => 0,
                    'avgLossTime' => 0
                ];
            }

            $issue_stats[$reject_name]['count']++;
            if ($minutes > 0) {
                $issue_stats[$reject_name]['totalLossTime'] += $minutes;
            }
        }

        foreach ($issue_stats as $reject_name => $stats) {
            $stats['avgLossTime'] = $stats['count'] > 0 ? round($stats['totalLossTime'] / $stats['count']) : 0;
            $loss_time_by_issue[] = [
                'reject_name' => $reject_name,
                'count' => $stats['count'],
                'totalLossTime' => $stats['totalLossTime'],
                'avgLossTime' => $stats['avgLossTime']
            ];
        }

        // Get loss time by period
        $loss_time_by_period = [];
        $period_stats = [];

        foreach ($detailed_records as $record) {
            $date = new DateTime($record['date_occured']);
            $minutes = parseLossTimeToMinutes($record['loss_time']);

            switch ($timePeriod) {
                case 'weekly':
                    $period = $date->format('Y-W');
                    break;
                case 'monthly':
                    $period = $date->format('Y-m');
                    break;
                case 'yearly':
                    $period = $date->format('Y');
                    break;
                default: // daily
                    $period = $date->format('Y-m-d');
                    break;
            }

            if (!isset($period_stats[$period])) {
                $period_stats[$period] = [
                    'count' => 0,
                    'totalLossTime' => 0,
                    'avgLossTime' => 0
                ];
            }

            $period_stats[$period]['count']++;
            if ($minutes > 0) {
                $period_stats[$period]['totalLossTime'] += $minutes;
            }
        }

        foreach ($period_stats as $period => $stats) {
            $stats['avgLossTime'] = $stats['count'] > 0 ? round($stats['totalLossTime'] / $stats['count']) : 0;
            $loss_time_by_period[] = [
                'period' => $period,
                'count' => $stats['count'],
                'totalLossTime' => $stats['totalLossTime'],
                'avgLossTime' => $stats['avgLossTime']
            ];
        }

        // Sort by period
        usort($loss_time_by_period, function($a, $b) {
            return strcmp($a['period'], $b['period']);
        });

        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => [
                    'totalRecords' => $total_records,
                    'totalNG' => $total_ng,
                    'totalLossTime' => $total_loss_time,
                    'avgLossTime' => $avg_loss_time
                ],
                'lossTimeByIssue' => $loss_time_by_issue,
                'lossTimeByPeriod' => $loss_time_by_period,
                'detailedRecords' => $detailed_records
            ]
        ]);
        break;

    case 'export_excel':
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Include PHPSpreadsheet
            require_once 'vendor/autoload.php';

        $dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
        $dateTo = $_GET['dateTo'] ?? date('Y-m-d');
        $timePeriod = $_GET['timePeriod'] ?? 'daily';
        $rejectFilter = $_GET['rejectFilter'] ?? '';
        $lineFilter = $_GET['lineFilter'] ?? '';
        $sectionFilter = $_GET['sectionFilter'] ?? '';
        $statusFilter = $_GET['statusFilter'] ?? '';

        // Build base query (same logic as get_report_data)
        $where_conditions = ["date_occured BETWEEN ? AND ?"];
        $params = [$dateFrom, $dateTo];
        $param_types = 'ss';

        // Add reject/issue filter (now handles database-driven reject options)
        if (!empty($rejectFilter)) {
            $where_conditions[] = "reject_name = ?";
            $params[] = $rejectFilter;
            $param_types .= 's';
        }

        // Add section filter
        if (!empty($sectionFilter)) {
            $where_conditions[] = "section_name = ?";
            $params[] = $sectionFilter;
            $param_types .= 's';
        }

        // Add line filter
        if (!empty($lineFilter)) {
            $where_conditions[] = "line_name = ?";
            $params[] = $lineFilter;
            $param_types .= 's';
        }

        // Add status filter
        if (!empty($statusFilter)) {
            switch ($statusFilter) {
                case 'approved':
                    $where_conditions[] = "approved_sv_status = 'approved'";
                    break;
                case 'line_verified':
                    $where_conditions[] = "verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
                case 'tech_verified':
                    $where_conditions[] = "verified_tech_status = 'verified' AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
                case 'ng':
                    $where_conditions[] = "(verified_tech_status = 'ng' OR verified_lineleader_status = 'ng' OR approved_sv_status = 'ng')";
                    break;
                case 'pending':
                    $where_conditions[] = "(verified_tech_status IS NULL OR verified_tech_status = '') AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                    break;
            }
        }

        $where_clause = implode(' AND ', $where_conditions);

        // Get detailed records
        $detailed_sql = "SELECT * FROM tbl_repair_records WHERE $where_clause ORDER BY date_occured DESC, id DESC";
        $stmt = $conn->prepare($detailed_sql);
        $stmt->bind_param($param_types, ...$params);
        $stmt->execute();
        $detailed_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Calculate summary statistics
        $total_records = count($detailed_records);
        $total_ng = 0;
        $total_loss_time = 0;
        $valid_loss_times = 0;

        foreach ($detailed_records as $record) {
            if ($record['verified_tech_status'] === 'ng' || 
                $record['verified_lineleader_status'] === 'ng' || 
                $record['approved_sv_status'] === 'ng') {
                $total_ng++;
            }

            $minutes = parseLossTimeToMinutes($record['loss_time']);
            if ($minutes > 0) {
                $total_loss_time += $minutes;
                $valid_loss_times++;
            }
        }

        $avg_loss_time = $valid_loss_times > 0 ? round($total_loss_time / $valid_loss_times) : 0;

        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();
        
        // === SUMMARY SHEET ===
        $summarySheet = $spreadsheet->getActiveSheet();
        $summarySheet->setTitle('Summary');

        // Set headers
        $summarySheet->setCellValue('A1', 'Repair Records Report');
        $summarySheet->setCellValue('A2', 'Generated on: ' . date('Y-m-d H:i:s'));
        $summarySheet->setCellValue('A3', 'Date Range: ' . $dateFrom . ' to ' . $dateTo);
        $summarySheet->setCellValue('A4', 'Time Period: ' . ucfirst($timePeriod));

        // Style the header
        $summarySheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(16);
        $summarySheet->mergeCells('A1:D1');
        $summarySheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $summarySheet->getStyle('A1')->getFont()->setColor(new Color('FFFFFFFF'));

        // Add summary statistics
        $row = 6;
        $summarySheet->setCellValue('A' . $row, 'Summary Statistics');
        $summarySheet->getStyle('A' . $row)->getFont()->setBold(true)->setSize(14);
        $summarySheet->getStyle('A' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');
        $row++;

        $summaryData = [
            ['Total Records:', $total_records],
            ['Total NG Records:', $total_ng],
            ['Total Loss Time:', formatTotalLossTime($total_loss_time)],
            ['Average Loss Time:', formatMinutesToLossTime($avg_loss_time)]
        ];

        foreach ($summaryData as $data) {
            $summarySheet->setCellValue('A' . $row, $data[0]);
            $summarySheet->setCellValue('B' . $row, $data[1]);
            $summarySheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
        }

        // === DETAILED RECORDS SHEET ===
        $detailsSheet = $spreadsheet->createSheet();
        $detailsSheet->setTitle('Detailed Records');

        // Headers for detailed records (as requested)
        $headers = [
            'Date Occurred',
            'Line Name',
            'Model Name',
            'Tool/Machine',
            'Machine Serial',
            'Attended By',
            'Reject/Issue',
            'Analysis/Cause',
            'Action Taken',
            'Date Start',
            'Start Time',
            'Date Ended',
            'End Time',
            'Loss Time',
            'Tech Verified Date',
            'Line Verified Date',
            'SV Approved Date'
        ];

        // Set headers
        $col = 'A';
        foreach ($headers as $header) {
            $detailsSheet->setCellValue($col . '1', $header);
            $detailsSheet->getStyle($col . '1')->getFont()->setBold(true);
            $detailsSheet->getStyle($col . '1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $detailsSheet->getStyle($col . '1')->getFont()->setColor(new Color('FFFFFFFF'));
            $col++;
        }

        // Add data rows
        $row = 2;
        foreach ($detailed_records as $record) {
            $col = 'A';
            $rowData = [
                $record['date_occured'],
                $record['line_name'] ?? '',
                $record['model_name'] ?? '',
                $record['tool_name'] ?? '',
                $record['machine_serial'] ?? '',
                $record['attented_by'] ?? '',
                $record['reject_name'] ?? '',
                $record['analysis_cause'] ?? '',
                $record['action_taken'] ?? '',
                $record['date_occured'], // Date Start (same as Date Occurred)
                $record['start_time'] ?? '',
                $record['date_ended'] ?? '',
                $record['end_time'] ?? '',
                $record['loss_time'] ?? '',
                $record['verified_tech_date'] ?? '',
                $record['verified_lineleader_date'] ?? '',
                $record['approved_sv_date'] ?? ''
            ];
            foreach ($rowData as $data) {
                $detailsSheet->setCellValue($col . $row, $data);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'Q') as $col) {
            $detailsSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to all data
        $detailsSheet->getStyle('A1:Q' . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // === ANALYTICS SHEET ===
        $analyticsSheet = $spreadsheet->createSheet();
        $analyticsSheet->setTitle('Analytics');

        // Loss Time by Issue
        $analyticsSheet->setCellValue('A1', 'Loss Time by Issue');
        $analyticsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $analyticsSheet->getStyle('A1')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE2EFDA');

        $analyticsSheet->setCellValue('A3', 'Issue/Reject');
        $analyticsSheet->setCellValue('B3', 'Count');
        $analyticsSheet->setCellValue('C3', 'Total Loss Time');
        $analyticsSheet->setCellValue('D3', 'Avg Loss Time');

        // Style headers
        $analyticsSheet->getStyle('A3:D3')->getFont()->setBold(true);
        $analyticsSheet->getStyle('A3:D3')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4472C4');
        $analyticsSheet->getStyle('A3:D3')->getFont()->setColor(new Color('FFFFFFFF'));

        // Get loss time by issue data
        $issue_stats = [];
        foreach ($detailed_records as $record) {
            $reject_name = $record['reject_name'];
            $minutes = parseLossTimeToMinutes($record['loss_time']);

            if (!isset($issue_stats[$reject_name])) {
                $issue_stats[$reject_name] = [
                    'count' => 0,
                    'totalLossTime' => 0,
                    'avgLossTime' => 0
                ];
            }

            $issue_stats[$reject_name]['count']++;
            if ($minutes > 0) {
                $issue_stats[$reject_name]['totalLossTime'] += $minutes;
            }
        }

        $row = 4;
        foreach ($issue_stats as $reject_name => $stats) {
            $stats['avgLossTime'] = $stats['count'] > 0 ? round($stats['totalLossTime'] / $stats['count']) : 0;
            
            $analyticsSheet->setCellValue('A' . $row, $reject_name);
            $analyticsSheet->setCellValue('B' . $row, $stats['count']);
            $analyticsSheet->setCellValue('C' . $row, formatTotalLossTime($stats['totalLossTime']));
            $analyticsSheet->setCellValue('D' . $row, formatMinutesToLossTime($stats['avgLossTime']));
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'D') as $col) {
            $analyticsSheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders
        $analyticsSheet->getStyle('A3:D' . ($row - 1))->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Set active sheet back to summary
        $spreadsheet->setActiveSheetIndex(0);

            // Set headers for download
            $filename = 'repair_report_' . date('Y-m-d_H-i-s') . '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Pragma: public');
            header('Expires: 0');

            // Write file to output
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
                // If there's an error, return JSON error response
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Excel export failed: ' . $e->getMessage()
                ]);
                exit;
            }

    case 'export_pdf':
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        try {
            require_once 'vendor/autoload.php';
            $dateFrom = $_GET['dateFrom'] ?? date('Y-m-01');
            $dateTo = $_GET['dateTo'] ?? date('Y-m-d');
            $timePeriod = $_GET['timePeriod'] ?? 'daily';
            $rejectFilter = $_GET['rejectFilter'] ?? '';
            $lineFilter = $_GET['lineFilter'] ?? '';
            $sectionFilter = $_GET['sectionFilter'] ?? '';
            $statusFilter = $_GET['statusFilter'] ?? '';

            // Build base query (same logic as get_report_data)
            $where_conditions = ["date_occured BETWEEN ? AND ?"];
            $params = [$dateFrom, $dateTo];
            $param_types = 'ss';

            // Add reject/issue filter (now handles database-driven reject options)
            if (!empty($rejectFilter)) {
                $where_conditions[] = "reject_name = ?";
                $params[] = $rejectFilter;
                $param_types .= 's';
            }

            // Add section filter
            if (!empty($sectionFilter)) {
                $where_conditions[] = "section_name = ?";
                $params[] = $sectionFilter;
                $param_types .= 's';
            }

            // Add line filter
            if (!empty($lineFilter)) {
                $where_conditions[] = "line_name = ?";
                $params[] = $lineFilter;
                $param_types .= 's';
            }
            // Add status filter
            if (!empty($statusFilter)) {
                switch ($statusFilter) {
                    case 'approved':
                        $where_conditions[] = "approved_sv_status = 'approved'";
                        break;
                    case 'line_verified':
                        $where_conditions[] = "verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                        break;
                    case 'tech_verified':
                        $where_conditions[] = "verified_tech_status = 'verified' AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                        break;
                    case 'ng':
                        $where_conditions[] = "(verified_tech_status = 'ng' OR verified_lineleader_status = 'ng' OR approved_sv_status = 'ng')";
                        break;
                    case 'pending':
                        $where_conditions[] = "(verified_tech_status IS NULL OR verified_tech_status = '') AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                        break;
                }
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            $detailed_sql = "SELECT * FROM tbl_repair_records WHERE $where_clause ORDER BY date_occured DESC, id DESC";
            $stmt = $conn->prepare($detailed_sql);
            $stmt->bind_param($param_types, ...$params);
            $stmt->execute();
            $detailed_records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calculate summary statistics
            $total_records = count($detailed_records);
            $total_ng = 0;
            $total_loss_time = 0;
            $valid_loss_times = 0;
            foreach ($detailed_records as $record) {
                if ($record['verified_tech_status'] === 'ng' || 
                    $record['verified_lineleader_status'] === 'ng' || 
                    $record['approved_sv_status'] === 'ng') {
                    $total_ng++;
                }
                $minutes = parseLossTimeToMinutes($record['loss_time']);
                if ($minutes > 0) {
                    $total_loss_time += $minutes;
                    $valid_loss_times++;
                }
            }
            $avg_loss_time = $valid_loss_times > 0 ? round($total_loss_time / $valid_loss_times) : 0;

            // Loss Time by Issue
            $issue_stats = [];
            foreach ($detailed_records as $record) {
                $reject_name = $record['reject_name'];
                $minutes = parseLossTimeToMinutes($record['loss_time']);
                if (!isset($issue_stats[$reject_name])) {
                    $issue_stats[$reject_name] = [
                        'count' => 0,
                        'totalLossTime' => 0,
                        'avgLossTime' => 0
                    ];
                }
                $issue_stats[$reject_name]['count']++;
                if ($minutes > 0) {
                    $issue_stats[$reject_name]['totalLossTime'] += $minutes;
                }
            }
            foreach ($issue_stats as $reject_name => &$stats) {
                $stats['avgLossTime'] = $stats['count'] > 0 ? round($stats['totalLossTime'] / $stats['count']) : 0;
            }
            unset($stats);

            // Loss Time by Period
            $period_stats = [];
            foreach ($detailed_records as $record) {
                $date = new DateTime($record['date_occured']);
                $minutes = parseLossTimeToMinutes($record['loss_time']);
                switch ($timePeriod) {
                    case 'weekly':
                        $period = $date->format('Y-W');
                        break;
                    case 'monthly':
                        $period = $date->format('Y-m');
                        break;
                    case 'yearly':
                        $period = $date->format('Y');
                        break;
                    default:
                        $period = $date->format('Y-m-d');
                        break;
                }
                if (!isset($period_stats[$period])) {
                    $period_stats[$period] = [
                        'count' => 0,
                        'totalLossTime' => 0,
                        'avgLossTime' => 0
                    ];
                }
                $period_stats[$period]['count']++;
                if ($minutes > 0) {
                    $period_stats[$period]['totalLossTime'] += $minutes;
                }
            }
            foreach ($period_stats as $period => &$stats) {
                $stats['avgLossTime'] = $stats['count'] > 0 ? round($stats['totalLossTime'] / $stats['count']) : 0;
            }
            unset($stats);
            ksort($period_stats);

            // Build HTML for PDF
            $html = '<h2 style="text-align:center;">Repair Records Report</h2>';
            $html .= '<p><b>Date Range:</b> ' . htmlspecialchars($dateFrom) . ' to ' . htmlspecialchars($dateTo) . '<br>';
            $html .= '<b>Time Period:</b> ' . htmlspecialchars(ucfirst($timePeriod)) . '</p>';
            $html .= '<h3>Summary Statistics</h3>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="50%" style="font-size:12px; margin-bottom:20px;">';
            $html .= '<tr><th>Total Records</th><th>Total NG</th><th>Total Loss Time</th><th>Avg Loss Time</th></tr>';
            $html .= '<tr>';
            $html .= '<td align="center">' . $total_records . '</td>';
            $html .= '<td align="center">' . $total_ng . '</td>';
            $html .= '<td align="center">' . htmlspecialchars(formatTotalLossTime($total_loss_time)) . '</td>';
            $html .= '<td align="center">' . htmlspecialchars(formatMinutesToLossTime($avg_loss_time)) . '</td>';
            $html .= '</tr></table>';

            // Loss Time by Issue
            $html .= '<h3>Loss Time by Issue</h3>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="font-size:11px; margin-bottom:20px;">';
            $html .= '<tr><th>Issue/Reject</th><th>Count</th><th>Total Loss Time</th><th>Avg Loss Time</th></tr>';
            foreach ($issue_stats as $reject_name => $stats) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($reject_name) . '</td>';
                $html .= '<td align="center">' . $stats['count'] . '</td>';
                $html .= '<td align="center">' . htmlspecialchars(formatTotalLossTime($stats['totalLossTime'])) . '</td>';
                $html .= '<td align="center">' . htmlspecialchars(formatMinutesToLossTime($stats['avgLossTime'])) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';

            // Loss Time by Period
            $html .= '<h3>Loss Time by ' . htmlspecialchars(ucfirst($timePeriod)) . '</h3>';
            $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="font-size:11px; margin-bottom:20px;">';
            $html .= '<tr><th>Period</th><th>Records</th><th>Total Loss Time</th><th>Avg Loss Time</th></tr>';
            foreach ($period_stats as $period => $stats) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($period) . '</td>';
                $html .= '<td align="center">' . $stats['count'] . '</td>';
                $html .= '<td align="center">' . htmlspecialchars(formatTotalLossTime($stats['totalLossTime'])) . '</td>';
                $html .= '<td align="center">' . htmlspecialchars(formatMinutesToLossTime($stats['avgLossTime'])) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</table>';

            // Detailed Records Table
            $html .= '<h3>Detailed Records</h3>';
            $html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%" style="font-size:10px;">';
            $headers = [
                'Date Occurred',
                'Line Name',
                'Model Name',
                'Tool/Machine',
                'Machine Serial',
                'Attended By',
                'Reject/Issue',
                'Analysis/Cause',
                'Action Taken',
                'Date Start',
                'Start Time',
                'Date Ended',
                'End Time',
                'Loss Time',
                'Tech Verified Date',
                'Line Verified Date',
                'SV Approved Date'
            ];
            $html .= '<tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr>';
            foreach ($detailed_records as $record) {
                $html .= '<tr>';
                $rowData = [
                    $record['date_occured'],
                    $record['line_name'] ?? '',
                    $record['model_name'] ?? '',
                    $record['tool_name'] ?? '',
                    $record['machine_serial'] ?? '',
                    $record['attented_by'] ?? '',
                    $record['reject_name'] ?? '',
                    $record['analysis_cause'] ?? '',
                    $record['action_taken'] ?? '',
                    $record['date_occured'], // Date Start (same as Date Occurred)
                    $record['start_time'] ?? '',
                    $record['date_ended'] ?? '',
                    $record['end_time'] ?? '',
                    $record['loss_time'] ?? '',
                    $record['verified_tech_date'] ?? '',
                    $record['verified_lineleader_date'] ?? '',
                    $record['approved_sv_date'] ?? ''
                ];
                foreach ($rowData as $data) {
                    $html .= '<td>' . htmlspecialchars($data) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</table>';

            // Generate PDF
            $mpdf = new \Mpdf\Mpdf(['tempDir' => __DIR__ . '/tmp']);
            $mpdf->SetTitle('Repair Records Report');
            $mpdf->WriteHTML($html);
            $filename = 'repair_report_' . date('Y-m-d_H-i-s') . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
            exit;
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'PDF export failed: ' . $e->getMessage()
            ]);
            exit;
        }
    case 'loss_time_by_line':
        lossTimeByLine();
        break;
    case 'edit_record':
        editRecord();
        break;
    case 'top_rejects_by_line':
        topRejectsByLine();
        break;
    case 'check_data_changes':
        checkDataChanges();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;

    
   
}

function getRecords() {
    global $conn;
    
    // Get parameters
    $page = max(1, intval($_GET['page'] ?? 1));
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    $sort = $_GET['sort'] ?? 'priority';
    $limit = 50;
    $offset = ($page - 1) * $limit;
    
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
            // Search functionality
            if (!empty($search)) {
                $where_conditions[] = "(id LIKE ? OR line_name LIKE ? OR model_name LIKE ? OR tool_name LIKE ? OR machine_serial LIKE ? OR reject_name LIKE ? OR analysis_cause LIKE ? OR attented_by LIKE ?)";
                $search_param = "%$search%";
                for ($i = 0; $i < 8; $i++) {
                    $params[] = $search_param;
                    $param_types .= 's';
                }
            }
    
    // Status filter
    if (!empty($status)) {
        switch ($status) {
            case 'pending':
                $where_conditions[] = "(verified_tech_status IS NULL OR verified_tech_status = '')";
                break;
            case 'ng':
                $where_conditions[] = "(verified_tech_status = 'ng' OR verified_lineleader_status = 'ng' OR approved_sv_status = 'ng')";
                break;
            case 'tech':
                $where_conditions[] = "verified_tech_status = 'verified' AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '')";
                break;
            case 'line':
                $where_conditions[] = "verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status = '')";
                break;
            case 'approved':
                $where_conditions[] = "approved_sv_status = 'approved'";
                break;
        }
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Build ORDER BY clause
    $order_clause = '';
    switch ($sort) {
        case 'priority':
            $order_clause = "ORDER BY 
                CASE 
                    WHEN (verified_tech_status IS NULL OR verified_tech_status = '') THEN 1
                    WHEN (verified_tech_status = 'ng' OR verified_lineleader_status = 'ng' OR approved_sv_status = 'ng') THEN 2
                    WHEN verified_tech_status = 'verified' AND (verified_lineleader_status IS NULL OR verified_lineleader_status = '') THEN 3
                    WHEN verified_lineleader_status = 'verified' AND (approved_sv_status IS NULL OR approved_sv_status = '') THEN 4
                    WHEN approved_sv_status = 'approved' THEN 5
                    ELSE 6
                END, id DESC";
            break;
        case 'date_desc':
            $order_clause = "ORDER BY date_occured DESC, id DESC";
            break;
        case 'date_asc':
            $order_clause = "ORDER BY date_occured ASC, id ASC";
            break;
        case 'id_desc':
            $order_clause = "ORDER BY id DESC";
            break;
        case 'id_asc':
            $order_clause = "ORDER BY id ASC";
            break;
        default:
            $order_clause = "ORDER BY id DESC";
    }
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM tbl_repair_records $where_clause";
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($param_types, ...$params);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
    
    // Get records
    $sql = "SELECT * FROM tbl_repair_records $where_clause $order_clause LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
    
    // Calculate pagination
    $total_pages = ceil($total_records / $limit);
    
    echo json_encode([
        'success' => true,
        'data' => $records,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'per_page' => $limit,
            'total_records' => $total_records
        ],
        'total' => $total_records
    ]);
}

function getRecord() {
    global $conn;
    
    $id = $_GET['id'] ?? '';
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
        return;
    }
    
    $sql = "SELECT * FROM tbl_repair_records WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($record = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $record]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function verifyRecord($type) {
    global $conn;
    
    $id = $_POST['id'] ?? '';
    $verifier_name = $_POST['verifier_name'] ?? '';
    
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
        return;
    }
    
    if (empty($verifier_name)) {
        echo json_encode(['success' => false, 'message' => 'Verifier name is required']);
        return;
    }
    
    $field = $type === 'tech' ? 'verified_tech_status' : 'verified_lineleader_status';
    $by_field = $type === 'tech' ? 'verified_tech_by' : 'verified_lineleader_by';
    $date_field = $type === 'tech' ? 'verified_tech_date' : 'verified_lineleader_date';
    $value = 'verified';
    $current_time = date('Y-m-d H:i:s');
    
    $sql = "UPDATE tbl_repair_records SET $field = ?, $by_field = ?, $date_field = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $value, $verifier_name, $current_time, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record verified successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to verify record']);
    }
}

function approveRecord() {
    global $conn;
    
    $id = $_POST['id'] ?? '';
    $approver_name = $_POST['approver_name'] ?? '';
    
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
        return;
    }
    
    if (empty($approver_name)) {
        echo json_encode(['success' => false, 'message' => 'Approver name is required']);
        return;
    }
    
    $current_time = date('Y-m-d H:i:s');
    
    $sql = "UPDATE tbl_repair_records SET approved_sv_status = 'approved', approved_sv_by = ?, approved_sv_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $approver_name, $current_time, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record approved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to approve record']);
    }
}

// --- API for Loss Time by Line ---
function lossTimeByLine() {
    global $conn;
    $section = isset($_GET['section_name']) ? $conn->real_escape_string($_GET['section_name']) : '';
    $date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';
    $min_minutes = isset($_GET['min_minutes']) ? (int)$_GET['min_minutes'] : 0;
    $where = [];
    if ($section) $where[] = "section_name = '" . $section . "'";
    if ($date_from) $where[] = "date_occured >= '" . $date_from . "'";
    if ($date_to) $where[] = "date_occured <= '" . $date_to . "'";
    $where[] = "loss_time IS NOT NULL AND loss_time != ''";
    $where_clause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    $sql = "SELECT line_name, loss_time FROM tbl_repair_records $where_clause";
    $result = $conn->query($sql);
    $lineLoss = [];
    while ($row = $result->fetch_assoc()) {
        $line = $row['line_name'];
        $minutes = parseLossTimeToMinutes($row['loss_time']);
        if ($minutes > 0 && $minutes >= $min_minutes) {
            if (!isset($lineLoss[$line])) $lineLoss[$line] = 0;
            $lineLoss[$line] += $minutes;
        }
    }
    $data = [];
    foreach ($lineLoss as $line => $total) {
        $data[] = [
            'line_name' => $line,
            'total_loss_time_minutes' => $total
        ];
    }
    usort($data, function($a, $b) { return $b['total_loss_time_minutes'] <=> $a['total_loss_time_minutes']; });
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// --- API for Top 10 Rejects by Line (with count and loss time) ---
function topRejectsByLine() {
    global $conn;
    $section = isset($_GET['section_name']) ? $conn->real_escape_string($_GET['section_name']) : '';
    $date_from = isset($_GET['date_from']) ? $conn->real_escape_string($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? $conn->real_escape_string($_GET['date_to']) : '';
    
    $where = [];
    if ($section) $where[] = "section_name = '" . $section . "'";
    if ($date_from) $where[] = "date_occured >= '" . $date_from . "'";
    if ($date_to) $where[] = "date_occured <= '" . $date_to . "'";
    $where[] = "reject_name IS NOT NULL AND reject_name != ''";
    $where_clause = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Get all records for the reject analysis
    $sql = "SELECT reject_name, loss_time FROM tbl_repair_records $where_clause";
    $result = $conn->query($sql);
    
    $rejectStats = [];
    while ($row = $result->fetch_assoc()) {
        $rejectName = $row['reject_name'];
        $lossTime = $row['loss_time'];
        
        if (!isset($rejectStats[$rejectName])) {
            $rejectStats[$rejectName] = [
                'count' => 0,
                'total_loss_time_minutes' => 0
            ];
        }
        
        $rejectStats[$rejectName]['count']++;
        
        // Parse loss time to minutes
        $minutes = parseLossTimeToMinutes($lossTime);
        if ($minutes > 0) {
            $rejectStats[$rejectName]['total_loss_time_minutes'] += $minutes;
        }
    }
    
    // Convert to array and sort by count
    $data = [];
    foreach ($rejectStats as $rejectName => $stats) {
        $data[] = [
            'reject_name' => $rejectName,
            'count' => $stats['count'],
            'total_loss_time_minutes' => $stats['total_loss_time_minutes']
        ];
    }
    
    // Sort by count descending and take top 10
    usort($data, function($a, $b) { 
        return $b['count'] <=> $a['count']; 
    });
    $data = array_slice($data, 0, 10);
    
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

// Helper function to parse loss time to minutes
function parseLossTimeToMinutes($lossTime) {
    if (empty($lossTime)) return 0;
    
    // Handle format like "120 mins" or "2h 30m" or "2:30"
    if (preg_match('/(\d+)\s*mins?/', $lossTime, $matches)) {
        return (int)$matches[1];
    }
    
    if (preg_match('/(\d+)h\s*(\d+)m/', $lossTime, $matches)) {
        return (int)$matches[1] * 60 + (int)$matches[2];
    }
    
    if (preg_match('/(\d+):(\d+)/', $lossTime, $matches)) {
        return (int)$matches[1] * 60 + (int)$matches[2];
    }
    
    // If it's just a number, assume it's minutes
    if (is_numeric($lossTime)) {
        return (int)$lossTime;
    }
    
    return 0;
}

// Helper function to format minutes to loss time (for averages - shows hours and minutes)
function formatMinutesToLossTime($minutes) {
    if ($minutes == 0) return '0 mins';
    
    // Round to whole number to remove decimal points
    $roundedMinutes = round($minutes);
    $hours = floor($roundedMinutes / 60);
    $mins = $roundedMinutes % 60;
    
    if ($hours > 0) {
        return $mins > 0 ? "${hours}h ${mins}m" : "${hours}h";
    } else {
        return "${mins} mins";
    }
}

// Helper function to format total loss time (shows only minutes for consistency)
function formatTotalLossTime($minutes) {
    if ($minutes == 0) return '0 mins';

    // Round to whole number to remove decimal points
    $roundedMinutes = round($minutes);
    return "${roundedMinutes} mins";
}

function editRecord() {
    global $conn;

    // Get form data
    $id = $_POST['id'] ?? '';
    $section_name = $_POST['section_name'] ?? '';
    $line_name = $_POST['line_name'] ?? '';
    $model_name = $_POST['model_name'] ?? '';
    $tool_name = $_POST['tool_name'] ?? '';
    $machine_serial = $_POST['machine_serial'] ?? '';
    $date_occured = $_POST['date_occured'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $date_ended = $_POST['date_ended'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $loss_time = $_POST['loss_time'] ?? '';
    $reject_name = $_POST['reject_name'] ?? '';
    $analysis_cause = $_POST['analysis_cause'] ?? '';
    $action_taken = $_POST['action_taken'] ?? '';
    $attented_by = $_POST['attented_by'] ?? '';

    // Checking result fields
    $check_result_n_appearance = $_POST['check_result_n_appearance'] ?? '';
    $check_result_r_appearance = $_POST['check_result_r_appearance'] ?? '';
    $check_result_p_appearance = $_POST['check_result_p_appearance'] ?? '';
    $check_result_n_dimension = $_POST['check_result_n_dimension'] ?? '';
    $check_result_r_dimension = $_POST['check_result_r_dimension'] ?? '';
    $check_result_p_dimension = $_POST['check_result_p_dimension'] ?? '';
    $check_result_n_tensile = $_POST['check_result_n_tensile'] ?? '';
    $check_result_r_tensile = $_POST['check_result_r_tensile'] ?? '';
    $check_result_p_tensile = $_POST['check_result_p_tensile'] ?? '';
    $check_result_n_electrical = $_POST['check_result_n_electrical'] ?? '';
    $check_result_r_electrical = $_POST['check_result_r_electrical'] ?? '';
    $check_result_p_electrical = $_POST['check_result_p_electrical'] ?? '';

    // Checking method fields
    $checking_method_appearance = $_POST['checking_method_appearance'] ?? '';   
    $checking_method_dim_a_1 = $_POST['checking_method_dim_a_1'] ?? '';
    $checking_method_dim_b_1 = $_POST['checking_method_dim_b_1'] ?? '';
    $checking_method_dim_c_1 = $_POST['checking_method_dim_c_1'] ?? '';
    $checking_method_dim_a_2 = $_POST['checking_method_dim_a_2'] ?? '';
    $checking_method_dim_b_2 = $_POST['checking_method_dim_b_2'] ?? '';
    $checking_method_dim_c_2 = $_POST['checking_method_dim_c_2'] ?? '';
    $checking_method_dim_a_3 = $_POST['checking_method_dim_a_3'] ?? '';
    $checking_method_dim_b_3 = $_POST['checking_method_dim_b_3'] ?? '';
    $checking_method_dim_c_3 = $_POST['checking_method_dim_c_3'] ?? '';
    $checking_method_ten_1 = $_POST['checking_method_ten_1'] ?? '';
    $checking_method_ten_2 = $_POST['checking_method_ten_2'] ?? '';
    $checking_method_ten_3 = $_POST['checking_method_ten_3'] ?? '';
    $checking_method_electrical = $_POST['checking_method_electrical'] ?? '';

    // Validation
    if (empty($id) || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
        return;
    }

    if (empty($section_name) || empty($line_name) || empty($model_name) || empty($tool_name) ||
        empty($machine_serial) || empty($date_occured) || empty($start_time) ||
        empty($date_ended) || empty($end_time) || empty($reject_name) ||
        empty($analysis_cause) || empty($action_taken) || empty($attented_by)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        return;
    }

    // Prepare the UPDATE query with all fields
    $sql = "UPDATE tbl_repair_records SET
        section_name = ?,
        line_name = ?,
        model_name = ?,
        tool_name = ?,
        machine_serial = ?,
        date_occured = ?,
        start_time = ?,
        date_ended = ?,
        end_time = ?,
        loss_time = ?,
        reject_name = ?,
        analysis_cause = ?,
        action_taken = ?,
        attented_by = ?,
        check_result_n_appearance = ?,
        check_result_r_appearance = ?,
        check_result_p_appearance = ?,
        check_result_n_dimension = ?,
        check_result_r_dimension = ?,
        check_result_p_dimension = ?,
        check_result_n_tensile = ?,
        check_result_r_tensile = ?,
        check_result_p_tensile = ?,
        check_result_n_electrical = ?,
        check_result_r_electrical = ?,
        check_result_p_electrical = ?,
        checking_method_appearance = ?,
        checking_method_dim_a_1 = ?,
        checking_method_dim_b_1 = ?,
        checking_method_dim_c_1 = ?,
        checking_method_dim_a_2 = ?,
        checking_method_dim_b_2 = ?,
        checking_method_dim_c_2 = ?,
        checking_method_dim_a_3 = ?,
        checking_method_dim_b_3 = ?,
        checking_method_dim_c_3 = ?,
        checking_method_ten_1 = ?,
        checking_method_ten_2 = ?,
        checking_method_ten_3 = ?,
        checking_method_electrical = ?
        WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssssssssssssssssssssssssssssssss",
        $section_name,
        $line_name,
        $model_name,
        $tool_name,
        $machine_serial,
        $date_occured,
        $start_time,
        $date_ended,
        $end_time,
        $loss_time,
        $reject_name,
        $analysis_cause,
        $action_taken,
        $attented_by,
        $check_result_n_appearance,
        $check_result_r_appearance,
        $check_result_p_appearance,
        $check_result_n_dimension,
        $check_result_r_dimension,
        $check_result_p_dimension,
        $check_result_n_tensile,
        $check_result_r_tensile,
        $check_result_p_tensile,
        $check_result_n_electrical,
        $check_result_r_electrical,
        $check_result_p_electrical,
        $checking_method_appearance,
        $checking_method_dim_a_1,
        $checking_method_dim_b_1,
        $checking_method_dim_c_1,
        $checking_method_dim_a_2,
        $checking_method_dim_b_2,
        $checking_method_dim_c_2,
        $checking_method_dim_a_3,
        $checking_method_dim_b_3,
        $checking_method_dim_c_3,
        $checking_method_ten_1,
        $checking_method_ten_2,
        $checking_method_ten_3,
        $checking_method_electrical,
        $id
    );

    if ($stmt->execute()) {
        // After updating the record, reset status fields to the previous step
        $statusQ = $conn->prepare("SELECT verified_tech_status, verified_lineleader_status, approved_sv_status FROM tbl_repair_records WHERE id = ?");
        $statusQ->bind_param("i", $id);
        $statusQ->execute();
        $statusRes = $statusQ->get_result()->fetch_assoc();
        $statusQ->close();

        if ($statusRes['approved_sv_status'] === 'ng') {
            // Demote to line leader; clear supervisor approvals only
            $resetSql = "UPDATE tbl_repair_records SET
                approved_sv_status = NULL,
                approved_sv_by = NULL,
                approved_sv_date = NULL,
                approved_sv_ng_remarks = NULL
                WHERE id = ?";
            $resetStmt = $conn->prepare($resetSql);
            $resetStmt->bind_param("i", $id);
            $resetStmt->execute();
            $resetStmt->close();
        } elseif ($statusRes['verified_lineleader_status'] === 'ng') {
            // Demote to tech; clear line leader AND supervisor approvals
            $resetSql = "UPDATE tbl_repair_records SET
                verified_lineleader_status = NULL,
                verified_lineleader_by = NULL,
                verified_lineleader_date = NULL,
                verified_lineleader_ng_remarks = NULL,
                approved_sv_status = NULL,
                approved_sv_by = NULL,
                approved_sv_date = NULL,
                approved_sv_ng_remarks = NULL
                WHERE id = ?";
            $resetStmt = $conn->prepare($resetSql);
            $resetStmt->bind_param("i", $id);
            $resetStmt->execute();
            $resetStmt->close();
        } elseif ($statusRes['verified_tech_status'] === 'ng') {
            // Demote to not verified at all; clear all verification fields
            $resetSql = "UPDATE tbl_repair_records SET
                verified_tech_status = NULL,
                verified_tech_by = NULL,
                verified_tech_date = NULL,
                verified_tech_ng_remarks = NULL,
                verified_lineleader_status = NULL,
                verified_lineleader_by = NULL,
                verified_lineleader_date = NULL,
                verified_lineleader_ng_remarks = NULL,
                approved_sv_status = NULL,
                approved_sv_by = NULL,
                approved_sv_date = NULL,
                approved_sv_ng_remarks = NULL
                WHERE id = ?";
            $resetStmt = $conn->prepare($resetSql);
            $resetStmt->bind_param("i", $id);
            $resetStmt->execute();
            $resetStmt->close();
        }

        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update record: ' . $conn->error]);
    }

    $stmt->close();
}

function checkDataChanges() {
    global $conn;
    
    // Get the last modification time from the database
    $sql = "SELECT MAX(GREATEST(
        COALESCE(verified_tech_date, '1970-01-01 00:00:00'),
        COALESCE(verified_lineleader_date, '1970-01-01 00:00:00'),
        COALESCE(approved_sv_date, '1970-01-01 00:00:00'),
        COALESCE(created_at, '1970-01-01 00:00:00')
    )) as last_change FROM tbl_repair_records";
    
    $result = $conn->query($sql);
    $lastChange = $result->fetch_assoc()['last_change'];
    
    // Get total record count
    $countSql = "SELECT COUNT(*) as total FROM tbl_repair_records";
    $countResult = $conn->query($countSql);
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => true,
        'data' => [
            'last_change' => $lastChange,
            'total_records' => $totalRecords,
            'timestamp' => time()
        ]
    ]);
}
?>

