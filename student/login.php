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
            header("Location: index.php"); // redirect to home
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
</head>
<body>
    <div class="container">
        <div class="glass-card">
            <div class="logo">
                <img src="https://static.vecteezy.com/system/resources/previews/012/892/296/non_2x/people-finder-logo-magnifying-glass-logo-free-vector.jpg" alt="Logo">
            </div>
            <div class="title">Welcome to SkillBridge</div>
            <form method="POST" action="">
                <input type="text" placeholder="SVV ID" name="svvid" required>
                <input type="password" placeholder="Password" name="password" required>
                <button type="submit" class="sign-in-button">Sign In</button>
            </form>
            <?php if (isset($error)) {
                echo "<p class='error'>$error</p>";
            } ?>
            <p class="signup-text">Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>
</body>
</html>
