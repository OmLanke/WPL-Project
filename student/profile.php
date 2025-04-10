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

$success_message = "";
$error_message = "";

// Handle form submission for updating profile
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $gender = $_POST["gender"];
    $mobile = $_POST["mobile"];
    $email = $_POST["email"];
    $branch = $_POST["branch"];
    $programme = $_POST["programme"];
    $graduation = $_POST["graduation"];
    $cgpa = $_POST["cgpa"];
    
    // Resume file handling
    $update_resume = false;
    
    if (isset($_FILES["resume"]) && $_FILES["resume"]["error"] == 0) {
        $allowed_types = ["application/pdf"];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES["resume"]["type"], $allowed_types)) {
            $error_message = "Only PDF files are allowed.";
        } elseif ($_FILES["resume"]["size"] > $max_size) {
            $error_message = "File size must be less than 5MB.";
        } else {
            // Create uploads directory if it doesn't exist
            if (!file_exists("../uploads/resumes")) {
                mkdir("../uploads/resumes", 0777, true);
            }
            
            // Generate a unique filename
            $file_ext = pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION);
            $new_filename = str_replace('@', '_', $email) . "_resume_" . time() . "." . $file_ext;
            $resume_path = "../uploads/resumes/" . $new_filename;
            
            if (move_uploaded_file($_FILES["resume"]["tmp_name"], $resume_path)) {
                $update_resume = true;
            } else {
                $error_message = "Failed to upload resume.";
            }
        }
    }
    
    // Prepare the SQL statement
    if (empty($error_message)) {
        if ($update_resume) {
            $stmt = $conn->prepare("UPDATE student SET name = ?, gender = ?, mobile = ?, email = ?, branch = ?, programme = ?, graduation = ?, cgpa = ?, resume_path = ? WHERE studentID = ?");
            $stmt->bind_param("sssssssssi", $name, $gender, $mobile, $email, $branch, $programme, $graduation, $cgpa, $resume_path, $studentID);
        } else {
            // Keep existing resume_path when no new file is uploaded
            $stmt = $conn->prepare("UPDATE student SET name = ?, gender = ?, mobile = ?, email = ?, branch = ?, programme = ?, graduation = ?, cgpa = ? WHERE studentID = ?");
            $stmt->bind_param("ssssssssi", $name, $gender, $mobile, $email, $branch, $programme, $graduation, $cgpa, $studentID);
        }
        
        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Get student information
$stmt = $conn->prepare("SELECT svvID, name, gender, mobile, email, branch, programme, graduation, cgpa, resume_path FROM student WHERE studentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    $error_message = "Student not found.";
    header("Location: login.php");
    exit();
}
$stmt->close();

// Get available branches
$branches_result = $conn->query("SELECT branch FROM branch");
$branches = [];
while ($row = $branches_result->fetch_assoc()) {
    $branches[] = $row["branch"];
}

// Get available genders
$genders_result = $conn->query("SELECT gender FROM gender");
$genders = [];
while ($row = $genders_result->fetch_assoc()) {
    $genders[] = $row["gender"];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>My Profile | SkillBridge</title>
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            padding: 30px;
            border-radius: var(--border-radius);
            color: white;
            margin-bottom: 25px;
            box-shadow: var(--box-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-right: 20px;
        }

        .profile-info {
            flex-grow: 1;
        }
        
        .profile-info h1, .profile-info p {
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .profile-resume {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .profile-resume a {
            color: white;
            text-decoration: none;
        }

        .grid-form {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
            border: 1px solid rgba(46, 204, 113, 0.3);
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .readonly-field {
            background-color: #f5f5f5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="profile-header">
            <div class="profile-image">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($student["name"]); ?></h1>
                <p><i class="fas fa-id-card"></i> <?php echo htmlspecialchars($student["svvID"]); ?></p>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($student["email"]); ?></p>
                <?php if (!empty($student["resume_path"])): ?>
                <div class="profile-resume">
                    <i class="fas fa-file-pdf"></i>
                    <a href="<?php echo htmlspecialchars($student["resume_path"]); ?>" target="_blank">View Resume</a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-user-edit"></i> Edit Profile</h2>
            </div>
            <div class="card-body">
                <form action="profile.php" method="POST" enctype="multipart/form-data">
                    <div class="grid-form">
                        <div class="form-group">
                            <label for="svvID">SVV ID</label>
                            <input type="text" id="svvID" class="form-control readonly-field" value="<?php echo htmlspecialchars($student["svvID"]); ?>" readonly>
                            <small>SVV ID cannot be changed</small>
                        </div>

                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($student["name"]); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select id="gender" name="gender" class="form-control">
                                <?php foreach ($genders as $gender): ?>
                                    <option value="<?php echo $gender; ?>" <?php echo ($student["gender"] == $gender) ? "selected" : ""; ?>>
                                        <?php echo str_replace("_", " ", $gender); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student["email"]); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="mobile">Mobile Number</label>
                            <input type="tel" id="mobile" name="mobile" class="form-control" value="<?php echo htmlspecialchars($student["mobile"]); ?>" pattern="[0-9]{10}" required>
                            <small>10-digit mobile number</small>
                        </div>

                        <div class="form-group">
                            <label for="branch">Branch</label>
                            <select id="branch" name="branch" class="form-control">
                                <?php foreach ($branches as $branch): ?>
                                    <option value="<?php echo $branch; ?>" <?php echo ($student["branch"] == $branch) ? "selected" : ""; ?>>
                                        <?php echo str_replace("_", " ", $branch); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="programme">Programme</label>
                            <input type="text" id="programme" name="programme" class="form-control" value="<?php echo htmlspecialchars($student["programme"]); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="graduation">Graduation Year</label>
                            <input type="number" id="graduation" name="graduation" class="form-control" value="<?php echo $student["graduation"]; ?>" min="2020" max="2030" required>
                        </div>

                        <div class="form-group">
                            <label for="cgpa">CGPA</label>
                            <input type="text" id="cgpa" name="cgpa" class="form-control" value="<?php echo htmlspecialchars($student["cgpa"]); ?>" pattern="^[0-9]\.[0-9]{1,2}$" required>
                            <small>Format: x.xx (e.g. 8.75)</small>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label for="resume">Resume (PDF only)</label>
                        <input type="file" id="resume" name="resume" class="form-control" accept="application/pdf">
                        <small>Leave empty to keep current resume. Max size: 5MB</small>
                    </div>

                    <div class="d-flex justify-between mt-3">
                        <a href="./" class="btn btn-danger">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h2 class="card-title"><i class="fas fa-lock"></i> Account Security</h2>
            </div>
            <div class="card-body">
                <p class="mb-2">To change your password, please use the form below:</p>
                
                <form id="password-form">
                    <div class="grid-form">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>

                    <div class="d-flex justify-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Add animation when cards enter the viewport
        document.addEventListener("DOMContentLoaded", function() {
            const cards = document.querySelectorAll('.card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = "1";
                        entry.target.style.transform = "translateY(0)";
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = "0";
                card.style.transform = "translateY(20px)";
                card.style.transition = "opacity 0.5s ease, transform 0.5s ease";
                observer.observe(card);
            });
        });

        // Form validation for password change
        document.getElementById('password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return;
            }
            
            if (newPassword.length < 8) {
                alert('Password must be at least 8 characters long!');
                return;
            }
            
            // Send AJAX request to change password
            const formData = new FormData();
            formData.append('current_password', currentPassword);
            formData.append('new_password', newPassword);
            
            fetch('change_password.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success';
                    alertDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    
                    // Insert at the top of the form
                    const passwordForm = document.getElementById('password-form');
                    passwordForm.parentNode.insertBefore(alertDiv, passwordForm);
                    
                    // Clear the form
                    document.getElementById('current_password').value = '';
                    document.getElementById('new_password').value = '';
                    document.getElementById('confirm_password').value = '';
                    
                    // Remove the alert after 5 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                } else {
                    // Show error message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                    
                    // Insert at the top of the form
                    const passwordForm = document.getElementById('password-form');
                    passwordForm.parentNode.insertBefore(alertDiv, passwordForm);
                    
                    // Remove the alert after 5 seconds
                    setTimeout(() => {
                        alertDiv.remove();
                    }, 5000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while changing your password. Please try again.');
            });
        });
    </script>
</body>

</html>