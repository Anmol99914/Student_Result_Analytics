<?php
// update_teacher.php
require_once '../../config.php';

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Get POST data
$teacher_id = $_POST['teacher_id'] ?? 0;
$status = $_POST['status'] ?? '';

if (!$teacher_id || !$status) {
    echo json_encode(['success' => false, 'error' => 'Missing data']);
    exit();
}

// Update teacher status
$query = "UPDATE teacher SET status = '$status' WHERE teacher_id = $teacher_id";
$result = mysqli_query($connection, $query);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Teacher updated']);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($connection)]);
}

mysqli_close($connection);
?>