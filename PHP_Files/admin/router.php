<?php
// router.php - Simple routing for admin panel

if (session_status() == PHP_SESSION_NONE) {
    session_start();
    
    // Check authentication only when starting new session
    if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
        header("Location: admin_login.php");
        exit();
    }
}

// Check if this is an AJAX request
$is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Get the requested page
$page = $_GET['page'] ?? '';

// Route to the appropriate page
switch($page) {
    case 'assign_teachers':
        $class_id = intval($_GET['class_id'] ?? 0);
        $_GET['inside_main'] = true;
        
        // Include assign_teachers.php
        if(file_exists('assign_teachers.php')) {
            include('assign_teachers.php');
        } else {
            die("Error: assign_teachers.php not found!");
        }
        break;
        
    case 'classes':
        // Include admin_classes.php
        if(file_exists('admin_classes.php')) {
            include('admin_classes.php');
        } else {
            die("Error: admin_classes.php not found!");
        }
        break;
        
    case 'teachers':
        // Include teacher management
        if(file_exists('admin_teachers_content.php')) {
            include('admin_teachers_content.php');
        } else {
            die("Error: admin_teachers_content.php not found!");
        }
        break;
        
    default:
        // Default to admin dashboard
        if(file_exists('admin_main_page.php')) {
            include('admin_main_page.php');
        } else {
            die("Error: admin_main_page.php not found!");
        }
        break;
}
?>