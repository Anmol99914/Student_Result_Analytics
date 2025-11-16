
<?php
session_start();

// Protect admin pages
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    // Either send a login page HTML
    echo file_get_contents('admin/admin_login.html');
    exit();
}
?>
<h1>Subjects</h1>
<p>Manage your Subjects.</p>


