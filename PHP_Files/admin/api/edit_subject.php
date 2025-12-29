<?php
// File: PHP_Files/admin/api/edit_subject.php
// Purpose: API endpoint to update an existing subject
// Methods: PUT
// Required fields: subject_id, subject_name, subject_code, faculty_id, semester, credits

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
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
$response = ['success' => false, 'message' => ''];

try {
    // Get PUT data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate required fields
    if (empty($data['subject_id'])) {
        throw new Exception('Subject ID is required');
    }
    
    $required = ['subject_name', 'subject_code', 'faculty_id', 'semester', 'credits'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    // Sanitize inputs
    $subject_id = intval($data['subject_id']);
    $subject_name = mysqli_real_escape_string($conn, trim($data['subject_name']));
    $subject_code = mysqli_real_escape_string($conn, strtoupper(trim($data['subject_code'])));
    $faculty_id = intval($data['faculty_id']);
    $semester = intval($data['semester']);
    $credits = intval($data['credits']);
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
    $description = isset($data['description']) ? mysqli_real_escape_string($conn, trim($data['description'])) : '';
    
    // Check if subject exists
    $subjectCheck = mysqli_query($conn, "SELECT subject_id FROM subject WHERE subject_id = $subject_id");
    if (mysqli_num_rows($subjectCheck) == 0) {
        throw new Exception('Subject not found');
    }
    
    // Validate faculty exists
    $facultyCheck = mysqli_query($conn, "SELECT faculty_id FROM faculty WHERE faculty_id = $faculty_id AND is_active = 1");
    if (mysqli_num_rows($facultyCheck) == 0) {
        throw new Exception('Invalid faculty selected');
    }
    
    // Check if subject code already exists (excluding current subject)
    $codeCheck = mysqli_query($conn, "SELECT subject_id FROM subject WHERE subject_code = '$subject_code' AND subject_id != $subject_id");
    if (mysqli_num_rows($codeCheck) > 0) {
        throw new Exception("Subject code '$subject_code' already exists for another subject");
    }
    
    // Validate semester (1-8)
    if ($semester < 1 || $semester > 8) {
        throw new Exception('Semester must be between 1 and 8');
    }
    
    // Validate credits (2,3,4,6,8)
    $validCredits = [2, 3, 4, 6, 8];
    if (!in_array($credits, $validCredits)) {
        throw new Exception('Credits must be 2, 3, 4, 6, or 8');
    }
    
    // Prepare SQL update
    $sql = "UPDATE subject SET
                subject_name = ?,
                subject_code = ?,
                faculty_id = ?,
                semester = ?,
                credits = ?,
                description = ?,
                is_active = ?,
                updated_at = NOW()
            WHERE subject_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Database error: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, 'ssiiisii', 
        $subject_name, 
        $subject_code, 
        $faculty_id, 
        $semester, 
        $credits, 
        $description, 
        $is_active,
        $subject_id
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        
        if ($affectedRows > 0) {
            $response['success'] = true;
            $response['message'] = 'Subject updated successfully';
            $response['subject_id'] = $subject_id;
        } else {
            $response['success'] = true;
            $response['message'] = 'No changes made to the subject';
        }
    } else {
        throw new Exception('Failed to update subject: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}

echo json_encode($response);
?>