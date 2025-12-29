<?php
// update_teacher.php - FIXED CONFIG PATH
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// TRY MULTIPLE PATHS - Same as other APIs
$possiblePaths = [
    dirname(dirname(dirname(__FILE__))) . '/config.php',
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
    echo json_encode(['success' => false, 'error' => 'Config not found']);
    exit();
}

// Get POST data
$teacher_id = intval($_POST['teacher_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$status = $_POST['status'] ?? 'active';
$password = $_POST['password'] ?? '';

// Validate
if ($teacher_id <= 0 || empty($name) || empty($email)) {
    echo json_encode(['success' => false, 'error' => 'Required fields missing']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Check if email exists for other teacher
$checkQuery = "SELECT teacher_id FROM teacher WHERE email = '$email' AND teacher_id != $teacher_id";
$checkResult = mysqli_query($connection, $checkQuery);
if (mysqli_num_rows($checkResult) > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already used by another teacher']);
    exit();
}

// Build update query
$updates = [];
$updates[] = "name = '$name'";
$updates[] = "email = '$email'";
$updates[] = "status = '$status'";

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $updates[] = "password = '$hashed_password'";
}

$query = "UPDATE teacher SET " . implode(', ', $updates) . " WHERE teacher_id = $teacher_id";

if (mysqli_query($connection, $query)) {
    echo json_encode([
        'success' => true, 
        'message' => 'Teacher updated successfully',
        'teacher_id' => $teacher_id
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($connection)]);
}

mysqli_close($connection);
?>