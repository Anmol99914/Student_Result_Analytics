<?php
session_start();
require_once '../../../config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Debug Faculty</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='container mt-4'>
    <h2>Faculty Table Contents</h2>";

// Check faculty table
$sql = "SELECT * FROM faculty ORDER BY faculty_name";
$result = $connection->query($sql);

echo "<table class='table table-bordered mt-3'>";
echo "<thead><tr><th>ID</th><th>Faculty Name</th><th>Created At</th><th>Status</th></tr></thead>";
echo "<tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['faculty_id'] . "</td>";
    echo "<td><strong>" . htmlspecialchars($row['faculty_name']) . "</strong></td>";
    echo "<td>" . $row['created_at'] . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}

echo "</tbody></table>";

// Also check class table to see what faculty names are used there
echo "<h3 class='mt-5'>Classes in Class Table</h3>";
$class_sql = "SELECT DISTINCT faculty FROM class ORDER BY faculty";
$class_result = $connection->query($class_sql);

echo "<table class='table table-bordered mt-3'>";
echo "<thead><tr><th>Faculty Name in Class Table</th></tr></thead>";
echo "<tbody>";

while ($row = $class_result->fetch_assoc()) {
    echo "<tr><td><strong>" . htmlspecialchars($row['faculty']) . "</strong></td></tr>";
}

echo "</tbody></table>";

// Check if there's a mismatch
echo "<h3 class='mt-5'>Comparison</h3>";
echo "<p>Your class table has faculty: <strong>BCA</strong></p>";
echo "<p>But your faculty table might have a different name or spelling.</p>";

echo "</body></html>";
?>