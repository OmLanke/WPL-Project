<?php
session_start();

setcookie("userType", "admin", time() + 3600, "/");

$adminID = $_SESSION["adminID"];

if (!isset($adminID)) {
    header("Location: login.php");
    setcookie("userType", "", time() - 3600, "/");
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

$stmt = $conn->prepare("SELECT name FROM admin WHERE adminID = ?");
$stmt->bind_param("i", $adminID);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name);
    $stmt->fetch();
} else {
    header("Location: login.php");
    exit();
}
$stmt->close();

// Get job counts 
$job_count_query = "SELECT COUNT(*) FROM job";
$job_count_result = $conn->query($job_count_query);
$job_count = $job_count_result->fetch_row()[0];

// Get student counts
$student_count_query = "SELECT COUNT(*) FROM student";
$student_count_result = $conn->query($student_count_query);
$student_count = $student_count_result->fetch_row()[0];

// Get application counts
$application_count_query = "SELECT COUNT(*) FROM application";
$application_count_result = $conn->query($application_count_query);
$application_count = $application_count_result->fetch_row()[0];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title>Admin Dashboard | SkillBridge</title>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .welcome-section {
            margin-bottom: 30px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h2 {
            font-size: 40px;
            margin: 0;
            color: #007bff;
        }
        
        .stat-card p {
            margin: 10px 0 0;
            color: #666;
        }
        
        .actions-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }
        
        .action-button:hover {
            background: #0056b3;
        }
        
        .action-button.secondary {
            background: #6c757d;
        }
        
        .action-button.secondary:hover {
            background: #5a6268;
        }
        
        .action-button.success {
            background: #28a745;
        }
        
        .action-button.success:hover {
            background: #218838;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($name); ?></h1>
            <p>Manage jobs, students, and applications</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <h2><?php echo $job_count; ?></h2>
                <p>Active Jobs</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $student_count; ?></h2>
                <p>Registered Students</p>
            </div>
            <div class="stat-card">
                <h2><?php echo $application_count; ?></h2>
                <p>Total Applications</p>
            </div>
        </div>
        
        <div class="actions-section">
            <h2 class="section-title">Quick Actions</h2>
            <div class="action-buttons">
                <a href="manage_jobs.php" class="action-button">Manage Jobs</a>
                <a href="students.php" class="action-button secondary">View Students</a>
                <a href="add_job.php" class="action-button success">Add New Job</a>
            </div>
        </div>
    </div>
</body>

</html>
