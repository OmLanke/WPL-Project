<?php
session_start();

$adminID = $_SESSION["adminID"];

if (!isset($adminID)) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_jobs.php");
    exit();
}

$jobID = $_GET['id'];

$host = "localhost";
$username = "root";
$password = "";
$database = "placement";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get job details
$job_stmt = $conn->prepare("
    SELECT j.*, c.name AS company_name, c.industry 
    FROM job j
    JOIN company c ON j.companyID = c.companyID
    WHERE j.jobID = ?
");
$job_stmt->bind_param("i", $jobID);
$job_stmt->execute();
$job_result = $job_stmt->get_result();

if ($job_result->num_rows == 0) {
    // Job not found
    header("Location: manage_jobs.php");
    exit();
}

$job = $job_result->fetch_assoc();
$job_stmt->close();

// Handle application status update
$status_updated = false;
$update_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateStatus'])) {
    $applicationID = $_POST['applicationID'];
    $newStatus = $_POST['newStatus'];
    $notes = $_POST['notes'] ?? '';
    
    // Start a transaction
    $conn->begin_transaction();
    
    try {
        // Update application status
        $update_stmt = $conn->prepare("
            UPDATE application 
            SET status = ?, updated_date = NOW() 
            WHERE applicationID = ?
        ");
        $update_stmt->bind_param("si", $newStatus, $applicationID);
        $update_stmt->execute();
        
        // Record status change in history
        $history_stmt = $conn->prepare("
            INSERT INTO application_status_history 
            (applicationID, status, changed_by, notes) 
            VALUES (?, ?, ?, ?)
        ");
        $history_stmt->bind_param("isis", $applicationID, $newStatus, $adminID, $notes);
        $history_stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        $status_updated = true;
    } catch (Exception $e) {
        // Roll back the transaction on error
        $conn->rollback();
        $update_error = "Error updating status: " . $e->getMessage();
    }
}

// Export to CSV functionality
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $applications_query = "
        SELECT s.svvid, s.name, s.email, s.mobile, s.branch, s.programme, s.graduation, s.cgpa,
               a.status, a.applied_date, a.updated_date
        FROM application a
        JOIN student s ON a.studentID = s.studentID
        WHERE a.jobID = ?
        ORDER BY a.applicationID DESC
    ";
    
    $export_stmt = $conn->prepare($applications_query);
    $export_stmt->bind_param("i", $jobID);
    $export_stmt->execute();
    $export_result = $export_stmt->get_result();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="applications_job_' . $jobID . '_' . date('Y-m-d') . '.csv"');
    
    // Create a file pointer
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add header row
    fputcsv($output, [
        'SVV ID', 'Name', 'Email', 'Mobile', 'Branch', 'Programme', 
        'Graduation Year', 'CGPA', 'Application Status', 'Applied Date', 'Last Updated'
    ]);
    
    // Add data rows
    while ($row = $export_result->fetch_assoc()) {
        fputcsv($output, [
            $row['svvid'],
            $row['name'],
            $row['email'],
            $row['mobile'],
            $row['branch'],
            $row['programme'],
            $row['graduation'],
            $row['cgpa'],
            $row['status'],
            $row['applied_date'],
            $row['updated_date'] ? $row['updated_date'] : 'N/A'
        ]);
    }
    
    fclose($output);
    exit;
}

// Get all applications for this job
$applications_query = "
    SELECT a.applicationID, a.status, a.applied_date, a.updated_date,
           s.studentID, s.svvid, s.name, s.email, s.branch, s.programme, s.graduation, s.cgpa, s.resume_path
    FROM application a
    JOIN student s ON a.studentID = s.studentID
    WHERE a.jobID = ?
    ORDER BY a.applicationID DESC
";

$app_stmt = $conn->prepare($applications_query);
$app_stmt->bind_param("i", $jobID);
$app_stmt->execute();
$applications_result = $app_stmt->get_result();
$applications = [];

while ($row = $applications_result->fetch_assoc()) {
    $applications[] = $row;
}
$app_stmt->close();

// Get status counts
$status_counts = [
    'total' => count($applications),
    'applied' => 0,
    'interviewing' => 0,
    'rejected' => 0,
    'accepted' => 0,
    'other' => 0
];

foreach ($applications as $app) {
    $status = strtolower($app['status']);
    
    if ($status === 'applied') {
        $status_counts['applied']++;
    } elseif (strpos($status, 'interview') !== false) {
        $status_counts['interviewing']++;
    } elseif ($status === 'rejected') {
        $status_counts['rejected']++;
    } elseif ($status === 'accepted') {
        $status_counts['accepted']++;
    } else {
        $status_counts['other']++;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Applications for <?php echo htmlspecialchars($job['title']); ?> | Admin</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .job-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .job-title {
            margin: 0;
            font-size: 24px;
        }
        
        .job-company {
            color: #6c757d;
            margin: 5px 0 0 0;
        }
        
        .job-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            padding: 10px 20px;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 16px;
            white-space: nowrap;
            min-width: 160px; /* Increased minimum width */
            text-align: center;
            height: 44px; /* Fixed height to ensure vertical alignment */
        }
        
        .btn-action i {
            margin-right: 8px; /* Add spacing between icon and text */
        }
        
        .btn-edit {
            background-color: #007bff;
        }
        
        .btn-edit:hover {
            background-color: #0069d9;
        }
        
        .btn-export {
            background-color: #28a745;
        }
        
        .btn-export:hover {
            background-color: #218838;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-count {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        
        .stat-label {
            color: #6c757d;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        
        .stat-total .stat-count {
            color: #17a2b8;
        }
        
        .stat-applied .stat-count {
            color: #007bff;
        }
        
        .stat-interviewing .stat-count {
            color: #fd7e14;
        }
        
        .stat-accepted .stat-count {
            color: #28a745;
        }
        
        .stat-rejected .stat-count {
            color: #dc3545;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .applications-table th, 
        .applications-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .applications-table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .applications-table tbody tr {
            border-bottom: 1px solid #f2f2f2;
        }
        
        .applications-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .applications-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .student-name {
            font-weight: bold;
            color: #333;
        }
        
        .student-id {
            color: #6c757d;
            font-size: 13px;
        }
        
        .student-details {
            font-size: 14px;
            color: #495057;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            color: white;
        }
        
        .status-applied {
            background-color: #17a2b8;
        }
        
        .status-interviewing {
            background-color: #fd7e14;
        }
        
        .status-rejected {
            background-color: #dc3545;
        }
        
        .status-accepted {
            background-color: #28a745;
        }
        
        .status-other {
            background-color: #6c757d;
        }
        
        .date-applied {
            font-size: 14px;
            color: #6c757d;
        }
        
        .btn-download {
            padding: 6px 12px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            display: inline-block;
        }
        
        .btn-download:hover {
            background-color: #138496;
        }
        
        .btn-change-status {
            padding: 6px 12px;
            background-color: #fd7e14;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .btn-change-status:hover {
            background-color: #e76b00;
        }
        
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            animation: fadeIn 0.3s;
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.3s;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .modal-title {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #555;
        }
        
        .modal-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .form-group label {
            font-weight: bold;
            color: #555;
        }
        
        .form-control {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-cancel {
            padding: 8px 16px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        .btn-update {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-update:hover {
            background-color: #0069d9;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state h2 {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        @keyframes fadeIn {
            from {opacity: 0}
            to {opacity: 1}
        }
        
        @keyframes slideIn {
            from {transform: translateY(-50px); opacity: 0}
            to {transform: translateY(0); opacity: 1}
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="job-header">
            <div>
                <h1 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h1>
                <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['industry']); ?></p>
            </div>
            <div class="job-actions">
                <a href="edit_job.php?id=<?php echo $jobID; ?>" class="btn-action btn-edit">
                    <i class="fas fa-edit"></i> Edit Job
                </a>
                <a href="job_applications.php?id=<?php echo $jobID; ?>&export=csv" class="btn-action btn-export">
                    <i class="fas fa-file-export"></i> Export to CSV
                </a>
            </div>
        </div>

        <?php if ($status_updated): ?>
            <div class="alert alert-success">
                Application status updated successfully!
            </div>
        <?php endif; ?>

        <?php if (!empty($update_error)): ?>
            <div class="alert alert-danger">
                <?php echo $update_error; ?>
            </div>
        <?php endif; ?>

        <div class="stats-cards">
            <div class="stat-card stat-total">
                <p class="stat-count"><?php echo $status_counts['total']; ?></p>
                <p class="stat-label">Total Applications</p>
            </div>
            <div class="stat-card stat-applied">
                <p class="stat-count"><?php echo $status_counts['applied']; ?></p>
                <p class="stat-label">Applied</p>
            </div>
            <div class="stat-card stat-interviewing">
                <p class="stat-count"><?php echo $status_counts['interviewing']; ?></p>
                <p class="stat-label">Interviewing</p>
            </div>
            <div class="stat-card stat-accepted">
                <p class="stat-count"><?php echo $status_counts['accepted']; ?></p>
                <p class="stat-label">Accepted</p>
            </div>
            <div class="stat-card stat-rejected">
                <p class="stat-count"><?php echo $status_counts['rejected']; ?></p>
                <p class="stat-label">Rejected</p>
            </div>
        </div>

        <?php if (empty($applications)): ?>
            <div class="empty-state">
                <h2>No applications yet</h2>
                <p>There are no applications for this job yet</p>
            </div>
        <?php else: ?>
            <table class="applications-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Education</th>
                        <th>Status</th>
                        <th>Resume</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <div class="student-name"><?php echo htmlspecialchars($app['name']); ?></div>
                                <div class="student-id">SVV ID: <?php echo htmlspecialchars($app['svvid']); ?></div>
                                <div class="student-details"><?php echo htmlspecialchars($app['email']); ?></div>
                            </td>
                            <td>
                                <div class="student-details">
                                    <?php echo htmlspecialchars($app['programme']); ?>, <?php echo htmlspecialchars($app['branch']); ?><br>
                                    Graduation: <?php echo htmlspecialchars($app['graduation']); ?><br>
                                    CGPA: <?php echo htmlspecialchars($app['cgpa']); ?>
                                </div>
                            </td>
                            <td>
                                <?php
                                $statusClass = 'status-other';
                                $status = strtolower($app['status']);
                                
                                if ($status === 'applied') {
                                    $statusClass = 'status-applied';
                                } elseif (strpos($status, 'interview') !== false) {
                                    $statusClass = 'status-interviewing';
                                } elseif ($status === 'rejected') {
                                    $statusClass = 'status-rejected';
                                } elseif ($status === 'accepted') {
                                    $statusClass = 'status-accepted';
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars($app['status']); ?>
                                </span>
                                <div class="date-applied">
                                    Applied: <?php echo date('M d, Y', strtotime($app['applied_date'])); ?>
                                </div>
                                <?php if ($app['updated_date']): ?>
                                    <div class="date-applied">
                                        Updated: <?php echo date('M d, Y', strtotime($app['updated_date'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($app['resume_path'])): ?>
                                    <a href="../<?php echo htmlspecialchars($app['resume_path']); ?>" class="btn-download" download>Download</a>
                                <?php else: ?>
                                    <span>No Resume</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn-change-status" onclick="openStatusModal(<?php echo $app['applicationID']; ?>, '<?php echo htmlspecialchars($app['name']); ?>', '<?php echo htmlspecialchars($app['status']); ?>')">Change Status</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <!-- Status Change Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Update Application Status</h2>
                <span class="close">&times;</span>
            </div>
            <form id="statusForm" class="modal-form" method="POST" action="">
                <input type="hidden" name="applicationID" id="applicationID">
                
                <div class="form-group">
                    <label for="studentName">Student:</label>
                    <input type="text" id="studentName" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="currentStatus">Current Status:</label>
                    <input type="text" id="currentStatus" class="form-control" readonly>
                </div>
                
                <div class="form-group">
                    <label for="newStatus">New Status:</label>
                    <select id="newStatus" name="newStatus" class="form-control" required>
                        <option value="">Select new status</option>
                        <option value="Applied">Applied</option>
                        <option value="Screening">Screening</option>
                        <option value="Selected for OA">Selected for Online Assessment</option>
                        <option value="Selected for Interview">Selected for Interview</option>
                        <option value="Interview Round 1">Interview Round 1</option>
                        <option value="Interview Round 2">Interview Round 2</option>
                        <option value="Selected for GD">Selected for Group Discussion</option>
                        <option value="HR Round">HR Round</option>
                        <option value="Accepted">Accepted</option>
                        <option value="Rejected">Rejected</option>
                        <option value="On Hold">On Hold</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes (optional):</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Add notes about this status change"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" name="updateStatus" class="btn-update">Update Status</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functionality
        const modal = document.getElementById("statusModal");
        const closeBtn = document.getElementsByClassName("close")[0];
        
        function openStatusModal(applicationID, studentName, currentStatus) {
            document.getElementById("applicationID").value = applicationID;
            document.getElementById("studentName").value = studentName;
            document.getElementById("currentStatus").value = currentStatus;
            
            // Select the current status in the dropdown
            const newStatusDropdown = document.getElementById("newStatus");
            for (let i = 0; i < newStatusDropdown.options.length; i++) {
                if (newStatusDropdown.options[i].value === currentStatus) {
                    newStatusDropdown.selectedIndex = i;
                    break;
                }
            }
            
            modal.style.display = "block";
        }
        
        function closeStatusModal() {
            modal.style.display = "none";
        }
        
        // Close the modal when clicking the X
        closeBtn.onclick = closeStatusModal;
        
        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target === modal) {
                closeStatusModal();
            }
        };
    </script>
</body>

</html>