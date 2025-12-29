// teacher-management.js - COMPLETE VERSION WITH ALL FEATURES
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
    
    // Teacher Manager - ALL METHODS IN ONE OBJECT
    const TeacherManager = {
        currentTab: 'active',
        searchTerm: '',
        
        // ========== INITIALIZATION ==========
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
        
        // ========== UI RENDERING ==========
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
                        <!-- Search and Controls -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="teacherSearch" 
                                           placeholder="Search teachers by name or email...">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-success" id="addTeacherBtn">
                                    <i class="bi bi-person-plus"></i> Add Teacher
                                </button>
                            </div>
                        </div>
                        
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
            
            // Add event listeners
            this.setupTabs();
            this.setupSearch();
            this.setupAddButton();
        },
        
        setupTabs: function() {
            document.querySelector('.btn-group').addEventListener('click', (e) => {
                const tabBtn = e.target.closest('[data-tab]');
                if (tabBtn) {
                    this.switchTab(tabBtn.dataset.tab);
                }
            });
        },
        
        setupSearch: function() {
            const searchInput = document.getElementById('teacherSearch');
            const clearBtn = document.getElementById('clearSearch');
            
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', (e) => {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        this.searchTeachers(e.target.value);
                    }, 300);
                });
            }
            
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    document.getElementById('teacherSearch').value = '';
                    this.searchTerm = '';
                    this.loadTeachers();
                });
            }
        },
        
        setupAddButton: function() {
            document.getElementById('addTeacherBtn').addEventListener('click', () => {
                this.showAddTeacherForm();
            });
        },
        
        // ========== CORE FUNCTIONS ==========
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
                    // Apply search filter if active
                    if (this.searchTerm) {
                        this.searchTeachers(this.searchTerm);
                    }
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
        },
        
        searchTeachers: function(searchTerm) {
            this.searchTerm = searchTerm;
            const rows = document.querySelectorAll('#teachers-table-container tbody tr');
            
            if (!searchTerm.trim()) {
                rows.forEach(row => row.style.display = '');
                return;
            }
            
            const term = searchTerm.toLowerCase();
            let visibleCount = 0;
            
            rows.forEach(row => {
                const name = row.cells[1].textContent.toLowerCase();
                const email = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(term) || email.includes(term)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update count display
            const countElement = document.querySelector('#teachers-table-container h5');
            if (countElement && visibleCount > 0) {
                countElement.textContent = `Teachers (${visibleCount} of ${rows.length} shown)`;
            }
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
        
        // ========== TEACHER CRUD OPERATIONS ==========
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
                    alert('✅ Teacher added successfully!');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTeacherModal'));
                    if (modal) modal.hide();
                    // Refresh list
                    this.loadTeachers();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            } catch (error) {
                alert('❌ Network error: ' + error.message);
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        },
        
        editTeacher: async function(teacherId) {
            console.log('Editing teacher #' + teacherId);
            
            try {
                // Fetch teacher details
                const response = await fetch(`../admin/api/get_teacher.php?teacher_id=${teacherId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load teacher details');
                }
                
                this.showEditTeacherForm(data.teacher);
                
            } catch (error) {
                console.error('Error loading teacher:', error);
                alert('❌ Error loading teacher details: ' + error.message);
            }
        },
        
        showEditTeacherForm: function(teacher) {
            // Create modal HTML
            const modalHTML = `
                <div class="modal fade" id="editTeacherModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-pencil"></i> Edit Teacher
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editTeacherForm">
                                    <input type="hidden" name="teacher_id" value="${teacher.teacher_id}">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name" 
                                               value="${teacher.name}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="${teacher.email}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">New Password (leave blank to keep current)</label>
                                        <input type="password" class="form-control" name="password">
                                        <small class="text-muted">Only enter if you want to change the password</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="active" ${teacher.status === 'active' ? 'selected' : ''}>Active</option>
                                            <option value="inactive" ${teacher.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                        </select>
                                    </div>
                                    
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i>
                                        Teacher ID: <strong>#${teacher.teacher_id}</strong> | 
                                        Created: ${teacher.created_at ? new Date(teacher.created_at).toLocaleDateString() : 'N/A'}
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="submitEditTeacher">Update Teacher</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editTeacherModal'));
            modal.show();
            
            // Handle form submission
            document.getElementById('submitEditTeacher').addEventListener('click', () => {
                this.submitEditTeacherForm();
            });
            
            // Remove modal when hidden
            document.getElementById('editTeacherModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        },
        
        submitEditTeacherForm: async function() {
            const form = document.getElementById('editTeacherForm');
            const formData = new FormData(form);
            
            // Show loading
            const submitBtn = document.getElementById('submitEditTeacher');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('../admin/api/update_teacher.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ Teacher updated successfully!');
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTeacherModal'));
                    if (modal) modal.hide();
                    // Refresh list
                    this.loadTeachers();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            } catch (error) {
                alert('❌ Network error: ' + error.message);
            } finally {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        },
        
        viewTeacher: async function(teacherId) {
            console.log('Viewing teacher #' + teacherId);
            
            try {
                // Fetch teacher details with stats
                const response = await fetch(`../admin/api/get_teacher.php?teacher_id=${teacherId}`);
                const data = await response.json();
                
                if (!data.success) {
                    throw new Error(data.error || 'Failed to load teacher details');
                }
                
                this.showTeacherDetails(data.teacher, data.stats);
                
            } catch (error) {
                console.error('Error loading teacher:', error);
                alert('❌ Error loading teacher details: ' + error.message);
            }
        },
        
        showTeacherDetails: function(teacher, stats) {
            // Store teacher data globally for updates
            window.currentTeacherInModal = teacher;
            
            const createdDate = teacher.created_at 
                ? new Date(teacher.created_at).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  })
                : 'N/A';
            
            // Create modal HTML - REMOVED duplicate status line
            const modalHTML = `
                <div class="modal fade" id="viewTeacherModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="bi bi-person-badge"></i> Teacher Details
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <div class="teacher-avatar-large bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                             style="width: 100px; height: 100px; font-size: 36px;">
                                            ${teacher.name.charAt(0).toUpperCase()}
                                        </div>
                                        <span class="badge ${teacher.status === 'active' ? 'bg-success' : 'bg-secondary'} fs-6">
                                            ${teacher.status}
                                        </span>
                                    </div>
                                    
                                    <div class="col-md-9">
                                        <h4 class="mb-3">${teacher.name}</h4>
                                        
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title text-muted">Contact Information</h6>
                                                        <p class="mb-1"><i class="bi bi-envelope me-2"></i> ${teacher.email}</p>
                                                        <p class="mb-0"><i class="bi bi-person me-2"></i> Teacher ID: #${teacher.teacher_id}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body">
                                                        <h6 class="card-title text-muted">Account Information</h6>
                                                        <p class="mb-0"><i class="bi bi-calendar me-2"></i> Created: ${createdDate}</p>
                                                        <!-- REMOVED: Status line here since it's shown in badge above -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h2 class="text-primary">${stats?.subject_count || 0}</h2>
                                                        <p class="text-muted mb-0">Subjects Assigned</p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="card">
                                                    <div class="card-body text-center">
                                                        <h2 class="text-success">${stats?.class_count || 0}</h2>
                                                        <p class="text-muted mb-0">Classes Assigned</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="alert alert-warning">
                                            <i class="bi bi-lightbulb"></i>
                                            <strong>Quick Actions:</strong>
                                            <div class="mt-2">
                                                <button class="btn btn-sm btn-primary me-2" onclick="window.teacherManager.editTeacher(${teacher.teacher_id})">
                                                    <i class="bi bi-pencil"></i> Edit Teacher
                                                </button>
                                                <button class="btn btn-sm btn-warning me-2" onclick="window.teacherManager.toggleStatus(${teacher.teacher_id}, '${teacher.status}', true)">
                                                    <i class="bi bi-power"></i> ${teacher.status === 'active' ? 'Deactivate' : 'Activate'}
                                                </button>
                                                <a href="assign_teachers.php?teacher_id=${teacher.teacher_id}" class="btn btn-sm btn-success">
                                                    <i class="bi bi-book"></i> Manage Assignments
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add modal to page
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewTeacherModal'));
            modal.show();
            
            // Remove modal when hidden and clean up
            document.getElementById('viewTeacherModal').addEventListener('hidden.bs.modal', function() {
                delete window.currentTeacherInModal;
                this.remove();
            });
        },
        
        toggleStatus: async function(teacherId, currentStatus, fromModal = false) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = currentStatus === 'active' ? 'deactivate' : 'activate';
            
            if (!confirm(`Are you sure you want to ${action} this teacher?`)) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('teacher_id', teacherId);
                formData.append('status', newStatus);
                
                const response = await fetch('../admin/api/update_teacher_status.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    this.showToast(`✅ Teacher ${action}d successfully!`, 'success');
                    
                    // 1. Refresh the main teachers table
                    this.loadTeachers();
                    
                    // 2. If called from modal, update modal UI
                    if (fromModal) {
                        this.updateModalAfterStatusChange(teacherId, newStatus);
                    }
                    
                    // 3. Update status in current table row (if visible)
                    this.updateRowStatus(teacherId, newStatus);
                    
                } else {
                    this.showToast(`❌ Error: ${data.error}`, 'error');
                }
            } catch (error) {
                this.showToast(`❌ Network error: ${error.message}`, 'error');
            }
        },
        
        // Helper method to update modal after status change
        updateModalAfterStatusChange: function(teacherId, newStatus) {
            const modal = document.getElementById('viewTeacherModal');
            if (!modal) return;
            
            // Update status badge in modal (near avatar)
            const statusBadge = modal.querySelector('.badge.fs-6');
            if (statusBadge) {
                statusBadge.className = newStatus === 'active' ? 'badge bg-success fs-6' : 'badge bg-secondary fs-6';
                statusBadge.textContent = newStatus;
            }
            
            // Update button text
            const toggleBtn = modal.querySelector('button.btn-warning');
            if (toggleBtn) {
                const action = newStatus === 'active' ? 'Deactivate' : 'Activate';
                toggleBtn.innerHTML = `<i class="bi bi-power"></i> ${action}`;
                toggleBtn.setAttribute('onclick', `window.teacherManager.toggleStatus(${teacherId}, '${newStatus}', true)`);
            }
            
            // Update teacher object status
            if (window.currentTeacherInModal) {
                window.currentTeacherInModal.status = newStatus;
            }
        },
        
        // Helper method to update table row
        updateRowStatus: function(teacherId, newStatus) {
            const rows = document.querySelectorAll('#teachers-table-container tbody tr');
            rows.forEach(row => {
                const idCell = row.querySelector('td:first-child strong');
                if (idCell && idCell.textContent.includes(`#${teacherId}`)) {
                    // Update status badge
                    const statusBadge = row.querySelector('.badge');
                    if (statusBadge) {
                        statusBadge.className = newStatus === 'active' ? 'badge bg-success' : 'badge bg-secondary';
                        statusBadge.textContent = newStatus;
                    }
                    
                    // Update toggle button tooltip
                    const toggleBtn = row.querySelector('button.btn-outline-warning');
                    if (toggleBtn) {
                        const action = newStatus === 'active' ? 'Deactivate' : 'Activate';
                        toggleBtn.title = action;
                        toggleBtn.setAttribute('onclick', `window.teacherManager.toggleStatus(${teacherId}, '${newStatus}')`);
                    }
                }
            });
        },
        
        // Toast notification helper
        showToast: function(message, type = 'info') {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.teacher-toast');
            existingToasts.forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `teacher-toast alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideIn 0.3s ease;';
            toast.innerHTML = `
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(toast);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 3000);
            
            // Add CSS animation
            if (!document.querySelector('#toast-animation')) {
                const style = document.createElement('style');
                style.id = 'toast-animation';
                style.textContent = `
                    @keyframes slideIn {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
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