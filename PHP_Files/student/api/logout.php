<?php
// File: PHP_Files/student/api/logout.php
session_start();

// Destroy all session data
$_SESSION = array();

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 86400, '/');
}

// Destroy session
session_destroy();
session_regenerate_id(true);

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login
header("Location: ../pages/login.php");
exit();
?>