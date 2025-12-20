<?php
// get_teacher_classes.php
session_start();
require_once '../../config.php'; // Adjust path based on your structure

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

try {
    // Query to get classes assigned to this teacher
    $sql = "SELECT c.*, 
                   (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count
            FROM class c
            WHERE c.teacher_id = ? 
            OR c.class_id = (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?)";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("ii", $teacher_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $classes = $result->fetch_all(MYSQLI_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($classes);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>