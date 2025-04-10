<?php
if (isset($_COOKIE["userType"])) {
    if ($_COOKIE["userType"] == "student") {
        header("Location: student/index.php");
        exit();
    } elseif ($_COOKIE["userType"] == "admin") {
        header("Location: admin/index.php");
        exit();
    }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="view-transition" content="same-origin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="index.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>SkillBridge Placement Portal</title>
</head>
<body>
    <div class="main-container">
        <div class="welcome-section">
            <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="SkillBridge Logo" class="logo">
            <h1 class="title">SkillBridge Placement Portal</h1>
            <p class="subtitle">Connecting students with their dream careers</p>
        </div>
        
        <div class="box-container">
            <a href="student/login.php" class="box student-box">
                <div class="icon-container">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="box-content">
                    <h2>Student Portal</h2>
                    <p>Browse opportunities and manage your applications</p>
                </div>
            </a>
            
            <a href="admin/login.php" class="box admin-box">
                <div class="icon-container">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="box-content">
                    <h2>Admin Portal</h2>
                    <p>Manage jobs, companies and student placements</p>
                </div>
            </a>
        </div>
        
        <footer>
            <p>&copy; 2025 SkillBridge Placement Portal. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
