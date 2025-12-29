<?php
// File: PHP_Files/admin/api/get_subject_stats.php
// Purpose: API endpoint to get subject statistics

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Include config from root folder
require_once '../../../config.php';

// Default response
$response = ['success' => false, 'message' => '', 'data' => []];

try {
    // FIX: Check if connection exists
    if (!isset($connection) || $connection === null) {
        throw new Exception('Database connection failed');
    }
    
    // Get total subjects count
    $totalQuery = "SELECT COUNT(*) as total FROM subject WHERE is_active = 1";
    $totalResult = mysqli_query($connection, $totalQuery);
    
    if (!$totalResult) {
        throw new Exception('Query failed: ' . mysqli_error($connection));
    }
    
    $totalRow = mysqli_fetch_assoc($totalResult);
    
    // Get subjects by faculty
    $facultyQuery = "
        SELECT 
            f.faculty_id,
            f.faculty_name,
            COUNT(s.subject_id) as count
        FROM faculty f
        LEFT JOIN subject s ON f.faculty_id = s.faculty_id AND s.is_active = 1
        GROUP BY f.faculty_id, f.faculty_name
        ORDER BY f.faculty_id
    ";
    
    $facultyResult = mysqli_query($connection, $facultyQuery);
    
    if (!$facultyResult) {
        throw new Exception('Faculty query failed: ' . mysqli_error($connection));
    }
    
    $stats = [
        'total' => intval($totalRow['total']),
        'by_faculty' => [],
        'by_semester' => []
    ];
    
    // Process faculty counts
    while ($row = mysqli_fetch_assoc($facultyResult)) {
        $facultyId = $row['faculty_id'];
        $stats['by_faculty'][$facultyId] = [
            'name' => $row['faculty_name'],
            'count' => intval($row['count'])
        ];
        
        // Add individual faculty counts for quick access
        if ($facultyId == 1) $stats['bca'] = intval($row['count']);
        if ($facultyId == 2) $stats['bbm'] = intval($row['count']);
        if ($facultyId == 3) $stats['bim'] = intval($row['count']);
    }
    
    // Get subjects by semester
    $semesterQuery = "
        SELECT 
            semester,
            COUNT(*) as count
        FROM subject 
        WHERE is_active = 1
        GROUP BY semester
        ORDER BY semester
    ";
    
    $semesterResult = mysqli_query($connection, $semesterQuery);
    while ($row = mysqli_fetch_assoc($semesterResult)) {
        $stats['by_semester'][$row['semester']] = intval($row['count']);
    }
    
    // Success response
    $response['success'] = true;
    $response['message'] = 'Statistics retrieved successfully';
    $response['data'] = $stats;
    
} catch (Exception $e) {
    error_log("Stats API Error: " . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
?>