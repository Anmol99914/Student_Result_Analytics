<?php
// get_teacher.php - FIXED CONFIG PATH
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// TRY MULTIPLE PATHS - Same as get_teachers.php
$possiblePaths = [
    dirname(dirname(dirname(__FILE__))) . '/config.php', // ../../../config.php
    $_SERVER['DOCUMENT_ROOT'] . '/Student_Result_Analytics/config.php',
    'C:/xampp/htdocs/Student_Result_Analytics/config.php'
];

$configLoaded = false;
foreach ($possiblePaths as $configPath) {
    if (file_exists($configPath)) {
        require_once $configPath;
        $configLoaded = true;
        break;
    }
}

if (!$configLoaded) {
    echo json_encode([
        'success' => false, 
        'error' => 'Config not found. Tried: ' . json_encode($possiblePaths)
    ]);
    exit();
}

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
    'stats' => $stats,
    'debug' => ['config_loaded' => true]
]);

mysqli_close($connection);
?>