<?php
// File: PHP_Files/admin/api/delete_subject.php

// Error handling
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../../config.php';

$response = ['success' => false, 'message' => ''];

try {
    // DEBUG: Log request info
    error_log("DELETE API: Method=" . $_SERVER['REQUEST_METHOD']);
    error_log("DELETE API: Content-Type=" . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    
    // Accept BOTH JSON and regular POST data
    $subject_id = null;
    
    // Try to get from regular POST first
    if (isset($_POST['subject_id']) && $_POST['subject_id'] !== '') {
        $subject_id = intval($_POST['subject_id']);
        error_log("DELETE API: Got subject_id from POST: " . $subject_id);
    } 
    // If not in POST, try to read JSON
    else {
        $input = file_get_contents('php://input');
        error_log("DELETE API: Raw input: " . $input);
        
        if (!empty($input)) {
            $data = json_decode($input, true);
            if ($data && isset($data['subject_id'])) {
                $subject_id = intval($data['subject_id']);
                error_log("DELETE API: Got subject_id from JSON: " . $subject_id);
            }
        }
    }
    
    // Validate subject_id
    if (!$subject_id || $subject_id <= 0) {
        throw new Exception('Valid Subject ID is required');
    }
    
    error_log("DELETE API: Processing subject_id: " . $subject_id);
    
    // Check if subject exists
    $check_query = "SELECT subject_id, subject_code, subject_name FROM subject WHERE subject_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_query);
    
    if (!$check_stmt) {
        throw new Exception('Database prepare failed: ' . mysqli_error($connection));
    }
    
    mysqli_stmt_bind_param($check_stmt, 'i', $subject_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        throw new Exception("Subject ID $subject_id not found");
    }
    
    $subject = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    error_log("DELETE API: Found subject: " . $subject['subject_code'] . " - " . $subject['subject_name']);
    
    // Soft delete: Set is_active = 0
    $update_query = "UPDATE subject SET is_active = 0 WHERE subject_id = ?";
    $update_stmt = mysqli_prepare($connection, $update_query);
    
    if (!$update_stmt) {
        throw new Exception('Update prepare failed: ' . mysqli_error($connection));
    }
    
    mysqli_stmt_bind_param($update_stmt, 'i', $subject_id);
    $update_success = mysqli_stmt_execute($update_stmt);
    
    if ($update_success) {
        $affected = mysqli_stmt_affected_rows($update_stmt);
        error_log("DELETE API: Update affected rows: " . $affected);
        
        $response['success'] = true;
        $response['message'] = "Subject '{$subject['subject_code']} - {$subject['subject_name']}' has been deactivated";
    } else {
        throw new Exception('Update failed: ' . mysqli_error($connection));
    }
    
    mysqli_stmt_close($update_stmt);
    
} catch (Exception $e) {
    error_log("DELETE API Exception: " . $e->getMessage());
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

error_log("DELETE API Response: " . json_encode($response));
echo json_encode($response);
?>