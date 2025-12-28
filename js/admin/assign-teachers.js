// assign-teachers.js
// JavaScript for teacher assignment page

document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const facultySelect = document.getElementById('facultySelect');
    const semesterSelect = document.getElementById('semesterSelect');
    const subjectSelect = document.getElementById('subjectSelect');
    const teachersTable = document.getElementById('teachersTable');
    const assignmentsList = document.getElementById('assignmentsList');
    const saveBtn = document.getElementById('saveBtn');
    const loadingSpinner = document.getElementById('loadingSpinner');
    
    let currentClassId = 0;
    let currentFaculty = '';
    let currentSemester = 0;
    let currentSubjectId = 0;
    let selectedTeachers = new Set(); // Store selected teacher IDs
    
    // Initialize
    init();
    
    function init() {
        // Get class ID from URL or data attribute
        const urlParams = new URLSearchParams(window.location.search);
        currentClassId = urlParams.get('class_id') || document.body.dataset.classId || 0;
        currentFaculty = document.body.dataset.faculty || 'BCA';
        currentSemester = document.body.dataset.semester || 1;
        
        // Set initial values
        if(facultySelect) facultySelect.value = currentFaculty;
        if(semesterSelect) semesterSelect.value = currentSemester;
        
        // Load initial data
        loadSubjects();
        loadCurrentAssignments();
        
        // Setup event listeners
        setupEventListeners();
    }
    
    function setupEventListeners() {
        // Faculty/Semester change
        if(facultySelect) {
            facultySelect.addEventListener('change', function() {
                currentFaculty = this.value;
                loadSubjects();
            });
        }
        
        if(semesterSelect) {
            semesterSelect.addEventListener('change', function() {
                currentSemester = parseInt(this.value);
                loadSubjects();
            });
        }
        
        // Subject selection
        if(subjectSelect) {
            subjectSelect.addEventListener('change', function() {
                currentSubjectId = parseInt(this.value);
                if(currentSubjectId > 0) {
                    loadTeachers();
                } else {
                    clearTeachersTable();
                }
            });
        }
        
        // Save button
        if(saveBtn) {
            saveBtn.addEventListener('click', saveAssignment);
        }
        
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllTeachers');
        if(selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.teacher-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    if(this.checked) {
                        selectedTeachers.add(parseInt(cb.value));
                    } else {
                        selectedTeachers.delete(parseInt(cb.value));
                    }
                });
                updateSelectedCount();
            });
        }
    }
    
    function loadSubjects() {
        if(!subjectSelect) return;
        
        showLoading(true);
        subjectSelect.innerHTML = '<option value="">Loading subjects...</option>';
        
        fetch(`../../PHP_Files/admin/api/get-subjects.php?faculty=${currentFaculty}&semester=${currentSemester}&class_id=${currentClassId}`)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    showError(data.error);
                    return;
                }
                
                subjectSelect.innerHTML = '<option value="">Select a subject</option>';
                
                data.subjects.forEach(subject => {
                    const option = document.createElement('option');
                    option.value = subject.subject_id;
                    option.textContent = `${subject.subject_code} - ${subject.subject_name} (${subject.credits} credits)`;
                    
                    // Mark as assigned if already assigned to this class
                    if(data.assigned_subjects.includes(subject.subject_id)) {
                        option.dataset.assigned = 'true';
                        option.textContent += ' ✓';
                    }
                    
                    subjectSelect.appendChild(option);
                });
                
                // Add count info
                const countSpan = document.getElementById('subjectCount');
                if(countSpan) {
                    countSpan.textContent = `${data.count} subjects found`;
                }
                
                showLoading(false);
            })
            .catch(error => {
                console.error('Error loading subjects:', error);
                showError('Failed to load subjects');
                showLoading(false);
            });
    }
    
    function loadTeachers() {
        if(!teachersTable) return;
        
        showLoading(true);
        
        fetch(`../../PHP_Files/admin/api/get-teachers.php?subject_id=${currentSubjectId}&class_id=${currentClassId}`)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    showError(data.error);
                    return;
                }
                
                const tbody = teachersTable.querySelector('tbody');
                if(!tbody) return;
                
                tbody.innerHTML = '';
                
                if(data.teachers.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No active teachers found
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                // Clear selection for new subject
                selectedTeachers.clear();
                
                data.teachers.forEach(teacher => {
                    const row = document.createElement('tr');
                    
                    // Check if teacher is already assigned to this subject+class
                    const isAssigned = teacher.is_assigned;
                    const isChecked = isAssigned;
                    
                    if(isChecked) {
                        selectedTeachers.add(teacher.id);
                    }
                    
                    row.innerHTML = `
                        <td>
                            <input type="checkbox" 
                                   class="form-check-input teacher-checkbox"
                                   value="${teacher.id}"
                                   ${isChecked ? 'checked' : ''}
                                   ${isAssigned ? 'disabled' : ''}>
                        </td>
                        <td>${teacher.name}</td>
                        <td>${teacher.email}</td>
                        <td>
                            <span class="badge ${isAssigned ? 'bg-success' : 'bg-secondary'}">
                                ${isAssigned ? 'Already Assigned' : 'Available'}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                ${teacher.current_assignments} current assignments
                            </span>
                        </td>
                    `;
                    
                    tbody.appendChild(row);
                });
                
                // Add checkbox change listeners
                document.querySelectorAll('.teacher-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const teacherId = parseInt(this.value);
                        if(this.checked) {
                            selectedTeachers.add(teacherId);
                        } else {
                            selectedTeachers.delete(teacherId);
                        }
                        updateSelectedCount();
                    });
                });
                
                updateSelectedCount();
                showLoading(false);
            })
            .catch(error => {
                console.error('Error loading teachers:', error);
                showError('Failed to load teachers');
                showLoading(false);
            });
    }
    
    function loadCurrentAssignments() {
        if(!assignmentsList) return;
        
        fetch(`../../PHP_Files/admin/api/get-assignments.php?class_id=${currentClassId}`)
            .then(response => response.json())
            .then(data => {
                if(data.error) {
                    assignmentsList.innerHTML = `
                        <div class="alert alert-warning">
                            Failed to load current assignments
                        </div>
                    `;
                    return;
                }
                
                const container = assignmentsList.querySelector('.list-group') || assignmentsList;
                
                if(data.assignments.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-3">
                            No teachers assigned yet
                        </div>
                    `;
                    return;
                }
                
                let html = '';
                data.assignments.forEach(assignment => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${assignment.subject_code} - ${assignment.subject_name}</strong><br>
                                    <small class="text-muted">
                                        <i class="bi bi-person"></i> ${assignment.teacher_name} 
                                        | Since: ${assignment.start_date}
                                    </small>
                                </div>
                                <button class="btn btn-sm btn-outline-danger remove-assignment-btn" 
                                        data-teacher-id="${assignment.teacher_id}"
                                        data-subject-id="${assignment.subject_id}">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                container.innerHTML = html;
                
                // Add remove button listeners
                document.querySelectorAll('.remove-assignment-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const teacherId = this.dataset.teacherId;
                        const subjectId = this.dataset.subjectId;
                        removeAssignment(teacherId, subjectId);
                    });
                });
                
                // Update class info
                updateClassInfo(data.class_details);
            })
            .catch(error => {
                console.error('Error loading assignments:', error);
            });
    }
    
    function saveAssignment() {
        if(selectedTeachers.size === 0) {
            showError('Please select at least one teacher');
            return;
        }
        
        if(currentSubjectId === 0) {
            showError('Please select a subject first');
            return;
        }
        
        showLoading(true);
        
        // Save each selected teacher
        const promises = Array.from(selectedTeachers).map(teacherId => {
            return fetch('../../PHP_Files/admin/api/save-assignment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    teacher_id: teacherId,
                    subject_id: currentSubjectId,
                    class_id: currentClassId,
                    action: 'assign'
                })
            })
            .then(response => response.json());
        });
        
        Promise.all(promises)
            .then(results => {
                const errors = results.filter(r => r.error);
                const successes = results.filter(r => r.success);
                
                if(errors.length > 0) {
                    showError(`Failed to save some assignments: ${errors.map(e => e.error).join(', ')}`);
                }
                
                if(successes.length > 0) {
                    showSuccess(`${successes.length} teacher(s) assigned successfully!`);
                    
                    // Refresh data
                    loadCurrentAssignments();
                    
                    // Clear selection
                    selectedTeachers.clear();
                    document.querySelectorAll('.teacher-checkbox').forEach(cb => cb.checked = false);
                    updateSelectedCount();
                    
                    // Reload teachers to show updated status
                    loadTeachers();
                }
                
                showLoading(false);
            })
            .catch(error => {
                console.error('Error saving assignments:', error);
                showError('Failed to save assignments');
                showLoading(false);
            });
    }
    
    function removeAssignment(teacherId, subjectId) {
        if(!confirm('Are you sure you want to remove this teacher from this subject?')) {
            return;
        }
        
        showLoading(true);
        
        fetch('../../PHP_Files/admin/api/save-assignment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                teacher_id: teacherId,
                subject_id: subjectId,
                class_id: currentClassId,
                action: 'remove'
            })
        })
        .then(response => response.json())
        .then(data => {
            if(data.error) {
                showError(data.error);
            } else {
                showSuccess(data.message);
                loadCurrentAssignments();
                loadTeachers(); // Refresh teacher list
            }
            showLoading(false);
        })
        .catch(error => {
            console.error('Error removing assignment:', error);
            showError('Failed to remove assignment');
            showLoading(false);
        });
    }
    
    function updateSelectedCount() {
        const countElement = document.getElementById('selectedCount');
        if(countElement) {
            countElement.textContent = selectedTeachers.size;
        }
        
        // Update save button state
        if(saveBtn) {
            saveBtn.disabled = selectedTeachers.size === 0;
        }
    }
    
    function updateClassInfo(classDetails) {
        const infoElement = document.getElementById('classInfo');
        if(infoElement && classDetails) {
            infoElement.innerHTML = `
                <strong>${classDetails.faculty} Semester ${classDetails.semester}</strong><br>
                <small class="text-muted">
                    Batch: ${classDetails.batch_year} | 
                    Students: ${classDetails.student_count || 0}
                </small>
            `;
        }
    }
    
    function clearTeachersTable() {
        const tbody = teachersTable?.querySelector('tbody');
        if(tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        Select a subject to view available teachers
                    </td>
                </tr>
            `;
        }
        selectedTeachers.clear();
        updateSelectedCount();
    }
    
    function showLoading(show) {
        if(loadingSpinner) {
            loadingSpinner.style.display = show ? 'block' : 'none';
        }
        if(saveBtn) {
            saveBtn.disabled = show;
        }
    }
    
    function showError(message) {
        // Create or update error alert
        let alertElement = document.getElementById('errorAlert');
        if(!alertElement) {
            alertElement = document.createElement('div');
            alertElement.id = 'errorAlert';
            alertElement.className = 'alert alert-danger alert-dismissible fade show';
            alertElement.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <span id="errorMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container').prepend(alertElement);
        }
        
        document.getElementById('errorMessage').textContent = message;
        alertElement.style.display = 'block';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            alertElement.style.display = 'none';
        }, 5000);
    }
    
    function showSuccess(message) {
        // Create or update success alert
        let alertElement = document.getElementById('successAlert');
        if(!alertElement) {
            alertElement = document.createElement('div');
            alertElement.id = 'successAlert';
            alertElement.className = 'alert alert-success alert-dismissible fade show';
            alertElement.innerHTML = `
                <i class="bi bi-check-circle-fill me-2"></i>
                <span id="successMessage"></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.container').prepend(alertElement);
        }
        
        document.getElementById('successMessage').textContent = message;
        alertElement.style.display = 'block';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            alertElement.style.display = 'none';
        }, 5000);
    }
});