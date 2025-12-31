<!-- debug_login.php -->
<?php
session_start();
include('../../config.php');

echo "<h3>Debug Teacher Login</h3>";

// Check what's in the teacher table
$result = $connection->query("SELECT name, email, password, status FROM teacher");
echo "<h4>Teachers in database:</h4>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Name</th><th>Email</th><th>Password (first 20 chars)</th><th>Status</th></tr>";

while($row = $result->fetch_assoc()){
    $pass_preview = substr($row['password'], 0, 20) . "...";
    echo "<tr>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $pass_preview . "</td>";
    echo "<td>" . $row['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test password verification
echo "<h4>Test password verification:</h4>";

$test_email = "ram@college.com";
$test_password = "ram123";

$stmt = $connection->prepare("SELECT password FROM teacher WHERE email = ?");
$stmt->bind_param("s", $test_email);
$stmt->execute();
$stmt->bind_result($hashed_password);
$stmt->fetch();
$stmt->close();

echo "Email: " . $test_email . "<br>";
echo "Plain password: " . $test_password . "<br>";
echo "Hashed in DB: " . $hashed_password . "<br>";

if(password_verify($test_password, $hashed_password)){
    echo "<span style='color:green;'>✓ Password verification SUCCESSFUL!</span>";
} else {
    echo "<span style='color:red;'>✗ Password verification FAILED!</span><br>";
    
    // Try to see what hash we get
    $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
    echo "New hash for 'ram123': " . $new_hash . "<br>";
    
    // Check if passwords match directly (maybe not hashed)
    if($hashed_password === $test_password){
        echo "<span style='color:orange;'>Note: Password is stored as plain text, not hashed!</span>";
    }
}
?>