<?php
session_start();
include('../../config.php');

// Prevent direct access
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: student_login.php");
    exit();
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

// Check in users table
$stmt = $connection->prepare("SELECT password, role FROM users WHERE username = ? AND role = 'student'");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($hashed_password, $role);

if($stmt->fetch() && password_verify($password, $hashed_password)) {
    // Get student details from student table
    $stmt2 = $connection->prepare("SELECT student_name, email, class_id, semester_id, is_active FROM student WHERE student_id = ?");
    $stmt2->bind_param("s", $username);
    $stmt2->execute();
    $stmt2->store_result();
    $stmt2->bind_result($student_name, $email, $class_id, $semester_id, $is_active);
    
    if($stmt2->fetch()){
        // Check if student is active
        if($is_active != 1){
            // Student is inactive
            session_unset();
            session_destroy();
            header("Location: student_login.php?error=inactive");
            exit();
        }
        
        // Set session variables
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_username'] = $username;
        $_SESSION['student_name'] = $student_name;
        $_SESSION['student_email'] = $email;
        $_SESSION['student_class'] = $class_id;
        $_SESSION['student_semester'] = $semester_id;

        header("Location: student_dashboard.php");
        exit();
    } else {
        // Student exists in users table but not in student table (data inconsistency)
        session_unset();
        session_destroy();
        header("Location: student_login.php?error=invalid");
        exit();
    }
    $stmt2->close();

} else {
    session_unset();
    session_destroy();
    header("Location: student_login.php?error=invalid");
    exit();
}
?>