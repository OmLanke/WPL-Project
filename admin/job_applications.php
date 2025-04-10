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
            color: var(--cool-gray);
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
        }
        
        .btn-edit {
            background-color: var(--trust-blue);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            background-color: var(--dark-link-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 74, 156, 0.3);
        }
        
        .btn-export {
            background-color: var(--light-brown);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-export:hover {
            background-color: var(--dark-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(162, 87, 35, 0.3);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }
        
        .stat-count {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 5px;
            line-height: 1;
        }
        
        .stat-label {
            color: var(--cool-gray);
            font-size: 0.9rem;
        }
        
        .stat-total .stat-count {
            color: var(--primary-color);
        }
        
        .stat-applied .stat-count {
            color: var(--trust-blue);
        }
        
        .stat-interviewing .stat-count {
            color: var(--yellow);
        }
        
        .stat-accepted .stat-count {
            color: var(--light-brown);
        }
        
        .stat-rejected .stat-count {
            color: var(--secondary-color);
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(162, 87, 35, 0.1);
            color: var(--light-brown);
            border-left: 4px solid var(--light-brown);
        }
        
        .alert-danger {
            background-color: rgba(237, 28, 36, 0.1);
            color: var(--secondary-color);
            border-left: 4px solid var(--secondary-color);
        }
        
        .applications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .applications-table th, 
        .applications-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .applications-table th {
            background-color: rgba(183, 32, 46, 0.1);
            color: var(--primary-color);
            font-weight: 600;
            white-space: nowrap;
        }
        
        .applications-table tbody tr {
            transition: background-color 0.3s;
        }
        
        .applications-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .applications-table tbody tr:hover {
            background-color: rgba(183, 32, 46, 0.03);
        }
        
        .student-name {
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .student-id {
            font-size: 0.85rem;
            color: var(--cool-gray);
            display: block;
            margin-top: 2px;
        }
        
        .student-details {
            font-size: 0.9rem;
            margin-top: 5px;
            color: var(--cool-gray);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
        }
        
        .status-applied {
            background-color: rgba(0, 74, 156, 0.15);
            color: var(--trust-blue);
        }
        
        .status-interviewing {
            background-color: rgba(255, 203, 5, 0.15);
            color: var(--dark-brown);
        }
        
        .status-rejected {
            background-color: rgba(237, 28, 36, 0.15);
            color: var(--secondary-color);
        }
        
        .status-accepted {
            background-color: rgba(162, 87, 35, 0.15);
            color: var(--light-brown);
        }
        
        .status-other {
            background-color: rgba(88, 89, 91, 0.15);
            color: var(--cool-gray);
        }
        
        .date-applied {
            font-size: 0.85rem;
            color: var(--cool-gray);
        }
        
        .btn-download {
            background-color: var(--trust-blue);
            color: var(--white);
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-download:hover {
            background-color: var(--dark-link-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 74, 156, 0.3);
        }
        
        .btn-change-status {
            background-color: var(--orange);
            color: var(--white);
            border: none;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-change-status:hover {
            background-color: var(--light-brown);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(245, 130, 32, 0.3);
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
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            animation: fadeIn 0.3s ease-out;
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            max-width: 450px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease-out;
            position: relative;
            transform: translateY(0);
        }
        
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.2rem;
            margin: 0;
        }
        
        .close {
            color: var(--cool-gray);
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }
        
        .close:hover {
            color: var(--primary-color);
        }
        
        .modal-form {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-gray);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: border-color 0.3s, box-shadow 0.3s;
            font-family: inherit;
            font-size: 0.95rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(183, 32, 46, 0.1);
            outline: none;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 0 20px 20px;
        }
        
        .btn-cancel {
            background-color: #e9e9e9;
            color: var(--dark-gray);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            font-weight: 500;
        }
        
        .btn-cancel:hover {
            background-color: #dbdbdb;
        }
        
        .btn-update {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            font-weight: 500;
        }
        
        .btn-update:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(183, 32, 46, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state h2 {
            color: var(--cool-gray);
            margin-bottom: 15px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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