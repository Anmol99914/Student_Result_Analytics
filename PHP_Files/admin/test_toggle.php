<?php
// test_toggle.php - Test the toggle functionality
include('../../config.php');

echo "<h3>Testing Teacher Status Toggle</h3>";

// Get a teacher to test with
$result = $connection->query("SELECT teacher_id, name, email, status FROM teacher LIMIT 1");
if($result->num_rows > 0) {
    $teacher = $result->fetch_assoc();
    echo "Test Teacher: " . $teacher['name'] . " (ID: " . $teacher['teacher_id'] . ")<br>";
    echo "Current Status: " . $teacher['status'] . "<br>";
    echo "Email: " . $teacher['email'] . "<br>";
    
    // Check users table
    $user_result = $connection->query("SELECT username, status FROM users WHERE username = '" . $teacher['email'] . "'");
    if($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo "Users table status: " . $user['status'] . "<br>";
    } else {
        echo "❌ Teacher not found in users table!<br>";
    }
    
    echo "<hr>";
    
    // Test the toggle
    echo "<h4>Manual Test:</h4>";
    echo "<form method='post' action='toggle_teacher_status.php'>";
    echo "<input type='hidden' name='teacher_id' value='" . $teacher['teacher_id'] . "'>";
    echo "<button type='submit' name='action' value='deactivate'>Deactivate</button> ";
    echo "<button type='submit' name='action' value='activate'>Activate</button>";
    echo "</form>";
} else {
    echo "No teachers found in database.";
}
?>