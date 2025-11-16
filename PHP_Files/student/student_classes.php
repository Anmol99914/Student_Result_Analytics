<?php
session_start();

// Protect admin pages
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    // Either send a login page HTML
    echo file_get_contents('admin/admin_login.php');
    exit();
}
?>

<h2>Student Classes</h2>
<p>Manage different student classes and sections here.</p>
<button class="btn btn-primary">Add New Class</button>
