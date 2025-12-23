<?php
// student_validation.php - MODIFIED VERSION
session_start();
include('../../config.php');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: student_login.php");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Check in STUDENT table (not users table)
$stmt = $connection->prepare("SELECT student_id, student_name, email, password, class_id, semester_id, is_active 
                             FROM student WHERE student_id = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($student_id, $student_name, $email, $hashed_password, $class_id, $semester_id, $is_active);

if($stmt->fetch() && password_verify($password, $hashed_password)) {
    
    // Check if student is active
    if($is_active != 1){
        session_unset();
        session_destroy();
        header("Location: student_login.php?error=inactive");
        exit();
    }
    
    // Set session variables
    $_SESSION['student_logged_in'] = true;
    $_SESSION['student_username'] = $student_id;
    $_SESSION['student_name'] = $student_name;
    $_SESSION['student_email'] = $email;
    $_SESSION['student_class'] = $class_id;
    $_SESSION['student_semester'] = $semester_id;

    header("Location: student_dashboard.php");
    exit();
    
} else {
    session_unset();
    session_destroy();
    header("Location: student_login.php?error=invalid");
    exit();
}
?>