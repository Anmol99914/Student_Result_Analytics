<?php
// File: PHP_Files/student/api/login_validate.php - TEMPORARY FIX VERSION
session_start();

// Include root config
require_once '../../../config.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/login.php");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Validate input
if (empty($username) || empty($password)) {
    header("Location: ../pages/login.php?error=empty");
    exit();
}

// Check in STUDENT table
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
    // TEMPORARY FIX: Compare plain text passwords
    // Your database has 'pass123' as plain text
    if($password === $db_password) {
        
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
        
        // Set session cookie parameters
        $sessionParams = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'lifetime' => 86400, // 24 hours
                'path' => $sessionParams['path'],
                'domain' => $sessionParams['domain'],
                'secure' => $sessionParams['secure'],
                'httponly' => $sessionParams['httponly'],
                'samesite' => 'Strict'
            ]
        );
        
        // Redirect to dashboard
        header("Location: ../pages/dashboard.php");
        exit();
        
    } else {
        session_unset();
        session_destroy();
        header("Location: ../pages/login.php?error=invalid");
        exit();
    }
} else {
    session_unset();
    session_destroy();
    header("Location: ../pages/login.php?error=invalid");
    exit();
}
?>