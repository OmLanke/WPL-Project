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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $industry = $_POST['industry'];
    $website = $_POST['website'];
    $mobile = $_POST['mobile'];
    $email = $_POST['email'];
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Company name is required.";
    }
    
    if (empty($industry)) {
        $errors[] = "Industry is required.";
    }
    
    if (empty($website)) {
        $errors[] = "Website is required.";
    } elseif (!filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = "Please enter a valid website URL.";
    }
    
    if (empty($mobile)) {
        $errors[] = "Mobile number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must be 10 digits.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Check if company already exists with same contact details
    if (empty($errors)) {
        $check_stmt = $conn->prepare("SELECT companyID FROM company WHERE email = ? OR mobile = ?");
        $check_stmt->bind_param("ss", $email, $mobile);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $errors[] = "A company with the same email or mobile already exists.";
        }
        $check_stmt->close();
    }
    
    // If no errors, proceed with insertion
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO company (name, industry, website, mobile, email) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $industry, $website, $mobile, $email);
        
        if ($stmt->execute()) {
            $success = "Company added successfully!";
            // Clear form data after successful submission
            $name = $industry = $website = $mobile = $email = "";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get existing companies for reference
$companies_query = "SELECT companyID, name, industry FROM company ORDER BY name";
$companies_result = $conn->query($companies_query);
$companies = [];

if ($companies_result) {
    while ($row = $companies_result->fetch_assoc()) {
        $companies[] = $row;
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
    <title>Add Company | Admin</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
            }
        }
        
        .page-header {
            margin-bottom: 20px;
            grid-column: 1 / -1;
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
        
        .companies-container {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .companies-title {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            grid-column: 1 / -1;
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
        
        .companies-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .company-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .company-item:last-child {
            border-bottom: none;
        }
        
        .company-name {
            font-weight: bold;
            color: #333;
        }
        
        .company-industry {
            color: #6c757d;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="page-header">
            <h1>Add New Company</h1>
            <p>Add a company to the database to create job listings for it</p>
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
                    <label for="name">Company Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="industry">Industry</label>
                    <input type="text" id="industry" name="industry" class="form-control" value="<?php echo isset($industry) ? htmlspecialchars($industry) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="website">Website</label>
                    <input type="url" id="website" name="website" class="form-control" value="<?php echo isset($website) ? htmlspecialchars($website) : ''; ?>" placeholder="https://example.com" required>
                </div>

                <div class="form-group">
                    <label for="mobile">Contact Number</label>
                    <input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo isset($mobile) ? htmlspecialchars($mobile) : ''; ?>" pattern="[0-9]{10}" maxlength="10" placeholder="10-digit mobile number" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" placeholder="hr@example.com" required>
                </div>

                <div class="form-actions">
                    <a href="manage_jobs.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-submit">Add Company</button>
                </div>
            </form>
        </div>

        <div class="companies-container">
            <h2 class="companies-title">Existing Companies</h2>

            <?php if (empty($companies)): ?>
                <div class="empty-state">
                    <p>No companies added yet</p>
                </div>
            <?php else: ?>
                <ul class="companies-list">
                    <?php foreach ($companies as $company): ?>
                        <li class="company-item">
                            <div>
                                <div class="company-name"><?php echo htmlspecialchars($company['name']); ?></div>
                                <div class="company-industry"><?php echo htmlspecialchars($company['industry']); ?></div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>