<?php
$activePage = basename($_SERVER["PHP_SELF"]);
$home = "";
$gettingStarted = "";

switch ($activePage) {
    case "index.php":
        $home = ' class="active"';
        break;
    case "gettingstarted.php":
        $gettingStarted = ' class="active"';
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
            gap: 20px;
            padding: 2px 10px
        }
        nav a {
            color: black;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        nav a.active {
            color: blue;
        }

        nav a:hover {
            background-color: #999;
        }

        #logout {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: end;
        }

        #logout img {
            width: 24px;
            height: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <nav>
        <?php echo '<a href="./" target="_parent"' . $home . ">Home</a>"; ?>
        <div id="logout">
            <a href="../index.html">
                <img src="https://cdn3.iconfinder.com/data/icons/ui-actions-solid/16/logout-arrow-right-exit-1024.png" alt="Logout">
            </a>
        </div>
    </nav>
</body>
</html>
