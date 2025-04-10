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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Dashboard | SkillBridge</title>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="card mb-3">
            <div class="card-header">
                <h1 class="card-title">Welcome, <?php echo htmlspecialchars($name); ?></h1>
                <div class="badge badge-info">
                    <i class="fas fa-briefcase mr-1"></i>
                    <span><?php echo $application_count; ?> Applications</span>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-2">Find and apply for the perfect job opportunity that matches your skills and career goals.</p>
                <a href="applications.php" class="btn btn-primary">
                    <i class="fas fa-list-check mr-1"></i> View My Applications
                </a>
            </div>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="card text-center p-2">
                <div class="mb-2">
                    <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--gray-text); margin: 20px 0;"></i>
                </div>
                <h2>No jobs available at the moment</h2>
                <p class="mb-2">Please check back later for new opportunities</p>
            </div>
        <?php else: ?>
            <h2 class="mb-2"><i class="fas fa-briefcase mr-1"></i> Available Opportunities</h2>
            <div class="job-listing">
                <?php foreach ($jobs as $job): ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        </div>
                        <div class="job-card-body">
                            <p class="company-name">
                                <i class="fas fa-building job-detail-icon"></i>
                                <?php echo htmlspecialchars($job['company_name']); ?> - <?php echo htmlspecialchars($job['industry']); ?>
                            </p>
                            
                            <p class="job-details">
                                <div class="job-detail-item">
                                    <i class="fas fa-money-bill-wave job-detail-icon"></i>
                                    <span>₹<?php echo number_format($job['salary']); ?>/year</span>
                                </div>
                                
                                <div class="job-detail-item">
                                    <i class="fas fa-align-left job-detail-icon"></i>
                                    <span><?php echo htmlspecialchars(substr($job['description'], 0, 100)) . '...'; ?></span>
                                </div>
                            </p>
                        </div>
                        <div class="job-card-footer">
                            <a href="job_details.php?id=<?php echo $job['jobID']; ?>" class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add animation when job cards enter the viewport
        document.addEventListener("DOMContentLoaded", function() {
            const jobCards = document.querySelectorAll('.job-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = "1";
                        entry.target.style.transform = "translateY(0)";
                    }
                });
            }, { threshold: 0.1 });
            
            jobCards.forEach(card => {
                card.style.opacity = "0";
                card.style.transform = "translateY(20px)";
                card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
                observer.observe(card);
            });
        });
    </script>
</body>

</html>
