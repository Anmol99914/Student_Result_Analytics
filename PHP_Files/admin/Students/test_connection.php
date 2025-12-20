<?php
session_start();
include("../../../config.php");

echo "Testing database connection...<br>";

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
echo "✓ Database connected successfully<br>";

// Check if semester table has data
$result = $connection->query("SELECT * FROM semester");
echo "Semester table rows: " . $result->num_rows . "<br>";

// Check student table structure
$result2 = $connection->query("DESCRIBE student");
echo "Student table columns:<br>";
while($row = $result2->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}

// Check users table structure
$result3 = $connection->query("DESCRIBE users");
echo "<br>Users table columns:<br>";
while($row = $result3->fetch_assoc()) {
    echo "- " . $row['Field'] . " (" . $row['Type'] . ")<br>";
}
?>