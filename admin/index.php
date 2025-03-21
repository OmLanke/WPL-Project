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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title>Students</title>
</head>

<body>
    <?php include "includes/navbar.php"; ?> <!-- Include the navbar -->

    <hr>
    <br><br>

    <table>
        <thead>
            <tr>
                <th>Sr.</th>
                <th>Name</th>
                <th>Graduation</th>
                <th>Branch</th>
                <th>Programme</th>
                <th>Resume</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql =
                "SELECT studentID, name, graduation, branch, programme FROM student";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($app = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$app["studentID"]}</td>
                            <td>{$app["name"]}</td>
                            <td>{$app["graduation"]}</td>
                            <td>{$app["branch"]}</td>
                            <td>{$app["programme"]}</td>
                            <td><a href='.' target='_blank' download><button>Download</button></a></td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No applications found</td></tr>";
            }

            $conn->close();
            ?>
        </tbody>
    </table>
</body>

</html>
