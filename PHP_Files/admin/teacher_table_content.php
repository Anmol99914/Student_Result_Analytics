<?php
// teacher_table_content.php - Included by admin_teachers.php
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="card-title mb-0">
        <?php 
        $title = '';
        switch($active_tab) {
            case 'active': $title = 'Active Teachers'; break;
            case 'inactive': $title = 'Inactive Teachers'; break;
            case 'unassigned': $title = 'Unassigned Teachers'; break;
            case 'all': $title = 'All Teachers'; break;
        }
        echo $title;
        ?>
        <span class="badge bg-secondary ms-2"><?php echo $total_count; ?> teachers</span>
    </h5>
    
    <!-- Records per page selector -->
    <div class="d-flex align-items-center">
        <small class="text-muted me-2">
            Showing <?php echo min($limit, $query->num_rows); ?> of <?php echo $total_count; ?> records
        </small>
        <select class="form-select form-select-sm w-auto" id="perPage" onchange="currentLimit = parseInt(this.value); currentPage = 1; loadTeacherTable();">
            <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5 per page</option>
            <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10 per page</option>
            <option value="20" <?php echo $limit == 20 ? 'selected' : ''; ?>>20 per page</option>
            <option value="50" <?php echo $limit == 50 ? 'selected' : ''; ?>>50 per page</option>
        </select>
    </div>
</div>

<?php if($query->num_rows == 0): ?>
<div class="no-data-placeholder text-center py-5">
    <i class="bi bi-people display-1 text-muted mb-3"></i>
    <h4 class="text-muted">No teachers found</h4>
    <p class="text-muted">Try a different filter or add new teachers.</p>
    <a href="admin_add_teacher.php" class="btn btn-primary mt-2">
        <i class="bi bi-plus-circle"></i> Add Teacher
    </a>
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
                        <?php echo ucfirst($teacher['status']); ?>
                    </span>
                </td>
                <td>
                    <small><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm action-buttons">
                        <button type="button" class="btn btn-outline-primary edit-teacher-btn" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        
                        <?php if($teacher['status'] == 'active'): ?>
                            <button type="button" class="btn btn-outline-danger deactivate-btn"
                                    data-teacher-id="<?php echo $teacher['teacher_id']; ?>"
                                    data-teacher-name="<?php echo htmlspecialchars($teacher['name']); ?>"
                                    title="Deactivate">
                                <i class="bi bi-person-x"></i>
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-outline-success reactivate-btn"
                                    data-teacher-id="<?php echo $teacher['teacher_id']; ?>"
                                    data-teacher-name="<?php echo htmlspecialchars($teacher['name']); ?>"
                                    title="Reactivate">
                                <i class="bi bi-person-check"></i>
                            </button>
                        <?php endif; ?>
                        
                        <div class="dropdown">
                            <button class="btn btn-outline-info dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown">
                                <i class="bi bi-gear"></i>
                            </button>
                            <ul class="dropdown-menu">
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
                            </ul>
                        </div>
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
            <a class="page-link" href="#" data-page="<?php echo $page - 1; ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <!-- Page numbers -->
        <?php 
        $start_page = max(1, $page - 2);
        $end_page = min($total_pages, $page + 2);
        
        if($start_page > 1): ?>
            <li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>
            <?php if($start_page > 2): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
        <?php endif; ?>
        
        <?php for($i = $start_page; $i <= $end_page; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="#" data-page="<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if($end_page < $total_pages): ?>
            <?php if($end_page < $total_pages - 1): ?>
                <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item"><a class="page-link" href="#" data-page="<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a></li>
        <?php endif; ?>
        
        <!-- Next button -->
        <li class="page-item <?php echo $page == $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="#" data-page="<?php echo $page + 1; ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>