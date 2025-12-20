<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$class_id = intval($data['class_id'] ?? 0);
$status = in_array($data['status'], ['active','inactive']) ? $data['status'] : 'active';

if($class_id <= 0){
    echo json_encode(['status'=>'error','message'=>'Invalid class ID']);
    exit();
}

$stmt = $connection->prepare("UPDATE class SET status = ? WHERE class_id = ?");
$stmt->bind_param("si", $status, $class_id);

if($stmt->execute()){
    echo json_encode([
        'status' => 'success', 
        'message' => 'Class status updated successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to update class status'
    ]);
}

$stmt->close();
?>