<?php
// test_session.php
session_name('SRA_SESSION');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Name: " . session_name() . "\n";
echo "Admin Logged In: " . (isset($_SESSION['admin_logged_in']) ? 'YES' : 'NO') . "\n";
echo "Session Data:\n";
print_r($_SESSION);
echo "Cookies:\n";
print_r($_COOKIE);
echo "</pre>";
?>