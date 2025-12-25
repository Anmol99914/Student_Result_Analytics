<?php
// admin_teachers.php - Main teacher management file
session_start();
include('../../config.php');

// Add admin authentication check here
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    header("Location: admin_login.php");
    exit();
}

// Check if we need to redirect to dashboard or show standalone
if(isset($_GET['standalone']) && $_GET['standalone'] == 'true') {
    // Show standalone version
    include 'admin_teachers_standalone.php';
} else {
    // Redirect to dashboard with teacher management loaded
    header("Location: admin_main_page.php");
    exit();
}
?>