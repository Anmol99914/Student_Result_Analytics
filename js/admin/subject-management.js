// File: js/admin/subject-management.js
// Purpose: Subject Management JavaScript - SIMPLIFIED WORKING VERSION



// Check if already loaded
if (window.subjectManagerLoaded) {
    console.log('SubjectManager already loaded');
} else {
    window.subjectManagerLoaded = true;
    
    
    const SubjectManager = {
        // Configuration
        currentPage: 1,
        itemsPerPage: 10,
        totalSubjects: 0,
        currentFaculty: '',
        currentSemester: '',
        currentStatus: '',
        searchQuery: '',
        
        // API Endpoints - FIXED PATHS
        API: {
            getSubjects: '../admin/api/get_subjects.php',
            addSubject: '../admin/api/add_subject.php',
            editSubject: '../admin/api/edit_subject.php',
            // deleteSubject: '../admin/api/delete_subject.php',
            deleteSubject: 'api/delete_subject.php',
            getStats: '../admin/api/get_subject_stats.php'
        },

        // Initialize
        init: function() {
            console.log('SubjectManager init called');
            console.log('API endpoints:', this.API);
            console.log('Current path:', window.location.pathname);
            this.bindEvents();
            this.loadSubjects();
            this.loadStats();
        },

        // Bind all events
        bindEvents: function() {
            const self = this;
            
            // Add subject button
            $('#addSubjectBtn, #addFirstSubjectBtn').on('click', function() {
                self.showAddModal();
            });
            
            // Filter changes
            $('#facultyFilter').on('change', function() {
                self.currentFaculty = $(this).val();
                self.currentPage = 1;
                self.loadSubjects();
            });
            
            $('#semesterFilter').on('change', function() {
                self.currentSemester = $(this).val();
                self.currentPage = 1;
                self.loadSubjects();
            });
            
            $('#statusFilter').on('change', function() {
                self.currentStatus = $(this).val();
                self.currentPage = 1;
                self.loadSubjects();
            });
            
            $('#searchInput').on('keyup', function() {
                self.searchQuery = $(this).val().trim();
                self.currentPage = 1;
                self.loadSubjects();
            });
            
            $('#clearFiltersBtn').on('click', function() {
                $('#facultyFilter, #semesterFilter, #statusFilter').val('');
                $('#searchInput').val('');
                self.currentFaculty = '';
                self.currentSemester = '';
                self.currentStatus = '';
                self.searchQuery = '';
                self.currentPage = 1;
                self.loadSubjects();
            });
            
            // Faculty tabs
            $('#facultyTabs .nav-link, #facultyTabs button').on('click', function() {
                $('#facultyTabs .nav-link, #facultyTabs button').removeClass('active');
                $(this).addClass('active');
                self.currentFaculty = $(this).data('faculty') || '';
                $('#facultyFilter').val(self.currentFaculty);
                self.currentPage = 1;
                self.loadSubjects();
            });
            
            // Save subject
            $('#saveSubjectBtn').on('click', function() {
                self.saveSubject();
            });
            
            // Delete subject
            $('#confirmDeleteBtn').on('click', function() {
                self.confirmDelete();
            });
            
            // Modal close resets
            $('#subjectModal').on('hidden.bs.modal', function() {
                $('#subjectForm')[0].reset();
                $('#subjectId').val('');
            });
        },
        
        // Load subjects
        loadSubjects: function() {
            this.showLoading();
            
            const params = {
                page: this.currentPage,
                limit: this.itemsPerPage,
                faculty_id: this.currentFaculty,
                semester: this.currentSemester,
                status: this.currentStatus,
                search: this.searchQuery
            };
            
            console.log('Loading subjects with:', params);
            
            $.ajax({
                url: this.API.getSubjects,
                method: 'GET',
                data: params,
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        this.renderSubjects(response.data);
                        this.renderPagination(response.total, response.pages);
                        this.updateEmptyState(response.data.length === 0);
                    } else {
                        this.showError('Failed to load subjects: ' + response.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('AJAX Error:', xhr.status, xhr.statusText);
                    this.showError('Network error: ' + error);
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        },
        
        // Load stats
        loadStats: function() {
            $.ajax({
                url: this.API.getStats,
                method: 'GET',
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        this.updateStats(response.data);
                    }
                }
            });
        },
        
        // Render subjects table
        renderSubjects: function(subjects) {
            let html = '';
            
            if (subjects.length === 0) {
                html = '<tr><td colspan="8" class="text-center py-4 text-muted">No subjects found</td></tr>';
            } else {
                subjects.forEach((subject, index) => {
                    const rowNumber = (this.currentPage - 1) * this.itemsPerPage + index + 1;
                    const facultyName = this.getFacultyName(subject.faculty_id);
                    const statusBadge = subject.is_active ? 
                        '<span class="badge bg-success">Active</span>' : 
                        '<span class="badge bg-danger">Inactive</span>';
                    
                    html += `
                        <tr data-id="${subject.subject_id}">
                            <td>${rowNumber}</td>
                            <td><strong class="text-primary">${this.escapeHtml(subject.subject_code)}</strong></td>
                            <td>${this.escapeHtml(subject.subject_name)}</td>
                            <td>${facultyName}</td>
                            <td>Semester ${subject.semester}</td>
                            <td><span class="badge bg-info">${subject.credits} Credits</span></td>
                            <td>${statusBadge}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-btn me-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-btn" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }
            
            $('#subjectsTable tbody').html(html);
            this.bindRowActions();
        },
        
        // Bind row actions
        bindRowActions: function() {
            const self = this;
            
            $('.edit-btn').on('click', function() {
                const subjectId = $(this).closest('tr').data('id');
                self.editSubject(subjectId);
            });
            
            $('.delete-btn').on('click', function() {
                const row = $(this).closest('tr');
                const subjectId = row.data('id');
                const subjectName = row.find('td:nth-child(3)').text();
                const subjectCode = row.find('td:nth-child(2) strong').text();
                self.showDeleteModal(subjectId, subjectName, subjectCode);
            });
        },
        
        // Show add modal
        showAddModal: function() {
            $('#modalTitle').html('<i class="fas fa-book me-1"></i> Add New Subject');
            $('#subjectForm')[0].reset();
            $('#subjectId').val('');
            $('#isActive').val('1');
            new bootstrap.Modal('#subjectModal').show();
        },
        
        // Edit subject
        editSubject: function(subjectId) {
            this.showLoading();
            
            $.ajax({
                url: this.API.getSubjects,
                method: 'GET',
                data: { id: subjectId },
                dataType: 'json',
                success: (response) => {
                    if (response.success && response.data.length > 0) {
                        const subject = response.data[0];
                        this.populateEditForm(subject);
                    } else {
                        this.showError('Subject not found');
                    }
                },
                error: () => {
                    this.showError('Failed to load subject data');
                },
                complete: () => {
                    this.hideLoading();
                }
            });
        },
        
        // Populate edit form
        populateEditForm: function(subject) {
            $('#modalTitle').html('<i class="fas fa-edit me-1"></i> Edit Subject');
            $('#subjectId').val(subject.subject_id);
            $('#subjectName').val(subject.subject_name);
            $('#subjectCode').val(subject.subject_code);
            $('#facultyId').val(subject.faculty_id);
            $('#semester').val(subject.semester);
            $('#credits').val(subject.credits);
            $('#isActive').val(subject.is_active);
            $('#description').val(subject.description || '');
            
            new bootstrap.Modal('#subjectModal').show();
        },
        
        // Show delete modal
        showDeleteModal: function(subjectId, subjectName, subjectCode) {

            console.log('=== DELETE MODAL DEBUG ===');
            console.log('Subject ID to delete:', subjectId);
            console.log('Subject Name:', subjectName);
            console.log('Subject Code:', subjectCode);
            console.log('Type of ID:', typeof subjectId);


            $('#deleteSubjectName').text(subjectName);
            $('#deleteSubjectCode').text(subjectCode);
            $('#confirmDeleteBtn').data('id', subjectId);

            console.log('Data-id set to:', $('#confirmDeleteBtn').data('id'));

            new bootstrap.Modal('#deleteModal').show();
        },
        
        // Save subject
        saveSubject: function() {
            if (!this.validateForm()) return;
            
            const formData = {
                subject_id: $('#subjectId').val(),
                subject_name: $('#subjectName').val().trim(),
                subject_code: $('#subjectCode').val().trim().toUpperCase(),
                faculty_id: $('#facultyId').val(),
                semester: $('#semester').val(),
                credits: $('#credits').val(),
                is_active: $('#isActive').val(),
                description: $('#description').val().trim()
            };
            
            const isEdit = formData.subject_id !== '';
            const apiUrl = isEdit ? this.API.editSubject : this.API.addSubject;
            
            const saveBtn = $('#saveSubjectBtn');
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
            
            $.ajax({
                url: apiUrl,
                method: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                dataType: 'json',
                success: (response) => {
                    if (response.success) {
                        this.showSuccess(response.message || 'Subject saved successfully!');
                        $('#subjectModal').modal('hide');
                        this.loadSubjects();
                        this.loadStats();
                    } else {
                        this.showError(response.message || 'Failed to save subject');
                    }
                },
                error: (xhr, status, error) => {
                    this.showError('Network error: ' + error);
                },
                complete: () => {
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save Subject');
                }
            });
        },
        
        // Confirm delete
        confirmDelete: function() {
            const subjectId = $('#confirmDeleteBtn').data('id');
            
            console.log('Deleting with POST, subject ID:', subjectId);
            
            $.ajax({
                url: this.API.deleteSubject,
                method: 'POST',  // ← MUST BE POST!
                data: { subject_id: subjectId },
                dataType: 'json',
                success: (response) => {
                    console.log('Delete response:', response);
                    if (response.success) {
                        alert('Success: ' + response.message);
                        $('#deleteModal').modal('hide');
                        this.loadSubjects();
                        this.loadStats();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Delete error:', xhr.responseText);
                    alert('Delete failed');
                }
            });
        },

        // Render pagination
        renderPagination: function(total, totalPages) {
            this.totalSubjects = total;
            
            // Update pagination info
            const start = ((this.currentPage - 1) * this.itemsPerPage) + 1;
            const end = Math.min(this.currentPage * this.itemsPerPage, total);
            $('#paginationInfo').text(`Showing ${start} to ${end} of ${total} subjects`);
            
            // Clear existing pagination
            $('#paginationControls').empty();
            
            if (totalPages <= 1) return;
            
            // Previous button
            const prevDisabled = this.currentPage === 1 ? 'disabled' : '';
            $('#paginationControls').append(`
                <li class="page-item ${prevDisabled}">
                    <a class="page-link" href="#" data-page="${this.currentPage - 1}">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `);
            
            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const active = i === this.currentPage ? 'active' : '';
                $('#paginationControls').append(`
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>
                `);
            }
            
            // Next button
            const nextDisabled = this.currentPage === totalPages ? 'disabled' : '';
            $('#paginationControls').append(`
                <li class="page-item ${nextDisabled}">
                    <a class="page-link" href="#" data-page="${this.currentPage + 1}">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `);
            
            // Bind pagination clicks
            const self = this;
            $('.page-link').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== self.currentPage) {
                    self.currentPage = page;
                    self.loadSubjects();
                }
            });
        },
        
        // Update stats
        updateStats: function(stats) {
            if (stats.bca !== undefined) $('#bcaSubjectsStat').text(stats.bca);
            if (stats.bbm !== undefined) $('#bbmSubjectsStat').text(stats.bbm);
            if (stats.bim !== undefined) $('#bimSubjectsStat').text(stats.bim);
            if (stats.total !== undefined) $('#totalSubjectsStat').text(stats.total);
        },
        
        // Update empty state
        updateEmptyState: function(isEmpty) {
            if (isEmpty) {
                $('#subjectsTable').hide();
                $('#paginationSection').hide();
                $('#noSubjectsPlaceholder').show();
            } else {
                $('#subjectsTable').show();
                $('#paginationSection').show();
                $('#noSubjectsPlaceholder').hide();
            }
        },
        
        // Show loading
        showLoading: function() {
            $('#loadingRow').show();
        },
        
        // Hide loading
        hideLoading: function() {
            $('#loadingRow').hide();
        },
        
        // Validate form
        validateForm: function() {
            const name = $('#subjectName').val().trim();
            const code = $('#subjectCode').val().trim();
            const faculty = $('#facultyId').val();
            const semester = $('#semester').val();
            const credits = $('#credits').val();
            
            if (!name) {
                this.showError('Subject name is required');
                $('#subjectName').focus();
                return false;
            }
            
            if (!code) {
                this.showError('Subject code is required');
                $('#subjectCode').focus();
                return false;
            }
            
            if (!faculty) {
                this.showError('Please select a faculty');
                $('#facultyId').focus();
                return false;
            }
            
            if (!semester) {
                this.showError('Please select a semester');
                $('#semester').focus();
                return false;
            }
            
            if (!credits) {
                this.showError('Please select credit hours');
                $('#credits').focus();
                return false;
            }
            
            return true;
        },
        
        // Get faculty name
        getFacultyName: function(facultyId) {
            const facultyMap = {
                1: 'BCA',
                2: 'BBM', 
                3: 'BIM'
            };
            return facultyMap[facultyId] || 'Unknown';
        },
        
        // Show success message
        showSuccess: function(message) {
            alert('Success: ' + message);
        },
        
        // Show error message
        showError: function(message) {
            alert('Error: ' + message);
        },
        
        // Escape HTML
        escapeHtml: function(text) {
            return text.replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[m];
            });
        }
    };
    
    // Expose to window
    window.SubjectManager = SubjectManager;
    
    // Auto-init if on subject management page
    $(document).ready(function() {
        if ($('#subjectsTable').length) {
            console.log('Auto-initializing SubjectManager');
            SubjectManager.init();
        }
    });
}

