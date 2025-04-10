<?php
session_start();

if (!isset($_SESSION["studentID"])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

$studentID = $_SESSION["studentID"];

// Check if all required fields are present
if (!isset($_POST["current_password"]) || !isset($_POST["new_password"])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$current_password = $_POST["current_password"];
$new_password = $_POST["new_password"];

// Basic password validation
if (strlen($new_password) < 8) {
    echo json_encode(["success" => false, "message" => "Password must be at least 8 characters long"]);
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "placement";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Verify current password
$stmt = $conn->prepare("SELECT password_hash FROM student WHERE studentID = ?");
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit();
}

$row = $result->fetch_assoc();
$stored_hash = $row["password_hash"];
$stmt->close();

// Verify the current password
if (!password_verify($current_password, $stored_hash)) {
    echo json_encode(["success" => false, "message" => "Current password is incorrect"]);
    exit();
}

// Hash the new password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update the password in the database
$update_stmt = $conn->prepare("UPDATE student SET password_hash = ? WHERE studentID = ?");
$update_stmt->bind_param("si", $new_hash, $studentID);
$result = $update_stmt->execute();
$update_stmt->close();

if ($result) {
    echo json_encode(["success" => true, "message" => "Password changed successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update password"]);
}

$conn->close();
?>