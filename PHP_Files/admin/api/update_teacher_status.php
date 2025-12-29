<?php
// update_teacher_status.php - FIXED CONFIG PATH
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// TRY MULTIPLE PATHS - From api folder to config.php
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
        'error' => 'Config not found. Tried paths: ' . json_encode($possiblePaths)
    ]);
    exit();
}

// Get POST data
$teacher_id = intval($_POST['teacher_id'] ?? 0);
$status = $_POST['status'] ?? 'active';

// Validate
if ($teacher_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid teacher ID']);
    exit();
}

// Check if teacher exists
$check = mysqli_query($connection, "SELECT teacher_id FROM teacher WHERE teacher_id = $teacher_id");
if (mysqli_num_rows($check) === 0) {
    echo json_encode(['success' => false, 'error' => 'Teacher not found']);
    exit();
}

// Update status
$query = "UPDATE teacher SET status = '$status' WHERE teacher_id = $teacher_id";

if (mysqli_query($connection, $query)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Teacher status updated to ' . $status,
        'teacher_id' => $teacher_id,
        'new_status' => $status
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($connection)]);
}

mysqli_close($connection);
?>