<?php
// admin_session.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
function checkAdminLogin() {
    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
        // If it's an AJAX request, return JSON
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['error' => 'Session expired']);
            exit;
        } else {
            // If it's a direct request, show message
            echo '<div class="alert alert-danger">Session expired. Please <a href="admin_login.php">login again</a>.</div>';
            exit;
        }
    }
}
?>