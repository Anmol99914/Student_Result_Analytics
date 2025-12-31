// teacher-common.js - Common utilities for teacher panel
console.log('Teacher Common.js loaded');

// Utility function for AJAX requests
async function ajaxRequest(url, options = {}) {
    try {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };
        
        const finalOptions = { ...defaultOptions, ...options };
        
        const response = await fetch(url, finalOptions);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return await response.json();
        } else {
            return await response.text();
        }
    } catch (error) {
        console.error('AJAX request failed:', error);
        throw error;
    }
}

// Show alert message
function showAlert(type, message, duration = 3000) {
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
        <div class="alert ${alertClass} alert-toast alert-dismissible fade show" role="alert">
            <i class="bi bi-${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto remove after duration
    setTimeout(() => {
        const alert = document.querySelector('.alert-toast');
        if (alert) alert.remove();
    }, duration);
}

// Initialize Bootstrap components
function initBootstrapComponents() {
    // Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(tooltipTriggerEl => {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (modal.id && !bootstrap.Modal.getInstance(modal)) {
            new bootstrap.Modal(modal);
        }
    });
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Validate email
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Export to global scope
window.ajaxRequest = ajaxRequest;
window.showAlert = showAlert;
window.initBootstrapComponents = initBootstrapComponents;
window.formatDate = formatDate;
window.validateEmail = validateEmail;