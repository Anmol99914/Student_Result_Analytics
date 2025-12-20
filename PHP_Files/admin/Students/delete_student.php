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

if(empty($student_id)){
    echo json_encode(['status'=>'error','message'=>'Invalid student ID']);
    exit();
}

// Start transaction
$connection->begin_transaction();

try {
    // Check if student has any results
    $checkResult = $connection->prepare("SELECT COUNT(*) as result_count FROM result WHERE student_id = ?");
    $checkResult->bind_param("s", $student_id);
    $checkResult->execute();
    $checkResult->bind_result($result_count);
    $checkResult->fetch();
    $checkResult->close();
    
    if($result_count > 0){
        // Instead of deleting, deactivate the student
        $stmt = $connection->prepare("UPDATE student SET is_active = 0 WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->close();
        
        // Also remove from users table to prevent login
        $stmtUser = $connection->prepare("DELETE FROM users WHERE username = ? AND role = 'student'");
        $stmtUser->bind_param("s", $student_id);
        $stmtUser->execute();
        $stmtUser->close();
        
        $message = "Student deactivated (has existing results)";
    } else {
        // Student has no results, can delete
        // Delete from student table
        $stmt = $connection->prepare("DELETE FROM student WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete from users table
        $stmtUser = $connection->prepare("DELETE FROM users WHERE username = ? AND role = 'student'");
        $stmtUser->bind_param("s", $student_id);
        $stmtUser->execute();
        $stmtUser->close();
        
        $message = "Student deleted successfully";
    }
    
    $connection->commit();
    
    echo json_encode([
        'status' => 'success', 
        'message' => $message
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode([
        'status' => 'error', 
        'message' => 'Failed to delete student: ' . $e->getMessage()
    ]);
}
?>