<?php
session_start();

$studentID = $_SESSION["studentID"];

if (!isset($studentID)) {
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

// Fetch student's applications
$applications_query = "
    SELECT a.applicationID, a.status, a.applied_date, 
           j.jobID, j.title, j.salary,
           c.name as company_name, c.industry
    FROM application a
    JOIN job j ON a.jobID = j.jobID
    JOIN company c ON j.companyID = c.companyID
    WHERE a.studentID = ?
    ORDER BY a.applied_date DESC
";

$stmt = $conn->prepare($applications_query);
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$applications = [];

while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications | SkillBridge</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="card mb-3">
            <div class="card-header">
                <h1 class="card-title"><i class="fas fa-clipboard-list mr-1"></i> My Applications</h1>
                <span class="badge badge-info">
                    Total: <?php echo count($applications); ?>
                </span>
            </div>
        </div>

        <?php if (empty($applications)): ?>
            <div class="card text-center p-2">
                <div class="mb-2">
                    <i class="fas fa-folder-open" style="font-size: 3rem; color: var(--gray-text); margin: 20px 0;"></i>
                </div>
                <h2>You haven't applied to any jobs yet</h2>
                <p class="mb-2">Browse available opportunities and submit your application</p>
                <a href="./" class="btn btn-primary mt-2">
                    <i class="fas fa-search mr-1"></i> Browse Jobs
                </a>
            </div>
        <?php else: ?>
            <div class="job-listing">
                <?php foreach ($applications as $app): ?>
                    <div class="job-card">
                        <div class="job-card-header">
                            <h3 style="color: white;"><?php echo htmlspecialchars($app['title']); ?></h3>
                        </div>
                        <div class="job-card-body">
                            <p class="company-name">
                                <i class="fas fa-building job-detail-icon"></i>
                                <?php echo htmlspecialchars($app['company_name']); ?> - <?php echo htmlspecialchars($app['industry']); ?>
                            </p>
                            
                            <div class="job-details">
                                <div class="job-detail-item">
                                    <i class="fas fa-calendar-alt job-detail-icon"></i>
                                    <span>Applied: <?php echo date('d M Y', strtotime($app['applied_date'])); ?></span>
                                </div>
                                
                                <div class="job-detail-item">
                                    <i class="fas fa-money-bill-wave job-detail-icon"></i>
                                    <span>₹<?php echo number_format($app['salary']); ?>/year</span>
                                </div>
                                
                                <div class="job-detail-item">
                                    <?php
                                    $statusIcon = 'fa-hourglass-half';
                                    $statusClass = 'badge-info';
                                    
                                    if (strpos(strtolower($app['status']), 'interview') !== false) {
                                        $statusIcon = 'fa-user-tie';
                                        $statusClass = 'badge-warning';
                                    } elseif (strtolower($app['status']) === 'rejected') {
                                        $statusIcon = 'fa-times-circle';
                                        $statusClass = 'badge-danger';
                                    } elseif (strtolower($app['status']) === 'accepted') {
                                        $statusIcon = 'fa-check-circle';
                                        $statusClass = 'badge-success';
                                    }
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <i class="fas <?php echo $statusIcon; ?> mr-1"></i>
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="job-card-footer">
                            <a href="job_details.php?id=<?php echo $app['jobID']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye mr-1"></i> View Details
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
            
            jobCards.forEach((card, index) => {
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