<?php
$activePage = basename($_SERVER["PHP_SELF"]);
$home = "";
$jobsNav = ""; 
$studentsNav = ""; 
$addJob = "";
$addCompany = "";

switch ($activePage) {
    case "index.php":
        $home = ' class="active"';
        break;
    case "manage_jobs.php":
    case "edit_job.php":
    case "job_applications.php":
        $jobsNav = ' class="active"';
        break;
    case "students.php":
        $studentsNav = ' class="active"';
        break;
    case "add_job.php":
        $addJob = ' class="active"';
        break;
    case "add_company.php":
        $addCompany = ' class="active"';
        break;
    default:
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #B7202E; /* Power Red */
            --secondary-color: #ED1C24; /* Vitality Red */
            --cool-gray: #58595B; /* Pantone Cool Gray */
            --dark-gray: #231F20; /* Pantone Dark Gray */
            --white: #FFFFFF; /* White */
            
            --orange: #F58220; /* Orange */
            --light-brown: #A25723; /* Light Brown */
            --yellow: #FFCB05; /* Yellow */
            --dark-brown: #603312; /* Dark Brown */
            
            --trust-blue: #004A9C; /* Trust Blue */
            --link-blue: #006699; /* Blue / Link Blue */
            --dark-link-blue: #004466; /* Dark Blue / Link Blue */
            
            --success-color: var(--light-brown);
            --success-hover: var(--dark-brown);
            --text-color: var(--dark-gray);
            --light-text: var(--white);
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        nav {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 12px 20px;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
            position: sticky;
            top: 0;
            z-index: 100;
            flex-wrap: wrap;
        }
        
        .nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        nav a {
            color: var(--light-text);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        nav a.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        nav a:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .logo {
            font-size: 22px;
            font-weight: 700;
            color: var(--light-text);
            font-family: 'Poppins', sans-serif;
            margin-right: 15px;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
        }
        
        .logo-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            margin-right: 10px;
        }
        
        .spacer {
            flex-grow: 1;
        }
        
        .add-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-add {
            background-color: var(--orange);
            color: var(--light-text);
            padding: 8px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(245, 130, 32, 0.3);
        }
        
        .btn-add:hover {
            background-color: var(--light-brown);
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(162, 87, 35, 0.4);
        }
        
        .btn-add.active {
            background-color: var(--light-brown);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 25px;
            padding: 8px 16px;
            color: var(--light-text);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .icon {
            font-size: 16px;
        }
        
        @media (max-width: 768px) {
            nav {
                justify-content: center;
                text-align: center;
                padding: 10px 15px;
            }
            
            .nav-links, .add-buttons {
                order: 3;
                width: 100%;
                justify-content: center;
                margin-top: 10px;
                flex-wrap: wrap;
            }
            
            .logo {
                font-size: 18px;
                margin-right: 0;
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }
            
            .spacer {
                display: none;
            }
            
            .logout-btn {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo" class="logo-img">
            SkillBridge Admin
        </div>
        <div class="nav-links">
            <a href="./" target="_parent"<?php echo $home; ?>>
                <i class="fas fa-chart-line icon"></i> Dashboard
            </a>
            <a href="manage_jobs.php" target="_parent"<?php echo $jobsNav; ?>>
                <i class="fas fa-briefcase icon"></i> Jobs
            </a>
            <a href="students.php" target="_parent"<?php echo $studentsNav; ?>>
                <i class="fas fa-user-graduate icon"></i> Students
            </a>
        </div>
        
        <div class="spacer"></div>
        
        <div class="add-buttons">
            <a href="add_job.php" target="_parent" class="btn-add<?php echo $addJob; ?>">
                <i class="fas fa-plus-circle icon"></i> Add Job
            </a>
            <a href="add_company.php" target="_parent" class="btn-add<?php echo $addCompany; ?>">
                <i class="fas fa-building icon"></i> Add Company
            </a>
        </div>
        
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt icon"></i> Logout
        </a>
    </nav>
</body>
</html>
