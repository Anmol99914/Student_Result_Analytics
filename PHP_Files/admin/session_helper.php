<?php
// File: PHP_Files/admin/session_helper.php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * Check if admin is logged in
 */
function require_admin_login() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: admin_login.php");
        exit();
    }
}

/**
 * Redirect to dashboard if already logged in
 */
function redirect_if_admin_logged_in() {
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        header("Location: admin_main_page.php");
        exit();
    }
}

/**
 * Get current admin ID
 */
function get_current_admin_id() {
    return $_SESSION['admin_id'] ?? null;
}

/**
 * Get current admin name
 */
function get_current_admin_name() {
    return $_SESSION['admin_name'] ?? 'Administrator';
}

// Update session activity time
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Session timeout (30 minutes)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: admin_login.php?error=session_expired");
    exit();
}
?>