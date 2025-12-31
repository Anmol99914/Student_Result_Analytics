<!-- get_students.php -->
<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['class_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Class ID required']);
    exit();
}

$class_id = $_GET['class_id'];
$teacher_id = $_SESSION['teacher_id'];

// Verify teacher has access to this class
$check_sql = "SELECT COUNT(*) as can_access FROM class c 
              WHERE c.class_id = ? 
              AND (c.teacher_id = ? OR c.class_id = 
                  (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?))";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("iii", $class_id, $teacher_id, $teacher_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$can_access = $check_result->fetch_assoc()['can_access'];

if ($can_access == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied to this class']);
    exit();
}

// Get students in this class
$sql = "SELECT student_id, student_name, email, is_active 
        FROM student 
        WHERE class_id = ? 
        ORDER BY student_name";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $class_id);
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($students);
?>