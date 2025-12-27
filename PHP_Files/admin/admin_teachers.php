<?php
// admin_teachers.php
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

// Get total stats
$total_teachers = $connection->query("SELECT COUNT(*) as count FROM teacher")->fetch_assoc()['count'];
$active_teachers = $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status='active'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stats-card { border-radius: 10px; }
        .action-btn { width: 35px; height: 35px; padding: 0; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_main_page.php">
                <i class="bi bi-arrow-left"></i> Teacher Management
            </a>
            <div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                    <i class="bi bi-person-plus"></i> Add Teacher
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white stats-card">
                    <div class="card-body">
                        <h6>TOTAL TEACHERS</h6>
                        <h2><?php echo $total_teachers; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white stats-card">
                    <div class="card-body">
                        <h6>ACTIVE TEACHERS</h6>
                        <h2><?php echo $active_teachers; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white stats-card">
                    <div class="card-body">
                        <h6>AVAILABLE FOR ASSIGNMENT</h6>
                        <h2><?php echo $active_teachers; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teacher List -->
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> All Teachers</h5>
            </div>
            <div class="card-body p-0">
                <?php
                $teachers = $connection->query("
                    SELECT t.*, 
                           (SELECT COUNT(*) FROM teacher_class_assignments WHERE teacher_id = t.teacher_id) as class_count
                    FROM teacher t
                    ORDER BY t.name
                ");
                
                if($teachers->num_rows > 0):
                ?>
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Classes</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($teacher = $teachers->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="bi bi-person-circle fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1"><?php echo $teacher['name']; ?></h6>
                                        <small class="text-muted">ID: #<?php echo $teacher['teacher_id']; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo $teacher['email']; ?></td>
                            <td><?php echo $teacher['phone'] ?? 'N/A'; ?></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo $teacher['class_count']; ?> classes</span>
                            </td>
                            <td>
                                <?php if($teacher['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm action-btn" 
                                            onclick="editTeacher(<?php echo $teacher['teacher_id']; ?>)"
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <?php if($teacher['status'] == 'active'): ?>
                                        <button class="btn btn-outline-danger btn-sm action-btn"
                                                onclick="toggleTeacherStatus(<?php echo $teacher['teacher_id']; ?>, 'deactivate')"
                                                title="Deactivate">
                                            <i class="bi bi-person-x"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-outline-success btn-sm action-btn"
                                                onclick="toggleTeacherStatus(<?php echo $teacher['teacher_id']; ?>, 'activate')"
                                                title="Activate">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-people display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No Teachers Found</h4>
                    <p class="text-muted">Add your first teacher to get started</p>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                        <i class="bi bi-person-plus"></i> Add First Teacher
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Teacher Modal -->
    <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus"></i> Add New Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTeacherForm">
    <div class="modal-body">
        <div id="formMessage"></div>
        
        <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" class="form-control" name="name" required>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Department/Subject Specialization</label>
            <input type="text" class="form-control" name="department" placeholder="E.g.: Computer Science, Mathematics">
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Password *</label>
                <input type="text" class="form-control" name="password" value="teacher123" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password *</label>
                <input type="text" class="form-control" name="confirm_password" value="teacher123" required>
            </div>
        </div>
        
        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle"></i> Note:</h6>
            <ul class="mb-0 small">
                <li>Teacher will be created as "Active"</li>
                <li>Teacher can login with email and password</li>
                <li>Assign teachers to classes separately</li>
            </ul>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">Add Teacher</button>
    </div>
</form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Add Teacher
    document.getElementById('addTeacherForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('add_teacher', 'true');
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';
        submitBtn.disabled = true;
        
        fetch('admin_add_teacher.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('formMessage').innerHTML = `
                    <div class="alert alert-success">
                        ${data.message}
                    </div>
                `;
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                document.getElementById('formMessage').innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message}
                    </div>
                `;
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            document.getElementById('formMessage').innerHTML = `
                <div class="alert alert-danger">
                    Network error occurred
                </div>
            `;
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Edit Teacher (placeholder)
    function editTeacher(teacherId) {
        alert('Edit teacher #' + teacherId + ' - Feature coming soon');
    }
    
    // Toggle Teacher Status
    function toggleTeacherStatus(teacherId, action) {
        if(!confirm(`Are you sure you want to ${action} this teacher?`)) {
            return;
        }
        
        fetch('toggle_teacher_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `teacher_id=${teacherId}&action=${action}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Network error');
        });
    }
    </script>
</body>
</html>