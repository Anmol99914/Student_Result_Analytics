<?php
// save_teacher_assignments.php - AJAX handler
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
    exit();
}

$class_id = intval($_POST['class_id'] ?? 0);
$teacher_ids = $_POST['teacher_ids'] ?? [];

if($class_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid class ID']);
    exit();
}

try {
    // Start transaction
    $connection->begin_transaction();
    
    // Clear existing assignments
    $clear_stmt = $connection->prepare("DELETE FROM teacher_class_assignments WHERE class_id = ?");
    $clear_stmt->bind_param("i", $class_id);
    $clear_stmt->execute();
    $clear_stmt->close();
    
    // Add new assignments
    if(!empty($teacher_ids)) {
        $insert_stmt = $connection->prepare("INSERT INTO teacher_class_assignments (teacher_id, class_id) VALUES (?, ?)");
        foreach($teacher_ids as $teacher_id) {
            $teacher_id_int = intval($teacher_id);
            if($teacher_id_int > 0) {
                $insert_stmt->bind_param("ii", $teacher_id_int, $class_id);
                if(!$insert_stmt->execute()) {
                    throw new Exception("Failed to assign teacher ID: $teacher_id_int");
                }
            }
        }
        $insert_stmt->close();
        
        // Update teacher's assigned_class_id for primary assignment
        $update_stmt = $connection->prepare("UPDATE teacher SET assigned_class_id = ? WHERE teacher_id = ?");
        foreach($teacher_ids as $teacher_id) {
            $teacher_id_int = intval($teacher_id);
            if($teacher_id_int > 0) {
                $update_stmt->bind_param("ii", $class_id, $teacher_id_int);
                $update_stmt->execute();
            }
        }
        $update_stmt->close();
        
        $message = "✅ Teachers assigned successfully!";
    } else {
        $message = "ℹ️ All teacher assignments removed.";
    }
    
    // Commit transaction
    $connection->commit();
    
    // Get updated assigned teachers list
    $assigned_teachers = [];
    $assigned_teacher_ids = [];
    
    $result = $connection->query("
        SELECT t.teacher_id, t.name, t.email
        FROM teacher_class_assignments tca
        JOIN teacher t ON tca.teacher_id = t.teacher_id
        WHERE tca.class_id = $class_id
        ORDER BY t.name
    ");
    
    while($row = $result->fetch_assoc()) {
        $assigned_teachers[] = $row;
        $assigned_teacher_ids[] = $row['teacher_id'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'assigned_teachers' => $assigned_teachers,
        'assigned_teacher_ids' => $assigned_teacher_ids,
        'class_id' => $class_id
    ]);
    
} catch (Exception $e) {
    $connection->rollback();
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>