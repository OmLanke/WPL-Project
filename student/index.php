<?php
$activePage = basename($_SERVER["PHP_SELF"]); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Job Listings</title>
</head>

<body>

    <?php include "includes/navbar.php"; ?>

    <hr>
    <br><br>

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
