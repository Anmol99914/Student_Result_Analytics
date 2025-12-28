<?php
// get-assignments.php
// API endpoint to get current assignments for a class
session_start();
require_once('../../../config.php');

header('Content-Type: application/json');

// Check admin session
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$class_id = intval($_GET['class_id'] ?? 0);

if($class_id <= 0) {
    echo json_encode(['error' => 'Invalid class ID']);
    exit();
}

try {
    $query = "
        SELECT 
            tsa.assignment_id,
            t.teacher_id,
            t.name as teacher_name,
            t.email as teacher_email,
            s.subject_id,
            s.subject_code,
            s.subject_name,
            s.credits,
            f.faculty_code,
            f.faculty_name,
            c.semester,
            c.batch_year,
            tsa.start_date,
            tsa.end_date,
            tsa.status
        FROM teacher_subject_assignment tsa
        JOIN teacher t ON tsa.teacher_id = t.teacher_id
        JOIN subject s ON tsa.subject_id = s.subject_id
        JOIN faculty f ON s.faculty_id = f.faculty_id
        JOIN class c ON tsa.class_id = c.class_id
        WHERE tsa.class_id = ? AND tsa.status = 'active'
        ORDER BY s.subject_code, t.name
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $assignments = [];
    while($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    
    // Get class details
    $class_query = "
        SELECT c.class_id, c.faculty, c.semester, c.batch_year,
               COUNT(DISTINCT se.student_id) as student_count
        FROM class c
        LEFT JOIN student_subject_enrollment se ON c.class_id = se.class_id
        WHERE c.class_id = ?
        GROUP BY c.class_id
    ";
    $class_stmt = $connection->prepare($class_query);
    $class_stmt->bind_param("i", $class_id);
    $class_stmt->execute();
    $class_details = $class_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'assignments' => $assignments,
        'class_details' => $class_details,
        'count' => count($assignments)
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>