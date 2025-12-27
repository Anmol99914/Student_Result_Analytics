<?php
// get_teacher_assignments.php
include_once("../../config.php");

$teacher_id = intval($_GET['teacher_id'] ?? 0);
$assignments = [];

if($teacher_id > 0) {
    $result = $connection->query("
        SELECT c.class_id, c.semester, c.batch_year
        FROM teacher_class_assignments tca
        JOIN class c ON tca.class_id = c.class_id
        WHERE tca.teacher_id = $teacher_id
        ORDER BY c.batch_year DESC, c.semester
    ");
    
    while($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'assignments' => $assignments
]);
?>