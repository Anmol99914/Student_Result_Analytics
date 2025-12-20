<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: ../teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $student_name = trim($_POST['student_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $class_id = $_POST['class_id'];
    $semester_id = $_POST['semester_id'];
    $phone_number = trim($_POST['phone_number']);
    $student_id = trim($_POST['student_id']);
    
    // Check if teacher is allowed to add to this class
    $check_sql = "SELECT COUNT(*) as can_add FROM class c 
                  WHERE c.class_id = ? 
                  AND (c.teacher_id = ? OR c.class_id = 
                      (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?))";
    $check_stmt = $connection->prepare($check_sql);
    $check_stmt->bind_param("iii", $class_id, $teacher_id, $teacher_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $can_add_row = $check_result->fetch_assoc();
    $can_add = $can_add_row['can_add'];
    
    if ($can_add == 0) {
        $_SESSION['error'] = "You are not authorized to add students to this class.";
        header("Location: add_student.php");
        exit();
    }
    
    // Generate student ID if not provided
    if (empty($student_id)) {
        // Get faculty code
        $faculty_sql = "SELECT faculty FROM class WHERE class_id = ?";
        $faculty_stmt = $connection->prepare($faculty_sql);
        $faculty_stmt->bind_param("i", $class_id);
        $faculty_stmt->execute();
        $faculty_result = $faculty_stmt->get_result();
        $class_data = $faculty_result->fetch_assoc();
        
        if ($class_data) {
            $faculty = $class_data['faculty'];
            $faculty_code = substr($faculty, 0, 3) . substr($faculty, -3, 3);
            $random_num = rand(100, 999);
            $student_id = strtoupper($faculty_code) . $random_num;
        } else {
            $student_id = 'STU' . rand(1000, 9999);
        }
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Insert student
        $sql = "INSERT INTO student (student_id, student_name, email, password, class_id, semester_id, phone_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssssiis", 
            $student_id, 
            $student_name, 
            $email, 
            $hashed_password, 
            $class_id, 
            $semester_id, 
            $phone_number
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "✅ Student '$student_name' added successfully!<br>Student ID: <strong>$student_id</strong><br>Password: <strong>$password</strong>";
            header("Location: add_student.php");
            exit();
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $_SESSION['error'] = "❌ Email or Student ID already exists.";
        } else {
            $_SESSION['error'] = "❌ Error adding student: " . $e->getMessage();
        }
        header("Location: add_student.php");
        exit();
    }
} else {
    header("Location: add_student.php");
    exit();
}
?>