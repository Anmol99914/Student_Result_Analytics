<?php
// add_class.php
session_start();
include('../../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty = $_POST['faculty'] ?? '';
    $semester = intval($_POST['semester'] ?? 0);
    $batch_year = $_POST['batch_year'] ?? date('Y');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($faculty) || $semester <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid faculty or semester']);
        exit;
    }
    
    // Check if class already exists
    $check_query = "SELECT class_id FROM class 
                    WHERE faculty = ? AND semester = ? AND batch_year = ?";
    $check_stmt = $connection->prepare($check_query);
    $check_stmt->bind_param("sis", $faculty, $semester, $batch_year);
    $check_stmt->execute();
    
    if ($check_stmt->get_result()->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Class already exists']);
        exit;
    }
    
    // Insert new class
    $insert_query = "INSERT INTO class (faculty, semester, batch_year, status) 
                     VALUES (?, ?, ?, ?)";
    $insert_stmt = $connection->prepare($insert_query);
    $insert_stmt->bind_param("siss", $faculty, $semester, $batch_year, $status);
    
    if ($insert_stmt->execute()) {
        $class_id = $connection->insert_id;
        echo json_encode([
            'status' => 'success', 
            'message' => 'Class created successfully!',
            'class_id' => $class_id,
            'faculty' => $faculty,
            'semester' => $semester
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $connection->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>