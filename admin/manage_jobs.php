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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Manage Jobs | SkillBridge Admin</title>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="card mb-3">
            <div class="card-header">
                <h1 class="card-title"><i class="fas fa-briefcase mr-1"></i> Manage Jobs</h1>
                <a href="add_job.php" class="btn btn-success">
                    <i class="fas fa-plus-circle mr-1"></i> Add New Job
                </a>
            </div>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle mr-1"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($jobs)): ?>
            <div class="card text-center p-2">
                <div class="mb-2">
                    <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--gray-text); margin: 20px 0;"></i>
                </div>
                <h2>No jobs found</h2>
                <p class="mb-2">Start by adding your first job posting</p>
                <a href="add_job.php" class="btn btn-success mt-2">
                    <i class="fas fa-plus-circle mr-1"></i> Add Job
                </a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Industry</th>
                                <th>Salary</th>
                                <th class="text-center">Applications</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobs as $job): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--primary-color);">
                                            <?php echo htmlspecialchars($job['title']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="color: var(--gray-text);">
                                            <?php echo htmlspecialchars($job['company_name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($job['industry']); ?></td>
                                    <td style="font-weight: 500; color: var(--success-color);">
                                        ₹<?php echo number_format($job['salary']); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            <?php echo $job['application_count']; ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex justify-between">
                                            <a href="job_applications.php?id=<?php echo $job['jobID']; ?>" class="btn btn-info btn-sm mr-1" title="View Applications">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="edit_job.php?id=<?php echo $job['jobID']; ?>" class="btn btn-primary btn-sm mr-1" title="Edit Job">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage_jobs.php?delete=<?php echo $job['jobID']; ?>" class="btn btn-danger btn-sm" 
                                               onclick="return confirm('Are you sure you want to delete this job? This action cannot be undone.')" title="Delete Job">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }
        
        .alert-danger {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        
        .table-responsive {
            overflow-x: auto;
            width: 100%;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        /* Added styles for btn-info */
        .btn-info {
            background-color: var(--info-color);
            color: white;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }
        
        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }
    </style>
</body>

</html>