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

    $stmt = $conn->prepare(
        "SELECT studentID, password_hash FROM student WHERE svvid = ?"
    );
    $stmt->bind_param("s", $svvid);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["studentID"] = $id;
            header("Location: ./"); // redirect to home
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "SVV ID not found.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="view-transition" content="same-origin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | SkillBridge</title>
    <link rel="stylesheet" href="login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Optional additional styles for login page */
        .glass-card {
            animation: fadeIn 0.8s ease-in-out;
        }
        
        form input {
            text-align: left;
            padding-left: 20px;
        }
        
        form input:focus {
            transform: translateY(-2px);
        }
        
        .error {
            background: rgba(255, 107, 107, 0.1);
            padding: 12px;
            border-radius: 8px;
            border-left: 3px solid #ff6b6b;
            font-size: 14px;
            text-align: left;
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        
        .error:before {
            content: "\f06a";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            margin-right: 10px;
            color: #ff6b6b;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 75, 43, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 75, 43, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 75, 43, 0);
            }
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="logo">
                <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo">
            </div>
            <div class="title">Welcome to SkillBridge</div>
            <form method="POST" action="">
                <div class="input-group">
                    <input type="text" placeholder="SVV ID" name="svvid" required>
                </div>
                <div class="input-group">
                    <input type="password" placeholder="Password" name="password" required>
                </div>
                <button type="submit" class="sign-in-button">Sign In</button>
            </form>
            <?php if (isset($error)) {
                echo "<div class='error'>$error</div>";
            } ?>
            <p class="signup-text">Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>

    <script>
        // Add focus effects
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.parentElement.classList.remove('focused');
                }
            });
        });
        
        // Add subtle animation to sign-in button
        const signInButton = document.querySelector('.sign-in-button');
        signInButton.addEventListener('mouseover', function() {
            this.classList.add('pulse');
        });
        
        signInButton.addEventListener('mouseout', function() {
            this.classList.remove('pulse');
        });
    </script>
</body>
</html>
