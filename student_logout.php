<?php
ob_start();
session_start();


// Destroy all session data
session_unset();
session_destroy();

// Prevent cached page access
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login page
header("Location: student_login.html");
exit();
?>
