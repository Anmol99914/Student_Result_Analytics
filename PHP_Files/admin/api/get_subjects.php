<?php
// File: PHP_Files/admin/api/get_subjects.php
// Purpose: API endpoint to fetch subjects with filtering and pagination

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
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
$response = [
    'success' => false,
    'message' => 'Unknown error',
    'data' => [],
    'total' => 0,
    'pages' => 0
];

try {
    // Get request parameters
    $subject_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
    $offset = ($page - 1) * $limit;
    
    $faculty_id = isset($_GET['faculty_id']) && $_GET['faculty_id'] !== '' 
    ? intval($_GET['faculty_id']) 
    : null;
    
    $semester = isset($_GET['semester']) && $_GET['semester'] !== '' 
        ? intval($_GET['semester']) 
        : null;
        
    $status = isset($_GET['status']) && $_GET['status'] !== '' 
        ? intval($_GET['status']) 
        : null;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Build WHERE conditions
    $conditions = [];
    $params = [];
    $types = '';
    
    // Single subject by ID
    if ($subject_id) {
        $conditions[] = "s.subject_id = ?";
        $params[] = $subject_id;
        $types .= 'i';
    }
    
    // Filter by faculty
    if ($faculty_id) {
        $conditions[] = "s.faculty_id = ?";
        $params[] = $faculty_id;
        $types .= 'i';
    }
    
    // Filter by semester
    if ($semester) {
        $conditions[] = "s.semester = ?";
        $params[] = $semester;
        $types .= 'i';
    }
    
    // Filter by status
    if ($status !== null && $status !== '') {
        $conditions[] = "s.is_active = ?";
        $params[] = $status;
        $types .= 'i';
    }else if(empty($subject_id)) {
        // Only apply active filter when NOT getting single subject
        $conditions[] = "s.is_active = 1";
    }
    
    // Search by name or code
    if (!empty($search)) {
        $conditions[] = "(s.subject_name LIKE ? OR s.subject_code LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= 'ss';
    }
    
    // Always get active subjects unless specified otherwise
    if ($status === null) {
        $conditions[] = "s.is_active = 1";
    }
    
    // Build WHERE clause
    $whereClause = '';
    if (!empty($conditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $conditions);
    }
    
    // === DEBUG LOGGING ===
    error_log("=== SUBJECT API DEBUG ===");
    error_log("Conditions: " . print_r($conditions, true));
    error_log("Params before limit: " . print_r($params, true));
    error_log("Types before limit: " . $types);
    error_log("WHERE Clause: " . $whereClause);
    error_log("Faculty ID: " . ($faculty_id ?? 'null'));
    error_log("Semester: " . ($semester ?? 'null'));
    error_log("Status: " . ($status ?? 'null'));
    error_log("Search: " . $search);
    
    // Get total count for pagination (USE SEPARATE PARAMS - no limit/offset)
    $countSql = "SELECT COUNT(*) as total FROM subject s $whereClause";
    error_log("Count SQL: " . $countSql);
    
    $countStmt = mysqli_prepare($connection, $countSql);
    
    // Bind parameters only if we have conditions
    if (!empty($conditions) && !empty($types)) {
        mysqli_stmt_bind_param($countStmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($countStmt);
    $countResult = mysqli_stmt_get_result($countStmt);
    $totalRow = mysqli_fetch_assoc($countResult);
    $total = $totalRow['total'];
    
    error_log("Total subjects with WHERE: " . $total);
    
    // Test without WHERE to verify data exists
    if ($total == 0) {
        $testSql = "SELECT COUNT(*) as test FROM subject";
        $testResult = mysqli_query($connection, $testSql);
        $testRow = mysqli_fetch_assoc($testResult);
        error_log("Total without WHERE: " . $testRow['test']);
    }
    
    // Calculate total pages
    $totalPages = ceil($total / $limit);
    
    // Get subjects data
    $sql = "SELECT 
                s.subject_id,
                s.subject_name,
                s.subject_code,
                s.faculty_id,
                f.faculty_name,
                s.semester,
                s.credits,
                s.is_elective,
                s.description,
                s.is_active,
                s.created_at
            FROM subject s
            LEFT JOIN faculty f ON s.faculty_id = f.faculty_id
            $whereClause
            ORDER BY s.faculty_id, s.semester, s.subject_code
            LIMIT ? OFFSET ?";
    
    error_log("Main SQL: " . $sql);
    
    // Add limit and offset to params for main query
    $mainParams = $params; // Copy original params
    $mainTypes = $types;   // Copy original types
    $mainParams[] = $limit;
    $mainParams[] = $offset;
    $mainTypes .= 'ii';
    
    error_log("Main params with limit: " . print_r($mainParams, true));
    error_log("Main types with limit: " . $mainTypes);
    
    $stmt = mysqli_prepare($connection, $sql);
    if (!$stmt) {
        throw new Exception('SQL Prepare error: ' . mysqli_error($connection));
    }
    
    // Bind parameters for main query
    if (!empty($mainParams) && !empty($mainTypes)) {
        mysqli_stmt_bind_param($stmt, $mainTypes, ...$mainParams);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $subjects = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $subjects[] = [
            'subject_id' => $row['subject_id'],
            'subject_name' => $row['subject_name'],
            'subject_code' => $row['subject_code'],
            'faculty_id' => $row['faculty_id'],
            'faculty_name' => $row['faculty_name'],
            'semester' => $row['semester'],
            'credits' => $row['credits'],
            'is_elective' => $row['is_elective'],
            'description' => $row['description'],
            'is_active' => $row['is_active'],
            'created_at' => $row['created_at']
        ];
    }
    
    error_log("Subjects fetched: " . count($subjects));
    
    // Success response
    $response['success'] = true;
    $response['message'] = 'Subjects retrieved successfully';
    $response['data'] = $subjects;
    $response['total'] = $total;
    $response['pages'] = $totalPages;
    $response['current_page'] = $page;
    
} catch (Exception $e) {
    error_log("EXCEPTION: " . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
    http_response_code(500);
} finally {
    if (isset($connection)) {
        mysqli_close($connection);
    }
}

echo json_encode($response);
?>