// File: PHP_Files/student/js/common.js
// Common JavaScript for Student Panel

// Base URL for AJAX calls
const BASE_URL = window.location.origin + '/Student_Result_Analytics';
const STUDENT_API = BASE_URL + '/PHP_Files/student/api/';

/**
 * Load page content via AJAX
 */
function loadPage(url) {
    showLoading();
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('main-content').innerHTML = data;
            hideLoading();
        })
        .catch(error => {
            console.error('Error loading page:', error);
            document.getElementById('main-content').innerHTML = 
                '<div class="alert alert-danger">Error loading page. Please try again.</div>';
            hideLoading();
        });
}

/**
 * Set up navigation links
 */
function setupNavigation() {
    const links = document.querySelectorAll('.ajax-link');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active state
            links.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Load page
            loadPage(this.getAttribute('href'));
            
            // Close mobile sidebar if open
            const offcanvas = bootstrap.Offcanvas.getInstance(
                document.getElementById('offcanvasSidebar')
            );
            if (offcanvas) {
                offcanvas.hide();
            }
        });
    });
}

/**
 * Show loading indicator
 */
function showLoading() {
    let loader = document.getElementById('page-loader');
    if (!loader) {
        loader = document.createElement('div');
        loader.id = 'page-loader';
        loader.className = 'text-center py-5';
        loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading...</p>
        `;
        document.getElementById('main-content').innerHTML = '';
        document.getElementById('main-content').appendChild(loader);
    }
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    const loader = document.getElementById('page-loader');
    if (loader) {
        loader.remove();
    }
}

/**
 * Make AJAX request with error handling
 */
function makeRequest(url, options = {}) {
    const defaultOptions = {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    return fetch(url, { ...defaultOptions, ...options })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('Request failed:', error);
            throw error;
        });
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

/**
 * Format date
 */
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

/**
 * Format currency (NPR)
 */
function formatCurrency(amount) {
    return 'NPR ' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    setupNavigation();
    
    // Set active link based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const activeLink = document.querySelector(`.ajax-link[href*="${currentPage}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
});


// To prevent back navigation:)
function preventBackNavigation() {
    window.history.forward();
    
    // Clear browser cache on page load
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
    
    // Disable back button
    window.onpopstate = function(event) {
        history.go(1);
    };
}

// Call it when dashboard loads
if (window.location.pathname.includes('dashboard.php') || 
    window.location.pathname.includes('home.php') ||
    window.location.pathname.includes('profile.php') ||
    window.location.pathname.includes('results.php') ||
    window.location.pathname.includes('payments.php')) {
    preventBackNavigation();
}