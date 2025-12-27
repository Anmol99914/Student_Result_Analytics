<?php
// assign_teacher_to_class.php
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

header('Content-Type: application/json');

$teacher_id = intval($_POST['teacher_id'] ?? 0);
$class_id = intval($_POST['class_id'] ?? 0);

if($teacher_id <= 0 || $class_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Check teacher exists and active
$teacher_check = $connection->query("SELECT * FROM teacher WHERE teacher_id = $teacher_id");
if($teacher_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Teacher not found']);
    exit();
}

$teacher = $teacher_check->fetch_assoc();
if($teacher['status'] != 'active') {
    echo json_encode(['success' => false, 'message' => 'Teacher is inactive']);
    exit();
}

// Check class exists
$class_check = $connection->query("SELECT * FROM class WHERE class_id = $class_id");
if($class_check->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Class not found']);
    exit();
}

// Check if already assigned
$existing = $connection->query("SELECT * FROM teacher_class_assignments WHERE teacher_id = $teacher_id AND class_id = $class_id");
if($existing->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Already assigned to this class']);
    exit();
}

// Assign teacher
$sql = "INSERT INTO teacher_class_assignments (teacher_id, class_id, assigned_date) VALUES ($teacher_id, $class_id, NOW())";
if($connection->query($sql)) {
    echo json_encode([
        'success' => true, 
        'message' => "✅ Successfully assigned {$teacher['name']} to class"
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $connection->error]);
}
?>