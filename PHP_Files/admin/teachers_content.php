<?php
// teachers_content.php - LOADS INSIDE admin_main_page.php
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in'])) {
    die("Access denied!");
}

// Handle status toggle
if(isset($_GET['toggle_status']) && isset($_GET['teacher_id'])) {
    $teacher_id = intval($_GET['teacher_id']);
    
    $result = $connection->query("SELECT status FROM teacher WHERE teacher_id = $teacher_id");
    if($result->num_rows > 0) {
        $teacher = $result->fetch_assoc();
        $new_status = $teacher['status'] == 'active' ? 'inactive' : 'active';
        
        $connection->query("UPDATE teacher SET status = '$new_status' WHERE teacher_id = $teacher_id");
        
        echo "<script>
            alert('Teacher status updated successfully!');
            window.location.href = 'teachers_content.php';
        </script>";
    }
}


// Pagination
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;

$offset = ($page - 1) * $records_per_page;

// Get counts
$total_teachers = $connection->query("SELECT COUNT(*) as total FROM teacher")->fetch_assoc()['total'];
$total_pages = ceil($total_teachers / $records_per_page);

if($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $records_per_page;
}

$active_teachers = $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status = 'active'")->fetch_assoc()['count'];
$inactive_teachers = $total_teachers - $active_teachers;

// Get teachers
$teachers = $connection->query("
    SELECT t.*, 
           (SELECT COUNT(*) FROM teacher_class_assignments WHERE teacher_id = t.teacher_id) as class_count
    FROM teacher t
    ORDER BY t.status DESC, t.name
    LIMIT $offset, $records_per_page
");
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Teacher Management</h4>
            <p class="text-muted mb-0">Manage all faculty members</p>
        </div>
        <a href="#" onclick="loadAddTeacher(); return false;" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add New Teacher
        </a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-left-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-people text-primary"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Teachers</div>
                            <div class="fw-bold h5"><?php echo $total_teachers; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-left-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-toggle-on text-success"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Active</div>
                            <div class="fw-bold h5"><?php echo $active_teachers; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-left-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-person-x text-warning"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Inactive</div>
                            <div class="fw-bold h5"><?php echo $inactive_teachers; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card stats-card border-left-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-2 rounded me-3">
                            <i class="bi bi-file-text text-info"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Page <?php echo $page; ?> of <?php echo $total_pages; ?></div>
                            <div class="fw-bold h5"><?php echo $teachers->num_rows; ?> teachers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Teachers Table -->
    <div class="card">
        <div class="card-header bg-white">
            <h6 class="mb-0">All Teachers</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="25%">Teacher</th>
                            <th width="25%">Email</th>
                            <th width="15%">Classes</th>
                            <th width="15%">Status</th>
                            <th width="20%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($teachers->num_rows > 0): 
                            while($teacher = $teachers->fetch_assoc()): 
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="teacher-avatar me-3">
                                        <?php echo strtoupper(substr($teacher['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                        <div class="small text-muted">ID: #<?php echo $teacher['teacher_id']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <i class="bi bi-envelope text-muted me-1"></i>
                                    <span class="small"><?php echo htmlspecialchars($teacher['email']); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php if($teacher['class_count'] > 0): ?>
                                    <span class="badge bg-success"><?php echo $teacher['class_count']; ?> classes</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($teacher['status'] == 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <!-- Edit Button -->
                                    <a href="#" onclick="loadEditTeacher(<?php echo $teacher['teacher_id']; ?>); return false;" 
                                       class="btn btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <!-- Status Toggle -->
                                    <?php if($teacher['status'] == 'active'): ?>
                                        <button onclick="toggleTeacherStatus(<?php echo $teacher['teacher_id']; ?>, 'deactivate', this)" 
                                            class="btn btn-outline-danger" title="Deactivate">
                                        </button>
                                    <?php else: ?>
                                        <button onclick="toggleTeacherStatus(<?php echo $teacher['teacher_id']; ?>, 'activate')" 
                                                class="btn btn-outline-success" title="Activate">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-people display-1 text-muted mb-3"></i>
                                <h5 class="text-muted">No Teachers Found</h5>
                                <p class="text-muted">Start by adding your first teacher</p>
                                <a href="#" onclick="loadAddTeacher(); return false;" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i> Add First Teacher
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <div class="card-footer bg-white">
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <!-- Previous Button -->
                        <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="teachers_content.php?page=<?php echo $page-1; ?>">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="bi bi-chevron-left"></i> Previous</span>
                        </li>
                        <?php endif; ?>
                        
                        <!-- Page Numbers -->
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="teachers_content.php?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Button -->
                        <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="teachers_content.php?page=<?php echo $page+1; ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="text-center text-muted small mt-2">
                        Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $records_per_page, $total_teachers); ?> 
                        of <?php echo $total_teachers; ?> teachers
                    </div>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>