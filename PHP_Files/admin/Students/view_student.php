<?php
// File: PHP_Files/admin/student/view_student.php
session_start();
include("../../../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['status'=>'error','message'=>'Unauthorized access']);
    exit();
}

$student_id = $_GET['id'] ?? '';

if(empty($student_id)){
    echo json_encode(['status'=>'error','message'=>'Student ID required']);
    exit();
}

// Fetch student details with all information
$stmt = $connection->prepare("
    SELECT 
        s.*,
        c.faculty,
        c.semester,
        sem.semester_name,
        f.faculty_name
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    LEFT JOIN semester sem ON s.semester_id = sem.semester_id
    LEFT JOIN faculty f ON c.faculty = f.faculty_code
    WHERE s.student_id = ?
");

$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if($student = $result->fetch_assoc()){
    // Get payment info
    $payment_stmt = $connection->prepare("
        SELECT SUM(amount_paid) as total_paid, 
               SUM(total_amount) as total_due,
               MAX(payment_date) as last_payment
        FROM payment 
        WHERE student_id = ?
    ");
    $payment_stmt->bind_param("s", $student_id);
    $payment_stmt->execute();
    $payment = $payment_stmt->get_result()->fetch_assoc();
    
    // Get results info
    $result_stmt = $connection->prepare("
        SELECT COUNT(*) as total_results,
               AVG(percentage) as avg_percentage
        FROM result 
        WHERE student_id = ? AND status = 'published'
    ");
    $result_stmt->bind_param("s", $student_id);
    $result_stmt->execute();
    $results = $result_stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'student' => $student,
        'payment' => $payment,
        'results' => $results
    ]);
} else {
    echo json_encode(['status'=>'error','message'=>'Student not found']);
}

$stmt->close();
?>