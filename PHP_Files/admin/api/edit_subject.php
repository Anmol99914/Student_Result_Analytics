<?php
// File: edit_subject.php - ADD DEBUGGING

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../../config.php';

$response = ['success' => false, 'message' => ''];

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    error_log("📨 Received JSON: " . $json);
    
    $input = json_decode($json, true);
    
    if (!$input) {
        throw new Exception('No input data received');
    }
    
    error_log("📝 Input data: " . print_r($input, true));
    
    $subject_id = intval($input['subject_id'] ?? 0);
    $subject_name = mysqli_real_escape_string($connection, trim($input['subject_name'] ?? ''));
    $subject_code = mysqli_real_escape_string($connection, trim($input['subject_code'] ?? ''));
    $faculty_id = intval($input['faculty_id'] ?? 0);
    $semester = intval($input['semester'] ?? 0);
    $credits = intval($input['credits'] ?? 0);
    $is_elective = intval($input['is_elective'] ?? 0); 
    $is_active = intval($input['is_active'] ?? 1);
    $description = mysqli_real_escape_string($connection, trim($input['description'] ?? ''));
    
    error_log("🎯 is_elective received: " . $is_elective);
    
    if ($subject_id <= 0) {
        throw new Exception('Invalid subject ID');
    }
    
    // Update subject
    $sql = "UPDATE subject SET 
            subject_name = ?,
            subject_code = ?,
            faculty_id = ?,
            semester = ?,
            credits = ?,
            is_elective = ?,      
            is_active = ?,
            description = ?
            WHERE subject_id = ?";
    
    error_log("📋 SQL: " . $sql);
    
    $stmt = mysqli_prepare($connection, $sql);
    mysqli_stmt_bind_param($stmt, 'ssiiiiisi', 
        $subject_name, $subject_code, $faculty_id, 
        $semester, $credits, $is_elective, $is_active, $description, $subject_id);
    
    if (mysqli_stmt_execute($stmt)) {
        error_log("✅ Database update successful for subject ID: " . $subject_id);
        $response['success'] = true;
        $response['message'] = 'Subject updated successfully';
    } else {
        throw new Exception('Database update failed: ' . mysqli_error($connection));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    error_log("❌ ERROR: " . $e->getMessage());
    $response['message'] = 'Error: ' . $e->getMessage();
}

error_log("📤 Sending response: " . json_encode($response));
echo json_encode($response);
?>