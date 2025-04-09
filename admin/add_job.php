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

// Get companies for dropdown
$companies_query = "SELECT companyID, name FROM company ORDER BY name";
$companies_result = $conn->query($companies_query);
$companies = [];

if ($companies_result) {
    while ($row = $companies_result->fetch_assoc()) {
        $companies[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $companyID = $_POST['companyID'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $salary = $_POST['salary'];
    
    // Validate input
    $errors = [];
    
    if (empty($companyID)) {
        $errors[] = "Company is required.";
    }
    
    if (empty($title)) {
        $errors[] = "Job title is required.";
    }
    
    if (empty($description)) {
        $errors[] = "Job description is required.";
    }
    
    if (empty($salary) || !is_numeric($salary)) {
        $errors[] = "Valid salary is required.";
    }
    
    // If no errors, proceed with insertion
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO job (companyID, adminID, title, description, salary) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd", $companyID, $adminID, $title, $description, $salary);
        
        if ($stmt->execute()) {
            $success = "Job added successfully!";
            // Clear form data after successful submission
            $companyID = $title = $description = $salary = "";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
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
    <title>Add New Job | Admin</title>
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            margin-bottom: 20px;
        }
        
        .page-header h1 {
            margin: 0 0 10px 0;
        }
        
        .form-container {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        textarea.form-control {
            height: 150px;
            resize: vertical;
        }
        
        .btn-submit {
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #0069d9;
        }
        
        .btn-cancel {
            padding: 12px 24px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-start;
            margin-top: 20px;
        }
        
        .errors-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .errors-list li {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="page-header">
            <h1>Add New Job</h1>
            <p>Create a new job posting to attract potential candidates</p>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="errors-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="companyID">Company</label>
                    <?php if (empty($companies)): ?>
                        <p>No companies available. <a href="add_company.php">Add a company first</a>.</p>
                    <?php else: ?>
                        <select id="companyID" name="companyID" class="form-control" required>
                            <option value="">Select a company</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['companyID']; ?>" <?php if (isset($companyID) && $companyID == $company['companyID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="title">Job Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Job Description</label>
                    <textarea id="description" name="description" class="form-control" required><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="salary">Salary (per annum in ₹)</label>
                    <input type="number" id="salary" name="salary" class="form-control" value="<?php echo isset($salary) ? htmlspecialchars($salary) : ''; ?>" required>
                </div>

                <div class="form-actions">
                    <a href="manage_jobs.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">Save Job</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>