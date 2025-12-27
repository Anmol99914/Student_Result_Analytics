<?php
// admin_classes.php - BCA Class Management with AJAX
session_start();
include('../../config.php');

// Check admin authentication
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

// ================================================
// AJAX REQUESTS HANDLING
// ================================================

// 1. Handle ADD CLASS request
if(isset($_POST['ajax_add_class']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $semester = intval($_POST['semester']);
    $batch_year = intval($_POST['batch_year']);
    $faculty = 'BCA';
    $status = 'active';
    
    $response = ['success' => false, 'message' => ''];
    
    // Validation
    if($semester < 1 || $semester > 8) {
        $response['message'] = "Invalid semester (1-8 only)";
    } elseif($batch_year < 2000 || $batch_year > 2030) {
        $response['message'] = "Invalid batch year (2000-2030)";
    } else {
        // Check if BCA class already exists
        $check = $connection->prepare("SELECT class_id FROM class WHERE faculty = ? AND semester = ? AND batch_year = ?");
        $check->bind_param("sii", $faculty, $semester, $batch_year);
        $check->execute();
        
        if($check->get_result()->num_rows > 0) {
            $response['message'] = "BCA Semester $semester for $batch_year batch already exists!";
        } else {
            // Insert new BCA class
            $stmt = $connection->prepare("INSERT INTO class (faculty, semester, batch_year, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siis", $faculty, $semester, $batch_year, $status);
            
            if($stmt->execute()) {
                $class_id = $stmt->insert_id;
                $response['success'] = true;
                $response['message'] = "✅ BCA Class created successfully! Class ID: #$class_id";
                $response['class_id'] = $class_id;
            } else {
                $response['message'] = "Error creating class: " . $connection->error;
            }
        }
    }
    
    echo json_encode($response);
    exit();
}

// 2. Handle TOGGLE STATUS request
if(isset($_POST['ajax_toggle_status']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $class_id = intval($_POST['class_id']);
    $action = $_POST['action'];
    
    $response = ['success' => false, 'message' => ''];
    
    if($class_id <= 0) {
        $response['message'] = "Invalid class ID";
    } else {
        $new_status = ($action == 'activate') ? 'active' : 'inactive';
        
        // Check if class exists
        $check = $connection->prepare("SELECT class_id FROM class WHERE class_id = ?");
        $check->bind_param("i", $class_id);
        $check->execute();
        
        if($check->get_result()->num_rows == 0) {
            $response['message'] = "Class not found";
        } else {
            // Update status
            $stmt = $connection->prepare("UPDATE class SET status = ? WHERE class_id = ?");
            $stmt->bind_param("si", $new_status, $class_id);
            
            if($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Class status updated to " . strtoupper($new_status);
                
                // Remove teacher assignments if deactivating
                if($action == 'deactivate') {
                    $connection->query("DELETE FROM teacher_class_assignments WHERE class_id = $class_id");
                }
            } else {
                $response['message'] = "Error updating class: " . $connection->error;
            }
        }
    }
    
    echo json_encode($response);
    exit();
}

// 3. Handle EDIT CLASS request
if(isset($_POST['ajax_edit_class']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $class_id = intval($_POST['class_id']);
    $semester = intval($_POST['semester']);
    $batch_year = intval($_POST['batch_year']);
    
    $response = ['success' => false, 'message' => ''];
    
    // Validation
    if($class_id <= 0) {
        $response['message'] = "Invalid class ID";
    } elseif($semester < 1 || $semester > 8) {
        $response['message'] = "Invalid semester (1-8 only)";
    } elseif($batch_year < 2000 || $batch_year > 2030) {
        $response['message'] = "Invalid batch year (2000-2030)";
    } else {
        // Check if class exists
        $check = $connection->prepare("SELECT class_id FROM class WHERE class_id = ?");
        $check->bind_param("i", $class_id);
        $check->execute();
        
        if($check->get_result()->num_rows == 0) {
            $response['message'] = "Class not found";
        } else {
            // Check if new semester+batch combination already exists (excluding current class)
            $check_duplicate = $connection->prepare("SELECT class_id FROM class WHERE semester = ? AND batch_year = ? AND class_id != ?");
            $check_duplicate->bind_param("iii", $semester, $batch_year, $class_id);
            $check_duplicate->execute();
            
            if($check_duplicate->get_result()->num_rows > 0) {
                $response['message'] = "BCA Semester $semester for $batch_year batch already exists!";
            } else {
                // Update class
                $stmt = $connection->prepare("UPDATE class SET semester = ?, batch_year = ? WHERE class_id = ?");
                $stmt->bind_param("iii", $semester, $batch_year, $class_id);
                
                if($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "✅ Class updated successfully!";
                } else {
                    $response['message'] = "Error updating class: " . $connection->error;
                }
            }
        }
    }
    
    echo json_encode($response);
    exit();
}
// Handle AJAX DELETE CLASS request
if(isset($_POST['ajax_delete_class']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    header('Content-Type: application/json');
    
    $class_id = intval($_POST['class_id']);
    $response = ['success' => false, 'message' => ''];
    
    if($class_id <= 0) {
        $response['message'] = "Invalid class ID";
    } else {
        // Check if class has students
        $check = $connection->query("SELECT COUNT(*) as count FROM student WHERE class_id = $class_id");
        $student_count = $check->fetch_assoc()['count'];
        
        if($student_count > 0) {
            $response['message'] = "Cannot delete class with $student_count students!";
        } else {
            // Start transaction
            $connection->begin_transaction();
            
            try {
                // Delete teacher assignments
                $connection->query("DELETE FROM teacher_class_assignments WHERE class_id = $class_id");
                
                // Delete class subjects if table exists
                if($connection->query("SHOW TABLES LIKE 'class_subjects'")->num_rows > 0) {
                    $connection->query("DELETE FROM class_subjects WHERE class_id = $class_id");
                }
                
                // Delete the class
                $delete_stmt = $connection->prepare("DELETE FROM class WHERE class_id = ?");
                $delete_stmt->bind_param("i", $class_id);
                
                if($delete_stmt->execute()) {
                    $connection->commit();
                    $response['success'] = true;
                    $response['message'] = "✅ Class deleted successfully!";
                } else {
                    throw new Exception("Failed to delete class");
                }
            } catch(Exception $e) {
                $connection->rollback();
                $response['message'] = "Error deleting class: " . $e->getMessage();
            }
        }
    }
    
    echo json_encode($response);
    exit();
}

// 4. Handle GET CLASS DATA for editing
if(isset($_GET['ajax_get_class']) && $_SERVER['REQUEST_METHOD'] == 'GET') {
    header('Content-Type: application/json');
    
    $class_id = intval($_GET['class_id']);
    $response = ['success' => false, 'message' => ''];
    
    if($class_id <= 0) {
        $response['message'] = "Invalid class ID";
    } else {
        $query = $connection->prepare("SELECT class_id, semester, batch_year, status FROM class WHERE class_id = ?");
        $query->bind_param("i", $class_id);
        $query->execute();
        $result = $query->get_result();
        
        if($result->num_rows > 0) {
            $class = $result->fetch_assoc();
            $response['success'] = true;
            $response['data'] = $class;
        } else {
            $response['message'] = "Class not found";
        }
    }
    
    echo json_encode($response);
    exit();
}

// 5. Handle AJAX REFRESH request with sorting
if(isset($_GET['ajax_refresh']) && $_GET['ajax_refresh'] == 'true') {
    // Get sorting parameters
    $sort_by = $_GET['sort_by'] ?? 'batch_year_desc';
    $search = $_GET['search'] ?? '';
    
    // Build ORDER BY clause based on sort option
    $order_by = '';
    switch($sort_by) {
        case 'semester_asc':
            $order_by = 'c.semester ASC, c.batch_year DESC';
            break;
        case 'semester_desc':
            $order_by = 'c.semester DESC, c.batch_year DESC';
            break;
        case 'batch_year_asc':
            $order_by = 'c.batch_year ASC, c.semester ASC';
            break;
        case 'batch_year_desc':
            $order_by = 'c.batch_year DESC, c.semester ASC';
            break;
        case 'students_asc':
            $order_by = 'student_count ASC, c.batch_year DESC';
            break;
        case 'students_desc':
            $order_by = 'student_count DESC, c.batch_year DESC';
            break;
        case 'teachers_asc':
            $order_by = 'teacher_count ASC, c.batch_year DESC';
            break;
        case 'teachers_desc':
            $order_by = 'teacher_count DESC, c.batch_year DESC';
            break;
        case 'date_asc':
            $order_by = 'c.created_at ASC';
            break;
        case 'date_desc':
            $order_by = 'c.created_at DESC';
            break;
        case 'status':
            $order_by = 'c.status DESC, c.batch_year DESC';
            break;
        default:
            $order_by = 'c.batch_year DESC, c.semester ASC';
    }
    
    // Build WHERE clause for search
    $where_clause = "WHERE c.faculty = 'BCA'";
    if(!empty($search)) {
        $search_term = $connection->real_escape_string($search);
        $where_clause .= " AND (c.semester LIKE '%$search_term%' OR c.batch_year LIKE '%$search_term%')";
    }
    
    // Get classes with counts
    $query = "
        SELECT 
            c.class_id,
            c.semester,
            c.batch_year,
            c.status,
            c.created_at,
            (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count,
            (SELECT COUNT(*) FROM teacher_class_assignments WHERE class_id = c.class_id) as teacher_count
        FROM class c
        $where_clause
        ORDER BY $order_by
    ";
    
    $classes_result = $connection->query($query);
    $total_classes = $classes_result->num_rows;
    

    // Output table HTML
    if($total_classes > 0): ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="60" class="text-center">ID</th>
                    <th>BCA Class Details</th>
                    <th width="120">Batch</th>
                    <th width="150">Teachers</th>
                    <th width="120">Students</th>
                    <th width="100">Status</th>
                    <th width="180" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="classesTableBody">
                <?php while($class = $classes_result->fetch_assoc()): 
                    // Get assigned teachers
                    $teachers_query = $connection->query("
                        SELECT t.name 
                        FROM teacher_class_assignments tca
                        JOIN teacher t ON tca.teacher_id = t.teacher_id
                        WHERE tca.class_id = {$class['class_id']}
                        ORDER BY t.name
                        LIMIT 3
                    ");
                ?>
                <tr class="class-row align-middle" id="classRow-<?php echo $class['class_id']; ?>">
                    <td class="text-center">
                        <span class="badge bg-secondary">#<?php echo $class['class_id']; ?></span>
                    </td>
                    
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="bi bi-mortarboard-fill text-primary fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">BCA Semester <?php echo $class['semester']; ?></h6>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar3"></i> 
                                    <?php echo date('M d, Y', strtotime($class['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    
                    <td>
                        <span class="badge bg-primary batch-badge fs-6">
                            <?php echo $class['batch_year']; ?> Batch
                        </span>
                    </td>
                    
                    <td>
                        <?php if($class['teacher_count'] > 0): ?>
                            <div class="teacher-list">
                                <?php while($teacher = $teachers_query->fetch_assoc()): ?>
                                    <div class="mb-1">
                                        <i class="bi bi-person-check text-success me-1"></i>
                                        <?php echo $teacher['name']; ?>
                                    </div>
                                <?php endwhile; ?>
                                <?php if($class['teacher_count'] > 3): ?>
                                    <div class="text-muted">
                                        <i class="bi bi-three-dots"></i> +<?php echo $class['teacher_count'] - 3; ?> more
                                    </div>
                                <?php endif; ?>
                                 <!-- Add warning if too few teachers -->
            <?php if($class['teacher_count'] < 3): ?>
                <div class="text-danger small">
                    <i class="bi bi-exclamation-triangle"></i> Needs more teachers
                </div>
            <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-person-x"></i> No teachers
                            </span>
                        <?php endif; ?>
                    </td>
                    
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary fs-6 me-2">
                                <?php echo $class['student_count']; ?>
                            </span>
                            <span class="small">
                                student<?php echo $class['student_count'] != 1 ? 's' : ''; ?>
                            </span>
                        </div>
                    </td>
                    
                    <td>
                        <?php if($class['status'] == 'active'): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Active
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Inactive
                            </span>
                        <?php endif; ?>
                    </td>
                    
                   <!-- In your class table actions column -->
<td class="text-center">
    <div class="btn-group" role="group">
        <!-- Assign Subjects Button -->
        <a href="assign_subjects.php?class_id=<?php echo $class['class_id']; ?>" 
           class="btn btn-outline-info btn-sm action-btn" 
           title="Assign Subjects">
            <i class="bi bi-book"></i>
        </a>
        
        <!-- Assign Teachers Button -->
        <a href="assign_teachers.php?class_id=<?php echo $class['class_id']; ?>" 
           class="btn btn-outline-primary btn-sm action-btn" 
           title="Assign Teachers">
            <i class="bi bi-person-plus"></i>
        </a>
        
        <!-- Edit Button -->
        <button onclick="editClass(<?php echo $class['class_id']; ?>)" 
                class="btn btn-outline-warning btn-sm action-btn" 
                title="Edit Class">
            <i class="bi bi-pencil"></i>
        </button>
        
        <!-- Activate/Deactivate Button -->
        <?php if($class['status'] == 'active'): ?>
            <button onclick="toggleClassStatus(<?php echo $class['class_id']; ?>, 'deactivate')" 
                    class="btn btn-outline-danger btn-sm action-btn" 
                    title="Deactivate">
                <i class="bi bi-x-circle"></i>
            </button>
        <?php else: ?>
            <button onclick="toggleClassStatus(<?php echo $class['class_id']; ?>, 'activate')" 
                    class="btn btn-outline-success btn-sm action-btn" 
                    title="Activate">
                <i class="bi bi-check-circle"></i>
            </button>
        <?php endif; ?>
         <!-- Delete button (only show if no students) -->
         <?php if($class['student_count'] == 0): ?>
        <button onclick="deleteClass(<?php echo $class['class_id']; ?>)" 
                class="btn btn-outline-danger btn-sm action-btn" 
                title="Delete Class">
            <i class="bi bi-trash"></i>
        </button>
        <?php endif; ?>
    </div>
</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Summary Footer -->
    <div class="card-footer bg-white border-top">
        <div class="row">
            <div class="col-md-6">
                <small class="text-muted">
                    Showing <?php echo $total_classes; ?> BCA class<?php echo $total_classes != 1 ? 'es' : ''; ?>
                </small>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Click action buttons to manage classes
                </small>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Empty State -->
    <div class="text-center py-5" id="emptyState">
        <div class="py-5">
            <i class="bi bi-mortarboard display-1 text-muted"></i>
            <h4 class="text-muted mt-4">No BCA Classes Found</h4>
            <p class="text-muted mb-4"><?php echo !empty($search) ? 'Try a different search term' : 'Get started by creating your first BCA class'; ?></p>
            <?php if(empty($search)): ?>
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addClassModal">
                <i class="bi bi-plus-circle"></i> Create First Class
            </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif;
    exit();
}

// ================================================
// REGULAR PAGE LOAD (NON-AJAX)
// ================================================

// Get initial data for stats
$total_classes = $connection->query("SELECT COUNT(*) as count FROM class WHERE faculty='BCA'")->fetch_assoc()['count'];
$active_classes = $connection->query("SELECT COUNT(*) as count FROM class WHERE status='active' AND faculty='BCA'")->fetch_assoc()['count'];
$batches = $connection->query("SELECT COUNT(DISTINCT batch_year) as count FROM class WHERE faculty='BCA'")->fetch_assoc()['count'];
$unassigned = $connection->query("SELECT COUNT(*) as count FROM class WHERE class_id NOT IN (SELECT DISTINCT class_id FROM teacher_class_assignments) AND faculty='BCA'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BCA Class Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .stats-card { border-radius: 10px; transition: transform 0.3s; }
        .stats-card:hover { transform: translateY(-5px); }
        .action-btn { width: 35px; height: 35px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
        .class-row:hover { background-color: #f8f9fa; }
        #loadingOverlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.9); z-index: 9999; justify-content: center; align-items: center; }
        .spinner-large { width: 3rem; height: 3rem; }
        .sort-dropdown { min-width: 200px; }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div id="loadingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary spinner-large" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Processing...</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-light bg-light sticky-top shadow-sm">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <a class="navbar-brand mb-0" href="admin_main_page.php">
                    <i class="bi bi-arrow-left me-2"></i>BCA Class Management
                </a>
            </div>
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-3">
                    <i class="bi bi-mortarboard"></i> BCA-Only System
                </span>
                <a href="admin_main_page.php" class="btn btn-outline-primary btn-sm me-2">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addClassModal">
                    <i class="bi bi-plus-circle"></i> Add Class
                </button>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-primary text-white stats-card shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-mortarboard display-6"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">TOTAL BCA CLASSES</h6>
                            <h2 class="card-text mb-0" id="totalClasses"><?php echo $total_classes; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-success text-white stats-card shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-check-circle display-6"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">ACTIVE CLASSES</h6>
                            <h2 class="card-text mb-0" id="activeClasses"><?php echo $active_classes; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-info text-white stats-card shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-calendar-range display-6"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">BATCH YEARS</h6>
                            <h2 class="card-text mb-0" id="batchYears"><?php echo $batches; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card bg-warning text-white stats-card shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-person-x display-6"></i>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">UNASSIGNED</h6>
                            <h2 class="card-text mb-0" id="unassignedClasses"><?php echo $unassigned; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Sort Controls -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by semester or batch year..." value="">
                            <button class="btn btn-primary" onclick="searchClasses()">
                                Search
                            </button>
                            <button class="btn btn-outline-secondary" onclick="clearSearch()">
                                Clear
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-2">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="sortDropdownBtn">
                                    <i class="bi bi-sort-down"></i> Sort By: Batch Year (Newest First)
                                </button>
                                <ul class="dropdown-menu sort-dropdown">
                                    <li><a class="dropdown-item" href="#" onclick="setSort('batch_year_desc')">
                                        <i class="bi bi-sort-numeric-down"></i> Batch Year (Newest First)
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('batch_year_asc')">
                                        <i class="bi bi-sort-numeric-up"></i> Batch Year (Oldest First)
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('semester_asc')">
                                        <i class="bi bi-1-circle"></i> Semester (1 to 8)
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('semester_desc')">
                                        <i class="bi bi-8-circle"></i> Semester (8 to 1)
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('students_desc')">
                                        <i class="bi bi-people-fill"></i> Most Students
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('students_asc')">
                                        <i class="bi bi-people"></i> Fewest Students
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('teachers_desc')">
                                        <i class="bi bi-person-badge-fill"></i> Most Teachers
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('teachers_asc')">
                                        <i class="bi bi-person-badge"></i> Fewest Teachers
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('date_desc')">
                                        <i class="bi bi-calendar-date"></i> Newest Created
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('date_asc')">
                                        <i class="bi bi-calendar"></i> Oldest Created
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="setSort('status')">
                                        <i class="bi bi-check-circle-fill"></i> Active First
                                    </a></li>
                                </ul>
                            </div>
                            <button class="btn btn-outline-primary" onclick="refreshClasses()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Classes Table -->
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul text-primary me-2"></i>All BCA Classes
                            <small class="text-muted ms-2" id="sortInfo">Sorted by batch year (newest first)</small>
                        </h5>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div id="classesTableContainer">
                    <!-- Loading spinner for initial load -->
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading classes...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading classes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Class Modal -->
    <div class="modal fade" id="addClassModal" tabindex="-1" aria-labelledby="addClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addClassModalLabel">
                        <i class="bi bi-plus-circle me-2"></i>Create New BCA Class
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addClassForm">
                    <div class="modal-body">
                        <div id="formMessage"></div>
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="faculty" value="BCA" readonly disabled>
                                <label for="faculty">Faculty</label>
                            </div>
                            <small class="text-muted">System is BCA-only</small>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="semester" name="semester" required>
                                        <option value="">Select</option>
                                        <?php for($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <label for="semester">Semester *</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <?php $currentYear = date('Y'); ?>
                                    <select class="form-select" id="batch_year" name="batch_year" required>
                                        <option value="">Select</option>
                                        <?php for($year = $currentYear - 2; $year <= $currentYear + 2; $year++): ?>
                                            <option value="<?php echo $year; ?>" <?php echo $year == $currentYear ? 'selected' : ''; ?>>
                                                <?php echo $year; ?> Batch
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <label for="batch_year">Batch Year *</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mb-0">
                            <h6><i class="bi bi-info-circle"></i> Notes:</h6>
                            <ul class="mb-0 small">
                                <li>Each semester+batch combination must be unique</li>
                                <li>You can assign teachers after creating the class</li>
                                <li>Class will be created as "Active"</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Create Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Class Modal -->
    <div class="modal fade" id="editClassModal" tabindex="-1" aria-labelledby="editClassModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClassModalLabel">
                        <i class="bi bi-pencil me-2"></i>Edit BCA Class
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editClassForm">
                    <div class="modal-body">
                        <div id="editFormMessage"></div>
                        
                        <input type="hidden" id="edit_class_id" name="class_id">
                        
                        <div class="mb-3">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_faculty" value="BCA" readonly disabled>
                                <label for="edit_faculty">Faculty</label>
                            </div>
                            <small class="text-muted">System is BCA-only</small>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="edit_semester" name="semester" required>
                                        <option value="">Select</option>
                                        <?php for($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                    <label for="edit_semester">Semester *</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="edit_batch_year" name="batch_year" required>
                                        <option value="">Select</option>
                                        <?php 
                                        $currentYear = date('Y');
                                        for($year = $currentYear - 5; $year <= $currentYear + 5; $year++): ?>
                                            <option value="<?php echo $year; ?>"><?php echo $year; ?> Batch</option>
                                        <?php endfor; ?>
                                    </select>
                                    <label for="edit_batch_year">Batch Year *</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mb-0">
                            <h6><i class="bi bi-exclamation-triangle"></i> Important:</h6>
                            <ul class="mb-0 small">
                                <li>Changing semester or batch year will update all related records</li>
                                <li>Each semester+batch combination must remain unique</li>
                                <li>Student and teacher assignments will be preserved</li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="editSubmitBtn">
                            <i class="bi bi-check-circle"></i> Update Class
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle me-2"></i>
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="toastMessage"></div>
        </div>
        
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body" id="errorToastMessage"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Global variables for sorting and searching
        window.currentSort = 'batch_year_desc';
        window.currentSearch = '';
        
        console.log('Page loaded, calling refreshClasses()');
        
        // Load classes on page load
        refreshClasses();
        
        // Setup form event listeners
        setupFormListeners();
        
        // Setup search input enter key
        $('#searchInput').on('keypress', function(e) {
            if(e.key === 'Enter') {
                searchClasses();
            }
        });
    });
    
    // ================================================
    // UTILITY FUNCTIONS
    // ================================================
    function showLoading() {
        console.log('Showing loading overlay');
        $('#loadingOverlay').show();
    }
    
    function hideLoading() {
        console.log('Hiding loading overlay');
        $('#loadingOverlay').hide();
    }
    
    function showToast(message, type = 'success') {
        console.log('Showing toast:', message, type);
        if(type === 'success') {
            $('#toastMessage').text(message);
            new bootstrap.Toast(document.getElementById('successToast')).show();
        } else {
            $('#errorToastMessage').text(message);
            new bootstrap.Toast(document.getElementById('errorToast')).show();
        }
    }
    
    function showFormMessage(elementId, message, type = 'info') {
        $('#' + elementId).html(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
    }
    
    // ================================================
    // SORTING AND SEARCHING
    // ================================================
    function setSort(sortType) {
        console.log('Setting sort to:', sortType);
        window.currentSort = sortType;
        updateSortButtonText();
        refreshClasses();
    }
    
    function updateSortButtonText() {
        const sortTexts = {
            'batch_year_desc': 'Batch Year (Newest First)',
            'batch_year_asc': 'Batch Year (Oldest First)',
            'semester_asc': 'Semester (1 to 8)',
            'semester_desc': 'Semester (8 to 1)',
            'students_desc': 'Most Students',
            'students_asc': 'Fewest Students',
            'teachers_desc': 'Most Teachers',
            'teachers_asc': 'Fewest Teachers',
            'date_desc': 'Newest Created',
            'date_asc': 'Oldest Created',
            'status': 'Active First'
        };
        
        if(sortTexts[window.currentSort]) {
            $('#sortDropdownBtn').html(`<i class="bi bi-sort-down"></i> Sort By: ${sortTexts[window.currentSort]}`);
            $('#sortInfo').text(`Sorted by: ${sortTexts[window.currentSort]}`);
        }
    }
    
    function searchClasses() {
        window.currentSearch = $('#searchInput').val().trim();
        console.log('Searching for:', window.currentSearch);
        refreshClasses();
    }
    
    function clearSearch() {
        $('#searchInput').val('');
        window.currentSearch = '';
        console.log('Cleared search');
        refreshClasses();
    }
    
    // ================================================
    // CLASS MANAGEMENT FUNCTIONS
    // ================================================
    function refreshClasses() {
        console.log('refreshClasses() called');
        showLoading();
        
        // Build URL with sorting and search parameters
        let url = `admin_classes.php?ajax_refresh=true&sort_by=${window.currentSort}`;
        if(window.currentSearch) {
            url += `&search=${encodeURIComponent(window.currentSearch)}`;
        }
        
        console.log('Fetching URL:', url);
        
        // Use jQuery AJAX
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                console.log('HTML received, length:', html.length);
                $('#classesTableContainer').html(html);
                updateStatsAfterRefresh();
                hideLoading();
            },
            error: function(xhr, status, error) {
                console.error('Error refreshing:', error);
                // Show error message in the container
                $('#classesTableContainer').html(`
                    <div class="alert alert-danger m-3">
                        <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Classes</h5>
                        <p>${error}</p>
                        <button class="btn btn-primary btn-sm" onclick="refreshClasses()">
                            <i class="bi bi-arrow-clockwise"></i> Try Again
                        </button>
                    </div>
                `);
                showToast('Error refreshing classes: ' + error, 'error');
                hideLoading();
            }
        });
    }
    
    function updateStatsAfterRefresh() {
        console.log('Updating stats after refresh');
        
        // Try to get class rows
        const classRows = $('#classesTableBody tr');
        const classCount = classRows.length;
        
        console.log('Found', classCount, 'classes');
        
        // Update stats if elements exist
        $('#totalClasses').text(classCount);
        
        const activeCount = $('.badge.bg-success').length;
        $('#activeClasses').text(activeCount);
        
        const unassignedCount = $('.badge.bg-warning').length;
        $('#unassignedClasses').text(unassignedCount);
        
        const batchYears = new Set();
        $('.batch-badge').each(function() {
            const year = $(this).text().match(/\d{4}/);
            if(year) batchYears.add(year[0]);
        });
        $('#batchYears').text(batchYears.size);
    }
    
    // ================================================
    // EDIT CLASS FUNCTIONALITY
    // ================================================
    function editClass(classId) {
        console.log('Editing class:', classId);
        showLoading();
        
        $.ajax({
            url: `admin_classes.php?ajax_get_class=true&class_id=${classId}`,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                hideLoading();
                
                if(data.success) {
                    // Fill edit form with class data
                    $('#edit_class_id').val(data.data.class_id);
                    $('#edit_semester').val(data.data.semester);
                    $('#edit_batch_year').val(data.data.batch_year);
                    
                    // Show edit modal
                    const editModal = new bootstrap.Modal(document.getElementById('editClassModal'));
                    editModal.show();
                } else {
                    showToast(data.message, 'error');
                }
            },
            error: function() {
                hideLoading();
                showToast('Error loading class data', 'error');
            }
        });
    }
    
    // ================================================
    // STATUS TOGGLE
    // ================================================
    function toggleClassStatus(classId, action) {
        let message = `Are you sure you want to ${action} this class?`;
        
        if(action === 'deactivate') {
            message += '\n\n⚠️ This will:';
            message += '\n• Remove all teacher assignments';
            message += '\n• Make the class unavailable for new enrollments';
        }
        
        if(!confirm(message)) {
            return;
        }
        
        showLoading();
        
        $.ajax({
            url: 'admin_classes.php',
            type: 'POST',
            data: {
                ajax_toggle_status: 'true',
                class_id: classId,
                action: action
            },
            dataType: 'json',
            success: function(data) {
                if(data.success) {
                    showToast(data.message, 'success');
                    refreshClasses();
                } else {
                    showToast(data.message, 'error');
                    hideLoading();
                }
            },
            error: function() {
                hideLoading();
                showToast('Network error occurred', 'error');
            }
        });
    }
    
    // ================================================
    // FORM SETUP
    // ================================================
    function setupFormListeners() {
        console.log('Setting up form listeners');
        
        // Add Class Form
        $('#addClassForm').on('submit', function(e) {
            e.preventDefault();
            
            const semester = $('#semester').val();
            const batchYear = $('#batch_year').val();
            
            if(!semester || !batchYear) {
                showFormMessage('formMessage', 'Please select both semester and batch year', 'danger');
                return;
            }
            
            const submitBtn = $('#submitBtn');
            const originalBtnText = submitBtn.html();
            submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Creating...');
            submitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'admin_classes.php',
                type: 'POST',
                data: {
                    ajax_add_class: 'true',
                    semester: semester,
                    batch_year: batchYear
                },
                dataType: 'json',
                success: function(data) {
                    if(data.success) {
                        showFormMessage('formMessage', data.message, 'success');
                        showToast(data.message);
                        
                        setTimeout(() => {
                            $('#addClassForm')[0].reset();
                            bootstrap.Modal.getInstance(document.getElementById('addClassModal')).hide();
                            refreshClasses();
                        }, 1500);
                    } else {
                        showFormMessage('formMessage', data.message, 'danger');
                        showToast(data.message, 'error');
                    }
                },
                error: function() {
                    showFormMessage('formMessage', 'Network error occurred', 'danger');
                    showToast('Network error occurred', 'error');
                },
                complete: function() {
                    submitBtn.html(originalBtnText);
                    submitBtn.prop('disabled', false);
                }
            });
        });
        
        // Edit Class Form
        $('#editClassForm').on('submit', function(e) {
            e.preventDefault();
            
            const classId = $('#edit_class_id').val();
            const semester = $('#edit_semester').val();
            const batchYear = $('#edit_batch_year').val();
            
            if(!semester || !batchYear) {
                showFormMessage('editFormMessage', 'Please select both semester and batch year', 'danger');
                return;
            }
            
            const editSubmitBtn = $('#editSubmitBtn');
            const originalBtnText = editSubmitBtn.html();
            editSubmitBtn.html('<span class="spinner-border spinner-border-sm"></span> Updating...');
            editSubmitBtn.prop('disabled', true);
            
            $.ajax({
                url: 'admin_classes.php',
                type: 'POST',
                data: {
                    ajax_edit_class: 'true',
                    class_id: classId,
                    semester: semester,
                    batch_year: batchYear
                },
                dataType: 'json',
                success: function(data) {
                    if(data.success) {
                        showFormMessage('editFormMessage', data.message, 'success');
                        showToast(data.message);
                        
                        setTimeout(() => {
                            $('#editClassForm')[0].reset();
                            bootstrap.Modal.getInstance(document.getElementById('editClassModal')).hide();
                            refreshClasses();
                        }, 1500);
                    } else {
                        showFormMessage('editFormMessage', data.message, 'danger');
                        showToast(data.message, 'error');
                    }
                },
                error: function() {
                    showFormMessage('editFormMessage', 'Network error occurred', 'danger');
                    showToast('Network error occurred', 'error');
                },
                complete: function() {
                    editSubmitBtn.html(originalBtnText);
                    editSubmitBtn.prop('disabled', false);
                }
            });
        });
        
        // Clear form messages when modals open
        $('#addClassModal').on('shown.bs.modal', function() {
            $('#formMessage').html('');
        });
        
        $('#editClassModal').on('shown.bs.modal', function() {
            $('#editFormMessage').html('');
        });
    }

    // To delete class
    // Add this function to your JavaScript section
function deleteClass(classId) {
    if(!confirm("Are you sure you want to delete this class?\n\nThis will also remove all teacher and subject assignments.")) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: 'admin_classes.php',
        type: 'POST',
        data: {
            ajax_delete_class: 'true',
            class_id: classId
        },
        dataType: 'json',
        success: function(data) {
            if(data.success) {
                showToast(data.message, 'success');
                refreshClasses();
            } else {
                showToast(data.message, 'error');
                hideLoading();
            }
        },
        error: function() {
            hideLoading();
            showToast('Network error occurred', 'error');
        }
    });
}
    </script>
</body>
</html>