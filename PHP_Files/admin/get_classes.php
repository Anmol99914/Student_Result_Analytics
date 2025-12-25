<?php
// get_classes.php - Simple version
session_start();
include('../../config.php');

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    echo json_encode([]);
    exit();
}

$query = "SELECT class_id, faculty, semester, status FROM class WHERE status='active' ORDER BY faculty, semester";
$result = $connection->query($query);

$classes = [];
while($row = $result->fetch_assoc()){
    $classes[] = $row;
}

echo json_encode($classes);
?>