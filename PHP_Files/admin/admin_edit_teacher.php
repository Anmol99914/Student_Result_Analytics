<?php
// admin_edit_teacher.php - Updated version
session_start();
include('../../config.php');

header('Content-Type: application/json'); // Always return JSON

$teacher_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0);
if($teacher_id == 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid teacher ID']);
    exit();
}

// Fetch teacher data
$stmt = $connection->prepare("SELECT * FROM teacher WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if(!$teacher) {
    echo json_encode(['success' => false, 'message' => 'Teacher not found']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $assigned_class_id = !empty($_POST['assigned_class_id']) ? intval($_POST['assigned_class_id']) : NULL;
    $status = $_POST['status'] ?? 'active';
    
    // Check if email changed and exists
    $error = '';
    if($email != $teacher['email']) {
        $check = $connection->prepare("SELECT teacher_id FROM teacher WHERE email = ? AND teacher_id != ?");
        $check->bind_param("si", $email, $teacher_id);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $error = "Email already registered to another teacher!";
        }
    }
    
    if(!$error) {
        // Update teacher
        $update = $connection->prepare("UPDATE teacher SET name=?, email=?, assigned_class_id=?, status=? WHERE teacher_id=?");
        $update->bind_param("ssisi", $name, $email, $assigned_class_id, $status, $teacher_id);
        
        if($update->execute()) {
            // Also update users table if email changed
            if($email != $teacher['email']) {
                $user_update = $connection->prepare("UPDATE users SET username=? WHERE username=? AND role='teacher'");
                $user_update->bind_param("ss", $email, $teacher['email']);
                $user_update->execute();
            }
            
            echo json_encode(['success' => true, 'message' => 'Teacher updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating teacher: ' . $connection->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $error]);
    }
    exit();
}

// For GET requests, return teacher data as JSON
echo json_encode([
    'success' => true,
    'teacher' => [
        'teacher_id' => $teacher['teacher_id'],
        'name' => $teacher['name'],
        'email' => $teacher['email'],
        'status' => $teacher['status'],
        'assigned_class_id' => $teacher['assigned_class_id']
    ]
]);
?>