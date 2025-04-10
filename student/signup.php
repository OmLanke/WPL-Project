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
    <meta name="view-transition" content="same-origin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | SkillBridge</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .glass-card {
            width: 380px;
            max-height: 90vh;
            overflow-y: auto;
            padding: 30px;
        }
        
        select {
            width: calc(100% - 24px);
            padding: 12px;
            margin: 10px auto;
            display: block;
            border: none;
            border-radius: 25px;
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 16px;
            text-align-last: center;
            cursor: pointer;
            appearance: none;
            font-family: "Poppins", sans-serif;
        }
        
        select option {
            background: #0072ff;
            color: #fff;
            font-family: "Poppins", sans-serif;
        }
        
        .file-upload {
            position: relative;
            display: inline-block;
            width: calc(100% - 24px);
            margin: 10px auto;
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
        
        .file-upload-button {
            display: inline-block;
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border: none;
            border-radius: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: "Poppins", sans-serif;
        }
        
        .file-upload:hover .file-upload-button {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .file-name {
            margin-top: 5px;
            font-size: 12px;
            color: #fff;
            text-align: center;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .error {
            color: #ff6b6b;
            margin-top: 10px;
            font-size: 14px;
            background: rgba(255, 107, 107, 0.1);
            padding: 10px;
            border-radius: 10px;
            border-left: 3px solid #ff6b6b;
        }
        
        /* Custom scrollbar */
        .glass-card::-webkit-scrollbar {
            width: 8px;
        }
        
        .glass-card::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }
        
        .glass-card::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        
        .glass-card::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        @media (max-width: 480px) {
            .glass-card {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="logo">
                <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo">
            </div>
            <div class="title">Create an Account</div>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="text" placeholder="SVV ID" name="svvid" required>

                <input type="text" placeholder="Full Name" name="name" required>

                <select name="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="MALE">Male</option>
                    <option value="FEMALE">Female</option>
                    <option value="OTHERS">Other</option>
                </select>

                <input type="tel" placeholder="Mobile Number" name="mobile" pattern="[0-9]{10}" maxlength="10" required>

                <input type="email" placeholder="Email Address" name="email" required>

                <select name="branch" required>
                    <option value="" disabled selected>Select Branch</option>
                    <option value="COMPUTER_ENGINEERING">Computer Science</option>
                    <option value="INFORMATION_TECHNOLOGY">Information Technology</option>
                    <option value="ELECTRONCICS_AND_COMPUTERS">Electronics</option>
                    <option value="MECHANICAL_ENGINEERING">Mechanical</option>
                </select>

                <select name="programme" required>
                    <option value="" disabled selected>Select Programme</option>
                    <option value="B.Tech">B.Tech</option>
                    <option value="M.Tech">M.Tech</option>
                </select>

                <input type="number" placeholder="Graduation Year" name="graduation" min="2000" max="2100" required>

                <input type="text" placeholder="CGPA (e.g. 8.5)" name="cgpa" pattern="^[0-9](\.[0-9]{1,2})?$" required>
                
                <div class="file-upload">
                    <div class="file-upload-button">Upload Resume (PDF, DOC, DOCX)</div>
                    <input type="file" name="resume" class="file-upload-input" accept=".pdf,.doc,.docx" required>
                    <div class="file-name" id="fileName">No file chosen</div>
                </div>

                <input type="password" placeholder="Password" name="password" required>

                <input type="password" placeholder="Confirm Password" name="confirm_password" required>

                <button type="submit" class="sign-in-button">Sign Up</button>
            </form>

            <?php if (isset($error)) {
                echo "<p class='error'>$error</p>";
            } ?>

            <p class="signup-text">Already have an account? <a href="login.php">Sign In</a></p>
        </div>
    </div>
    
    <script>
        // Display selected filename
        document.querySelector('.file-upload-input').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'No file chosen';
            document.getElementById('fileName').textContent = fileName;
        });
    </script>
</body>
</html>
