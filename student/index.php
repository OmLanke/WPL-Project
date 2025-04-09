<?php
session_start();

setcookie("userType", "student", time() + 3600, "/");

$studentID = $_SESSION["studentID"];

if (!isset($studentID)) {
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

// Get student information
$stmt = $conn->prepare("SELECT name, resume_path FROM student WHERE studentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $resume_path);
    $stmt->fetch();
} else {
    $error = "Student not found.";
    header("Location: login.php");
    exit();
}
$stmt->close();

// Get available jobs
$jobs_query = "
    SELECT j.jobID, j.title, j.description, j.salary, 
           c.name as company_name, c.industry, c.companyID
    FROM job j
    JOIN company c ON j.companyID = c.companyID
    ORDER BY j.jobID DESC
";
$jobs_result = $conn->query($jobs_query);
$jobs = [];

while ($row = $jobs_result->fetch_assoc()) {
    $jobs[] = $row;
}

// Get a count of student's applications
$app_count_stmt = $conn->prepare("SELECT COUNT(*) FROM application WHERE studentID = ?");
$app_count_stmt->bind_param("i", $studentID);
$app_count_stmt->execute();
$app_count_stmt->bind_result($application_count);
$app_count_stmt->fetch();
$app_count_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="view-transition" content="same-origin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Job Listings | SkillBridge</title>
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .welcome-text h1 {
            margin: 0;
            color: #333;
        }
        .welcome-text p {
            margin: 5px 0 0;
            color: #666;
        }
        .application-stats {
            background: #fff;
            border-radius: 8px;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .application-stats h3 {
            margin: 0 0 5px;
            color: #007bff;
        }
        .application-stats a {
            color: #007bff;
            text-decoration: none;
        }
        .application-stats a:hover {
            text-decoration: underline;
        }
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .job-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .job-card:hover {
            transform: translateY(-5px);
        }
        .job-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .job-company {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .job-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        .job-body {
            padding: 15px;
        }
        .job-description {
            color: #555;
            font-size: 14px;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
        }
        .job-salary {
            color: #28a745;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .job-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        .btn-view-job {
            display: inline-block;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .btn-view-job:hover {
            background-color: #0056b3;
            text-decoration: none;
        }
        .no-jobs {
            padding: 50px;
            text-align: center;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .no-jobs h2 {
            color: #333;
            margin-bottom: 10px;
        }
        .no-jobs p {
            color: #666;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="dashboard-container">
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars($name); ?></h1>
                <p>Find and apply for the perfect job opportunity</p>
            </div>
            <div class="application-stats">
                <h3><?php echo $application_count; ?></h3>
                <a href="applications.php">Active Applications</a>
            </div>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="no-jobs">
                <h2>No jobs available at the moment</h2>
                <p>Please check back later for new opportunities</p>
            </div>
        <?php else: ?>
            <div class="jobs-grid">
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <div class="job-header">
                            <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['industry']); ?></p>
                            <h3 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="job-body">
                            <p class="job-description"><?php echo htmlspecialchars(substr($job['description'], 0, 150)) . '...'; ?></p>
                            <p class="job-salary">₹<?php echo number_format($job['salary']); ?>/year</p>
                        </div>
                        <div class="job-footer">
                            <a href="job_details.php?id=<?php echo $job['jobID']; ?>" class="btn-view-job">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
