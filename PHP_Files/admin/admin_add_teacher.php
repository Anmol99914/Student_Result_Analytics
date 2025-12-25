<?php
// admin_add_teacher.php - AJAX compatible version
session_start();
include('../../config.php');

// Check admin authentication
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    header("Location: admin_login.php");
    exit();
}

// Always return JSON for AJAX requests
header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'teacher_id' => 0
];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $assigned_class_id = !empty($_POST['assigned_class_id']) ? intval($_POST['assigned_class_id']) : NULL;
    
    // Validation
    if(empty($name) || empty($email) || empty($password)) {
        $response['message'] = "All fields are required!";
    } elseif($password !== $confirm_password) {
        $response['message'] = "Passwords do not match!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Invalid email format!";
    } elseif(strlen($password) < 6) {
        $response['message'] = "Password must be at least 6 characters!";
    } else {
        // Check if email already exists
        $check = $connection->prepare("SELECT teacher_id FROM teacher WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        if($check->get_result()->num_rows > 0) {
            $response['message'] = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert teacher
            $stmt = $connection->prepare("INSERT INTO teacher (name, email, password, status, assigned_class_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssi", $name, $email, $hashed_password, $status, $assigned_class_id);
            
            if($stmt->execute()) {
                $teacher_id = $stmt->insert_id;
                
                // Also add to users table for login
                $user_stmt = $connection->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher')");
                $user_stmt->bind_param("ss", $email, $hashed_password);
                $user_stmt->execute();
                
                $response['success'] = true;
                $response['message'] = "Teacher added successfully! Teacher ID: #$teacher_id";
                $response['teacher_id'] = $teacher_id;
                
            } else {
                $response['message'] = "Error adding teacher: " . $connection->error;
            }
        }
    }
    
    echo json_encode($response);
    exit();
}

// If not POST request, return error
$response['message'] = "Invalid request method";
echo json_encode($response);
?>