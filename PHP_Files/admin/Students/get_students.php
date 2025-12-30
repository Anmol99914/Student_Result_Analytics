<?php
session_start();
include("../../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode([]);
    exit();
}

// Check if stats are requested
if (isset($_GET['stats']) && $_GET['stats'] == 'true') {
    // Get total students
    $totalQuery = "SELECT COUNT(*) as total FROM student";
    $totalResult = $connection->query($totalQuery);
    $total = $totalResult->fetch_assoc()['total'];
    
    // Get active students
    $activeQuery = "SELECT COUNT(*) as active FROM student WHERE is_active = 1";
    $activeResult = $connection->query($activeQuery);
    $active = $activeResult->fetch_assoc()['active'];
    
    // Get students with pending payments (you need to implement this based on your payment table)
    $pendingQuery = "SELECT COUNT(DISTINCT p.student_id) as pending 
                     FROM payment p 
                     WHERE p.payment_status != 'Paid'";
    $pendingResult = $connection->query($pendingQuery);
    $pending = $pendingResult->fetch_assoc()['pending'] ?? 0;
    
    // Get recent students (last 7 days)
    $recentQuery = "SELECT COUNT(*) as recent FROM student 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    $recentResult = $connection->query($recentQuery);
    $recent = $recentResult->fetch_assoc()['recent'];
    
    echo json_encode([
        'total' => $total,
        'active' => $active,
        'pending' => $pending,
        'recent' => $recent
    ]);
    exit();
}

$query = "
    SELECT 
        s.student_id,
        s.student_name,
        s.email,
        s.phone_number,
        s.class_id,
        s.semester_id,
        s.is_active,
        s.created_at,
        c.faculty,
        c.semester
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    ORDER BY s.created_at DESC
";

$result = $connection->query($query);
$students = [];

while($row = $result->fetch_assoc()){
    $students[] = $row;
}

echo json_encode($students);
?>