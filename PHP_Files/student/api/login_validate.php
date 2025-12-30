<?php
// File: PHP_Files/student/api/login_validate.php
session_start();
require_once '../../../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    header("Location: ../pages/login.php?error=empty");
    exit();
}

$stmt = $connection->prepare("
    SELECT student_id, student_name, email, password, class_id, semester_id, is_active 
    FROM student 
    WHERE student_id = ?
");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($student_id, $student_name, $email, $db_password, $class_id, $semester_id, $is_active);

if($stmt->fetch()) {
    // Check password - supports both plain text and hashed
    $password_valid = false;
    
    // Method 1: Check if password is already hashed (starts with $2y$)
    if (strpos($db_password, '$2y$') === 0) {
        // Password is hashed, verify it
        $password_valid = password_verify($password, $db_password);
    } else {
        // Password is plain text, compare directly
        $password_valid = ($password === $db_password);
        
        // Optional: Auto-upgrade to hash
        if ($password_valid) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $connection->prepare("UPDATE student SET password = ? WHERE student_id = ?");
            $update_stmt->bind_param("ss", $hashed_password, $student_id);
            $update_stmt->execute();
        }
    }
    
    if($password_valid) {
        // Check if student is active
        if($is_active != 1){
            session_unset();
            session_destroy();
            header("Location: ../pages/login.php?error=inactive");
            exit();
        }
        
        // Set session variables
        $_SESSION['student_logged_in'] = true;
        $_SESSION['student_username'] = $student_id;
        $_SESSION['student_name'] = $student_name;
        $_SESSION['student_email'] = $email;
        $_SESSION['student_class'] = $class_id;
        $_SESSION['student_semester'] = $semester_id;
        
        // Redirect to dashboard
        header("Location: ../pages/dashboard.php");
        exit();
        
    } else {
        header("Location: ../pages/login.php?error=invalid");
        exit();
    }
} else {
    header("Location: ../pages/login.php?error=invalid");
    exit();
}
?>