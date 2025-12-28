<?php
// check_column.php
include('../../config.php');

echo "<h3>Checking users table columns</h3>";

$result = $connection->query("SHOW COLUMNS FROM users");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    foreach($row as $value) {
        echo "<td>$value</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Try to insert a test record
echo "<h3>Testing insert with status column</h3>";
$test_email = "test" . time() . "@test.com";
$hashed_password = password_hash("test123", PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password, role, status) VALUES (?, ?, 'teacher', 'active')";
$stmt = $connection->prepare($sql);

if($stmt) {
    $stmt->bind_param("ss", $test_email, $hashed_password);
    if($stmt->execute()) {
        echo "✅ Test insert successful!<br>";
        echo "Test email: $test_email<br>";
        
        // Clean up
        $connection->query("DELETE FROM users WHERE username = '$test_email'");
    } else {
        echo "❌ Test insert failed: " . $stmt->error;
    }
} else {
    echo "❌ Prepare failed: " . $connection->error;
}
?>