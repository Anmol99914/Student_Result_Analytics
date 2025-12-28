<?php
// edit_teacher_content.php - WITH FIXED AJAX
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in'])) {
    die("Access denied!");
}

$teacher_id = intval($_GET['teacher_id'] ?? 0);
if($teacher_id == 0) {
    die("Invalid teacher ID!");
}

// Get teacher data
$teacher = $connection->query("SELECT * FROM teacher WHERE teacher_id = $teacher_id")->fetch_assoc();
if(!$teacher) {
    die("Teacher not found!");
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Edit Teacher</h4>
            <p class="text-muted mb-0">Update teacher information</p>
        </div>
        <a href="#" onclick="loadTeacherManagement(); return false;" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Teachers
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil me-2"></i> Edit Teacher: <?php echo htmlspecialchars($teacher['name']); ?></h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Teacher ID:</strong> #<?php echo $teacher['teacher_id']; ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Created:</strong> <?php echo date('M d, Y', strtotime($teacher['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                    
                    <form id="editTeacherForm" onsubmit="return saveTeacherChanges()">
    <input type="hidden" name="teacher_id" value="<?php echo $teacher['teacher_id']; ?>">
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" name="name" 
                   value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Email *</label>
            <input type="email" class="form-control" name="email" 
                   value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
                <option value="active" <?php echo $teacher['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $teacher['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        
        <div class="col-md-6 mb-3">
            <label class="form-label">Change Password (Optional)</label>
            <input type="text" class="form-control" name="new_password" 
                   placeholder="Leave blank to keep current password">
            <small class="text-muted">Minimum 6 characters if changing</small>
        </div>
    </div>
    
    <div class="d-flex justify-content-between mt-4">
        <a href="#" onclick="loadTeacherManagement(); return false;" class="btn btn-secondary">
            <i class="bi bi-x-circle me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="bi bi-check-circle me-1"></i> Save Changes
        </button>
    </div>
</form>

<div id="editMessage" class="mt-3"></div>
                    
                    <div class="alert alert-warning mt-4">
                        <h6><i class="bi bi-exclamation-triangle"></i> Important Notes:</h6>
                        <ul class="mb-0">
                            <li>Changing email will update login username</li>
                            <li>Password changes affect both teacher and login credentials</li>
                            <li>Status changes affect login access</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editTeacherForm');
    const saveBtn = document.getElementById('saveBtn');
    const messageDiv = document.getElementById('editMessage');
    
    if(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop traditional submission
            
            console.log('Saving teacher changes...');
            
            // Show loading
            const originalBtnText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            saveBtn.disabled = true;
            
            // Clear previous messages
            messageDiv.innerHTML = '';
            
            // Get form data
            const formData = new FormData(this);
            
            // DEBUG: Log what we're sending
            console.log('Sending data to update_teacher.php');
            for(let [key, value] of formData.entries()) {
                console.log(key + ': ' + value);
            }
            
            // Send AJAX request to update_teacher.php
            fetch('update_teacher.php', {  // ← FIXED: Specific file
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Update response:', data);
                
                if(data.success) {
                    // Show success message
                    messageDiv.innerHTML = `
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle me-2"></i> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    
                    // SUCCESS: Go back to teacher list after 2 seconds
                    setTimeout(() => {
                        console.log('Returning to teacher management...');
                        if(typeof loadTeacherManagement === 'function') {
                            loadTeacherManagement();
                        } else {
                            // Fallback
                            window.location.href = 'teachers_content.php';
                        }
                    }, 2000);
                    
                } else {
                    // Show error message
                    messageDiv.innerHTML = `
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-x-circle me-2"></i> ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    
                    // Restore button
                    saveBtn.innerHTML = originalBtnText;
                    saveBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                
                messageDiv.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-wifi-off me-2"></i> Network error: ${error.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                // Restore button
                saveBtn.innerHTML = originalBtnText;
                saveBtn.disabled = false;
            });
        });
    }
});

function handleEditSubmit(event) {
    event.preventDefault();
    console.log('Form submit intercepted!');
    
    const form = event.target;
    const formData = new FormData(form);
    const saveBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = saveBtn.innerHTML;
    
    // Show loading
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    saveBtn.disabled = true;
    
    // Send AJAX request
    fetch('update_teacher.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
        
        if(data.success) {
            // Show success message
            showAlert('success', data.message);
            // Return to teacher list after 1.5 seconds
            setTimeout(() => loadTeacherManagement(), 1500);
        } else {
            showAlert('danger', data.message);
            saveBtn.innerHTML = originalBtnText;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'Network error: ' + error.message);
        saveBtn.innerHTML = originalBtnText;
        saveBtn.disabled = false;
    });
    
    return false;
}

function saveTeacherChanges() {
    console.log('saveTeacherChanges function called');
    
    // Get the form
    const form = document.getElementById('editTeacherForm');
    if(!form) {
        console.error('Form not found with ID editTeacherForm');
        showAlert('danger', 'Form not found');
        return;
    }
    
    // Get form data
    const teacherId = form.querySelector('[name="teacher_id"]')?.value;
    const name = form.querySelector('[name="name"]')?.value;
    const email = form.querySelector('[name="email"]')?.value;
    const status = form.querySelector('[name="status"]')?.value;
    const newPassword = form.querySelector('[name="new_password"]')?.value;
    
    // Validate
    if(!name || !email) {
        showAlert('danger', 'Name and email are required');
        return;
    }
    
    console.log('Form data:', { teacherId, name, email, status, newPassword });
    
    // Get save button
    const saveBtn = form.querySelector('button[type="submit"]');
    const messageDiv = document.getElementById('editMessage');
    
    // Show loading
    const originalBtnText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
    saveBtn.disabled = true;
    
    // Clear previous messages
    if(messageDiv) {
        messageDiv.innerHTML = '';
    }
    
    // Prepare form data
    const formData = new FormData();
    formData.append('teacher_id', teacherId);
    formData.append('name', name);
    formData.append('email', email);
    formData.append('status', status);
    if(newPassword) {
        formData.append('new_password', newPassword);
    }
    
    // Send AJAX request to update_teacher.php
    fetch('update_teacher.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Update response:', data);
        
        if(data.success) {
            // Show success message
            const successMsg = `
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            if(messageDiv) {
                messageDiv.innerHTML = successMsg;
            } else {
                showAlert('success', data.message);
            }
            
            // Return to teacher list after 1.5 seconds
            setTimeout(() => {
                if(typeof loadTeacherManagement === 'function') {
                    loadTeacherManagement();
                } else {
                    console.warn('loadTeacherManagement not found, reloading page');
                    window.location.reload();
                }
            }, 1500);
            
        } else {
            // Show error message
            const errorMsg = `
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-x-circle me-2"></i> ${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            if(messageDiv) {
                messageDiv.innerHTML = errorMsg;
            } else {
                showAlert('danger', data.message);
            }
            
            // Restore button
            saveBtn.innerHTML = originalBtnText;
            saveBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        const errorMsg = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-wifi-off me-2"></i> Network error: ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        if(messageDiv) {
            messageDiv.innerHTML = errorMsg;
        } else {
            showAlert('danger', 'Network error: ' + error.message);
        }
        
        // Restore button
        saveBtn.innerHTML = originalBtnText;
        saveBtn.disabled = false;
    });
    
    // Prevent default form submission
    return false;
}
</script>