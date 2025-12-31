<?php
session_start();
require_once '../../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get POST data
$class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
$subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
$teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : $_SESSION['teacher_id'];
$marks = isset($_POST['marks']) ? $_POST['marks'] : [];

if (!$class_id || !$subject_id || empty($marks)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

// Get semester from class
$semester_sql = "SELECT semester FROM class WHERE class_id = ?";
$semester_stmt = $connection->prepare($semester_sql);
$semester_stmt->bind_param("i", $class_id);
$semester_stmt->execute();
$semester_result = $semester_stmt->get_result();
$class_data = $semester_result->fetch_assoc();
$semester_id = $class_data['semester'] ?? 1;

$success_count = 0;
$error_count = 0;
$errors = [];

foreach ($marks as $student_id => $marks_obtained) {
    $marks_obtained = floatval($marks_obtained);
    $total_marks = 100; // Default total marks
    $percentage = ($marks_obtained / $total_marks) * 100;
    
    // Calculate grade
    $grade = calculateGrade($percentage);
    
    // Check if marks already exist
    $check_sql = "SELECT result_id FROM result WHERE student_id = ? AND subject_id = ? AND class_id = ?";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("sii", $student_id, $subject_id, $class_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Update existing
        $update_sql = "UPDATE result SET 
              marks_obtained = ?, 
              total_marks = ?, 
              percentage = ?, 
              grade = ?,
              verification_status = 'pending',
              published_date = NOW()
              WHERE student_id = ? AND subject_id = ?";
        
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("ddddsi", $marks_obtained, $total_marks, $percentage, $grade, $student_id, $subject_id);
        
        if ($update_stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
            $errors[] = "Student $student_id: " . $update_stmt->error;
        }
    } else {
        // Insert new
        $insert_sql = "INSERT INTO result (
            student_id, subject_id, marks_obtained, total_marks, 
            percentage, grade, semester_id, entered_by_teacher_id, 
            verification_status, published_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";
        
        $insert_stmt = $connection->prepare($insert_sql);
        $insert_stmt->bind_param("sidddsii", $student_id, $subject_id, $marks_obtained, 
                       $total_marks, $percentage, $grade, $semester_id, $teacher_id);
        
        if ($insert_stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
            $errors[] = "Student $student_id: " . $insert_stmt->error;
        }
    }
}

if ($error_count === 0) {
    echo json_encode([
        'success' => true, 
        'message' => "Marks saved for $success_count students",
        'status' => 'pending',
        'count' => $success_count
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => "Saved $success_count, failed $error_count",
        'errors' => $errors
    ]);
}

// Grade calculation function
function calculateGrade($percentage) {
    if ($percentage >= 90) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C+';
    if ($percentage >= 40) return 'C';
    return 'F';
}
?>