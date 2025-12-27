<?php
session_start();
include('../../config.php');

// Check if session is working
echo json_encode([
    'session_id' => session_id(),
    'admin_logged_in' => isset($_SESSION['admin_logged_in']),
    'timestamp' => date('Y-m-d H:i:s')
]);
?>