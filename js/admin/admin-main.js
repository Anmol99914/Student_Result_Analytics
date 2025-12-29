// File: js/admin/admin-main.js
// Purpose: Core navigation and utility functions for admin panel

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
    
    // Remove existing management scripts to prevent duplicate
    const scriptsToRemove = [
        'script[src*="class-management.js"]',
        'script[src*="teacher-management.js"]',
        'script[src*="assign-teachers.js"]',
        'script[src*="subject-management.js"]'
    ];
    
    scriptsToRemove.forEach(selector => {
        const existingScript = document.querySelector(selector);
        if (existingScript) {
            existingScript.remove();
            console.log('Removed duplicate script:', selector);
        }
    });
    
    // Show loading
    mainContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading...</p>
        </div>
    `;
    
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
            
            // Execute scripts in the loaded content
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
                const newScript = document.createElement('script');
                newScript.textContent = script.textContent;
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

// ===== PAGE LOADING FUNCTIONS =====

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

// Load subject management
function loadSubjectManagement() {
    console.log('Loading subject management...');
    loadPage('pages/subject_management.php');
}

// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel initialized');
    
    // Setup sidebar click events
    document.querySelectorAll('.admin-sidebar .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href') === '#') {
                e.preventDefault();
                
                // Update active menu
                document.querySelectorAll('.admin-sidebar .nav-link').forEach(l => {
                    l.classList.remove('active');
                });
                this.classList.add('active');
                
                // Get page from onclick
                const onclick = this.getAttribute('onclick') || '';
                
                if (onclick.includes('loadTeacherManagement')) {
                    loadTeacherManagement();
                } else if (onclick.includes('loadClassManagement')) {
                    loadClassManagement();
                } else if (onclick.includes('loadDashboard')) {
                    loadDashboard();
                } else if (onclick.includes('loadSubjectManagement')) {
                    loadSubjectManagement();
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
        loadDashboard(); // Load dashboard on initial load
    }
    
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
});