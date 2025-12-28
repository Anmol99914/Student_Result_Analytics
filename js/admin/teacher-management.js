// teacher-management.js - Teacher management JavaScript

// Check if already loaded to prevent duplicate declaration
if (typeof window.TeacherManager !== 'undefined') {
    console.log('TeacherManager already loaded, reinitializing...');
    if (window.teacherManager && typeof window.teacherManager.init === 'function') {
        window.teacherManager.init();
    }
} else {
    console.log('Loading TeacherManager for the first time...');
    
    // Define the class
    class TeacherManager {
        constructor() {
            console.log('TeacherManager constructor called');
            this.currentTab = 'active';
            this.init();
        }
        
        init() {
            console.log('TeacherManager.init() called');
            this.loadTeacherTab('active');
            this.setupEventListeners();
        }
        
        setupEventListeners() {
            console.log('Setting up teacher event listeners...');
            
            // Tab change events
            document.querySelectorAll('#teacherTabs button').forEach(tab => {
                tab.addEventListener('shown.bs.tab', (event) => {
                    const tabId = event.target.id.replace('-tab', '');
                    this.currentTab = tabId;
                    this.loadTeacherTab(tabId);
                });
            });
            
            // Add teacher button
            const addTeacherBtn = document.getElementById('addTeacherBtn');
            if (addTeacherBtn) {
                addTeacherBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.showAddTeacherForm();
                });
            }
            
            console.log('Teacher event listeners setup complete');
        }
        
        loadTeacherTab(tab, page = 1) {
            console.log(`Loading teacher tab: ${tab}, page: ${page}`);
            const containerId = `${tab}-teachers-container`;
            const container = document.getElementById(containerId);
            
            if (!container) {
                console.error(`Container ${containerId} not found!`);
                return;
            }
            
            // Show loading
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading teachers...</p>
                </div>
            `;
            
            // Load via AJAX
            fetch(`admin_teachers_table.php?tab=${tab}&page=${page}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    this.initTeacherTableEvents(tab);
                    console.log(`Teacher tab ${tab} loaded successfully`);
                })
                .catch(error => {
                    console.error(`Error loading teacher tab ${tab}:`, error);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Error loading teachers. Please try again.
                        </div>
                    `;
                });
        }
        
        initTeacherTableEvents(tab) {
            console.log(`Initializing events for ${tab} teachers table`);
            const container = document.getElementById(`${tab}-teachers-container`);
            
            if (!container) {
                console.error(`Container for ${tab} not found`);
                return;
            }
            
            // Edit button events
            container.querySelectorAll('.edit-teacher-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const teacherId = btn.getAttribute('data-teacher-id');
                    this.showEditTeacherModal(teacherId);
                });
            });
            
            // Status toggle events
            container.querySelectorAll('.deactivate-btn, .reactivate-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const teacherId = btn.getAttribute('data-teacher-id') || 
                                     btn.getAttribute('href')?.split('=')[1];
                    if (!teacherId) {
                        console.error('Could not find teacher ID from button:', btn);
                        return;
                    }
                    
                    const action = btn.classList.contains('deactivate-btn') ? 'deactivate' : 'activate';
                    this.toggleTeacherStatus(teacherId, action);
                });
            });
            
            // Pagination events
            container.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const href = link.getAttribute('href');
                    if (href && href !== '#') {
                        const url = new URL(href, window.location.origin);
                        const page = url.searchParams.get('page') || 1;
                        this.loadTeacherTab(this.currentTab, page);
                    }
                });
            });
        }
        
        showAddTeacherForm() {
            console.log('Showing add teacher form...');
            
            // Create a modal for adding teacher
            this.showAddTeacherModal();
        }
        
        showAddTeacherModal() {
            console.log('Creating add teacher modal...');
            
            // Remove existing modal if any
            const existingModal = document.getElementById('addTeacherModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="addTeacherModalLabel">
                                    <i class="bi bi-person-plus me-2"></i>Add New Teacher
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="add-teacher-loading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">Loading form...</p>
                                </div>
                                <div id="add-teacher-form" style="display: none;">
                                    <!-- Form will be loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Load form via AJAX
            this.loadAddTeacherForm();
            
            // Show modal
            const modalElement = document.getElementById('addTeacherModal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
            
            // Remove modal from DOM when hidden
            modalElement.addEventListener('hidden.bs.modal', function() {
                setTimeout(() => {
                    if (modalElement.parentNode) {
                        modalElement.remove();
                    }
                }, 300);
            });
        }
        
        loadAddTeacherForm() {
            console.log('Loading add teacher form...');
            const formContainer = document.getElementById('add-teacher-form');
            const loadingContainer = document.getElementById('add-teacher-loading');
            
            if (!formContainer || !loadingContainer) {
                console.error('Form containers not found');
                return;
            }
            
            fetch('add_teacher_content.php')
                .then(response => response.text())
                .then(html => {
                    loadingContainer.style.display = 'none';
                    formContainer.style.display = 'block';
                    formContainer.innerHTML = html;
                    
                    // Initialize form submission
                    this.setupAddTeacherForm();
                })
                .catch(error => {
                    console.error('Error loading teacher form:', error);
                    loadingContainer.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> 
                            Error loading form: ${error.message}
                        </div>
                    `;
                });
        }
        
        setupAddTeacherForm() {
            const form = document.getElementById('addTeacherForm');
            if (!form) {
                console.error('Add teacher form not found in loaded content');
                return;
            }
            
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitAddTeacherForm(form);
            });
            
            console.log('Add teacher form setup complete');
        }
        
        submitAddTeacherForm(form) {
            console.log('Submitting add teacher form...');
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            submitBtn.disabled = true;
            
            // Submit via AJAX
            fetch('admin_add_teacher.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Add teacher response:', data);
                
                if (data.success) {
                    showAlert('success', data.message || 'Teacher added successfully');
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTeacherModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Reload teachers after 1 second
                    setTimeout(() => {
                        this.loadTeacherTab(this.currentTab);
                    }, 1000);
                    
                } else {
                    showAlert('danger', data.message || 'Error adding teacher');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Network error: ' + error.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        }
        
        showEditTeacherModal(teacherId) {
            console.log('Showing edit modal for teacher:', teacherId);
            
            // For now, just load the edit page
            // You can create a modal similar to add teacher if needed
            loadPage(`edit_teacher_content.php?teacher_id=${teacherId}`);
        }
        
        toggleTeacherStatus(teacherId, action) {
            console.log(`Toggling teacher status: ${teacherId}, ${action}`);
            
            const confirmMsg = action === 'deactivate' 
                ? 'Are you sure you want to deactivate this teacher?' 
                : 'Are you sure you want to activate this teacher?';
            
            if (!confirm(confirmMsg)) return;
            
            // Show loading in the button if possible
            const button = event?.target || document.querySelector(`[data-teacher-id="${teacherId}"]`);
            if (button) {
                const originalText = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                button.disabled = true;
                
                // Restore button after 2 seconds even if error
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 2000);
            }
            
            fetch(`toggle_teacher_status.php?teacher_id=${teacherId}&action=${action}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert('success', data.message);
                        this.loadTeacherTab(this.currentTab);
                    } else {
                        showAlert('danger', data.message);
                    }
                })
                .catch(error => {
                    showAlert('danger', 'Error updating teacher status');
                });
        }
    }
    
    // Make available globally
    window.TeacherManager = TeacherManager;
}

// Initialize when appropriate
function initializeTeacherManager() {
    console.log('initializeTeacherManager called');
    
    // Check if we're on the teacher management page
    const isTeacherPage = document.querySelector('.teacher-management-container') !== null;
    
    if (isTeacherPage) {
        console.log('On teacher management page, initializing...');
        
        if (typeof TeacherManager !== 'undefined' && !window.teacherManager) {
            console.log('Creating new TeacherManager instance');
            window.teacherManager = new TeacherManager();
        } else if (window.teacherManager) {
            console.log('Reinitializing existing teacherManager');
            window.teacherManager.init();
        } else {
            console.error('TeacherManager not available');
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initializeTeacherManager);

// Also initialize when page loads via AJAX
window.addEventListener('pageLoaded', function(event) {
    console.log('pageLoaded event:', event.detail.url);
    if (event.detail.url.includes('teacher_management.php')) {
        // Small delay to ensure DOM is ready
        setTimeout(initializeTeacherManager, 100);
    }
});