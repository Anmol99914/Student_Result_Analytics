<?php
// toggle_teacher_status.php - FOR SYNCHRONIZED TABLES
session_start();
header('Content-Type: application/json');
include('../../config.php');

if(!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

$teacher_id = intval($_POST['teacher_id'] ?? 0);
$action = $_POST['action'] ?? '';

if($teacher_id < 1 || !in_array($action, ['activate', 'deactivate'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    // Get teacher details
    $sql = "SELECT teacher_id, name, email, status FROM teacher WHERE teacher_id = ?";
    $stmt = $connection->prepare($sql);
    
    if(!$stmt) {
        throw new Exception("Database error: " . $connection->error);
    }
    
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 0) {
        throw new Exception("Teacher not found with ID: " . $teacher_id);
    }

    $teacher = $result->fetch_assoc();
    $teacher_email = $teacher['email'];
    $current_status = $teacher['status'];
    $new_status = ($action === 'activate') ? 'active' : 'inactive';

    // Don't update if already in desired state
    if($current_status === $new_status) {
        $message = ($action === 'activate') 
            ? 'Teacher is already active' 
            : 'Teacher is already inactive';
        echo json_encode(['success' => true, 'message' => $message]);
        exit();
    }

    // Start transaction
    $connection->begin_transaction();
    
    // 1. Update teacher table
    $update_teacher = "UPDATE teacher SET status = ? WHERE teacher_id = ?";
    $stmt1 = $connection->prepare($update_teacher);
    if(!$stmt1) throw new Exception("Teacher update prepare failed");
    $stmt1->bind_param("si", $new_status, $teacher_id);
    
    if(!$stmt1->execute()) {
        throw new Exception("Teacher update failed: " . $stmt1->error);
    }
    
    // 2. Update users table (teacher should exist there now after sync)
    $update_user = "UPDATE users SET status = ? WHERE username = ? AND role = 'teacher'";
    $stmt2 = $connection->prepare($update_user);
    
    $users_updated = false;
    if($stmt2) {
        $stmt2->bind_param("ss", $new_status, $teacher_email);
        if($stmt2->execute()) {
            $users_updated = true;
        }
    }
    
    // Commit transaction
    $connection->commit();
    
    // Prepare message
    $login_note = $users_updated 
        ? " Login status has been updated." 
        : " Note: Could not update login credentials.";
    
    $message = ($action === 'activate') 
        ? "✅ Teacher activated successfully!" . $login_note
        : "⚠️ Teacher deactivated successfully!" . $login_note;
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'new_status' => $new_status,
        'teacher_name' => $teacher['name'],
        'users_updated' => $users_updated
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $connection->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$connection->close();
?>