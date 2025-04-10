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

// Get company counts
$company_count_query = "SELECT COUNT(*) FROM company";
$company_count_result = $conn->query($company_count_query);
$company_count = $company_count_result->fetch_row()[0];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="view-transition" content="same-origin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Admin Dashboard | SkillBridge</title>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="card mb-3">
            <div class="card-header">
                <h1 class="card-title">Welcome, <?php echo htmlspecialchars($name); ?></h1>
                <span>Administration Dashboard</span>
            </div>
            <div class="card-body">
                <p class="mb-2">Manage job listings, student applications, and company details from this central dashboard.</p>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card jobs">
                <div class="d-flex align-center">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $job_count; ?></div>
                        <div class="stat-title">Active Jobs</div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card students">
                <div class="d-flex align-center">
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $student_count; ?></div>
                        <div class="stat-title">Registered Students</div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card applications">
                <div class="d-flex align-center">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $application_count; ?></div>
                        <div class="stat-title">Total Applications</div>
                    </div>
                </div>
            </div>
            
            <div class="stat-card companies">
                <div class="d-flex align-center">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div>
                        <div class="stat-number"><?php echo $company_count; ?></div>
                        <div class="stat-title">Partner Companies</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-bolt mr-1"></i> Quick Actions</h2>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap" style="gap: 15px;">
                    <a href="manage_jobs.php" class="btn btn-primary">
                        <i class="fas fa-list-ul mr-1"></i> Manage Jobs
                    </a>
                    <a href="students.php" class="btn btn-info">
                        <i class="fas fa-users mr-1"></i> View Students
                    </a>
                    <a href="add_job.php" class="btn btn-success">
                        <i class="fas fa-plus-circle mr-1"></i> Add New Job
                    </a>
                    <a href="add_company.php" class="btn btn-warning">
                        <i class="fas fa-building mr-1"></i> Add Company
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-chart-line mr-1"></i> System Overview</h2>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap justify-between">
                    <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                        <h3><i class="fas fa-thumbs-up mr-1"></i> Placement Statistics</h3>
                        <div class="mb-2 mt-2">
                            <div class="badge badge-success mb-1">
                                <i class="fas fa-check-circle mr-1"></i> System Working Properly
                            </div>
                        </div>
                        <p>Monitor student applications, job postings, and placement rates in real-time.</p>
                    </div>
                    
                    <div style="flex: 1; min-width: 250px; margin-bottom: 20px;">
                        <h3><i class="fas fa-tasks mr-1"></i> Administrative Tasks</h3>
                        <ul style="list-style: none; padding-left: 0; margin-top: 10px;">
                            <li style="margin-bottom: 8px;"><i class="fas fa-check-circle mr-1" style="color: var(--success-color);"></i> Verify new student registrations</li>
                            <li style="margin-bottom: 8px;"><i class="fas fa-check-circle mr-1" style="color: var(--success-color);"></i> Review recent job applications</li>
                            <li style="margin-bottom: 8px;"><i class="fas fa-check-circle mr-1" style="color: var(--success-color);"></i> Update company information</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add animation to stat cards
        document.addEventListener("DOMContentLoaded", function() {
            const statCards = document.querySelectorAll('.stat-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = "1";
                        entry.target.style.transform = "translateY(0)";
                    }
                });
            }, { threshold: 0.1 });
            
            statCards.forEach((card, index) => {
                card.style.opacity = "0";
                card.style.transform = "translateY(20px)";
                card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
                card.style.transitionDelay = (index * 0.1) + "s";
                observer.observe(card);
            });
        });
    </script>
</body>

</html>
