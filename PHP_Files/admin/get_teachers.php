<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode([]);
    exit();
}

// Modified query to include class count
$query = "
    SELECT 
        t.teacher_id, 
        t.name, 
        t.email, 
        t.status,
        t.created_at,
        COUNT(c.class_id) as class_count
    FROM teacher t
    LEFT JOIN class c ON t.teacher_id = c.teacher_id
    GROUP BY t.teacher_id
    ORDER BY t.name ASC
";

$result = $connection->query($query);

$teachers = [];
while($row = $result->fetch_assoc()){
    $teachers[] = $row;
}

echo json_encode($teachers);
?>