<?php
// get_teacher.php - Get single teacher details
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Load config
$configPath = dirname(dirname(dirname(__FILE__))) . '/config.php';
if (!file_exists($configPath)) {
    echo json_encode(['success' => false, 'error' => 'Config not found']);
    exit();
}

require_once $configPath;

$teacher_id = intval($_GET['teacher_id'] ?? 0);

if ($teacher_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid teacher ID']);
    exit();
}

// Get teacher details
$query = "SELECT teacher_id, name, email, status, created_at FROM teacher WHERE teacher_id = $teacher_id";
$result = mysqli_query($connection, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'error' => 'Teacher not found']);
    exit();
}

$teacher = mysqli_fetch_assoc($result);

// Get teacher assignments stats
$statsQuery = "SELECT 
    COUNT(DISTINCT subject_id) as subject_count,
    COUNT(DISTINCT class_id) as class_count
    FROM teacher_subject_assignment 
    WHERE teacher_id = $teacher_id";
    
$statsResult = mysqli_query($connection, $statsQuery);
$stats = mysqli_fetch_assoc($statsResult);

echo json_encode([
    'success' => true,
    'teacher' => $teacher,
    'stats' => $stats
]);

mysqli_close($connection);
?>