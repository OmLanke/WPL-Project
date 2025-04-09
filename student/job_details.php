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

// Get job ID from URL parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ./");
    exit();
}

$jobID = $_GET['id'];

// Fetch job details
$job_stmt = $conn->prepare("
    SELECT j.jobID, j.title, j.description, j.salary, 
           c.name as company_name, c.industry, c.website
    FROM job j
    JOIN company c ON j.companyID = c.companyID
    WHERE j.jobID = ?
");
$job_stmt->bind_param("i", $jobID);
$job_stmt->execute();
$job_result = $job_stmt->get_result();

if ($job_result->num_rows == 0) {
    // Job not found
    header("Location: ./");
    exit();
}

$job = $job_result->fetch_assoc();
$job_stmt->close();

// Check if student has already applied
$applied_stmt = $conn->prepare("
    SELECT applicationID, status FROM application 
    WHERE jobID = ? AND studentID = ?
");
$applied_stmt->bind_param("ii", $jobID, $studentID);
$applied_stmt->execute();
$applied_result = $applied_stmt->get_result();
$already_applied = $applied_result->num_rows > 0;
$application = $already_applied ? $applied_result->fetch_assoc() : null;
$applied_stmt->close();

// Handle job application
$application_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['apply'])) {
    if (!$already_applied) {
        $apply_stmt = $conn->prepare("
            INSERT INTO application (jobID, studentID, status, applied_date) 
            VALUES (?, ?, 'Applied', NOW())
        ");
        $apply_stmt->bind_param("ii", $jobID, $studentID);
        
        if ($apply_stmt->execute()) {
            $application_message = "You have successfully applied for this job!";
            $already_applied = true;
            $application = [
                'applicationID' => $apply_stmt->insert_id,
                'status' => 'Applied'
            ];
        } else {
            $application_message = "Error applying for job: " . $apply_stmt->error;
        }
        $apply_stmt->close();
    } else {
        $application_message = "You have already applied for this job.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($job['title']); ?> | Job Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .job-details-container {
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .job-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .job-company-logo {
            width: 80px;
            height: 80px;
            margin-right: 20px;
            object-fit: contain;
        }
        .job-title {
            margin: 0 0 5px 0;
            color: #333;
        }
        .job-company {
            font-size: 18px;
            color: #666;
            margin: 0;
        }
        .job-section {
            margin-bottom: 20px;
        }
        .job-section h3 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .job-description {
            line-height: 1.6;
            color: #444;
            white-space: pre-line;
        }
        .job-meta {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .job-meta p {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .job-meta span {
            font-weight: bold;
            color: #555;
        }
        .btn-apply {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        .btn-apply:hover {
            background: #218838;
        }
        .btn-applied {
            background: #6c757d;
            cursor: default;
        }
        .btn-applied:hover {
            background: #6c757d;
        }
        .application-message {
            text-align: center;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .application-status {
            text-align: center;
            padding: 10px;
            background-color: #e2f3f5;
            border-radius: 5px;
            margin-top: 10px;
            font-weight: bold;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include "includes/navbar.php"; ?>
    
    <div class="job-details-container">
        <div class="job-header">
            <img class="job-company-logo" src="https://via.placeholder.com/80?text=<?php echo htmlspecialchars(substr($job['company_name'], 0, 2)); ?>" alt="<?php echo htmlspecialchars($job['company_name']); ?> Logo">
            <div>
                <h1 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h1>
                <p class="job-company"><?php echo htmlspecialchars($job['company_name']); ?></p>
            </div>
        </div>
        
        <div class="job-meta">
            <p><span>Industry:</span> <?php echo htmlspecialchars($job['industry']); ?></p>
            <p><span>Salary:</span> ₹<?php echo number_format($job['salary']); ?> per annum</p>
            <p><span>Website:</span> <a href="<?php echo htmlspecialchars($job['website']); ?>" target="_blank"><?php echo htmlspecialchars($job['website']); ?></a></p>
        </div>
        
        <div class="job-section">
            <h3>Job Description</h3>
            <div class="job-description">
                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
            </div>
        </div>
        
        <?php if ($application_message): ?>
            <div class="application-message <?php echo strpos($application_message, 'successfully') !== false ? 'message-success' : 'message-error'; ?>">
                <?php echo $application_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($already_applied): ?>
            <div class="application-status">
                Application Status: <?php echo htmlspecialchars($application['status']); ?>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <button type="submit" name="apply" class="btn-apply">Apply Now</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>