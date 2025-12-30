<?php
// File: PHP_Files/student/pages/login.php
require_once '../includes/auth_check.php';
redirect_if_logged_in();

$page_title = 'Student Portal - Login';
$page_css = 'login';
$page_js = 'login';

require_once '../includes/header.php';

// Error messages
$error_msg = '';
if(isset($_GET['error'])){
    if($_GET['error'] === "invalid"){
        $error_msg = "Invalid Student ID or password!";
    } elseif ($_GET['error'] === "inactive") {
        $error_msg = "Your account is inactive. Please contact administration.";
    } elseif ($_GET['error'] === "empty") {
        $error_msg = "Please fill in all fields.";
    } elseif ($_GET['error'] === "network") {
        $error_msg = "Network error. Please check your connection.";
    }
}
?>

<!-- Back to Home -->
<a href="../../index.html" class="btn back-home-btn position-fixed" style="top: 25px; left: 25px; z-index: 1000;">
    <i class="bi bi-arrow-left me-2"></i>Back to Home
</a>

<!-- Main Container -->
<div class="login-wrapper">
    <div class="login-container p-5 position-relative">
        <!-- Status Badge -->
        <div class="status-badge">
            <i class="bi bi-mortarboard me-2"></i>Student Portal
        </div>
        
        <!-- Student Icon -->
        <div class="student-icon">
            <i class="bi bi-person-circle text-white fs-2"></i>
        </div>
        
        <!-- Page heading -->
        <div class="header-text">
            <h2>Student Portal</h2>
            <p>View Results & Academic Profile</p>
        </div>

        <!-- Welcome Note -->
        <div class="welcome-note">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle-fill fs-5 me-3" style="color: #28a745;"></i>
                <div>
                    <h6 class="mb-2" style="color: #218838;">Welcome Students!</h6>
                    <p class="mb-0 small">Access your academic results, view profile details, and check payment status securely.</p>
                </div>
            </div>
        </div>

        <?php if($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div>
                    <strong class="d-block">Login Failed</strong>
                    <span class="small"><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="../api/login_validate.php" method="POST" id="studentForm" class="needs-validation" novalidate>
            <div class="floating-label">
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder=" " required autocomplete="username">
                <label class="floating-text" for="username">
                    <i class="bi bi-person-badge me-2"></i>Student ID
                </label>
                <div class="invalid-feedback">
                    Please enter your Student ID.
                </div>
                <small class="form-text text-muted mt-1">e.g., BCA001, BBM001</small>
            </div>
            
            <div class="floating-label">
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder=" " required autocomplete="current-password">
                <label class="floating-text" for="password">
                    <i class="bi bi-key me-2"></i>Password
                </label>
                <div class="invalid-feedback">
                    Please enter your password.
                </div>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                    <label class="form-check-label text-muted" for="remember">
                        <i class="bi bi-clock-history me-1"></i>Remember me
                    </label>
                </div>
                <a href="#" class="help-links small">
                    <i class="bi bi-question-circle me-1"></i>Forgot password?
                </a>
            </div>
            
            <button type="submit" class="btn btn-student w-100" id="loginBtn">
                <i class="bi bi-box-arrow-in-right me-2"></i>Access Student Dashboard
            </button>
        </form>
        
        <!-- Copyright -->
        <div class="copyright">
            <p class="mb-2">© 2025 Student Result Analytics System</p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>