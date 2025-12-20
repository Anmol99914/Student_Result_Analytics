<?php
session_start();
include("../../config.php");

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true){
    echo json_encode(['status'=>'error','message'=>'Unauthorized access']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $faculty = trim($_POST['faculty'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    $teacher_id = intval($_POST['teacher_id'] ?? 0);

    // Validation - all fields are required
    if($faculty === '' || $semester <= 0 || $teacher_id <= 0){
        echo json_encode(['status'=>'error','message'=>'All fields are required: Faculty, Semester, and Teacher']);
        exit();
    }

    // Verify teacher is active
    $stmtCheck = $connection->prepare("SELECT name, status FROM teacher WHERE teacher_id=? LIMIT 1");
    $stmtCheck->bind_param("i", $teacher_id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($teacherName, $teacherStatus);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if(empty($teacherName)){
        echo json_encode(['status'=>'error','message'=>'Selected teacher not found']);
        exit();
    }
    
    if(strtolower(trim($teacherStatus)) !== 'active'){
        echo json_encode([
            'status' => 'error',
            'message' => 'Cannot assign suspended teacher. Please select an active teacher.'
        ]);
        exit();
    }

    // Check for duplicate class (same faculty, semester, and teacher)
    $stmtDup = $connection->prepare("SELECT class_id FROM class WHERE faculty=? AND semester=? AND teacher_id=? LIMIT 1");
    $stmtDup->bind_param("sii", $faculty, $semester, $teacher_id);
    $stmtDup->execute();
    $stmtDup->store_result();

    if($stmtDup->num_rows > 0){
        echo json_encode(['status'=>'error','message'=>'This class already exists with the same teacher']);
    } else {
        // Insert new class
        $stmt = $connection->prepare("INSERT INTO class (faculty, semester, teacher_id, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("sii", $faculty, $semester, $teacher_id);
        
        if($stmt->execute()){
            $class_id = $stmt->insert_id;
            
            // Update teacher's assigned class
            $updateTeacher = $connection->prepare("UPDATE teacher SET assigned_class_id = ? WHERE teacher_id = ?");
            $updateTeacher->bind_param("ii", $class_id, $teacher_id);
            $updateTeacher->execute();
            $updateTeacher->close();
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Class created successfully with teacher assigned!',
                'teacher_assigned' => true,
                'teacher_name' => $teacherName,
                'class_id' => $class_id
            ]);
        } else {
            echo json_encode(['status'=>'error','message'=>'Database error: ' . $connection->error]);
        }
        $stmt->close();
    }
    $stmtDup->close();
    exit();
}

echo json_encode(['status'=>'error','message'=>'Invalid request']);
?>