/**
 * Results Management System
 * File: js/results.js
 */

console.log('=== RESULTS.JS LOADED ===');

// Check if we're on results page
if (!document.getElementById('classes-container')) {
    console.log('Not on results page - exiting');
    return;
}

// Main Results System
const ResultsSystem = (function() {
    'use strict';
    
    console.log('Initializing ResultsSystem...');
    
    // Private variables
    let teacherId = window.TEACHER_DATA?.id || 0;
    let teacherName = window.TEACHER_DATA?.name || '';
    
    // Private methods
    function log(message, data = null) {
        console.log(`[Results] ${message}`, data || '');
    }
    
    function showError(message) {
        const container = document.getElementById('classes-container');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error</h5>
                    <p>${message}</p>
                    <button class="btn btn-sm btn-primary" onclick="window.location.reload()">
                        Reload Page
                    </button>
                </div>
            `;
        }
    }
    
    function showLoading(message = 'Loading...') {
        const container = document.getElementById('classes-container');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">${message}</p>
                </div>
            `;
        }
    }
    
    // Public methods
    return {
        init: function() {
            log('System initialized');
            log('Teacher:', { id: teacherId, name: teacherName });
            
            if (!teacherId) {
                showError('Teacher ID not found. Please login again.');
                return;
            }
            
            this.loadClasses();
        },
        
        loadClasses: function() {
            log('Loading classes for teacher:', teacherId);
            showLoading('Loading your classes...');
            
            const url = `get_classes_for_results.php?teacher_id=${teacherId}`;
            log('Fetch URL:', url);
            
            fetch(url)
                .then(response => {
                    log('Response status:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    log('Response received, length:', html.length);
                    
                    if (html.length === 0) {
                        throw new Error('Empty response from server');
                    }
                    
                    document.getElementById('classes-container').innerHTML = html;
                    this.bindClassEvents();
                })
                .catch(error => {
                    log('Error loading classes:', error);
                    showError(`Failed to load classes: ${error.message}`);
                });
        },
        
        bindClassEvents: function() {
            const classCards = document.querySelectorAll('.class-card');
            log('Binding events to', classCards.length, 'class cards');
            
            classCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    // Don't trigger if clicking button
                    if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                        return;
                    }
                    
                    const classId = card.getAttribute('data-class-id');
                    const faculty = card.getAttribute('data-faculty');
                    const semester = card.getAttribute('data-semester');
                    
                    log('Class selected:', { classId, faculty, semester });
                    this.loadSubjects(classId, faculty, semester);
                });
            });
        },
        
        loadSubjects: function(classId, faculty, semester) {
            log('Loading subjects for class:', { classId, faculty, semester });
            showLoading(`Loading subjects for ${faculty} - Semester ${semester}...`);
            
            // You'll implement this later
            setTimeout(() => {
                document.getElementById('classes-container').innerHTML = `
                    <div class="alert alert-info">
                        <h5>Subjects Loading...</h5>
                        <p>Class: ${faculty} - Semester ${semester}</p>
                        <p>This feature will load subjects here.</p>
                        <button class="btn btn-sm btn-secondary" onclick="ResultsSystem.loadClasses()">
                            ← Back to Classes
                        </button>
                    </div>
                `;
            }, 1000);
        },
        
        // Public method for testing
        test: function() {
            alert('ResultsSystem is working!');
        }
    };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready - initializing ResultsSystem');
    
    // Give a small delay to ensure everything is loaded
    setTimeout(() => {
        ResultsSystem.init();
    }, 100);
});

// Make available globally
window.ResultsSystem = ResultsSystem;