<?php
session_start();

// Protect admin pages
if(!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] !== true){
    // Either send a login page HTML
    header("Location: teacher_login.html?ts=" . time());
    // echo file_get_contents('teacher_login.html');
    exit();
}
?>
<h2>Profile/Account</h2>
<p>Change password, view details</p>
