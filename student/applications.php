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
    <title>My Applications</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .applications-container {
            max-width: 1000px;
            margin: 20px auto;
            padding: 0 15px;
        }
        .page-title {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .applications-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .application-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .application-card:hover {
            transform: translateY(-5px);
        }
        .application-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .application-company {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .application-title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }
        .application-body {
            padding: 15px;
        }
        .application-details {
            margin-bottom: 15px;
        }
        .application-details p {
            margin: 5px 0;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }
        .application-details span {
            font-weight: bold;
            color: #555;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            color: white;
        }
        .status-applied {
            background-color: #17a2b8;
        }
        .status-interviewing {
            background-color: #ffc107;
            color: #212529;
        }
        .status-rejected {
            background-color: #dc3545;
        }
        .status-accepted {
            background-color: #28a745;
        }
        .application-footer {
            padding: 15px;
            border-top: 1px solid #eee;
            text-align: center;
        }
        .btn-view {
            display: inline-block;
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        .btn-view:hover {
            background-color: #0056b3;
            text-decoration: none;
        }
        .no-applications {
            text-align: center;
            padding: 40px 0;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="applications-container">
        <h1 class="page-title">My Applications</h1>

        <?php if (empty($applications)): ?>
            <div class="no-applications">
                <h3>You haven't applied to any jobs yet</h3>
                <p>Browse available jobs and submit your applications</p>
                <a href="./" class="btn-view">Browse Jobs</a>
            </div>
        <?php else: ?>
            <div class="applications-list">
                <?php foreach ($applications as $app): ?>
                    <div class="application-card">
                        <div class="application-header">
                            <p class="application-company"><?php echo htmlspecialchars($app['company_name']); ?> - <?php echo htmlspecialchars($app['industry']); ?></p>
                            <h3 class="application-title"><?php echo htmlspecialchars($app['title']); ?></h3>
                        </div>
                        <div class="application-body">
                            <div class="application-details">
                                <p>
                                    <span>Status:</span>
                                    <?php
                                    $statusClass = 'status-applied';
                                    
                                    if (strpos(strtolower($app['status']), 'interview') !== false) {
                                        $statusClass = 'status-interviewing';
                                    } elseif (strtolower($app['status']) === 'rejected') {
                                        $statusClass = 'status-rejected';
                                    } elseif (strtolower($app['status']) === 'accepted') {
                                        $statusClass = 'status-accepted';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars($app['status']); ?>
                                    </span>
                                </p>
                                <p><span>Applied on:</span> <?php echo date('d M Y', strtotime($app['applied_date'])); ?></p>
                                <p><span>Salary:</span> ₹<?php echo number_format($app['salary']); ?>/year</p>
                            </div>
                        </div>
                        <div class="application-footer">
                            <a href="job_details.php?id=<?php echo $app['jobID']; ?>" class="btn-view">View Job Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>