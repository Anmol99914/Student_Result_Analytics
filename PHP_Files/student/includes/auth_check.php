<?php
session_start();

// Prevent caching - MORE STRICT HEADERS
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0, private");
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

/**
 * Require student to be logged in
 * Redirects to login if not authenticated
 */
function require_student_login() {
    if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
        // Clear any existing session data
        $_SESSION = array();
        
        // Destroy session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 86400, '/');
        }
        
        // Destroy session
        session_destroy();
        session_regenerate_id(true);
        
        // Redirect to login with message
        header("Location: ../pages/login.php?error=session_expired");
        exit();
    }
}

/**
 * Redirect to dashboard if already logged in
 * Used on login page
 */
function redirect_if_logged_in() {
    if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
        // Check session age (optional: expire after 30 minutes)
        if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
            // Session expired
            session_unset();
            session_destroy();
            header("Location: ../pages/login.php?error=session_expired");
            exit();
        }
        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time
        
        header("Location: ../pages/dashboard.php");
        exit();
    }
}

/**
 * Get current student ID from session
 */
function get_current_student_id() {
    return $_SESSION['student_username'] ?? null;
}

/**
 * Check if student has paid fees
 */
function has_paid_fees($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT payment_status FROM payment WHERE student_id = ? ORDER BY payment_date DESC LIMIT 1");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['payment_status'] === 'Paid';
    }
    return false;
}

// Update session activity on every page load
if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    $_SESSION['LAST_ACTIVITY'] = time();
}
?>