<?php
// admin_main_page.php 
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Result Analytics - Admin</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <!-- Our CSS Files -->
    <link rel="stylesheet" href="../../css/admin/admin-main.css">
    <link rel="stylesheet" href="../../css/admin/admin-responsive.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-primary d-md-none me-2" type="button" 
                        data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
                    <i class="bi bi-list"></i> Menu
                </button>
                <a class="navbar-brand mb-0">Student Result Analytics | Admin</a>
            </div>
            <form role="search" action="logout.php">
                <button class="btn btn-outline-danger" type="submit">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid flex-grow-1">
        <div class="row flex-md-nowrap flex-wrap">
            <!-- Desktop Sidebar -->
            <div class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3 d-none d-md-block admin-sidebar">
                <h5>SRA | Admin</h5>
                <ul class="nav flex-column mt-4">
                <li class="nav-item mb-2">
        <a href="#" onclick="loadDashboard()" class="nav-link text-white">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </li>
                    
                    <!-- Classes: -->
                    <li class="nav-item mb-2">
        <a href="#" onclick="loadClassManagement()" class="nav-link text-white">
            <i class="bi bi-mortarboard-fill"></i> Classes
        </a>
    </li>
                      <!-- Teachers -->
                      <li class="nav-item mb-2">
        <a href="#" onclick="loadTeacherManagement()" class="nav-link text-white">
            <i class="bi bi-person-square"></i> Teachers
        </a>
    </li>
                    <!-- Students -->
                    <li class="nav-item mb-2">
        <a href="students_list.php" class="nav-link text-white">
            <i class="bi bi-people"></i> Students
        </a>
    </li>
                    
                    <!-- Subjects -->
                    <li class="nav-item mb-2">
        <a href="subjects.php" class="nav-link text-white">
            <i class="bi bi-book"></i> Subjects
        </a>
    </li>
                    <!-- Results -->
                    <li class="nav-item mb-2">
        <a href="results.php" class="nav-link text-white">
            <i class="bi bi-trophy"></i> Results
        </a>
    </li>
    
                    
                    <!-- Assign Teachers -->
                    <li class="nav-item mb-2">
        <a href="assign_teachers.php" class="nav-link text-white">
            <i class="bi bi-person-plus"></i> Assign Teachers
        </a>
    </li>
                </ul>
            </div>
            
            <!-- Mobile Offcanvas Sidebar -->
            <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="offcanvasSidebar">
                <div class="offcanvas-header bg-dark text-white">
                    <h5 class="offcanvas-title">SRA | Admin</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body bg-dark text-white">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('home.php')" class="nav-link text-white">
                                <i class="bi bi-house"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('pages/class_management.php')" class="nav-link text-white">
                                <i class="bi bi-mortarboard-fill"></i> Classes
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('pages/teacher_management.php')" class="nav-link text-white">
                                <i class="bi bi-person-square"></i> Teachers
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('students_list.php')" class="nav-link text-white">
                                <i class="bi bi-people"></i> Students
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('subjects.php')" class="nav-link text-white">
                                <i class="bi bi-book"></i> Subjects
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('results.php')" class="nav-link text-white">
                                <i class="bi bi-trophy"></i> Results
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="#" onclick="loadPage('assign_teachers.php')" class="nav-link text-white">
                                <i class="bi bi-person-plus"></i> Assign Teachers
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content Area -->
            <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4">
                <?php 
                // Load default page
                if(isset($_GET['page'])) {
                    $page = basename($_GET['page']);
                    $allowed_pages = ['home', 'classes', 'teachers', 'students', 'subjects', 'results'];
                    
                    if(in_array($page, $allowed_pages)) {
                        include("pages/{$page}_management.php");
                    } else {
                        include("pages/home.php");
                    }
                } else {
                    include("pages/home.php");
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-auto bg-light text-center py-2 border-top">
        © 2025 Student Result Analytics | Admin Panel
    </footer>

   <!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Our JavaScript Files -->
<script>
// ===== CORE FUNCTIONS =====

// Global function to load any page via AJAX
function loadPage(url) {
    console.log('Loading page:', url);
    const mainContent = document.getElementById('main-content');
    
    if (!mainContent) {
        console.error('Main content container not found!');
        window.location.href = url; // Fallback to normal navigation
        return;
    }
    
    //debug 

    // In loadPage function, before setting innerHTML:
// Remove existing management scripts to prevent duplicate
const scriptsToRemove = [
    'script[src*="class-management.js"]',
    'script[src*="teacher-management.js"]',
    'script[src*="assign-teachers.js"]'
];

scriptsToRemove.forEach(selector => {
    const existingScript = document.querySelector(selector);
    if (existingScript) {
        existingScript.remove();
        console.log('Removed duplicate script:', selector);
    }
});
    // debug
    // Show loading
    mainContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
    // Update active link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        // Check if this link points to the URL we're loading
        const href = link.getAttribute('href') || link.getAttribute('onclick') || '';
        if (href.includes(url)) {
            link.classList.add('active');
        }
    });
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(html => {
            // Set HTML first
            mainContent.innerHTML = html;
            console.log('Page loaded successfully:', url);
            
            // CRITICAL: Execute scripts in the loaded content
            executeScriptsInContent(mainContent);
            
            // Initialize Bootstrap components
            initBootstrapComponents();
            
            // Dispatch page loaded event
            window.dispatchEvent(new CustomEvent('pageLoaded', { 
                detail: { url, content: html } 
            }));
        })
        .catch(error => {
            console.error('Error loading page:', error);
            mainContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> 
                    Error loading content: ${error.message}
                    <div class="mt-2">
                        <a href="${url}" class="btn btn-sm btn-danger">Try Again</a>
                        <button onclick="location.reload()" class="btn btn-sm btn-secondary">Reload Page</button>
                    </div>
                </div>
            `;
        });
}

// Function to execute scripts in dynamically loaded content
function executeScriptsInContent(container) {
    console.log('Executing scripts in loaded content...');
    
    // Find all script tags in the container
    const scripts = container.querySelectorAll('script');
    
    scripts.forEach(script => {
        console.log('Found script:', script.src || 'inline script');
        
        // If it's an external script
        if (script.src) {
            const newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.async = false;
            
            // Copy all attributes
            Array.from(script.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            
            // Replace old script with new one (this triggers execution)
            script.parentNode.replaceChild(newScript, script);
            
        } else {
            // If it's inline script
            try {
                // Create a new script element with the content
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
                
                // Append to body to execute
                document.body.appendChild(newScript);
                document.body.removeChild(newScript);
                
                console.log('Inline script executed');
            } catch (error) {
                console.error('Error executing inline script:', error);
            }
        }
    });
}

// Initialize Bootstrap components
function initBootstrapComponents() {
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (modal.id && !bootstrap.Modal.getInstance(modal)) {
            new bootstrap.Modal(modal);
        }
    });
    
    // Dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-toggle');
    dropdowns.forEach(dropdown => {
        if (!bootstrap.Dropdown.getInstance(dropdown)) {
            new bootstrap.Dropdown(dropdown);
        }
    });
}

// Show alert message
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-toast');
    existingAlerts.forEach(alert => alert.remove());
    
    // Set icon and color
    let icon, alertClass;
    switch(type) {
        case 'success':
            icon = 'check-circle';
            alertClass = 'alert-success';
            break;
        case 'warning':
            icon = 'exclamation-triangle';
            alertClass = 'alert-warning';
            break;
        case 'danger':
            icon = 'x-circle';
            alertClass = 'alert-danger';
            break;
        default:
            icon = 'info-circle';
            alertClass = 'alert-info';
    }
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-toast alert-dismissible fade show" 
             role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert-toast');
        if (alert) alert.remove();
    }, 3000);
}

// ===== PAGE-SPECIFIC FUNCTIONS =====

// Load teacher management
function loadTeacherManagement() {
    console.log('Loading teacher management...');
    loadPage('pages/teacher_management.php');
}

// Load class management
function loadClassManagement() {
    console.log('Loading class management...');
    loadPage('pages/class_management.php');
}

// Load dashboard/home
function loadDashboard() {
    console.log('Loading dashboard...');
    loadPage('pages/home.php');
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel initialized');
    
    // Setup sidebar click events
    document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                
                // Get page from onclick or data attribute
                const onclick = this.getAttribute('onclick') || '';
                if (onclick.includes('loadTeacherManagement')) {
                    loadTeacherManagement();
                } else if (onclick.includes('loadClassManagement')) {
                    loadClassManagement();
                } else if (onclick.includes('loadDashboard')) {
                    loadDashboard();
                }
            }
        });
    });
    
    // Initialize Bootstrap
    initBootstrapComponents();
    
    // Set home as active by default
    const homeLink = document.querySelector('.admin-sidebar .nav-link[onclick*="loadDashboard"]');
    if (homeLink) {
        homeLink.classList.add('active');
    }
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    showAlert('danger', 'JavaScript Error: ' + e.message);
});

// Unhandled promise rejection
window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled promise rejection:', e.reason);
    showAlert('danger', 'Async Error: ' + e.reason.message);
});

// In admin_main_page.php, around line 480-510 where it loads scripts
function loadScriptOnce(src) {
    // Check if script already loaded
    const existing = document.querySelector(`script[src*="${src}"]`);
    if (existing) {
        console.log('Script already loaded:', src);
        return Promise.resolve();
    }
    
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

// Then use it instead of directly appending scripts
// loadScriptOnce('js/admin/teacher-management.js');
</script>

<!-- Load page-specific JS -->
<script src="../../js/admin/common.js"></script>
<script src="../../js/admin/assign-teachers.js"></script>
</body>
</html>