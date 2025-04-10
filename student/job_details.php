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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 0 15px;
        }
        
        .job-details-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .job-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 30px;
            color: var(--light-text);
            position: relative;
        }
        
        .job-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .company-logo {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .job-title-container {
            flex: 1;
        }
        
        .job-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: white;
        }
        
        .company-name {
            font-size: 1.1rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .job-body {
            padding: 30px;
        }
        
        .job-meta {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--light-bg);
            border-radius: var(--border-radius);
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 0.9rem;
            color: var(--gray-text);
            margin-bottom: 5px;
        }
        
        .meta-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: var(--dark-text);
        }
        
        .meta-value a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .meta-value a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .job-description {
            line-height: 1.7;
            color: var(--dark-text);
            font-size: 1rem;
            white-space: pre-line;
        }
        
        .action-container {
            margin-top: 30px;
            text-align: center;
        }
        
        .btn-apply {
            display: inline-block;
            padding: 12px 30px;
            background: var(--success-color);
            color: white;
            font-size: 1rem;
            font-weight: 500;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }
        
        .btn-apply:hover {
            background: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(46, 204, 113, 0.4);
        }
        
        .btn-applied {
            background: var(--gray-text);
            cursor: default;
        }
        
        .btn-applied:hover {
            background: var(--gray-text);
            transform: none;
            box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
        }
        
        .application-message {
            margin: 20px 0;
            padding: 15px;
            border-radius: var(--border-radius);
            text-align: center;
            font-size: 1rem;
        }
        
        .message-success {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(46, 204, 113, 0.2);
        }
        
        .message-error {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }
        
        .application-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 30px;
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--info-color);
            font-weight: 500;
            margin-top: 15px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            color: var(--gray-text);
            text-decoration: none;
            margin-bottom: 15px;
            transition: color 0.3s;
        }
        
        .back-link:hover {
            color: var(--primary-color);
        }
        
        @media (max-width: 600px) {
            .job-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .job-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include "includes/navbar.php"; ?>
    
    <div class="container">
        <a href="./" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Jobs
        </a>
        
        <div class="job-details-card">
            <div class="job-header">
                <div class="job-header-content">
                    <div class="company-logo">
                        <?php echo htmlspecialchars(substr($job['company_name'], 0, 2)); ?>
                    </div>
                    <div class="job-title-container">
                        <h1 class="job-title"><?php echo htmlspecialchars($job['title']); ?></h1>
                        <div class="company-name">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($job['company_name']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="job-body">
                <div class="job-meta">
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-industry"></i> Industry
                        </div>
                        <div class="meta-value">
                            <?php echo htmlspecialchars($job['industry']); ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-money-bill-wave"></i> Annual Salary
                        </div>
                        <div class="meta-value">
                            ₹<?php echo number_format($job['salary']); ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <div class="meta-label">
                            <i class="fas fa-globe"></i> Website
                        </div>
                        <div class="meta-value">
                            <a href="<?php echo htmlspecialchars($job['website']); ?>" target="_blank" rel="noopener">
                                <?php echo htmlspecialchars($job['website']); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <h2 class="section-title">Job Description</h2>
                <div class="job-description">
                    <?php echo nl2br(htmlspecialchars($job['description'])); ?>
                </div>
                
                <div class="action-container">
                    <?php if ($application_message): ?>
                        <div class="application-message <?php echo strpos($application_message, 'successfully') !== false ? 'message-success' : 'message-error'; ?>">
                            <?php echo $application_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($already_applied): ?>
                        <div class="application-status">
                            <i class="fas fa-clipboard-check"></i>
                            Application Status: <?php echo htmlspecialchars($application['status']); ?>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <button type="submit" name="apply" class="btn-apply">
                                <i class="fas fa-paper-plane"></i> Apply Now
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>