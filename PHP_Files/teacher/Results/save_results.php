<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get form data
$student_id = $_POST['student_id'] ?? '';
$subject_id = $_POST['subject_id'] ?? '';
$marks_obtained = $_POST['marks_obtained'] ?? '';
$total_marks = $_POST['total_marks'] ?? '';
$semester_id = $_POST['semester_id'] ?? '';
$remarks = $_POST['remarks'] ?? '';
$teacher_id = $_SESSION['teacher_id'];

// Validate inputs
if (empty($student_id) || empty($subject_id) || empty($marks_obtained) || empty($total_marks)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!is_numeric($marks_obtained) || !is_numeric($total_marks)) {
    echo json_encode(['success' => false, 'message' => 'Marks must be numbers']);
    exit();
}

if ($marks_obtained > $total_marks) {
    echo json_encode(['success' => false, 'message' => 'Obtained marks cannot exceed total marks']);
    exit();
}

// Verify teacher has access to this student
$check_sql = "SELECT s.class_id, c.semester 
              FROM student s 
              JOIN class c ON s.class_id = c.class_id
              WHERE s.student_id = ? 
              AND (c.teacher_id = ? OR c.class_id = 
                  (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?))";
$check_stmt = $connection->prepare($check_sql);
$check_stmt->bind_param("sii", $student_id, $teacher_id, $teacher_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$student_data = $check_result->fetch_assoc();

if (!$student_data) {
    echo json_encode(['success' => false, 'message' => 'Access denied to this student']);
    exit();
}

// Use student's semester if not provided
if (empty($semester_id)) {
    $semester_id = $student_data['semester'];
}

// Check if result already exists
$check_existing_sql = "SELECT result_id FROM result 
                       WHERE student_id = ? AND subject_id = ? AND semester_id = ?";
$check_existing_stmt = $connection->prepare($check_existing_sql);
$check_existing_stmt->bind_param("sii", $student_id, $subject_id, $semester_id);
$check_existing_stmt->execute();
$existing_result = $check_existing_stmt->get_result()->fetch_assoc();

// Helper functions for grade calculation
function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C+';
    if ($percentage >= 40) return 'C';
    return 'F';
}

function calculateStatus($grade) {
    $statusMap = [
        'A+' => 'Distinction',
        'A' => 'Excellent',
        'B+' => 'Very Good',
        'B' => 'Good',
        'C+' => 'Satisfactory',
        'C' => 'Pass',
        'F' => 'Fail'
    ];
    return $statusMap[$grade] ?? 'Fail';
}

try {
    // Calculate percentage
    $percentage = ($marks_obtained / $total_marks) * 100;
    
    // Calculate grade based on percentage
    $grade = calculateGrade($percentage);
    $status = calculateStatus($grade);

    if ($existing_result) {
        // Update existing result with grade and status
        $sql = "UPDATE result 
                SET marks_obtained = ?, total_marks = ?, 
                    percentage = ?, grade = ?, status = ?,
                    remarks = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE result_id = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("dddsssi", 
            $marks_obtained, $total_marks, $percentage, 
            $grade, $status, $remarks, $existing_result['result_id']
        );
    } else {
        // Insert new result with grade and status
        $sql = "INSERT INTO result 
                (student_id, subject_id, marks_obtained, total_marks, 
                 percentage, grade, status, semester_id, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sidddssis", 
            $student_id, $subject_id, $marks_obtained, $total_marks,
            $percentage, $grade, $status, $semester_id, $remarks
        );
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Results saved successfully!',
            'action' => $existing_result ? 'updated' : 'created',
            'data' => [
                'percentage' => round($percentage, 2),
                'grade' => $grade,
                'status' => $status
            ]
        ]);
    } else {
        throw new Exception($stmt->error);
    }
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error saving results: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error saving results: ' . $e->getMessage()
    ]);
}
?>