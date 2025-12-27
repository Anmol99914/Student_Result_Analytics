<?php
// test_login.php
session_name('SRA_SESSION');
session_start();
require_once __DIR__ . '/../../config.php';

echo "<h1>Login Test</h1>";
echo "<p>Connection status: " . ($connection ? "Connected ✓" : "Failed ✗") . "</p>";

// Test the administrator table
$result = mysqli_query($connection, "SELECT * FROM administrator");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    echo "<p>Admin found in database: " . ($row ? "Yes ✓" : "No ✗") . "</p>";
    if ($row) {
        echo "<pre>";
        print_r($row);
        echo "</pre>";
        
        // Test password
        $test_password = "admin123"; // Default password from your database
        echo "<p>Testing password 'admin123': ";
        if (password_verify($test_password, $row['password'])) {
            echo "✓ Password verified!</p>";
        } else {
            echo "✗ Password verification failed!</p>";
        }
    }
} else {
    echo "<p>Error querying administrator table: " . mysqli_error($connection) . "</p>";
}

// Test form submission
echo '<form method="POST">';
echo '<input type="email" name="email" placeholder="admin@gmail.com"> ';
echo '<input type="password" name="password" placeholder="admin123"> ';
echo '<button type="submit">Test Login</button>';
echo '</form>';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = mysqli_prepare($connection, "SELECT * FROM administrator WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            echo "<p style='color:green;'>✓ Login successful!</p>";
        } else {
            echo "<p style='color:red;'>✗ Password incorrect</p>";
        }
    } else {
        echo "<p style='color:red;'>✗ User not found</p>";
    }
}
?>