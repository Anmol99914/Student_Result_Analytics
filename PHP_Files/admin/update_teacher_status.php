<?php
// get_teacher_stats.php - Optimized version
session_start();
include('../../config.php');

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    echo json_encode([]);
    exit();
}

// Single query to get all stats
$query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
    SUM(CASE WHEN status = 'active' AND (assigned_class_id IS NULL OR assigned_class_id = 0 OR assigned_class_id = '') THEN 1 ELSE 0 END) as unassigned,
    (SELECT COUNT(DISTINCT t.teacher_id) FROM teacher t INNER JOIN class c ON t.teacher_id = c.teacher_id WHERE t.status = 'active') as teaching
FROM teacher";

$result = $connection->query($query);
$stats = $result->fetch_assoc();

// Calculate assigned percentage
$assignedCount = $stats['active'] - $stats['unassigned'];
$stats['assigned_percent'] = $stats['active'] > 0 ? round(($assignedCount / $stats['active']) * 100) : 0;

echo json_encode([
    'total' => (int)$stats['total'],
    'active' => (int)$stats['active'],
    'inactive' => (int)$stats['inactive'],
    'unassigned' => (int)$stats['unassigned'],
    'teaching' => (int)$stats['teaching'],
    'assigned_percent' => (int)$stats['assigned_percent']
]);
?>