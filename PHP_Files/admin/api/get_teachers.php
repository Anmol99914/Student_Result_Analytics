<?php
// get_teachers.php - FIXED CONFIG PATH
header('Content-Type: application/json');

session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'success' => false, 
        'error' => 'Not authenticated. Please login.',
        'session_id' => session_id()
    ]);
    exit();
}

// Try multiple paths to find config.php
$possiblePaths = [
    // From PHP_Files/admin/api/ to root: ../../../config.php
    dirname(dirname(dirname(__FILE__))) . '/config.php', // ../../../config.php
    
    // Absolute path
    $_SERVER['DOCUMENT_ROOT'] . '/Student_Result_Analytics/config.php',
    
    // Windows path
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
        'error' => 'Config file not found. Tried: ' . implode(', ', $possiblePaths)
    ]);
    exit();
}

// Check connection
if (!$connection) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed'
    ]);
    exit();
}

// Get filter parameter
$status = $_GET['status'] ?? 'active';

// Build query
$query = "SELECT teacher_id, name, email, status, created_at FROM teacher";

if ($status === 'active' || $status === 'inactive') {
    $query .= " WHERE status = '$status'";
}
// 'all' shows all teachers

$query .= " ORDER BY created_at DESC";

// Execute query
$result = mysqli_query($connection, $query);

if (!$result) {
    echo json_encode([
        'success' => false,
        'error' => 'Query failed: ' . mysqli_error($connection)
    ]);
    exit();
}

$teachers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $teachers[] = $row;
}

// Get counts
$countQuery = "SELECT 
    SUM(status = 'active') as active_count,
    SUM(status = 'inactive') as inactive_count,
    COUNT(*) as total_count
    FROM teacher";

$countResult = mysqli_query($connection, $countQuery);
$counts = mysqli_fetch_assoc($countResult);

// Success response
echo json_encode([
    'success' => true,
    'teachers' => $teachers,
    'counts' => $counts,
    'total' => count($teachers),
    'status' => $status,
    'debug' => [
        'config_path' => 'found',
        'teachers_found' => count($teachers)
    ]
]);

// Close connection
mysqli_close($connection);
?>