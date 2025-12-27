<?php
// session_helper.php - Save in PHP_Files/admin/
function checkAdminSession() {
    session_start();
    
    // Set cache control headers
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // Check authentication
    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
        header("Location: admin_login.php");
        exit();
    }
}

// Include this in all protected admin pages
// require_once 'session_helper.php';
// checkAdminSession();
?>