<?php
session_start();

$adminID = $_SESSION["adminID"];

if (!isset($adminID)) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "placement";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle job deletion if requested
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $jobID = $_GET['delete'];
    
    // Check if admin has permission to delete this job
    $check_stmt = $conn->prepare("SELECT jobID FROM job WHERE jobID = ? AND adminID = ?");
    $check_stmt->bind_param("ii", $jobID, $adminID);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        // Delete the job
        $delete_stmt = $conn->prepare("DELETE FROM job WHERE jobID = ?");
        $delete_stmt->bind_param("i", $jobID);
        
        if ($delete_stmt->execute()) {
            $success_message = "Job deleted successfully.";
        } else {
            $error_message = "Error deleting job: " . $conn->error;
        }
        $delete_stmt->close();
    } else {
        $error_message = "You don't have permission to delete this job.";
    }
    $check_stmt->close();
}

// Fetch all jobs with company information
$jobs_query = "
    SELECT j.jobID, j.title, j.salary, 
           c.name AS company_name, c.industry,
           (SELECT COUNT(*) FROM application a WHERE a.jobID = j.jobID) AS application_count
    FROM job j
    JOIN company c ON j.companyID = c.companyID
    ORDER BY j.jobID DESC
";
$jobs_result = $conn->query($jobs_query);
$jobs = [];

if ($jobs_result) {
    while ($row = $jobs_result->fetch_assoc()) {
        $jobs[] = $row;
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
    <title>Manage Jobs | Admin</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-header h1 {
            margin: 0;
        }
        
        .btn-add {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        
        .btn-add:hover {
            background-color: #218838;
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
        
        .jobs-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .jobs-table th, 
        .jobs-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .jobs-table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .jobs-table tbody tr {
            border-bottom: 1px solid #f2f2f2;
        }
        
        .jobs-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .jobs-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .job-title {
            font-weight: bold;
            color: #333;
        }
        
        .company-name {
            color: #6c757d;
            font-size: 14px;
        }
        
        .job-salary {
            font-weight: bold;
            color: #28a745;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: opacity 0.3s;
        }
        
        .btn-action:hover {
            opacity: 0.9;
        }
        
        .btn-view {
            background-color: #17a2b8;
        }
        
        .btn-edit {
            background-color: #007bff;
        }
        
        .btn-delete {
            background-color: #dc3545;
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
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="page-header">
            <h1>Manage Jobs</h1>
            <a href="add_job.php" class="btn-add">+ Add New Job</a>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <h2>No jobs found</h2>
                <p>Start by adding your first job posting</p>
            </div>
        <?php else: ?>
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Company</th>
                        <th>Industry</th>
                        <th>Salary</th>
                        <th>Applications</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $jobs ?>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                            </td>
                            <td>
                                <div class="company-name"><?php echo htmlspecialchars($job['company_name']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($job['industry']); ?></td>
                            <td class="job-salary">₹<?php echo number_format($job['salary']); ?></td>
                            <td><?php echo $job['application_count']; ?></td>
                            <td>
                                <div class="actions">
                                    <a href="job_applications.php?id=<?php echo $job['jobID']; ?>" class="btn-action btn-view">Applications</a>
                                    <a href="edit_job.php?id=<?php echo $job['jobID']; ?>" class="btn-action btn-edit">Edit</a>
                                    <a href="manage_jobs.php?delete=<?php echo $job['jobID']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this job? This cannot be undone.')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>