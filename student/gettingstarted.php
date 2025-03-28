<?php
$activePage = basename($_SERVER["PHP_SELF"]); ?>

<?php
session_start();

error_reporting(E_ALL);
ini_set("display_errors", 1);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Basic Details</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <?php include "includes/navbar.php"; ?>

    <hr>
    <br><br>

    <div class="parent-container">
        <div class="center-text">Enter your basic details</div>

        <div class="form-container">
            <div class="section-title">Personal Info:</div>

            <input type="text" placeholder="SVV ID" name="svvid" required>

            <input type="text" placeholder="Full Name" name="name" required>

            <div class="input-line">
                <div>
                    <label for="phone">Phone:</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div>
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>
                </div>
            </div>

            <div class="section-title">Education Info:</div>

            <div class="input-group">
                <div>
                    <label for="branch">Branch:</label>
                    <input type="text" id="branch" name="branch" required>
                </div>
                <div>
                    <label for="program">Program:</label>
                    <input type="text" id="program" name="program" required>
                </div>
                <div>
                    <label for="graduation-year">Graduation Year:</label>
                    <input type="number" id="graduation-year" name="graduation-year" required>
                </div>
            </div>

            <div>
                <label for="cgpa">CGPA:</label>
                <input type="text" id="cgpa" name="cgpa" required>
            </div>

            <div class="drag-drop" onclick="document.getElementById('resume').click();">
                Drag and drop your Resume here or click to upload
                <input type="file" id="resume" name="resume" style="display:none;">
            </div>

            <button type="submit">Submit</button>
        </div>
    </div>

</body>

</html>
