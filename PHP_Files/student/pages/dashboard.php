<?php
// File: PHP_Files/student/pages/dashboard.php
require_once '../includes/auth_check.php';
require_student_login();

$page_title = 'Student Dashboard';
$page_css = 'dashboard';
$page_js = 'dashboard';

require_once '../includes/header.php';

$student_name = $_SESSION['student_name'] ?? 'Student';
$student_id = $_SESSION['student_username'] ?? '';
?>

<!-- Navbar -->
<nav class="navbar navbar-light bg-light border-bottom sticky-top">
    <div class="container-fluid">
        <!-- Mobile Toggle -->
        <button class="btn btn-outline-secondary d-lg-none me-2" type="button" 
                data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Brand -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="#">
            <i class="bi bi-mortarboard-fill text-primary me-2"></i>
            <span class="d-none d-md-inline">Student Portal</span>
        </a>
        
        <!-- Right Side -->
        <div class="d-flex align-items-center">
            <!-- Welcome -->
            <div class="me-3 d-none d-md-block">
                <small class="text-muted">Welcome,</small>
                <span class="fw-semibold"><?php echo htmlspecialchars($student_name); ?></span>
            </div>
            
            <!-- Notifications -->
            <div class="dropdown me-2">
                <button class="btn btn-outline-secondary position-relative" type="button" 
                        data-bs-toggle="dropdown">
                    <i class="bi bi-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        3
                        <span class="visually-hidden">unread notifications</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header">Notifications</h6></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-check-circle text-success me-2"></i>Result Published for Sem 2</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-cash-coin text-warning me-2"></i>Payment Due: 15 Days</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-calendar-event text-info me-2"></i>Exam Schedule Updated</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-center" href="#">View All</a></li>
                </ul>
            </div>
            
            <!-- Profile Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                        data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><h6 class="dropdown-header"><?php echo htmlspecialchars($student_id); ?></h6></li>
                    <li><a class="dropdown-item ajax-link" href="../pages/profile.php">
                        <i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="../api/logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar (Desktop) -->
        <div class="col-lg-2 bg-dark d-none d-lg-block min-vh-100 p-0">
            <div class="p-3">
                <!-- Student Info Card -->
                <div class="card bg-dark border-secondary mb-4">
                    <div class="card-body text-center p-3">
                        <div class="mb-3">
                            <i class="bi bi-person-circle text-white fs-1"></i>
                        </div>
                        <h6 class="text-white mb-1"><?php echo htmlspecialchars($student_name); ?></h6>
                        <small class="text-muted"><?php echo htmlspecialchars($student_id); ?></small>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="nav flex-column">
                    <a class="nav-link text-white mb-2 ajax-link active" href="../pages/home.php">
                        <i class="bi bi-house-door me-2"></i>Dashboard
                    </a>
                    <a class="nav-link text-white mb-2 ajax-link" href="../pages/profile.php">
                        <i class="bi bi-person me-2"></i>My Profile
                    </a>
                    <a class="nav-link text-white mb-2 ajax-link" href="../pages/results.php">
                        <i class="bi bi-clipboard-data me-2"></i>View Results
                    </a>
                    <a class="nav-link text-white mb-2 ajax-link" href="../pages/payments.php">
                        <i class="bi bi-credit-card me-2"></i>Fee Payments
                    </a>
                    
                    <!-- Divider -->
                    <div class="my-3 border-top border-secondary"></div>
                    
                    <!-- Quick Links -->
                    <small class="text-muted mb-2">QUICK LINKS</small>
                    <a class="nav-link text-white mb-2" href="#" target="_blank">
                        <i class="bi bi-calendar-event me-2"></i>Exam Schedule
                    </a>
                    <a class="nav-link text-white mb-2" href="#" target="_blank">
                        <i class="bi bi-book me-2"></i>Study Materials
                    </a>
                    <a class="nav-link text-white mb-2" href="#" target="_blank">
                        <i class="bi bi-question-circle me-2"></i>Help Desk
                    </a>
                    
                    <!-- Logout -->
                    <div class="mt-4 pt-3 border-top border-secondary">
                        <a class="nav-link text-danger" href="../api/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </div>
                </nav>
            </div>
        </div>
        
        <!-- Mobile Sidebar (Offcanvas) -->
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="offcanvasSidebar">
            <div class="offcanvas-header bg-dark text-white">
                <h5 class="offcanvas-title">Student Portal</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body bg-dark p-0">
                <!-- Same navigation as desktop sidebar -->
                <div class="p-3">
                    <div class="card bg-dark border-secondary mb-4">
                        <div class="card-body text-center p-3">
                            <div class="mb-3">
                                <i class="bi bi-person-circle text-white fs-1"></i>
                            </div>
                            <h6 class="text-white mb-1"><?php echo htmlspecialchars($student_name); ?></h6>
                            <small class="text-muted"><?php echo htmlspecialchars($student_id); ?></small>
                        </div>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link text-white mb-2 ajax-link" href="../pages/home.php">
                            <i class="bi bi-house-door me-2"></i>Dashboard
                        </a>
                        <a class="nav-link text-white mb-2 ajax-link" href="../pages/profile.php">
                            <i class="bi bi-person me-2"></i>My Profile
                        </a>
                        <a class="nav-link text-white mb-2 ajax-link" href="../pages/results.php">
                            <i class="bi bi-clipboard-data me-2"></i>View Results
                        </a>
                        <a class="nav-link text-white mb-2 ajax-link" href="../pages/payments.php">
                            <i class="bi bi-credit-card me-2"></i>Fee Payments
                        </a>
                        
                        <div class="my-3 border-top border-secondary"></div>
                        
                        <small class="text-muted mb-2">QUICK LINKS</small>
                        <a class="nav-link text-white mb-2" href="#">
                            <i class="bi bi-calendar-event me-2"></i>Exam Schedule
                        </a>
                        <a class="nav-link text-white mb-2" href="#">
                            <i class="bi bi-book me-2"></i>Study Materials
                        </a>
                        <a class="nav-link text-white mb-2" href="#">
                            <i class="bi bi-question-circle me-2"></i>Help Desk
                        </a>
                        
                        <div class="mt-4 pt-3 border-top border-secondary">
                            <a class="nav-link text-danger" href="../api/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Logout
                            </a>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-lg-10">
            <div id="main-content" class="p-4">
                <!-- Content loaded via AJAX will appear here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading dashboard...</p>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="mt-auto py-3 border-top text-center text-muted small">
                <div class="container">
                    <p class="mb-1">© 2025 Student Result Analytics System</p>
                    <p class="mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i>
                        Secure Student Portal • 
                        <span class="text-primary">Last login: Today, <?php echo date('h:i A'); ?></span>
                    </p>
                </div>
            </footer>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>