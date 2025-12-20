<?php
require_once '../../../config.php';

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];
    
    $sql = "SELECT faculty FROM class WHERE class_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $class = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode($class ?: []);
}
?>