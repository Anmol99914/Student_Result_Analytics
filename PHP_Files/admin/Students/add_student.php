<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['status'=>'error','message'=>'Unauthorized access']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $student_name = trim($_POST['student_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $class_id = intval($_POST['class_id'] ?? 0);
    $semester_id = intval($_POST['semester_id'] ?? 0);
    $password = trim($_POST['password'] ?? '');

    // Validation
    if(empty($student_name) || empty($email) || $class_id <= 0 || $semester_id <= 0){
        echo json_encode(['status'=>'error','message'=>'All required fields must be filled']);
        exit();
    }

    // Validate email format
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo json_encode(['status'=>'error','message'=>'Invalid email format']);
        exit();
    }

    // Check if email already exists in student table
    $stmtCheck = $connection->prepare("SELECT student_id FROM student WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    
    if($stmtCheck->num_rows > 0){
        echo json_encode(['status'=>'error','message'=>'Email already registered']);
        $stmtCheck->close();
        exit();
    }
    $stmtCheck->close();

    // Generate student ID (format: STU-XXXX)
    $stmtMax = $connection->query("SELECT MAX(student_id) as max_id FROM student WHERE student_id LIKE 'STU-%'");
    $maxRow = $stmtMax->fetch_assoc();
    $maxId = $maxRow['max_id'] ?? 'STU-0000';
    
    preg_match('/STU-(\d+)/', $maxId, $matches);
    $nextNumber = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
    $student_id = "STU-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

    // Check if student_id already exists in users table
    $stmtCheckUser = $connection->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmtCheckUser->bind_param("s", $student_id);
    $stmtCheckUser->execute();
    $stmtCheckUser->store_result();
    
    if($stmtCheckUser->num_rows > 0){
        // If exists, try a different number
        $nextNumber++;
        $student_id = "STU-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    $stmtCheckUser->close();

    // If password not provided, generate random password
    if(empty($password)){
        $password = bin2hex(random_bytes(4)); // 8 character random password
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction
    $connection->begin_transaction();

    try {
        // Insert student WITHOUT password (into student table)
        $stmt = $connection->prepare("INSERT INTO student (student_id, student_name, email, phone_number, class_id, semester_id, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("ssssii", $student_id, $student_name, $email, $phone_number, $class_id, $semester_id);
        $stmt->execute();
        $stmt->close();
        
        // Insert into users table for login (with hashed password)
        $stmtUser = $connection->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
        $stmtUser->bind_param("ss", $student_id, $hashed_password);
        $stmtUser->execute();
        $stmtUser->close();
        
        $connection->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Student registered successfully!',
            'student_id' => $student_id,
            'password' => $password, // Send plain password for admin to share
            'student_name' => $student_name
        ]);
        
    } catch (Exception $e) {
        $connection->rollback();
        echo json_encode(['status'=>'error','message'=>'Database error: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['status'=>'error','message'=>'Invalid request']);
?>