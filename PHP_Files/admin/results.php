<?php
session_start();

// Protect admin pages
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    // Either send a login page HTML
    echo file_get_contents(__DIR__ . '/../admin/admin_login.php');
    exit();
}
?>
<h2>Results</h2>
<p>View and manage student exam results here.</p>
<button class="btn btn-success">Upload New Result</button>
