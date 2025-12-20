<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode([]);
    exit();
}

$query = "SELECT teacher_id, name, email, status FROM teacher ORDER BY name ASC";
$result = $connection->query($query);

$teachers = [];
while($row = $result->fetch_assoc()){
    $teachers[] = $row;
}

echo json_encode($teachers);
?>