<?php
// save-assignment.php
// API endpoint to save teacher-subject assignment
session_start();
require_once('../../../config.php');

header('Content-Type: application/json');

// Check admin session
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if(!$data || empty($data['teacher_id']) || empty($data['subject_id']) || empty($data['class_id'])) {
    echo json_encode(['error' => 'Invalid data']);
    exit();
}

$teacher_id = intval($data['teacher_id']);
$subject_id = intval($data['subject_id']);
$class_id = intval($data['class_id']);
$action = $data['action'] ?? 'assign'; // 'assign' or 'remove'

try {
    $connection->begin_transaction();
    
    if($action === 'assign') {
        // Check if already assigned
        $check_query = "
            SELECT assignment_id FROM teacher_subject_assignment 
            WHERE teacher_id = ? AND subject_id = ? AND class_id = ? AND status = 'active'
        ";
        $check_stmt = $connection->prepare($check_query);
        $check_stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
        $check_stmt->execute();
        
        if($check_stmt->get_result()->num_rows == 0) {
            // Insert new assignment
            $insert_query = "
                INSERT INTO teacher_subject_assignment 
                (teacher_id, subject_id, class_id, academic_year, start_date, status)
                VALUES (?, ?, ?, YEAR(CURDATE()), CURDATE(), 'active')
            ";
            $insert_stmt = $connection->prepare($insert_query);
            $insert_stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
            $insert_stmt->execute();
            
            $message = "Teacher assigned successfully";
        } else {
            $message = "Teacher already assigned to this subject";
        }
        
    } elseif($action === 'remove') {
        // Set end_date and status to completed (soft delete)
        $update_query = "
            UPDATE teacher_subject_assignment 
            SET end_date = CURDATE(), status = 'completed'
            WHERE teacher_id = ? AND subject_id = ? AND class_id = ? AND status = 'active'
        ";
        $update_stmt = $connection->prepare($update_query);
        $update_stmt->bind_param("iii", $teacher_id, $subject_id, $class_id);
        $update_stmt->execute();
        
        $message = "Teacher assignment removed";
    }
    
    $connection->commit();
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch(Exception $e) {
    $connection->rollback();
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>