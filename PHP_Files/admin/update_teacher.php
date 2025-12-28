<?php
// update_teacher.php - AJAX endpoint for updating teacher
session_start();
include('../../config.php');

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$teacher_id = intval($_POST['teacher_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$status = $_POST['status'] ?? 'active';
$new_password = trim($_POST['new_password'] ?? '');

if($teacher_id < 1 || empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Required fields missing']);
    exit();
}

try {
    // Get current teacher data
    $current_sql = "SELECT email, status FROM teacher WHERE teacher_id = ?";
    $current_stmt = $connection->prepare($current_sql);
    $current_stmt->bind_param("i", $teacher_id);
    $current_stmt->execute();
    $current_result = $current_stmt->get_result();
    
    if($current_result->num_rows === 0) {
        throw new Exception("Teacher not found");
    }
    
    $current_teacher = $current_result->fetch_assoc();
    $old_email = $current_teacher['email'];
    
    // Check if new email exists (for another teacher)
    if($email != $old_email) {
        $check_sql = "SELECT teacher_id FROM teacher WHERE email = ? AND teacher_id != ?";
        $check_stmt = $connection->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $teacher_id);
        $check_stmt->execute();
        
        if($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already registered to another teacher");
        }
    }
    
    // Start transaction
    $connection->begin_transaction();
    
    // Update teacher table
    if(!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE teacher SET name = ?, email = ?, status = ?, password = ? WHERE teacher_id = ?";
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("ssssi", $name, $email, $status, $hashed_password, $teacher_id);
    } else {
        $update_sql = "UPDATE teacher SET name = ?, email = ?, status = ? WHERE teacher_id = ?";
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("sssi", $name, $email, $status, $teacher_id);
    }
    
    if(!$update_stmt->execute()) {
        throw new Exception("Failed to update teacher: " . $update_stmt->error);
    }
    
    // Update users table
    if($email != $old_email || !empty($new_password)) {
        if(empty($new_password)) {
            // Get current password
            $pass_sql = "SELECT password FROM teacher WHERE teacher_id = ?";
            $pass_stmt = $connection->prepare($pass_sql);
            $pass_stmt->bind_param("i", $teacher_id);
            $pass_stmt->execute();
            $password = $pass_stmt->get_result()->fetch_assoc()['password'];
        } else {
            $password = $hashed_password;
        }
        
        // Update or insert user
        $user_check = "SELECT user_id FROM users WHERE username = ? AND role = 'teacher'";
        $user_stmt = $connection->prepare($user_check);
        $user_stmt->bind_param("s", $old_email);
        $user_stmt->execute();
        
        if($user_stmt->get_result()->num_rows > 0) {
            // Update existing
            $user_update = "UPDATE users SET username = ?, password = ?, status = ? WHERE username = ? AND role = 'teacher'";
            $user_upd_stmt = $connection->prepare($user_update);
            $user_upd_stmt->bind_param("ssss", $email, $password, $status, $old_email);
            $user_upd_stmt->execute();
        } else {
            // Insert new
            $user_insert = "INSERT INTO users (username, password, role, status) VALUES (?, ?, 'teacher', ?)";
            $user_ins_stmt = $connection->prepare($user_insert);
            $user_ins_stmt->bind_param("sss", $email, $password, $status);
            $user_ins_stmt->execute();
        }
    } else {
        // Only update status
        $user_status = "UPDATE users SET status = ? WHERE username = ? AND role = 'teacher'";
        $user_stat_stmt = $connection->prepare($user_status);
        $user_stat_stmt->bind_param("ss", $status, $email);
        $user_stat_stmt->execute();
    }
    
    // Commit
    $connection->commit();
    
    echo json_encode([
        'success' => true,
        'message' => '✅ Teacher updated successfully!',
        'teacher_id' => $teacher_id
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode([
        'success' => false,
        'message' => '❌ Error: ' . $e->getMessage()
    ]);
}

$connection->close();
?>