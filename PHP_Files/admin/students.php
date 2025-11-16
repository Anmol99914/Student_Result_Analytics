<?php
session_start();


// Protect admin pages
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    // Either send a login page HTML
    echo file_get_contents('admin/admin_login.html');
    exit();
}
?>
<h2>Students</h2>
<p>Manage all registered students here.</p>
<table class="table table-bordered">
  <thead class="table-light">
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Class</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td>Anmol</td>
      <td>BCA 2nd Sem</td>
    </tr>
  </tbody>
</table>
