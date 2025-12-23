<?php
// admin_teachers.php
session_start();
include('../../config.php');
// Add admin authentication check here

// Handle deactivation
if(isset($_GET['deactivate'])) {
    $teacher_id = intval($_GET['deactivate']);
    
    // 1. Set teacher as inactive
    $update_teacher = $connection->prepare("
        UPDATE teacher 
        SET status = 'inactive' 
        WHERE teacher_id = ?
    ");
    $update_teacher->bind_param("i", $teacher_id);
    $update_teacher->execute();
    
    // 2. Remove teacher from ALL classes they teach
    $remove_from_classes = $connection->prepare("
        UPDATE class 
        SET teacher_id = NULL 
        WHERE teacher_id = ?
    ");
    $remove_from_classes->bind_param("i", $teacher_id);
    $remove_from_classes->execute();
    
    // 3. Clear their assigned_class_id
    $clear_assignment = $connection->prepare("
        UPDATE teacher 
        SET assigned_class_id = NULL 
        WHERE teacher_id = ?
    ");
    $clear_assignment->bind_param("i", $teacher_id);
    $clear_assignment->execute();
    
    $message = "Teacher deactivated and removed from all classes!";
}

// Handle reactivation
if(isset($_GET['reactivate'])) {
    $teacher_id = intval($_GET['reactivate']);
    
    $update = $connection->prepare("
        UPDATE teacher 
        SET status = 'active' 
        WHERE teacher_id = ?
    ");
    $update->bind_param("i", $teacher_id);
    
    if($update->execute()) {
        $message = "Teacher reactivated! (Assign classes manually)";
    } else {
        $error = "Error reactivating teacher!";
    }
}

// Handle AJAX update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_teacher') {
    $teacher_id = intval($_POST['teacher_id']);
    $name = $connection->real_escape_string(trim($_POST['name']));
    $email = $connection->real_escape_string(trim($_POST['email']));
    $status = $connection->real_escape_string(trim($_POST['status']));
    
    // Check if email already exists for another teacher
    $check_email = $connection->query("SELECT teacher_id FROM teacher WHERE email = '$email' AND teacher_id != $teacher_id");
    if($check_email->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists!']);
        exit;
    }
    
    $update = $connection->prepare("
        UPDATE teacher 
        SET name = ?, email = ?, status = ?
        WHERE teacher_id = ?
    ");
    $update->bind_param("sssi", $name, $email, $status, $teacher_id);
    
    if($update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Teacher updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed!']);
    }
    exit;
}

// Tabs for active/inactive teachers
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'active';

// Pagination setup
$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .modal-backdrop.fade.show {
            z-index: 1040;
        }
        .modal.fade.show {
            z-index: 1050;
        }
        .teacher-row:hover {
            background-color: #f8f9fa;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
        }
        .action-buttons {
            min-width: 220px;
        }
        .badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .stats-card {
            transition: transform 0.2s;
            height: 100%;
            cursor: default;
        }
        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,.1);
        }
        .tab-content {
            min-height: 400px;
        }
        .pagination .page-item.active .page-link {
            background-color: #ff6b35;
            border-color: #ff6b35;
        }
        .pagination .page-link {
            color: #ff6b35;
        }
        .pagination .page-link:hover {
            color: #e55a2b;
        }
        .btn-action-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .no-data {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .action-dropdown {
            min-width: 200px;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Teacher Management</h2>
        <div>
            <a href="admin_add_teacher.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New Teacher
            </a>
            <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
        </div>
    </div>
    
    <?php if(isset($message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Quick Stats - Smaller and more compact -->
    <div class="row mb-3">
        <?php
        // Get statistics
        $stats = [
            'total' => $connection->query("SELECT COUNT(*) as count FROM teacher")->fetch_assoc()['count'],
            'active' => $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status='active'")->fetch_assoc()['count'],
            'inactive' => $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status='inactive'")->fetch_assoc()['count'],
            'unassigned' => $connection->query("SELECT COUNT(*) as count FROM teacher WHERE assigned_class_id IS NULL AND status='active'")->fetch_assoc()['count'],
            'with_classes' => $connection->query("SELECT COUNT(DISTINCT teacher_id) as count FROM class WHERE teacher_id IS NOT NULL")->fetch_assoc()['count']
        ];
        
        // Calculate percentage of teachers with classes
        $with_classes_percent = $stats['active'] > 0 ? round(($stats['with_classes'] / $stats['active']) * 100) : 0;
        ?>
        
        <!-- Total Teachers -->
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-primary text-white stats-card py-2">
                <div class="card-body text-center p-2">
                    <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                    <small class="opacity-75">Total</small>
                    <div class="mt-1">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Teachers -->
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-success text-white stats-card py-2">
                <div class="card-body text-center p-2">
                    <h4 class="mb-0"><?php echo $stats['active']; ?></h4>
                    <small class="opacity-75">Active</small>
                    <div class="mt-1">
                        <i class="bi bi-person-check"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inactive Teachers -->
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-secondary text-white stats-card py-2">
                <div class="card-body text-center p-2">
                    <h4 class="mb-0"><?php echo $stats['inactive']; ?></h4>
                    <small class="opacity-75">Inactive</small>
                    <div class="mt-1">
                        <i class="bi bi-person-x"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Unassigned Teachers -->
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-warning text-dark stats-card py-2">
                <div class="card-body text-center p-2">
                    <h4 class="mb-0"><?php echo $stats['unassigned']; ?></h4>
                    <small class="opacity-75">Unassigned</small>
                    <div class="mt-1">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Teachers with Classes -->
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-info text-white stats-card py-2">
                <div class="card-body text-center p-2">
                    <h4 class="mb-0"><?php echo $stats['with_classes']; ?></h4>
                    <small class="opacity-75">Teaching</small>
                    <div class="mt-1">
                        <i class="bi bi-book"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Percentage Card -->
        <div class="col-md-2 col-6 mb-2">
            <div class="card bg-dark text-white stats-card py-2">
                <div class="card-body text-center p-2">
                    <h4 class="mb-0"><?php echo $with_classes_percent; ?>%</h4>
                    <small class="opacity-75">Assigned</small>
                    <div class="mt-1">
                        <i class="bi bi-percent"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tabs - Fixed with proper URLs -->
    <ul class="nav nav-tabs mb-3" id="teacherTabs">
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'active') ? 'active' : ''; ?>" 
               href="admin_teachers.php?tab=active" data-tab="active">
               <i class="bi bi-person-check"></i> Active Teachers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'inactive') ? 'active' : ''; ?>" 
               href="admin_teachers.php?tab=inactive" data-tab="inactive">
               <i class="bi bi-person-x"></i> Inactive Teachers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'unassigned') ? 'active' : ''; ?>" 
               href="admin_teachers.php?tab=unassigned" data-tab="unassigned">
               <i class="bi bi-exclamation-triangle"></i> Unassigned Teachers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'all') ? 'active' : ''; ?>" 
               href="admin_teachers.php?tab=all" data-tab="all">
               <i class="bi bi-people-fill"></i> All Teachers
            </a>
        </li>
    </ul>
    
    <!-- Teachers Table -->
    <div class="card shadow">
        <div class="card-body">
            <?php
            // Build query based on tab
            $where = "";
            $title = "";
            $count_where = "";
            
            switch($active_tab) {
                case 'active':
                    $where = "WHERE t.status = 'active'";
                    $count_where = "WHERE status = 'active'";
                    $title = "Active Teachers";
                    break;
                case 'inactive':
                    $where = "WHERE t.status = 'inactive'";
                    $count_where = "WHERE status = 'inactive'";
                    $title = "Inactive Teachers";
                    break;
                case 'unassigned':
                    $where = "WHERE t.assigned_class_id IS NULL AND t.status = 'active'";
                    $count_where = "WHERE assigned_class_id IS NULL AND status = 'active'";
                    $title = "Active Teachers Without Class Assignment";
                    break;
                case 'all':
                    $where = "";
                    $count_where = "";
                    $title = "All Teachers";
                    break;
                default:
                    $where = "WHERE t.status = 'active'";
                    $count_where = "WHERE status = 'active'";
                    $title = "Active Teachers";
            }
            
            // Get total count for pagination
            $total_count_query = $connection->query("SELECT COUNT(*) as total FROM teacher $count_where");
            $total_count = $total_count_query->fetch_assoc()['total'];
            $total_pages = ceil($total_count / $limit);
            
            // Main query with pagination
            $query = $connection->query("
                SELECT 
                    t.*,
                    COALESCE(GROUP_CONCAT(DISTINCT CONCAT(c.faculty, ' (Sem ', c.semester, ')') ORDER BY c.faculty, c.semester SEPARATOR ', '), 'No classes assigned') as teaching_classes,
                    COUNT(DISTINCT c.class_id) as class_count
                FROM teacher t
                LEFT JOIN class c ON t.teacher_id = c.teacher_id
                $where
                GROUP BY t.teacher_id
                ORDER BY 
                    t.status DESC,
                    t.created_at DESC
                LIMIT $limit OFFSET $offset
            ");
            ?>
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title mb-0">
                    <?php echo $title; ?>
                    <span class="badge bg-secondary"><?php echo $total_count; ?> teachers</span>
                </h5>
                
                <!-- Records per page selector -->
                <div class="d-flex align-items-center">
                    <small class="text-muted me-2">Showing <?php echo min($limit, $query->num_rows); ?> of <?php echo $total_count; ?> records</small>
                    <select class="form-select form-select-sm w-auto" id="perPage" onchange="changePerPage(this.value)">
                        <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 per page</option>
                        <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25 per page</option>
                        <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 per page</option>
                        <option value="100" <?php echo $limit == 100 ? 'selected' : ''; ?>>100 per page</option>
                    </select>
                </div>
            </div>
            
            <?php if($query->num_rows == 0): ?>
                <div class="no-data text-center py-5">
                    <div>
                        <i class="bi bi-people display-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No teachers found</h4>
                        <p class="text-muted">Try a different filter or add new teachers.</p>
                        <a href="admin_add_teacher.php" class="btn btn-primary mt-2">
                            <i class="bi bi-plus-circle"></i> Add First Teacher
                        </a>
                    </div>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Classes</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($teacher = $query->fetch_assoc()): ?>
                        <tr class="teacher-row <?php echo ($teacher['status'] == 'inactive') ? 'table-secondary' : ''; ?>"
                            data-teacher-id="<?php echo $teacher['teacher_id']; ?>"
                            data-teacher-name="<?php echo htmlspecialchars($teacher['name']); ?>"
                            data-teacher-email="<?php echo htmlspecialchars($teacher['email']); ?>"
                            data-teacher-status="<?php echo $teacher['status']; ?>">
                            <td class="fw-bold">#<?php echo $teacher['teacher_id']; ?></td>
                            <td>
                                <div class="d-flex flex-column">
                                    <strong><?php echo htmlspecialchars($teacher['name']); ?></strong>
                                    <?php if($teacher['assigned_class_id'] === NULL && $teacher['status'] == 'active'): ?>
                                        <small class="text-warning"><i class="bi bi-exclamation-circle"></i> No primary class</small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <small><?php echo htmlspecialchars($teacher['email']); ?></small>
                            </td>
                            <td>
                                <?php if($teacher['class_count'] > 0): ?>
                                    <span class="badge bg-info text-dark">
                                        <?php echo $teacher['class_count']; ?> class<?php echo $teacher['class_count'] > 1 ? 'es' : ''; ?>
                                    </span>
                                    <small class="text-muted d-block mt-1"><?php echo $teacher['teaching_classes']; ?></small>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($teacher['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                    <span class="badge-dot"></span>
                                    <?php echo ucfirst($teacher['status']); ?>
                                </span>
                            </td>
                            <td>
                                <small><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm action-buttons">
                                    <button type="button" class="btn btn-outline-primary edit-teacher-btn" 
                                            data-bs-toggle="modal" data-bs-target="#editTeacherModal"
                                            title="Edit Teacher">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    
                                    <?php if($teacher['status'] == 'active'): ?>
                                        <a href="?deactivate=<?php echo $teacher['teacher_id']; ?>&tab=<?php echo $active_tab; ?>&page=<?php echo $page; ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirm('Deactivate this teacher?\\nThey will be removed from all classes.')" 
                                           title="Deactivate">
                                            <i class="bi bi-person-x"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?reactivate=<?php echo $teacher['teacher_id']; ?>&tab=<?php echo $active_tab; ?>&page=<?php echo $page; ?>" 
                                           class="btn btn-outline-success" 
                                           onclick="return confirm('Reactivate this teacher?')" 
                                           title="Reactivate">
                                            <i class="bi bi-person-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-outline-info dropdown-toggle" 
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-gear"></i>
                                    </button>
                                    <ul class="dropdown-menu action-dropdown">
                                        <li>
                                            <a class="dropdown-item" href="admin_assign_subjects.php?teacher_id=<?php echo $teacher['teacher_id']; ?>">
                                                <i class="bi bi-book"></i> Manage Subjects
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="admin_assign_classes.php?teacher_id=<?php echo $teacher['teacher_id']; ?>">
                                                <i class="bi bi-building"></i> Assign Classes
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-warning" href="#"
                                               onclick="return confirm('Reset password for <?php echo htmlspecialchars($teacher['name']); ?>?')">
                                                <i class="bi bi-key"></i> Reset Password
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item text-info" href="teacher_profile.php?id=<?php echo $teacher['teacher_id']; ?>">
                                                <i class="bi bi-eye"></i> View Profile
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous button -->
                    <li class="page-item <?php echo $page == 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page numbers -->
                    <?php 
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if($start_page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=1">1</a></li>
                        <?php if($start_page > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if($end_page < $total_pages): ?>
                        <?php if($end_page < $total_pages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
                    <?php endif; ?>
                    
                    <!-- Next button -->
                    <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editTeacherForm">
                <div class="modal-body">
                    <input type="hidden" id="edit_teacher_id" name="teacher_id">
                    <input type="hidden" name="action" value="update_teacher">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-info-circle"></i> Password reset requires separate action.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="saveSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle edit button click
    $('.edit-teacher-btn').on('click', function() {
        const row = $(this).closest('tr');
        const teacherId = row.data('teacher-id');
        const teacherName = row.data('teacher-name');
        const teacherEmail = row.data('teacher-email');
        const teacherStatus = row.data('teacher-status');
        
        // Populate modal fields
        $('#edit_teacher_id').val(teacherId);
        $('#edit_name').val(teacherName);
        $('#edit_email').val(teacherEmail);
        $('#edit_status').val(teacherStatus);
    });
    
    // Handle form submission via AJAX
    $('#editTeacherForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        const saveBtn = $(this).find('button[type="submit"]');
        const spinner = $('#saveSpinner');
        
        // Show loading state
        saveBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: 'admin_teachers.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Show success message
                    showAlert('success', response.message);
                    
                    // Close modal after a short delay
                    setTimeout(() => {
                        $('#editTeacherModal').modal('hide');
                        // Reload the page to show updated data
                        window.location.reload();
                    }, 1000);
                } else {
                    showAlert('danger', response.message);
                    saveBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            },
            error: function() {
                showAlert('danger', 'An error occurred while updating. Please try again.');
                saveBtn.prop('disabled', false);
                spinner.addClass('d-none');
            }
        });
    });
    
    // Function to show alert messages
    function showAlert(type, message) {
        // Remove existing alerts
        $('.alert-dismissible').remove();
        
        // Create new alert
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="bi bi-${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert alert at the top of the container
        $('.container-fluid').prepend(alertHtml);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            $('.alert-dismissible').alert('close');
        }, 5000);
    }
    
    // Initialize tooltips
    $('[title]').tooltip({
        trigger: 'hover'
    });
    
    // Confirm before deactivating/reactivating
    $('a[href*="deactivate="], a[href*="reactivate="]').on('click', function(e) {
        const confirmMsg = $(this).attr('onclick')?.match(/confirm\('([^']+)'/)?.[1] || 'Are you sure?';
        if(!confirm(confirmMsg)) {
            e.preventDefault();
        }
    });
});

// Change records per page
function changePerPage(perPage) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('limit', perPage);
    urlParams.set('page', 1); // Reset to first page
    window.location.href = '?' + urlParams.toString();
}
</script>
</body>
</html>