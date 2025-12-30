<?php
// File: PHP_Files/admin/api/activate_subject.php
// Purpose: Activate a subject (set is_active = 1)

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../../config.php';

$response = ['success' => false, 'message' => ''];

try {
    // Get subject_id from POST
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) : 0;
    
    if ($subject_id <= 0) {
        throw new Exception('Invalid subject ID');
    }
    
    // Check if subject exists
    $check_sql = "SELECT subject_id, subject_code, subject_name, is_active 
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
    
    // Check if already active
    if ($subject['is_active'] == 1) {
        throw new Exception("Subject '{$subject['subject_code']}' is already active");
    }
    
    // Activate the subject
    $update_sql = "UPDATE subject SET is_active = 1 WHERE subject_id = ?";
    $update_stmt = mysqli_prepare($connection, $update_sql);
    mysqli_stmt_bind_param($update_stmt, 'i', $subject_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $response['success'] = true;
        $response['message'] = "Subject '{$subject['subject_code']}' activated successfully";
    } else {
        throw new Exception('Database update failed');
    }
    
    mysqli_stmt_close($update_stmt);
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log('Activate Subject Error: ' . $e->getMessage());
}

echo json_encode($response);
?>