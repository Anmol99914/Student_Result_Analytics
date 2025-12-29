<?php
// add_teacher.php - FIXED CONFIG PATH
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// TRY MULTIPLE PATHS
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
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$status = $_POST['status'] ?? 'active';

// Validate
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email format']);
    exit();
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if email exists
$check = mysqli_query($connection, "SELECT teacher_id FROM teacher WHERE email = '$email'");
if (mysqli_num_rows($check) > 0) {
    echo json_encode(['success' => false, 'error' => 'Email already exists']);
    exit();
}

// Insert teacher
$query = "INSERT INTO teacher (name, email, password, status) 
          VALUES ('$name', '$email', '$hashed_password', '$status')";

if (mysqli_query($connection, $query)) {
    $new_id = mysqli_insert_id($connection);
    echo json_encode([
        'success' => true, 
        'message' => 'Teacher added successfully',
        'teacher_id' => $new_id
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($connection)]);
}

mysqli_close($connection);
?>