// teacher-management.js - PROPERLY FORMATTED VERSION
(function() {
    'use strict';
    
    console.log('Teacher Management module loading...');
    
    // Configuration
    const CONFIG = {
        apiUrl: '../admin/api/get_teachers.php',
        containerId: 'teachers-container'
    };
    
    // Check if already initialized
    if (window.teacherManager) {
        console.log('teacherManager already exists');
        if (typeof window.teacherManager.init === 'function') {
            window.teacherManager.init();
        }
        return;
    }
    
    // Teacher Manager
    const TeacherManager = {
        currentTab: 'active',
        
        init: function() {
            console.log('TeacherManager.init() called');
            
            if (!this.validateEnvironment()) {
                return;
            }
            
            this.renderUI();
            this.loadTeachers();
        },
        
        validateEnvironment: function() {
            const container = document.getElementById(CONFIG.containerId);
            if (!container) {
                console.error('Container not found:', CONFIG.containerId);
                return false;
            }
            return true;
        },
        
        renderUI: function() {
            const container = document.getElementById(CONFIG.containerId);
            
            container.innerHTML = `
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-people"></i> Teacher Management
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- Tabs -->
                        <div class="btn-group mb-4" role="group">
                            <button type="button" class="btn btn-primary active" data-tab="active">
                                Active Teachers
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-tab="inactive">
                                Inactive Teachers
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-tab="all">
                                All Teachers
                            </button>
                        </div>
                        
                        <!-- Table Container -->
                        <div id="teachers-table-container">
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary"></div>
                                <p class="mt-2">Loading teachers...</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add tab event listeners
            this.setupTabs();
        },
        
        setupTabs: function() {
            document.querySelector('.btn-group').addEventListener('click', (e) => {
                const tabBtn = e.target.closest('[data-tab]');
                if (tabBtn) {
                    this.switchTab(tabBtn.dataset.tab);
                }
            });
        },
        
        switchTab: function(tabName) {
            console.log('Switching to tab:', tabName);
            this.currentTab = tabName;
            
            // Update UI
            document.querySelectorAll('[data-tab]').forEach(btn => {
                if (btn.dataset.tab === tabName) {
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-primary', 'active');
                } else {
                    btn.classList.remove('btn-primary', 'active');
                    btn.classList.add('btn-outline-primary');
                }
            });
            
            this.loadTeachers();
        },
        
        async loadTeachers() {
            const container = document.getElementById('teachers-table-container');
            if (!container) return;
            
            // Show loading
            container.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading ${this.currentTab} teachers...</p>
                </div>
            `;
            
            try {
                const url = `${CONFIG.apiUrl}?status=${this.currentTab}`;
                console.log('Fetching from:', url);
                
                const response = await fetch(url);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                console.log('API Response:', data);
                
                if (data.success) {
                    this.displayTeachers(data.teachers || []);
                } else {
                    throw new Error(data.error || 'API error');
                }
                
            } catch (error) {
                console.error('Error:', error);
                this.showError(error.message);
            }
        },
        
        displayTeachers: function(teachers) {
            const container = document.getElementById('teachers-table-container');
            
            if (!teachers || teachers.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No teachers found.
                    </div>
                `;
                return;
            }
            
            let html = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Teachers (${teachers.length})</h5>
                        <small class="text-muted">Showing ${this.currentTab} teachers</small>
                    </div>
                    <button class="btn btn-success" id="addTeacherBtn">
                        <i class="bi bi-person-plus"></i> Add Teacher
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            teachers.forEach(teacher => {
                const created = teacher.created_at 
                    ? new Date(teacher.created_at).toLocaleDateString() 
                    : 'N/A';
                    
                html += `
                    <tr>
                        <td><strong>#${teacher.teacher_id}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-placeholder bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                     style="width: 36px; height: 36px;">
                                    ${teacher.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <strong>${teacher.name}</strong>
                                </div>
                            </div>
                        </td>
                        <td>${teacher.email}</td>
                        <td>
                            <span class="badge ${teacher.status === 'active' ? 'bg-success' : 'bg-secondary'}">
                                ${teacher.status}
                            </span>
                        </td>
                        <td><small class="text-muted">${created}</small></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-primary" title="Edit" 
                                        onclick="window.teacherManager.editTeacher(${teacher.teacher_id})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" title="View Details"
                                        onclick="window.teacherManager.viewTeacher(${teacher.teacher_id})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-warning" title="${teacher.status === 'active' ? 'Deactivate' : 'Activate'}"
                                        onclick="window.teacherManager.toggleStatus(${teacher.teacher_id}, '${teacher.status}')">
                                    <i class="bi bi-power"></i>
                                </button>
                                <a href="assign_teachers.php?teacher_id=${teacher.teacher_id}" 
                                   class="btn btn-outline-success" title="Assign Subjects">
                                    <i class="bi bi-book"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
            container.innerHTML = html;
            
            // Add event listener for add button
            document.getElementById('addTeacherBtn').addEventListener('click', () => {
                this.showAddTeacherForm();
            });
        },
        
        showError: function(message) {
            const container = document.getElementById('teachers-table-container');
            
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error</h5>
                    <p>${message}</p>
                    <button class="btn btn-sm btn-danger" onclick="window.teacherManager.loadTeachers()">
                        Try Again
                    </button>
                </div>
            `;
        },
        
        showAddTeacherForm: function() {
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="addTeacherModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-person-plus"></i> Add New Teacher
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addTeacherForm">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password *</label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="active" selected>Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="submitAddTeacher">Add Teacher</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addTeacherModal'));
            modal.show();
            
            // Handle form submission
            document.getElementById('submitAddTeacher').addEventListener('click', () => {
                this.submitAddTeacherForm();
            });
            
            // Remove modal when hidden
            document.getElementById('addTeacherModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        },
        
        submitAddTeacherForm: async function() {
            const form = document.getElementById('addTeacherForm');
            const formData = new FormData(form);
            
            // Show loading
            const submitBtn = document.getElementById('submitAddTeacher');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../admin/api/add_teacher.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Teacher added successfully!');
                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('addTeacherModal')).hide();
                    // Refresh list
                    this.loadTeachers();
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        },
        
        editTeacher: function(teacherId) {
            alert('Edit teacher #' + teacherId + ' - To be implemented');
            // Similar to add but with pre-filled data
        },
        
        viewTeacher: function(teacherId) {
            alert('View teacher details #' + teacherId + ' - To be implemented');
            // Show modal with teacher details, assignments, etc.
        },
        
        toggleStatus: async function(teacherId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = currentStatus === 'active' ? 'deactivate' : 'activate';
            
            if (!confirm(`Are you sure you want to ${action} this teacher?`)) {
                return;
            }
            
            try {
                const response = await fetch('../admin/api/update_teacher_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `teacher_id=${teacherId}&status=${newStatus}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`Teacher ${action}d successfully!`);
                    this.loadTeachers(); // Refresh list
                } else {
                    alert('Error: ' + data.error);
                }
            } catch (error) {
                alert('Network error: ' + error.message);
            }
        }

        
    };
    
    // Export to window
    window.teacherManager = TeacherManager;
    
    console.log('TeacherManager module loaded');
    
    // Auto-initialize if container exists
    if (document.getElementById('teachers-container')) {
        console.log('Auto-initializing...');
        setTimeout(() => TeacherManager.init(), 100);
    }
})();