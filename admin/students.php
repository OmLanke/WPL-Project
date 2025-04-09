<?php
session_start();

$adminID = $_SESSION["adminID"];

if (!isset($adminID)) {
    header("Location: login.php");
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

// Fetch all students with their details
$students_query = "
    SELECT s.studentID, s.svvid, s.name, s.gender, s.mobile, 
           s.email, s.branch, s.programme, s.graduation, s.cgpa, 
           s.resume_path,
           (SELECT COUNT(*) FROM application a WHERE a.studentID = s.studentID) AS application_count
    FROM student s
    ORDER BY s.studentID DESC
";
$students_result = $conn->query($students_query);
$students = [];

if ($students_result) {
    while ($row = $students_result->fetch_assoc()) {
        $students[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Students | Admin</title>
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .page-header h1 {
            margin: 0;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .students-table th, 
        .students-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .students-table th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        .students-table tbody tr {
            border-bottom: 1px solid #f2f2f2;
        }
        
        .students-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .students-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .student-name {
            font-weight: bold;
            color: #333;
        }
        
        .student-id {
            color: #6c757d;
            font-size: 13px;
        }
        
        .student-email {
            color: #007bff;
        }
        
        .student-contact {
            color: #495057;
        }
        
        .student-education {
            font-size: 14px;
        }
        
        .student-applications {
            font-weight: bold;
            color: #28a745;
            text-align: center;
        }
        
        .btn-download {
            padding: 6px 12px;
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            display: inline-block;
        }
        
        .btn-download:hover {
            background-color: #138496;
        }
        
        .btn-download.disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state h2 {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .search-section {
            margin-bottom: 20px;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
        }
        
        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .search-button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: #0069d9;
        }
    </style>
</head>

<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="page-header">
            <h1>Students</h1>
        </div>
        
        <div class="search-section">
            <div class="search-box">
                <input type="text" id="searchInput" class="search-input" placeholder="Search by name, SVV ID, branch...">
                <button class="search-button" onclick="searchStudents()">Search</button>
            </div>
        </div>

        <?php if (empty($students)): ?>
            <div class="empty-state">
                <h2>No students found</h2>
                <p>There are no registered students in the system</p>
            </div>
        <?php else: ?>
            <table class="students-table" id="studentsTable">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Contact</th>
                        <th>Education</th>
                        <th>Applications</th>
                        <th>Resume</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td>
                                <div class="student-name"><?php echo htmlspecialchars($student['name']); ?></div>
                                <div class="student-id">SVV ID: <?php echo htmlspecialchars($student['svvid']); ?></div>
                            </td>
                            <td>
                                <div class="student-email"><?php echo htmlspecialchars($student['email']); ?></div>
                                <div class="student-contact"><?php echo htmlspecialchars($student['mobile']); ?></div>
                            </td>
                            <td class="student-education">
                                <?php echo htmlspecialchars($student['programme']); ?>, <?php echo htmlspecialchars($student['branch']); ?><br>
                                Graduation: <?php echo htmlspecialchars($student['graduation']); ?><br>
                                CGPA: <?php echo htmlspecialchars($student['cgpa']); ?>
                            </td>
                            <td class="student-applications">
                                <?php echo $student['application_count']; ?>
                            </td>
                            <td>
                                <?php if (!empty($student['resume_path'])): ?>
                                    <a href="../<?php echo htmlspecialchars($student['resume_path']); ?>" class="btn-download" download>Download</a>
                                <?php else: ?>
                                    <span class="btn-download disabled">No Resume</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <script>
        function searchStudents() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('studentsTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                let found = false;
                const cells = rows[i].getElementsByTagName('td');
                
                for (let j = 0; j < cells.length; j++) {
                    const cellText = cells[j].textContent || cells[j].innerText;
                    if (cellText.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>

</html>