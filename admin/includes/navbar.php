<?php
$activePage = basename($_SERVER["PHP_SELF"]);
$home = "";
$jobsNav = ""; // Renamed from $jobs to $jobsNav
$studentsNav = ""; // Renamed from $students to $studentsNav
$addJob = "";
$addCompany = "";

switch ($activePage) {
    case "index.php":
        $home = ' class="active"';
        break;
    case "manage_jobs.php":
    case "edit_job.php":
    case "job_applications.php":
        $jobsNav = ' class="active"'; // Updated variable reference
        break;
    case "students.php":
        $studentsNav = ' class="active"'; // Updated variable reference
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
    <style>
        nav {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 8px 15px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .nav-brand {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-right: 10px;
        }
        
        nav a {
            color: #333;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s;
            font-size: 15px;
        }
        
        nav a.active {
            color: #007bff;
            background-color: rgba(0, 123, 255, 0.1);
        }

        nav a:hover {
            background-color: #f0f0f0;
        }

        #logout {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        #logout a {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #logout img {
            width: 18px;
            height: 18px;
        }
        
        .add-buttons {
            display: flex;
            gap: 10px;
        }
        
        .btn-add {
            background-color: #28a745;
            color: white !important;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .btn-add:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-brand">SkillBridge</div>
        <?php echo '<a href="./"' . $home . ">Dashboard</a>"; ?>
        <?php echo '<a href="manage_jobs.php"' . $jobsNav . ">Jobs</a>"; ?> <!-- Updated variable reference -->
        <?php echo '<a href="students.php"' . $studentsNav . ">Students</a>"; ?> <!-- Updated variable reference -->
        
        <div class="add-buttons">
            <?php echo '<a href="add_job.php"' . $addJob . ' class="btn-add">+ Add Job</a>'; ?>
            <?php echo '<a href="add_company.php"' . $addCompany . ' class="btn-add">+ Add Company</a>'; ?>
        </div>
        
        <div id="logout">
            <a href="../logout.php">
                Logout <img src="https://cdn3.iconfinder.com/data/icons/ui-actions-solid/16/logout-arrow-right-exit-1024.png" alt="Logout">
            </a>
        </div>
    </nav>
</body>
</html>
