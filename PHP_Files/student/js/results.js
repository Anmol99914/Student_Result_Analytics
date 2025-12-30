// File: PHP_Files/student/js/results.js
// Student Results Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Results page loaded');
    
    // Initialize page
    initResultsPage();
    
    // Add active class to current semester filter
    const currentSemester = new URLSearchParams(window.location.search).get('semester');
    if (currentSemester) {
        document.querySelectorAll('.btn-outline-primary').forEach(btn => {
            if (btn.getAttribute('href')?.includes(`semester=${currentSemester}`)) {
                btn.classList.add('active');
            }
        });
    }
});

function initResultsPage() {
    // Add click handlers for semester buttons
    document.querySelectorAll('.btn-outline-primary').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!this.classList.contains('active')) {
                // Show loading
                showLoading();
            }
        });
    });
    
    // Initialize tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(t => new bootstrap.Tooltip(t));
}

function downloadResultPDF() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Generating...';
    btn.disabled = true;
    
    // Simulate PDF generation
    setTimeout(() => {
        showToast('PDF will be downloaded shortly', 'info');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        // In real implementation, this would trigger a file download
        // window.location.href = '../api/download_result.php?semester=' + getCurrentSemester();
    }, 2000);
}

function getCurrentSemester() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('semester') || '1';
}

function showLoading() {
    const loader = document.createElement('div');
    loader.className = 'loading-overlay';
    loader.innerHTML = '<div class="spinner-border text-primary"></div>';
    document.getElementById('main-content').appendChild(loader);
}

function hideLoading() {
    const loader = document.querySelector('.loading-overlay');
    if (loader) loader.remove();
}

// Make functions available globally
window.downloadResultPDF = downloadResultPDF;