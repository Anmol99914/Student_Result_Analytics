<?php
// admin_teachers_table.php - Contains ONLY the teacher table content
// session_start();
include('../../config.php');
require_once 'admin_session.php'; 
checkAdminLogin(); // Check admin login

// Add admin authentication check here
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    echo '<div class="alert alert-danger">Session expired. Please login again.</div>';
    exit;
}

// Tabs for active/inactive teachers
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'active';

// Pagination setup
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;
?>

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
                // FIX: Check if assigned_class_id is NULL or 0 or empty
                $where = "WHERE (t.assigned_class_id IS NULL OR t.assigned_class_id = 0 OR t.assigned_class_id = '') AND t.status = 'active'";
                $count_where = "WHERE (assigned_class_id IS NULL OR assigned_class_id = 0 OR assigned_class_id = '') AND status = 'active'";
                $title = "Unassigned Teachers";
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
        
        // CORRECTED MAIN QUERY: Using consistent field names
        $query = $connection->query("
            SELECT
                t.*,
                c.faculty,
                c.semester,
                COALESCE(GROUP_CONCAT(DISTINCT CONCAT(c2.faculty, ' (Sem ', c2.semester, ')') ORDER BY c2.faculty, c2.semester SEPARATOR ', '), 'No classes assigned') as teaching_classes,
                COUNT(DISTINCT c2.class_id) as class_count
            FROM teacher t
            LEFT JOIN class c ON t.assigned_class_id = c.class_id
            LEFT JOIN class c2 ON t.teacher_id = c2.teacher_id
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
                <select class="form-select form-select-sm w-auto" id="perPage">
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
                    <h4 class="text-muted">No <?php echo $title; ?> found</h4>
                    <p class="text-muted">Try a different filter or use the "Add New Teacher" button above.</p>
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
                                <?php if(empty($teacher['assigned_class_id']) && $teacher['status'] == 'active'): ?>
                                    <small class="text-warning"><i class="bi bi-exclamation-circle"></i> No primary class</small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <small><?php echo htmlspecialchars($teacher['email']); ?></small>
                        </td>
                        <td>
                            <!-- FIXED: Using the correct field names from the query -->
                            <?php if($teacher['assigned_class_id'] && $teacher['faculty']): ?>
                                <span class="badge bg-info text-dark">
                                    <?php echo $teacher['faculty'] . " - Sem " . $teacher['semester']; ?>
                                </span>
                                <?php if($teacher['class_count'] > 0): ?>
                                    <small class="text-muted d-block mt-1">+<?php echo $teacher['class_count']; ?> more classes</small>
                                <?php endif; ?>
                            <?php elseif($teacher['class_count'] > 0): ?>
                                <span class="badge bg-success">
                                    <?php echo $teacher['class_count']; ?> class<?php echo $teacher['class_count'] > 1 ? 'es' : ''; ?>
                                </span>
                                <small class="text-muted d-block mt-1"><?php echo $teacher['teaching_classes']; ?></small>
                            <?php else: ?>
                                <span class="badge bg-warning">Not Assigned</span>
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
                                    data-teacher-id="<?php echo $teacher['teacher_id']; ?>"
                                    data-teacher-name="<?php echo htmlspecialchars($teacher['name']); ?>"
                                    data-teacher-email="<?php echo htmlspecialchars($teacher['email']); ?>"
                                    data-teacher-status="<?php echo $teacher['status']; ?>"
                                    title="Edit Teacher">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                                 
                            
                            <?php if($teacher['status'] == 'active'): ?>
                                <a href="?deactivate=<?php echo $teacher['teacher_id']; ?>&tab=<?php echo $active_tab; ?>&page=<?php echo $page; ?>" 
                                class="btn btn-outline-danger deactivate-btn" 
                                title="Deactivate">
                                    <i class="bi bi-person-x"></i>
                                </a>
                            <?php else: ?>
                                <a href="?reactivate=<?php echo $teacher['teacher_id']; ?>&tab=<?php echo $active_tab; ?>&page=<?php echo $page; ?>" 
                                class="btn btn-outline-success reactivate-btn" 
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
                    <a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                
                <!-- Page numbers -->
                <?php 
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if($start_page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=1&limit=<?php echo $limit; ?>">1</a></li>
                    <?php if($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if($end_page < $total_pages): ?>
                    <?php if($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>"><?php echo $total_pages; ?></a></li>
                <?php endif; ?>
                
                <!-- Next button -->
                <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?tab=<?php echo $active_tab; ?>&page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        
        <?php endif; ?>
        
    </div>
</div>