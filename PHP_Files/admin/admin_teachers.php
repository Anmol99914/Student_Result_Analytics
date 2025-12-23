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

// Tabs for active/inactive teachers
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'active';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Teacher Management</h2>
        <a href="admin_add_teacher.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Teacher
        </a>
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
    
    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'active') ? 'active' : ''; ?>" 
               href="?tab=active">Active Teachers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'inactive') ? 'active' : ''; ?>" 
               href="?tab=inactive">Inactive Teachers</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($active_tab == 'unassigned') ? 'active' : ''; ?>" 
               href="?tab=unassigned">Unassigned Teachers</a>
        </li>
    </ul>
    
    <!-- Teachers Table -->
    <div class="card shadow">
        <div class="card-body">
            <?php
            // Build query based on tab
            $where = "";
            $title = "";
            
            switch($active_tab) {
                case 'active':
                    $where = "WHERE t.status = 'active'";
                    $title = "Active Teachers";
                    break;
                case 'inactive':
                    $where = "WHERE t.status = 'inactive'";
                    $title = "Inactive Teachers";
                    break;
                case 'unassigned':
                    $where = "WHERE t.assigned_class_id IS NULL AND t.status = 'active'";
                    $title = "Active Teachers Without Class Assignment";
                    break;
            }
            
            $query = $connection->query("
                SELECT 
                    t.*,
                    GROUP_CONCAT(CONCAT(c.faculty, ' (Sem ', c.semester, ')') SEPARATOR ', ') as teaching_classes,
                    COUNT(DISTINCT c.class_id) as class_count
                FROM teacher t
                LEFT JOIN class c ON t.teacher_id = c.teacher_id
                $where
                GROUP BY t.teacher_id
                ORDER BY t.created_at DESC
            ");
            ?>
            
            <h5 class="card-title mb-3"><?php echo $title; ?></h5>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Teaching Classes</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($teacher = $query->fetch_assoc()): ?>
                        <tr class="<?php echo ($teacher['status'] == 'inactive') ? 'table-secondary' : ''; ?>">
                            <td>#<?php echo $teacher['teacher_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($teacher['name']); ?></strong>
                                <?php if($teacher['assigned_class_id'] === NULL && $teacher['status'] == 'active'): ?>
                                    <span class="badge bg-warning text-dark ms-1">No Primary Class</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                            <td>
                                <?php if($teacher['teaching_classes']): ?>
                                    <span class="badge bg-info text-dark">
                                        <?php echo $teacher['class_count']; ?> class(es)
                                    </span>
                                    <small class="text-muted ms-2"><?php echo $teacher['teaching_classes']; ?></small>
                                <?php else: ?>
                                    <span class="text-muted">No classes assigned</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($teacher['status'] == 'active') ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($teacher['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($teacher['created_at'])); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="admin_edit_teacher.php?id=<?php echo $teacher['teacher_id']; ?>" 
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    
                                    <?php if($teacher['status'] == 'active'): ?>
                                        <a href="?deactivate=<?php echo $teacher['teacher_id']; ?>" 
                                           class="btn btn-outline-danger" 
                                           onclick="return confirm('Deactivate this teacher?\nThey will be removed from all classes.')" 
                                           title="Deactivate">
                                            <i class="bi bi-person-x"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?reactivate=<?php echo $teacher['teacher_id']; ?>" 
                                           class="btn btn-outline-success" 
                                           onclick="return confirm('Reactivate this teacher?')" 
                                           title="Reactivate">
                                            <i class="bi bi-person-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="admin_assign_subjects.php?teacher_id=<?php echo $teacher['teacher_id']; ?>" 
                                       class="btn btn-outline-info" title="Manage Subjects">
                                        <i class="bi bi-book"></i>
                                    </a>
                                    
                                    <a href="admin_assign_classes.php?teacher_id=<?php echo $teacher['teacher_id']; ?>" 
                                       class="btn btn-outline-warning" title="Assign Classes">
                                        <i class="bi bi-building"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="row mt-4">
        <?php
        $stats = [
            'total' => $connection->query("SELECT COUNT(*) as count FROM teacher")->fetch_assoc()['count'],
            'active' => $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status='active'")->fetch_assoc()['count'],
            'inactive' => $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status='inactive'")->fetch_assoc()['count'],
            'unassigned' => $connection->query("SELECT COUNT(*) as count FROM teacher WHERE assigned_class_id IS NULL AND status='active'")->fetch_assoc()['count'],
            'with_classes' => $connection->query("SELECT COUNT(DISTINCT teacher_id) as count FROM class WHERE teacher_id IS NOT NULL")->fetch_assoc()['count']
        ];
        ?>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center p-3">
                    <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                    <small class="opacity-75">Total Teachers</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center p-3">
                    <h4 class="mb-0"><?php echo $stats['active']; ?></h4>
                    <small class="opacity-75">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body text-center p-3">
                    <h4 class="mb-0"><?php echo $stats['inactive']; ?></h4>
                    <small class="opacity-75">Inactive</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center p-3">
                    <h4 class="mb-0"><?php echo $stats['unassigned']; ?></h4>
                    <small class="opacity-75">Unassigned</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center p-3">
                    <h4 class="mb-0"><?php echo $stats['with_classes']; ?></h4>
                    <small class="opacity-75">Teaching Classes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-dark text-white">
                <div class="card-body text-center p-3">
                    <?php 
                    $avg_classes = $stats['with_classes'] > 0 ? 
                        round($connection->query("SELECT AVG(class_count) as avg FROM (
                            SELECT teacher_id, COUNT(*) as class_count FROM class WHERE teacher_id IS NOT NULL GROUP BY teacher_id
                        ) as counts")->fetch_assoc()['avg'], 1) : 0;
                    ?>
                    <h4 class="mb-0"><?php echo $avg_classes; ?></h4>
                    <small class="opacity-75">Avg Classes/Teacher</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>