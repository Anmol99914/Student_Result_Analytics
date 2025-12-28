<?php
// get-subjects.php
// API endpoint to get subjects by faculty and semester
session_start();
require_once('../../../config.php');

header('Content-Type: application/json');

// Check admin session
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get parameters
$faculty = $_GET['faculty'] ?? '';
$semester = intval($_GET['semester'] ?? 0);
$class_id = intval($_GET['class_id'] ?? 0);

if(empty($faculty) || $semester <= 0) {
    echo json_encode(['error' => 'Invalid parameters']);
    exit();
}

try {
    // Get subjects for the faculty and semester
    $query = "
        SELECT s.subject_id, s.subject_code, s.subject_name, s.credits,
               f.faculty_code, f.faculty_name
        FROM subject s
        JOIN faculty f ON s.faculty_id = f.faculty_id
        WHERE f.faculty_code = ? AND s.semester = ? AND s.status = 'active'
        ORDER BY s.subject_code
    ";
    
    $stmt = $connection->prepare($query);
    $stmt->bind_param("si", $faculty, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    while($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    
    // If class_id provided, get already assigned subjects
    $assigned_subjects = [];
    if($class_id > 0) {
        $assignment_query = "
            SELECT DISTINCT subject_id 
            FROM teacher_subject_assignment 
            WHERE class_id = ? AND status = 'active'
        ";
        $stmt2 = $connection->prepare($assignment_query);
        $stmt2->bind_param("i", $class_id);
        $stmt2->execute();
        $assignment_result = $stmt2->get_result();
        
        while($row = $assignment_result->fetch_assoc()) {
            $assigned_subjects[] = $row['subject_id'];
        }
    }
    
    echo json_encode([
        'success' => true,
        'subjects' => $subjects,
        'assigned_subjects' => $assigned_subjects,
        'count' => count($subjects)
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>