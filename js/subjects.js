/**
 * Teacher Subjects Management JavaScript
 * File: js/subjects.js
 */

// Global variables
let currentSubjectData = null;

/**
 * Select subject for marks entry
 */
function selectSubject(subjectId, subjectName) {
    console.log("Subject selected:", subjectId, subjectName);
    
    // Get parent window context (teacher dashboard)
    const parentWindow = window.parent || window.opener || window;
    
    // Visual feedback
    document.querySelectorAll('.subject-card').forEach(card => {
        card.classList.remove('selected');
        card.style.borderColor = '';
    });
    
    const selectedCard = document.querySelector(`[data-subject-id="${subjectId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('selected');
        selectedCard.style.borderColor = '#0d6efd';
    }
    
    // If using step-by-step workflow in parent
    if (typeof parentWindow.selectSubject === 'function') {
        parentWindow.selectSubject(subjectId, subjectName);
    } else {
        // Direct entry - get class data from URL or data attributes
        const urlParams = new URLSearchParams(window.location.search);
        const classId = urlParams.get('class_id') || selectedCard?.getAttribute('data-class-id');
        const faculty = urlParams.get('faculty') || selectedCard?.getAttribute('data-faculty');
        const semester = urlParams.get('semester') || selectedCard?.getAttribute('data-semester');
        
        enterMarks(subjectId, subjectName, classId, faculty, semester);
    }
}

/**
 * Enter marks for selected subject
 */
function enterMarks(subjectId, subjectName, classId, faculty, semester) {
    console.log("Entering marks for:", {subjectId, subjectName, classId, faculty, semester});
    
    // Get parent window context
    const parentWindow = window.parent || window.opener || window;
    const mainContent = parentWindow.document.getElementById('main-content');
    
    if (!mainContent) {
        console.error("Cannot find main content area");
        showAlert('error', 'Cannot find main content area. Please refresh the page.');
        return;
    }
    
    // Show loading
    mainContent.innerHTML = `
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Enter Marks - ${subjectName}</h5>
                <button class="btn btn-sm btn-light" onclick="showAddResultForm()">
                    <i class="bi bi-arrow-left"></i> Back to Subjects
                </button>
            </div>
            <div class="card-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading marks entry form...</p>
            </div>
        </div>
    `;
    
    // Load marks entry form
    setTimeout(() => {
        // Construct the correct path to marks entry form
        const marksFormPath = '../teacher/Results/enter_marks_form.php';
        
        fetch(`${marksFormPath}?class_id=${classId}&subject_id=${subjectId}&faculty=${encodeURIComponent(faculty)}&semester=${semester}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.text();
            })
            .then(html => {
                mainContent.innerHTML = html;
                
                // Execute any scripts in the loaded content
                executeScripts(html);
            })
            .catch(error => {
                console.error("Error loading marks form:", error);
                mainContent.innerHTML = `
                    <div class="alert alert-danger">
                        <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Form</h5>
                        <p>${error.message}</p>
                        <p>Form path: ${marksFormPath}</p>
                        <button class="btn btn-primary mt-2" onclick="showAddResultForm()">
                            <i class="bi bi-arrow-left"></i> Back to Subjects
                        </button>
                    </div>
                `;
            });
    }, 500);
}

/**
 * View pending marks for a subject
 */
