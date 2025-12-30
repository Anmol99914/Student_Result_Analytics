<?php
// File: PHP_Files/admin/student/edit_student.php
session_start();
include("../../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['status'=>'error','message'=>'Unauthorized access']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
    // Get student data for editing
    $student_id = $_GET['id'] ?? '';
    
    if(empty($student_id)){
        echo json_encode(['status'=>'error','message'=>'Student ID required']);
        exit();
    }
    
    $stmt = $connection->prepare("
        SELECT s.*, c.faculty, c.semester 
        FROM student s 
        LEFT JOIN class c ON s.class_id = c.class_id 
        WHERE s.student_id = ?
    ");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($student = $result->fetch_assoc()){
        echo json_encode(['status'=>'success','student'=>$student]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Student not found']);
    }
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Update student data
    $student_id = $_POST['student_id'] ?? '';
    $student_name = trim($_POST['student_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);
    $semester_id = intval($_POST['semester_id'] ?? 0);
    $is_active = intval($_POST['is_active'] ?? 1);
    
    // Validation
    if(empty($student_id) || empty($student_name) || empty($email)){
        echo json_encode(['status'=>'error','message'=>'Required fields missing']);
        exit();
    }
    
    // Check if email exists for another student
    $check_stmt = $connection->prepare("SELECT student_id FROM student WHERE email = ? AND student_id != ?");
    $check_stmt->bind_param("ss", $email, $student_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if($check_stmt->num_rows > 0){
        echo json_encode(['status'=>'error','message'=>'Email already registered for another student']);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
    
    // Update student
    $stmt = $connection->prepare("
        UPDATE student 
        SET student_name = ?, email = ?, phone_number = ?, 
            class_id = ?, semester_id = ?, is_active = ?, 
            updated_at = CURRENT_TIMESTAMP 
        WHERE student_id = ?
    ");
    $stmt->bind_param("sssiiiss", $student_name, $email, $phone_number, $class_id, $semester_id, $is_active, $student_id);
    
    if($stmt->execute()){
        echo json_encode(['status'=>'success','message'=>'Student updated successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>'Failed to update student']);
    }
    $stmt->close();
    exit();
}

echo json_encode(['status'=>'error','message'=>'Invalid request']);
?>