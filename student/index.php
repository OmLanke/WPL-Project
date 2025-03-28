<?php
session_start();

$studentID = $_SESSION["studentID"];

if (!isset($studentID)) {
    header("Location: login.php"); // haale dil tujho yeh chahta dil agar yeh bol paata yah khudda tujhko hi chahta naa
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

$stmt = $conn->prepare("SELECT name FROM student WHERE studentID = ?");
$stmt->bind_param("s", $studentID);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name);
    $stmt->fetch();
} else {
    $error = "SVV ID not found.";
    header("Location: login.php"); // redirect to login
    exit();
}

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="view-transition" content="same-origin">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Job Listings</title>
</head>

<body>

    <?php include "includes/navbar.php"; ?>

    <hr/>
    <br/>
    <h1>Welcome, <?php echo $name; ?></h1>
    <br/>

    <table border="1">
        <tr>
            <th>Job Title</th>
            <th>Role</th>
            <th>Company Logo</th>
            <th>Apply</th>
            <th rowspan="10%">AI summary of your resume</th>
        </tr>

        <?php
        $jobs = [
            [
                "Software Engineer",
                "Development",
                "https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg",
            ],
            [
                "Data Analyst",
                "Analytics",
                "https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg",
            ],
            [
                "UX Designer",
                "Design",
                "https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg",
            ],
            [
                "Project Manager",
                "Management",
                "https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg",
            ],
            [
                "Marketing Specialist",
                "Marketing",
                "https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg",
            ],
            [
                "Cybersecurity Analyst",
                "Security",
                "https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg",
            ],
            [
                "Cloud Engineer",
                "Infrastructure",
                "https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg",
            ],
            [
                "AI Researcher",
                "AI/ML",
                "https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg",
            ],
            [
                "Sales Executive",
                "Sales",
                "https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg",
            ],
        ];

        foreach ($jobs as $job) {
            echo "<tr>
                <td>{$job[0]}</td>
                <td>{$job[1]}</td>
                <td><img src='{$job[2]}' alt='Company Logo' width='50' onerror=\"this.src='path/to/default_logo.png';\"></td>
                <td><a href='#modal'><button>Apply</button></a></td>
            </tr>";
        }
        ?>

    </table>

    <br><br>

    <div id="modal">You have successfully applied</div>

</body>

</html>
