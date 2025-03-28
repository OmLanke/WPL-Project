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

    if ($password !== $confirm_password) {
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
                "INSERT INTO student (svvid, password_hash, name, gender, mobile, email, branch, programme, graduation, cgpa)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                "ssssssssss",
                $svvid,
                $hashed_password,
                $name,
                $gender,
                $mobile,
                $email,
                $branch,
                $programme,
                $graduation,
                $cgpa
            );

            if ($stmt->execute()) {
                $_SESSION["studentID"] = $conn->insert_id;
                header("Location: index.php");
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
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="logo">
                <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo">
            </div>
            <div class="title">Create an Account</div>
            <form method="POST" action="">
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

                <input type="password" placeholder="Password" name="password" required>

                <input type="password" placeholder="Confirm Password" name="confirm_password" required>

                <button type="submit" class="sign-in-button">Sign Up</button>
            </form>

            <?php if (isset($error)) {
                echo "<p class='error'>$error</p>";
            } ?>

            <p class="signup-text">Already have an account? <a href="signin.php">Sign In</a></p>
        </div>
    </div>
</body>
</html>
