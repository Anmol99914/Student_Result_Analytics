<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle different actions
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch($action) {
    case 'update_teacher':
        handleUpdateTeacher();
        break;
    case 'activate':
        handleToggleStatus('active');
        break;
    case 'deactivate':
        handleToggleStatus('inactive');
        break;
    default:
        // Return teacher management HTML
        include('teacher_management_content.php');
}

function handleUpdateTeacher() {
    global $connection;
    
    $teacher_id = intval($_POST['teacher_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';
    $new_password = trim($_POST['new_password'] ?? '');
    
    // Validation
    if($teacher_id <= 0){
        echo json_encode(['success' => false, 'message' => 'Invalid teacher ID']);
        exit();
    }
    
    if(empty($name) || empty($email)){
        echo json_encode(['success' => false, 'message' => 'Name and email are required']);
        exit();
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit();
    }
    
    // Check if email already exists for another teacher
    $check_stmt = $connection->prepare("SELECT teacher_id FROM teacher WHERE email = ? AND teacher_id != ?");
    $check_stmt->bind_param("si", $email, $teacher_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0){
        echo json_encode(['success' => false, 'message' => 'Email already exists for another teacher']);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
    
    // Prepare update query
    if(!empty($new_password)){
        // Update with new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("UPDATE teacher SET name = ?, email = ?, status = ?, password = ? WHERE teacher_id = ?");
        $stmt->bind_param("ssssi", $name, $email, $status, $hashed_password, $teacher_id);
    } else {
        // Update without changing password
        $stmt = $connection->prepare("UPDATE teacher SET name = ?, email = ?, status = ? WHERE teacher_id = ?");
        $stmt->bind_param("sssi", $name, $email, $status, $teacher_id);
    }
    
    if($stmt->execute()){
        echo json_encode([
            'success' => true, 
            'message' => 'Teacher updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update teacher: ' . $connection->error
        ]);
    }
    
    $stmt->close();
}

function handleToggleStatus($newStatus) {
    global $connection;
    
    $teacher_id = intval($_GET['id'] ?? 0);
    
    if($teacher_id <= 0){
        echo json_encode(['success' => false, 'message' => 'Invalid teacher ID']);
        exit();
    }
    
    $stmt = $connection->prepare("UPDATE teacher SET status = ? WHERE teacher_id = ?");
    $stmt->bind_param("si", $newStatus, $teacher_id);
    
    if($stmt->execute()){
        echo json_encode([
            'success' => true, 
            'message' => 'Teacher status updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update teacher status'
        ]);
    }
    
    $stmt->close();
}
?>