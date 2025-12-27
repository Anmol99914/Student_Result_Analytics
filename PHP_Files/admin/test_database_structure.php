<?php
// test_database_structure.php
session_start();
include("../../config.php");

echo "<h3>Database Structure Check</h3>";

// Check if teacher_class_assignments table exists
$check_table = $connection->query("SHOW TABLES LIKE 'teacher_class_assignments'");
if($check_table->num_rows == 0) {
    echo "❌ teacher_class_assignments table doesn't exist!<br>";
    // Create it
    $create_table = "CREATE TABLE teacher_class_assignments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        class_id INT NOT NULL,
        assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES teacher(teacher_id) ON DELETE CASCADE,
        FOREIGN KEY (class_id) REFERENCES class(class_id) ON DELETE CASCADE,
        UNIQUE KEY unique_assignment (teacher_id, class_id)
    )";
    
    if($connection->query($create_table)) {
        echo "✅ Created teacher_class_assignments table<br>";
    } else {
        echo "❌ Failed to create table: " . $connection->error . "<br>";
    }
} else {
    echo "✅ teacher_class_assignments table exists<br>";
}

// Check teacher table structure
echo "<h4>Teacher Table Structure:</h4>";
$teacher_structure = $connection->query("DESCRIBE teacher");
while($row = $teacher_structure->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

// Check assigned_class_id column
echo "<h4>Checking assigned_class_id values:</h4>";
$check_assigned = $connection->query("
    SELECT 
        teacher_id,
        name,
        assigned_class_id,
        CASE 
            WHEN assigned_class_id IS NULL THEN 'NULL'
            WHEN assigned_class_id = 0 THEN 'ZERO'
            WHEN assigned_class_id = '' THEN 'EMPTY'
            ELSE 'ASSIGNED'
        END as status
    FROM teacher 
    WHERE status = 'active'
    LIMIT 10
");

while($row = $check_assigned->fetch_assoc()) {
    echo "Teacher: {$row['name']} - assigned_class_id: {$row['assigned_class_id']} ({$row['status']})<br>";
}
?>