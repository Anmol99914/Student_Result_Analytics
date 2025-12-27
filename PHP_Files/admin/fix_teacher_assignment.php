<?php
// fix_teacher_assignment.php
session_start();
include("../../config.php");

echo "<h3>Fixing Teacher Assignment System</h3>";

// 1. Add missing column if it doesn't exist
echo "<h4>Step 1: Checking Database Structure</h4>";

$check_column = $connection->query("
    SHOW COLUMNS FROM teacher LIKE 'assigned_class_id'
");

if($check_column->num_rows == 0) {
    echo "❌ assigned_class_id column is missing in teacher table<br>";
    
    // Add the column
    $add_column = "ALTER TABLE teacher 
                  ADD COLUMN assigned_class_id INT DEFAULT NULL,
                  ADD CONSTRAINT fk_teacher_class 
                  FOREIGN KEY (assigned_class_id) 
                  REFERENCES class(class_id) 
                  ON DELETE SET NULL";
    
    if($connection->query($add_column)) {
        echo "✅ Added assigned_class_id column to teacher table<br>";
    } else {
        echo "❌ Failed to add column: " . $connection->error . "<br>";
    }
} else {
    echo "✅ assigned_class_id column exists in teacher table<br>";
}

// 2. Update existing assignments from teacher_class_assignments table
echo "<h4>Step 2: Syncing Existing Assignments</h4>";

$sync_assignments = $connection->query("
    UPDATE teacher t
    INNER JOIN (
        SELECT teacher_id, MIN(class_id) as primary_class_id
        FROM teacher_class_assignments 
        GROUP BY teacher_id
    ) tca ON t.teacher_id = tca.teacher_id
    SET t.assigned_class_id = tca.primary_class_id
    WHERE t.assigned_class_id IS NULL 
    OR t.assigned_class_id != tca.primary_class_id
");

if($sync_assignments) {
    $affected_rows = $connection->affected_rows;
    echo "✅ Synced $affected_rows teacher assignments<br>";
} else {
    echo "❌ Failed to sync assignments: " . $connection->error . "<br>";
}

// 3. Show current status
echo "<h4>Step 3: Current Assignment Status</h4>";

// Get unassigned teachers count
$unassigned_query = "
    SELECT COUNT(*) as count 
    FROM teacher 
    WHERE status = 'active' 
    AND (assigned_class_id IS NULL OR assigned_class_id = 0 OR assigned_class_id = '')
";

$unassigned_result = $connection->query($unassigned_query);
$unassigned_count = $unassigned_result->fetch_assoc()['count'];
echo "Unassigned Teachers: <strong>$unassigned_count</strong><br>";

// Get assigned teachers count
$assigned_query = "
    SELECT COUNT(*) as count 
    FROM teacher 
    WHERE status = 'active' 
    AND assigned_class_id IS NOT NULL 
    AND assigned_class_id != 0 
    AND assigned_class_id != ''
";

$assigned_result = $connection->query($assigned_query);
$assigned_count = $assigned_result->fetch_assoc()['count'];
echo "Assigned Teachers: <strong>$assigned_count</strong><br>";

// Get teacher_class_assignments count
$tca_count = $connection->query("SELECT COUNT(*) as count FROM teacher_class_assignments")->fetch_assoc()['count'];
echo "Teacher-Class Assignments: <strong>$tca_count</strong><br>";

// 4. Show sample data
echo "<h4>Step 4: Sample Teacher Data</h4>";

$sample_data = $connection->query("
    SELECT 
        t.teacher_id,
        t.name,
        t.email,
        t.assigned_class_id,
        c.faculty,
        c.semester,
        c.batch_year,
        (SELECT COUNT(*) FROM teacher_class_assignments WHERE teacher_id = t.teacher_id) as assignment_count
    FROM teacher t
    LEFT JOIN class c ON t.assigned_class_id = c.class_id
    WHERE t.status = 'active'
    LIMIT 10
");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>
    <tr style='background-color: #f2f2f2;'>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Assigned Class ID</th>
        <th>Class Details</th>
        <th>Assignments</th>
    </tr>";

while($row = $sample_data->fetch_assoc()) {
    $row_color = $row['assigned_class_id'] ? '#e8f5e8' : '#fff3cd';
    echo "<tr style='background-color: $row_color;'>";
    echo "<td>{$row['teacher_id']}</td>";
    echo "<td><strong>{$row['name']}</strong></td>";
    echo "<td>{$row['email']}</td>";
    echo "<td>" . ($row['assigned_class_id'] ? $row['assigned_class_id'] : 'NULL') . "</td>";
    echo "<td>";
    if($row['assigned_class_id']) {
        echo "{$row['faculty']} Sem {$row['semester']} ({$row['batch_year']})";
    } else {
        echo "<span style='color: #dc3545;'>Not Assigned</span>";
    }
    echo "</td>";
    echo "<td>{$row['assignment_count']}</td>";
    echo "</tr>";
}
echo "</table>";

// 5. Show sample classes for testing
echo "<h4>Step 5: Sample Classes for Testing</h4>";

$sample_classes = $connection->query("
    SELECT class_id, faculty, semester, batch_year 
    FROM class 
    WHERE status = 'active'
    ORDER BY batch_year DESC, semester
    LIMIT 10
");

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>
    <tr style='background-color: #f2f2f2;'>
        <th>ID</th>
        <th>Faculty</th>
        <th>Semester</th>
        <th>Batch Year</th>
        <th>Assign Link</th>
    </tr>";

while($row = $sample_classes->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['class_id']}</td>";
    echo "<td>{$row['faculty']}</td>";
    echo "<td>{$row['semester']}</td>";
    echo "<td>{$row['batch_year']}</td>";
    echo "<td><a href='assign_teachers.php?class_id={$row['class_id']}' target='_blank'>Assign Teachers</a></td>";
    echo "</tr>";
}
echo "</table>";

// 6. Test assignment functionality
echo "<h4>Step 6: Testing Assignment Function</h4>";

echo "To test assignment:<br>";
echo "1. Click any 'Assign Teachers' link above<br>";
echo "2. Select teachers for that class<br>";
echo "3. Save assignments<br>";
echo "4. Return here and refresh to see updated counts<br>";

// 7. Fix any inconsistencies
echo "<h4>Step 7: Fixing Data Inconsistencies</h4>";

// Remove orphaned assignments
$fix_orphans = $connection->query("
    DELETE FROM teacher_class_assignments 
    WHERE teacher_id NOT IN (SELECT teacher_id FROM teacher)
    OR class_id NOT IN (SELECT class_id FROM class)
");

if($fix_orphans) {
    echo "✅ Cleaned up orphaned assignments<br>";
} else {
    echo "✅ No orphaned assignments found<br>";
}

echo "<h4>Step 8: Final Status Check</h4>";

$final_status = $connection->query("
    SELECT 
        (SELECT COUNT(*) FROM teacher WHERE status = 'active') as total_active_teachers,
        (SELECT COUNT(*) FROM teacher WHERE status = 'active' AND assigned_class_id IS NOT NULL) as assigned_teachers,
        (SELECT COUNT(*) FROM teacher_class_assignments) as total_assignments,
        (SELECT COUNT(DISTINCT class_id) FROM teacher_class_assignments) as classes_with_teachers
");

$status = $final_status->fetch_assoc();
echo "Total Active Teachers: <strong>{$status['total_active_teachers']}</strong><br>";
echo "Teachers with Primary Class: <strong>{$status['assigned_teachers']}</strong><br>";
echo "Total Assignments: <strong>{$status['total_assignments']}</strong><br>";
echo "Classes with Teachers: <strong>{$status['classes_with_teachers']}</strong><br>";

echo "<hr>";
echo "<h3 style='color: green;'>✅ Database fix completed!</h3>";
echo "Now you can use the 'Assign Teachers' feature properly.<br>";
echo "The 'Unassigned: 39' count on your admin panel should start decreasing as you assign teachers.";
?>