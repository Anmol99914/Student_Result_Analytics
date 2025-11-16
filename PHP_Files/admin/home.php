<?php
session_start();

// Protect admin pages
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    // Either send a login page HTML
    echo file_get_contents('admin_login.html');
    exit();
}
?>
<h2>Home</h2>
<p>Welcome to the Student Result Analytics Admin Panel.</p>
<p>Use the sidebar to navigate through sections like Students, Subjects, and Results.</p>
