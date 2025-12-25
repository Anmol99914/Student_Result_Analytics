<?php
// get_teacher_stats.php - Updated to match query logic
session_start();
include('../../config.php');

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    echo json_encode([]);
    exit();
}

// Get total teachers
$total_query = "SELECT COUNT(*) as count FROM teacher";
$total_result = $connection->query($total_query);
$total = $total_result->fetch_assoc()['count'];

// Get active teachers
$active_query = "SELECT COUNT(*) as count FROM teacher WHERE status = 'active'";
$active_result = $connection->query($active_query);
$active = $active_result->fetch_assoc()['count'];

// Get inactive teachers
$inactive_query = "SELECT COUNT(*) as count FROM teacher WHERE status = 'inactive'";
$inactive_result = $connection->query($inactive_query);
$inactive = $inactive_result->fetch_assoc()['count'];

// Get unassigned teachers - MUST MATCH THE QUERY IN admin_teachers_table.php
$unassigned_query = "SELECT COUNT(*) as count FROM teacher 
                     WHERE status = 'active' 
                     AND (assigned_class_id IS NULL OR assigned_class_id = 0 OR assigned_class_id = '')";
$unassigned_result = $connection->query($unassigned_query);
$unassigned = $unassigned_result->fetch_assoc()['count'];

// DEBUG: Let's see the breakdown
$debug_query = "SELECT 
    teacher_id, 
    name, 
    status, 
    assigned_class_id,
    CASE 
        WHEN assigned_class_id IS NULL THEN 'NULL'
        WHEN assigned_class_id = 0 THEN 'ZERO'
        WHEN assigned_class_id = '' THEN 'EMPTY'
        ELSE 'ASSIGNED'
    END as assignment_status
FROM teacher 
WHERE status = 'active' 
ORDER BY teacher_id";
$debug_result = $connection->query($debug_query);

$debug_data = [];
while($row = $debug_result->fetch_assoc()) {
    $debug_data[] = $row;
}

echo json_encode([
    'total' => (int)$total,
    'active' => (int)$active,
    'inactive' => (int)$inactive,
    'unassigned' => (int)$unassigned,
    'debug' => $debug_data // Remove this line in production
]);
?>