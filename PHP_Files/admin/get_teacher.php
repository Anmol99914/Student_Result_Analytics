<?php
// get_teacher.php
session_start();
include("../../config.php");

header('Content-Type: application/json');

// Check admin login
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Get teacher ID
$teacher_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($teacher_id <= 0){
    echo json_encode(['error' => 'Invalid teacher ID']);
    exit();
}

// Fetch teacher data
$query = "SELECT teacher_id, name, email, status FROM teacher WHERE teacher_id = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0){
    echo json_encode(['error' => 'Teacher not found']);
    exit();
}

$teacher = $result->fetch_assoc();
echo json_encode($teacher);

$stmt->close();
?>