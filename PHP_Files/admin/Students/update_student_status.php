<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $connection->real_escape_string($data['student_id'] ?? '');
$status = intval($data['status'] ?? 0);

if(empty($student_id)){
    echo json_encode(['status'=>'error','message'=>'Invalid student ID']);
    exit();
}

$stmt = $connection->prepare("UPDATE student SET is_active = ? WHERE student_id = ?");
$stmt->bind_param("is", $status, $student_id);

if($stmt->execute()){
    echo json_encode([
        'status' => 'success', 
        'message' => 'Student status updated successfully'
    ]);
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to update student status'
    ]);
}

$stmt->close();
?>