function viewPendingMarks(subjectId, subjectName, classId) {
    const parentWindow = window.parent || window.opener || window;
    const mainContent = parentWindow.document.getElementById('main-content');
    
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <div class="card shadow">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i> Pending Marks - ${subjectName}</h5>
                <button class="btn btn-sm btn-light" onclick="showAddResultForm()">
                    <i class="bi bi-arrow-left"></i> Back to Subjects
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h5><i class="bi bi-info-circle"></i> Pending Verification</h5>
                    <p>These marks are waiting for admin approval. You can view but not edit them.</p>
                </div>
                <div id="pendingMarksContainer" class="text-center py-4">
                    <div class="spinner-border text-warning" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading pending marks...</p>
                </div>
            </div>
        </div>
    `;
    
    // Load pending marks data
    loadPendingMarks(subjectId, classId);
}

/**
 * View verified marks for a subject
 */
function viewVerifiedMarks(subjectId, subjectName, classId) {
    const parentWindow = window.parent || window.opener || window;
    const mainContent = parentWindow.document.getElementById('main-content');
    
    if (!mainContent) return;
    
    mainContent.innerHTML = `
        <div class="card shadow">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-check-circle me-2"></i> Verified Results - ${subjectName}</h5>
                <button class="btn btn-sm btn-light" onclick="showAddResultForm()">
                    <i class="bi bi-arrow-left"></i> Back to Subjects
                </button>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <h5><i class="bi bi-check-circle"></i> All Marks Verified</h5>
                    <p>These marks have been approved by admin and published to students.</p>
                </div>
                <div id="verifiedMarksContainer" class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading verified results...</p>
                </div>
            </div>
        </div>
    `;
    
    // Load verified marks data
    loadVerifiedMarks(subjectId, classId);
}

/**
 * Load pending marks via AJAX
 */
function loadPendingMarks(subjectId, classId) {
    fetch(`../teacher/Results/get_pending_marks.php?subject_id=${subjectId}&class_id=${classId}`)
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('pendingMarksContainer');
            if (!container) return;
            
            if (data.error) {
                container.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }
            
            if (data.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-check-circle"></i> No pending marks found.
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Submitted On</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(mark => {
                html += `
                    <tr>
                        <td>${mark.student_name} (${mark.student_id})</td>
                        <td>${mark.marks_obtained}/${mark.total_marks}</td>
                        <td>${mark.percentage}%</td>
                        <td><span class="badge bg-info">${mark.grade}</span></td>
                        <td>${formatDate(mark.created_at)}</td>
                        <td><span class="badge bg-warning">Pending</span></td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
            
            container.innerHTML = html;
        })
        .catch(error => {
            const container = document.getElementById('pendingMarksContainer');
            if (container) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        Error loading pending marks: ${error.message}
                    </div>
                `;
            }
        });
}

/**
 * Execute scripts from loaded HTML
 */
function executeScripts(html) {
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = html;
    const scripts = tempDiv.querySelectorAll('script');
    
    scripts.forEach(script => {
        const newScript = document.createElement('script');
        if (script.src) {
            newScript.src = script.src;
        } else {
            newScript.textContent = script.textContent;
        }
        document.body.appendChild(newScript);
        setTimeout(() => {
            if (newScript.parentNode) {
                document.body.removeChild(newScript);
            }
        }, 100);
    });
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Show alert message
 */
function showAlert(type, message) {
    const alertTypes = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertClass = alertTypes[type] || 'alert-info';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    const container = document.querySelector('.container-fluid') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Initialize subject cards with hover effects
 */
function initializeSubjectCards() {
    console.log("Initializing subject cards...");
    
    const subjectCards = document.querySelectorAll('.subject-card');
    console.log(`Found ${subjectCards.length} subject cards`);
    
    subjectCards.forEach(card => {
        // Add click handler
        card.addEventListener('click', function(e) {
            // Don't trigger if clicking on buttons inside
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            
            if (subjectId && subjectName) {
                selectSubject(subjectId, subjectName);
            }
        });
        
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                this.style.borderColor = '#0d6efd';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('selected')) {
                this.style.transform = '';
                this.style.boxShadow = '';
                this.style.borderColor = '';
            }
        });
    });
    
    // Initialize button event listeners
    initializeButtonHandlers();
}

/**
 * Initialize button event handlers
 */
function initializeButtonHandlers() {
    // Handle all subject action buttons
    document.querySelectorAll('.subject-action-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent card click
            
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            const classId = this.getAttribute('data-class-id');
            const action = this.getAttribute('data-action');
            
            if (!subjectId || !subjectName || !classId) {
                console.error("Missing data attributes on button:", this);
                return;
            }
            
            switch(action) {
                case 'enter':
                    const faculty = this.getAttribute('data-faculty');
                    const semester = this.getAttribute('data-semester');
                    enterMarks(subjectId, subjectName, classId, faculty, semester);
                    break;
                case 'view-pending':
                    viewPendingMarks(subjectId, subjectName, classId);
                    break;
                case 'view-verified':
                    viewVerifiedMarks(subjectId, subjectName, classId);
                    break;
                case 'view-all':
                    // Implement view all marks
                    break;
                default:
                    console.warn("Unknown action:", action);
            }
        });
    });
}

/**
 * Load more subjects via AJAX (for pagination)
 */
function loadMoreSubjects(page = 1) {
    const loadingDiv = document.getElementById('subjectsLoading');
    const container = document.getElementById('subjectsContainer');
    
    if (loadingDiv) loadingDiv.style.display = 'block';
    
    const urlParams = new URLSearchParams(window.location.search);
    const classId = urlParams.get('class_id');
    const faculty = urlParams.get('faculty');
    const semester = urlParams.get('semester');
    
    fetch(`../teacher/Results/get_more_subjects.php?class_id=${classId}&faculty=${faculty}&semester=${semester}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (loadingDiv) loadingDiv.style.display = 'none';
            
            if (data.error) {
                showAlert('error', data.error);
                return;
            }
            
            if (data.subjects && data.subjects.length > 0) {
                // Append new subjects to container
                // Implementation depends on your HTML structure
            }
            
            if (!data.has_more) {
                const loadMoreBtn = document.getElementById('loadMoreBtn');
                if (loadMoreBtn) loadMoreBtn.style.display = 'none';
            }
        })
        .catch(error => {
            if (loadingDiv) loadingDiv.style.display = 'none';
            showAlert('error', 'Error loading more subjects: ' + error.message);
        });
}

