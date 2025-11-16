<?php
include('config.php');

session_start();


// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

header("Cache-Control: no-store, max-age=0, must-revalidate, no-cache, private");

//Get data from form
$username = $_POST['username'];
$password = $_POST['password'];

$valid_username = "admin999";
$valid_password = "iamadmin";

if($username === $valid_username && $password === $valid_password){
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_username'] = $username;
    header("Location: admin_main_page.php");
    exit();
}
else{
    header("Location: admin_login.php?error=invalid+username+or+password");
    exit();

    // echo "<script>
    // alert('Invalid username or password!');
    // window.location.href = 'admin_login.html';
    // </script>
    // ";
}
?>