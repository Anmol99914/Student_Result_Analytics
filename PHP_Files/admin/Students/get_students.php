<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode([]);
    exit();
}

$query = "
    SELECT 
        s.student_id,
        s.student_name,
        s.email,
        s.phone_number,
        s.class_id,
        s.semester_id,
        s.is_active,
        s.created_at,
        c.faculty,
        c.semester
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    ORDER BY s.created_at DESC
";

$result = $connection->query($query);
$students = [];

while($row = $result->fetch_assoc()){
    $students[] = $row;
}

echo json_encode($students);
?>