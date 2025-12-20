<?php
$password = "iamadmin";  // Your desired admin password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>