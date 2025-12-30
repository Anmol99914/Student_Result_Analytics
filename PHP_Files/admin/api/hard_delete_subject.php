<?php
// File: PHP_Files/admin/api/hard_delete_subject.php
// Purpose: Permanently delete an inactive subject

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../../config.php';

$response = ['success' => false, 'message' => ''];

try {
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
    
    if ($subject_id <= 0) {
        throw new Exception('Invalid subject ID');
    }
    
    // Check if subject exists and is inactive
    $check_sql = "SELECT subject_code, subject_name, is_active 
                  FROM subject WHERE subject_id = ?";
    $check_stmt = mysqli_prepare($connection, $check_sql);
    mysqli_stmt_bind_param($check_stmt, 'i', $subject_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) == 0) {
        mysqli_stmt_close($check_stmt);
        throw new Exception("Subject not found");
    }
    
    $subject = mysqli_fetch_assoc($check_result);
    mysqli_stmt_close($check_stmt);
    
    // Check if subject is active
    if ($subject['is_active'] == 1) {
        throw new Exception("Cannot delete active subject. Deactivate it first.");
    }
    
    // Hard delete
    $delete_sql = "DELETE FROM subject WHERE subject_id = ?";
    $delete_stmt = mysqli_prepare($connection, $delete_sql);
    mysqli_stmt_bind_param($delete_stmt, 'i', $subject_id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        $response['success'] = true;
        $response['message'] = "Subject '{$subject['subject_code']}' permanently deleted";
    } else {
        throw new Exception('Database delete failed');
    }
    
    mysqli_stmt_close($delete_stmt);
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Hard Delete Subject Error: ' . $e->getMessage());
}

echo json_encode($response);
?>