<?php
session_start();

$host = "localhost";
$username = "root";
$password = "";
$database = "placement";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch branch options from the database
$branch_query = "SELECT branch FROM branch ORDER BY branch";
$branch_result = $conn->query($branch_query);
$branches = [];
if ($branch_result && $branch_result->num_rows > 0) {
    while ($row = $branch_result->fetch_assoc()) {
        $branches[] = $row['branch'];
    }
}

// Fetch gender options from the database
$gender_query = "SELECT gender FROM gender ORDER BY gender";
$gender_result = $conn->query($gender_query);
$genders = [];
if ($gender_result && $gender_result->num_rows > 0) {
    while ($row = $gender_result->fetch_assoc()) {
        $genders[] = $row['gender'];
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $svvid = $_POST["svvid"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $name = $_POST["name"];
    $gender = $_POST["gender"];
    $mobile = $_POST["mobile"];
    $email = $_POST["email"];
    $branch = $_POST["branch"];
    $programme = $_POST["programme"];
    $graduation = $_POST["graduation"];
    $cgpa = $_POST["cgpa"];
    
    // Resume upload handling
    $resume_path = "";
    $upload_error = "";
    
    if(isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
        $allowed_ext = ['pdf', 'doc', 'docx'];
        $file_name = $_FILES['resume']['name'];
        $file_size = $_FILES['resume']['size'];
        $file_tmp = $_FILES['resume']['tmp_name'];
        $tmp = explode('.', $file_name);
        $file_ext = strtolower(end($tmp));
        
        // Validate file extension
        if(in_array($file_ext, $allowed_ext)) {
            // Validate file size (5MB max)
            if($file_size <= 5000000) {
                $resume_new_name = $svvid . '_resume_' . time() . '.' . $file_ext;
                // Use absolute path to ensure upload directory is correctly located
                $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/placement/uploads/resumes/';
                // Check if directory exists, create if it doesn't
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $resume_path = $upload_dir . $resume_new_name;
                
                if(move_uploaded_file($file_tmp, $resume_path)) {
                    // Store only the relative path in the database
                    $resume_path = 'uploads/resumes/' . $resume_new_name;
                } else {
                    $upload_error = "Failed to upload resume. Please try again. Error: " . error_get_last()['message'];
                }
            } else {
                $upload_error = "Resume file too large. Maximum size is 5MB.";
            }
        } else {
            $upload_error = "Invalid file format. Allowed formats: PDF, DOC, DOCX.";
        }
    } else if ($_FILES['resume']['error'] != 4) { // Error 4 is "no file uploaded"
        $upload_error = "Resume upload error: " . $_FILES['resume']['error'];
    } else {
        $upload_error = "Resume is required. Please upload your resume.";
    }
    
    if($upload_error) {
        $error = $upload_error;
    } else if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $check_stmt = $conn->prepare(
            "SELECT svvid FROM student WHERE svvid = ?"
        );
        $check_stmt->bind_param("s", $svvid);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "SVV ID already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO student (svvid, password_hash, name, gender, mobile, email, branch, programme, graduation, cgpa, resume_path)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "sssssssssss",
                $svvid,
                $hashed_password,
                $name,
                $gender,
                $mobile,
                $email,
                $branch,
                $programme,
                $graduation,
                $cgpa,
                $resume_path
            );

            if ($stmt->execute()) {
                $_SESSION["studentID"] = $conn->insert_id;
                header("Location: ./");
                exit();
            } else {
                $error = "MySQL Error: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | SkillBridge</title>
    <link rel="stylesheet" href="signup.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Additional styles specific to signup page */
        body, html {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            height: 100%;
            font-family: "Poppins", sans-serif;
            background: linear-gradient(135deg, #ED1C24, #B7202E); /* Updated to Vitality Red and Power Red */
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #fff;
            overflow: hidden; /* Changed from overflow-x to prevent all scrollbars */
        }
        
        .container {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            height: 100vh; /* Use height instead of min-height */
            overflow-y: auto; /* Allow scrolling only in the container if needed */
            overflow-x: hidden;
        }
        
        .signup-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 16px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            animation: fadeIn 0.8s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 20px;
            margin: 0 auto; /* Simplified margin */
        }
        
        .form-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .logo {
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .logo img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .title {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        
        .signup-form {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px; /* Increased from 12px to 16px for better spacing */
            text-align: left;
        }
        
        .form-group {
            position: relative;
            margin-bottom: 4px; /* Add small margin to prevent inputs from touching */
        }
        
        .form-group.full-width {
            grid-column: span 3;
        }
        
        label {
            display: block;
            margin-bottom: 3px;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
        }
        
        input, select {
            width: 100%;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            border-radius: 6px;
            color: #fff;
            font-family: "Poppins", sans-serif;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        input:focus, select:focus {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
            outline: none;
        }
        
        input::placeholder, select::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='white' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: calc(100% - 12px) center;
            padding-right: 30px;
        }
        
        select option {
            background-color: #0072ff;
            color: #fff;
        }
        
        .file-upload {
            position: relative;
            display: flex;
            align-items: center;
            padding: 8px 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            border: 1px dashed rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }
        
        .file-icon {
            font-size: 18px;
            margin-right: 8px;
            flex-shrink: 0;
        }
        
        .file-label-container {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .file-label {
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .file-name {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.8);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .sign-up-button {
            grid-column: span 3;
            padding: 10px;
            font-size: 15px;
            font-weight: 600;
            background: linear-gradient(to right, #F58220, #A25723); /* Updated to Orange and Light Brown */
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(245, 130, 32, 0.3);
            margin-top: 12px; /* Increased margin for better spacing */
        }
        
        .sign-up-button:hover {
            background: linear-gradient(to right, #A25723, #F58220); /* Reversed gradient */
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(245, 130, 32, 0.4);
        }
        
        .error {
            grid-column: span 3;
            color: #ED1C24; /* Updated to Vitality Red */
            background: rgba(237, 28, 36, 0.1);
            padding: 8px 10px;
            border-radius: 6px;
            border-left: 3px solid #ED1C24;
            font-size: 13px;
            text-align: left;
            margin: 6px 0;
        }
        
        .signup-text {
            grid-column: span 3;
            margin-top: 12px;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
        }
        
        .signup-text a {
            color: #FFCB05; /* Updated to Yellow */
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .signup-text a:hover {
            color: #fff;
            text-shadow: 0 0 10px rgba(255, 203, 5, 0.5);
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .signup-form {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px; /* Slightly larger gap on medium screens */
            }
            
            .form-group.full-width {
                grid-column: span 2;
            }
            
            .sign-up-button, .error, .signup-text {
                grid-column: span 2;
            }
        }
        
        @media (max-width: 480px) {
            .signup-form {
                grid-template-columns: 1fr;
                gap: 14px; /* Adjusted gap for mobile screens */
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .sign-up-button, .error, .signup-text {
                grid-column: span 1;
            }
            
            .signup-card {
                padding: 15px;
                margin: 5px;
            }
            
            .form-header {
                flex-direction: column;
                text-align: center;
            }
            
            .logo {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .title {
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-card">
            <div class="form-header">
                <div class="logo">
                    <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo">
                </div>
                <h1 class="title">Create Your Account</h1>
            </div>

            <?php if (isset($error)) {
                echo "<div class='error'><i class='fas fa-exclamation-circle'></i> $error</div>";
            } ?>

            <form class="signup-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="svvid">SVV ID</label>
                    <input type="text" id="svvid" name="svvid" required>
                </div>

                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="" disabled selected>Select Gender</option>
                        <?php foreach ($genders as $gender): ?>
                            <option value="<?= htmlspecialchars($gender) ?>"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $gender)))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <input type="tel" id="mobile" name="mobile" pattern="[0-9]{10}" maxlength="10" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="branch">Branch</label>
                    <select id="branch" name="branch" required>
                        <option value="" disabled selected>Select Branch</option>
                        <?php foreach ($branches as $branch): ?>
                            <option value="<?= htmlspecialchars($branch) ?>"><?= htmlspecialchars(ucwords(strtolower(str_replace('_', ' ', $branch)))) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="programme">Programme</label>
                    <select id="programme" name="programme" required>
                        <option value="" disabled selected>Select Programme</option>
                        <option value="B.Tech">B.Tech</option>
                        <option value="M.Tech">M.Tech</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="graduation">Graduation Year</label>
                    <input type="number" id="graduation" name="graduation" min="2000" max="2100" required>
                </div>

                <div class="form-group">
                    <label for="cgpa">CGPA</label>
                    <input type="text" id="cgpa" name="cgpa" pattern="^[0-9](\.[0-9]{1,2})?$" placeholder="e.g. 8.5" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label for="resume">Resume</label>
                    <div class="file-upload" id="fileUploadContainer">
                        <input type="file" name="resume" id="resume" class="file-upload-input" accept=".pdf,.doc,.docx" required>
                        <i class="fas fa-file-upload file-icon"></i>
                        <div class="file-label-container">
                            <span class="file-label">Upload Resume (PDF, DOC)</span>
                            <span class="file-name" id="fileName">No file chosen</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="sign-up-button">Create Account</button>
                
                <div class="signup-text">
                    Already have an account? <a href="login.php">Sign In</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Display selected filename
        document.getElementById('resume').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.getElementById('fileName').textContent = fileName;
            
            // Visual feedback when file is selected
            const container = document.getElementById('fileUploadContainer');
            if (this.files[0]) {
                container.style.borderColor = 'rgba(46, 204, 113, 0.5)';
                container.style.background = 'rgba(46, 204, 113, 0.1)';
            } else {
                container.style.borderColor = 'rgba(255, 255, 255, 0.3)';
                container.style.background = 'rgba(255, 255, 255, 0.1)';
            }
        });
        
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                
                // Visual feedback for password mismatch
                password.style.borderColor = '#ff6b6b';
                confirmPassword.style.borderColor = '#ff6b6b';
                
                // Create error message if it doesn't exist
                if (!document.querySelector('.password-error')) {
                    const errorMsg = document.createElement('div');
                    errorMsg.className = 'error password-error';
                    errorMsg.innerHTML = '<i class="fas fa-exclamation-circle"></i> Passwords do not match.';
                    confirmPassword.parentNode.appendChild(errorMsg);
                }
            }
        });
    </script>
</body>
</html>
