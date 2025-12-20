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
        c.class_id,
        c.faculty,
        c.semester,
        c.status,
        c.created_at,
        t.name as teacher_name,
        t.email as teacher_email
    FROM class c
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id
    ORDER BY c.faculty, c.semester
";

$result = $connection->query($query);
$classes = [];

while($row = $result->fetch_assoc()){
    $classes[] = $row;
}

echo json_encode($classes);
?>