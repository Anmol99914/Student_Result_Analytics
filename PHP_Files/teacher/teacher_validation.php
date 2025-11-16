<?php
// Connect to database
include_once __DIR__ . '/../../config.php';

session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

header("Cache-Control: no-store, max-age=0, must-revalidate, no-cache, private");

if($_SERVER['REQUEST_METHOD'] == "POST"){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
}

// Validation 
if (empty($email) || empty($password)) {
    echo "<script>
    alert('Please enter Email and Password'); 
    window.location.href='teacher_login.html';
    </script>";
    exit();
}

// Fetch teacher from teacher table
$sql = "SELECT * FROM teacher WHERE email = ?";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s",$email);
$stmt->execute();
$result = $stmt->get_result();

// The PHP script checks if the Email and password exist in the database.
// If valid, it stores them in the session

if($result->num_rows === 1){
    $teacher = $result->fetch_assoc();

    // If passwords are plain text (temporary)
    if ($password === $teacher['password']) {
        $_SESSION['teacher_logged_in'] = true;
        $_SESSION['teacher_id'] = $teacher['teacher_id'];
        $_SESSION['teacher_name'] = $teacher['name'];
        $_SESSION['assigned_class_id'] = $teacher['assigned_class_id'];
        header("Location: teacher_dashboard.php");
        exit();
    }   
    else{
        // Redirect with error message :)
        header('Location: teacher_login.html?error=Incorrect+password');
        exit();
    }
}
else {
    header('Location: teacher_login.html?error=No+teacher+found+with+this+email');
    exit();
}

?>