/**
 * Search subjects
 */
function searchSubjects() {
    const searchInput = document.getElementById('subjectSearch');
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    
    document.querySelectorAll('.subject-card').forEach(card => {
        const subjectName = card.getAttribute('data-subject-name').toLowerCase();
        const subjectCode = card.querySelector('.subject-code')?.textContent.toLowerCase() || '';
        
        if (subjectName.includes(searchTerm) || subjectCode.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

/**
 * Filter subjects by status
 */
function filterSubjects(status) {
    document.querySelectorAll('.subject-card').forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        
        if (!status || status === 'all' || cardStatus === status) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}

/**
 * Export subject data (for future use)
 */
function exportSubjectData(subjectId) {
    console.log(`Exporting data for subject ${subjectId}`);
    // Implement export functionality
}

/**
 * Initialize when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log("Subjects module initialized");
    
    // Initialize subject cards
    initializeSubjectCards();
    
    // Initialize search if exists
    const searchInput = document.getElementById('subjectSearch');
    if (searchInput) {
        searchInput.addEventListener('input', searchSubjects);
    }
    
    // Initialize filter if exists
    const filterSelect = document.getElementById('subjectFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterSubjects(this.value);
        });
    }
    
    // Initialize load more button if exists
    const loadMoreBtn = document.getElementById('loadMoreBtn');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            const currentPage = parseInt(this.getAttribute('data-page') || '1');
            loadMoreSubjects(currentPage + 1);
            this.setAttribute('data-page', currentPage + 1);
        });
    }
});

/**
 * Make functions available globally
 */
window.selectSubject = selectSubject;
window.enterMarks = enterMarks;
window.viewPendingMarks = viewPendingMarks;
window.viewVerifiedMarks = viewVerifiedMarks;
window.searchSubjects = searchSubjects;
window.filterSubjects = filterSubjects;
window.exportSubjectData = exportSubjectData;