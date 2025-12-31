<!-- check_previous_marks.php -->
<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['student_id']) || !isset($_GET['subject_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Student ID and Subject ID required']);
    exit();
}

$student_id = $_GET['student_id'];
$subject_id = $_GET['subject_id'];
$teacher_id = $_SESSION['teacher_id'];

// Verify teacher has access to this student
$check_sql = "SELECT COUNT(*) as can_access 
              FROM student s 
              JOIN class c ON s.class_id = c.class_id
              WHERE s.student_id = ? 
              AND (c.teacher_id = ? OR c.class_id = 
                  (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?))";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("sii", $student_id, $teacher_id, $teacher_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$can_access = $check_result->fetch_assoc()['can_access'];

if ($can_access == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied to this student']);
    exit();
}

// Check for previous marks
$sql = "SELECT marks_obtained, total_marks 
        FROM result 
        WHERE student_id = ? AND subject_id = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("si", $student_id, $subject_id);
$stmt->execute();
$result = $stmt->get_result();
$previous = $result->fetch_assoc();

if ($previous) {
    $percentage = ($previous['marks_obtained'] / $previous['total_marks']) * 100;
    echo json_encode([
        'exists' => true,
        'marks_obtained' => $previous['marks_obtained'],
        'total_marks' => $previous['total_marks'],
        'percentage' => round($percentage, 1)
    ]);
} else {
    echo json_encode(['exists' => false]);
}
?>