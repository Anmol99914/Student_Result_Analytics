<?php
// get-teachers.php
// API endpoint to get teachers (with current assignments info)
session_start();
require_once('../../../config.php');

header('Content-Type: application/json');

// Check admin session
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get parameters
$subject_id = intval($_GET['subject_id'] ?? 0);
$class_id = intval($_GET['class_id'] ?? 0);

try {
    // Base query
    $query = "
        SELECT t.teacher_id, t.name, t.email, t.status,
               COUNT(tsa.assignment_id) as current_assignments
        FROM teacher t
        LEFT JOIN teacher_subject_assignment tsa ON t.teacher_id = tsa.teacher_id 
            AND tsa.status = 'active'
        WHERE t.status = 'active'
        GROUP BY t.teacher_id
        ORDER BY t.name
    ";
    
    $result = $connection->query($query);
    
    $teachers = [];
    while($row = $result->fetch_assoc()) {
        // Check if teacher is already assigned to this subject+class
        $is_assigned = false;
        if($subject_id > 0 && $class_id > 0) {
            $check_query = "
                SELECT 1 FROM teacher_subject_assignment 
                WHERE teacher_id = ? AND subject_id = ? AND class_id = ? AND status = 'active'
            ";
            $check_stmt = $connection->prepare($check_query);
            $check_stmt->bind_param("iii", $row['teacher_id'], $subject_id, $class_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $is_assigned = $check_result->num_rows > 0;
        }
        
        $teachers[] = [
            'id' => $row['teacher_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'current_assignments' => $row['current_assignments'],
            'is_assigned' => $is_assigned
        ];
    }
    
    echo json_encode([
        'success' => true,
        'teachers' => $teachers,
        'count' => count($teachers)
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>