<?php
// session_init.php - Include this at top of EVERY admin page

// Always use the same session name
session_name('SRA_SESSION');

// Set cookie parameters
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: Regenerate ID for security every hour
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 3600) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>