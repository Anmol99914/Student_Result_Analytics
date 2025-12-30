<?php
// File: PHP_Files/student/includes/auth_check.php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * Require student to be logged in
 * Redirects to login if not authenticated
 */
function require_student_login() {
    if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] != true) {
        header("Location: ../pages/login.php");
        exit();
    }
}

/**
 * Redirect to dashboard if already logged in
 * Used on login page
 */
function redirect_if_logged_in() {
    if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] == true) {
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
 * (To be implemented with payment table)
 */
function has_paid_fees($student_id) {
    // TODO: Implement with payment table
    return true; // Temporary
}
?>