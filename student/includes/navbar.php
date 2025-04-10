<?php
$activePage = basename($_SERVER["PHP_SELF"]);
$home = "";
$applicationsActive = "";
$profileActive = "";

switch ($activePage) {
    case "index.php":
        $home = ' class="active"';
        break;
    case "applications.php":
        $applicationsActive = ' class="active"';
        break;
    case "profile.php":
        $profileActive = ' class="active"';
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
            --primary-color: #0072ff;
            --secondary-color: #00c6ff;
            --accent-color: #ff416c;
            --text-color: #333;
            --light-text: #fff;
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
                flex-wrap: wrap;
                padding: 10px 15px;
            }
            
            .nav-links {
                order: 3;
                width: 100%;
                margin-top: 10px;
                justify-content: center;
            }
            
            .logo {
                font-size: 18px;
            }
            
            .logo-img {
                width: 28px;
                height: 28px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo">
            <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo" class="logo-img">
            SkillBridge
        </div>
        <div class="nav-links">
            <a href="./" target="_parent"<?php echo $home; ?>>
                <i class="fas fa-home icon"></i> Home
            </a>
            <a href="applications.php" target="_parent"<?php echo $applicationsActive; ?>>
                <i class="fas fa-briefcase icon"></i> My Applications
            </a>
            <a href="profile.php" target="_parent"<?php echo $profileActive; ?>>
                <i class="fas fa-user icon"></i> My Profile
            </a>
        </div>
        <div class="spacer"></div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt icon"></i> Logout
        </a>
    </nav>
</body>
</html>
