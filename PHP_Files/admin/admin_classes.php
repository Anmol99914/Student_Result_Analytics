<?php
// admin_classes.php - BCA Class Management with AJAX Add Class
session_start();
include('../../config.php');

// Check admin authentication
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

// Handle AJAX request for adding class
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
                $response['semester'] = $semester;
                $response['batch_year'] = $batch_year;
            } else {
                $response['message'] = "Error creating class: " . $connection->error;
            }
        }
    }
    
    echo json_encode($response);
    exit();
}

// ================================================
// IMPORTANT: Handle AJAX refresh request SEPARATELY
// ================================================
if(isset($_GET['ajax_refresh']) && $_GET['ajax_refresh'] == 'true') {
    // Get ONLY the table content for AJAX refresh
    $classes_query = "
        SELECT 
            c.class_id,
            c.semester,
            c.batch_year,
            c.status,
            c.created_at,
            (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count,
            (SELECT COUNT(*) FROM teacher_class_assignments WHERE class_id = c.class_id) as teacher_count
        FROM class c
        WHERE c.faculty = 'BCA'
        ORDER BY c.batch_year DESC, c.semester
    ";
    $classes_result = $connection->query($classes_query);
    $total_classes = $classes_result->num_rows;
    
    // Output ONLY the table HTML, not the entire page
    if($classes_result->num_rows > 0): ?>
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
                    $teachers_query = $connection->query("
                        SELECT t.name 
                        FROM teacher_class_assignments tca
                        JOIN teacher t ON tca.teacher_id = t.teacher_id
                        WHERE tca.class_id = {$class['class_id']}
                        AND t.status = 'active'
                        ORDER BY t.name
                        LIMIT 3
                    ");
                    
                    $teacher_count = $class['teacher_count'];
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
                        <?php if($teacher_count > 0): ?>
                            <div class="teacher-list">
                                <?php while($teacher = $teachers_query->fetch_assoc()): ?>
                                    <div class="mb-1">
                                        <i class="bi bi-person-check text-success me-1"></i>
                                        <?php echo $teacher['name']; ?>
                                    </div>
                                <?php endwhile; ?>
                                <?php if($teacher_count > 3): ?>
                                    <div class="text-muted">
                                        <i class="bi bi-three-dots"></i> +<?php echo $teacher_count - 3; ?> more
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
                    
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <a href="assign_teachers.php?class_id=<?php echo $class['class_id']; ?>" 
                               class="btn btn-outline-primary btn-sm action-btn" 
                               title="Assign Teachers">
                                <i class="bi bi-person-plus"></i>
                            </a>
                            
                            <button onclick="editClass(<?php echo $class['class_id']; ?>)" 
                                    class="btn btn-outline-warning btn-sm action-btn" 
                                    title="Edit Class">
                                <i class="bi bi-pencil"></i>
                            </button>
                            
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
            <p class="text-muted mb-4">Get started by creating your first BCA class</p>
            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addClassModal">
                <i class="bi bi-plus-circle"></i> Create First Class
            </button>
        </div>
    </div>
    <?php endif;
    
    // IMPORTANT: Exit here to prevent loading full page
    exit();
}

// ================================================
// REGULAR PAGE LOAD (NON-AJAX)
// ================================================
// Get all BCA classes with teacher counts
$classes_query = "
    SELECT 
        c.class_id,
        c.semester,
        c.batch_year,
        c.status,
        c.created_at,
        (SELECT COUNT(*) FROM student WHERE class_id = c.class_id) as student_count,
        (SELECT COUNT(*) FROM teacher_class_assignments WHERE class_id = c.class_id) as teacher_count
    FROM class c
    WHERE c.faculty = 'BCA'
    ORDER BY c.batch_year DESC, c.semester
";
$classes_result = $connection->query($classes_query);

// Get statistics
$total_classes = $classes_result->num_rows;
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
        /* ... keep your existing styles ... */
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
                <!-- Single Add Class Button -->
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

        <!-- Classes Table -->
        <div class="card shadow">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul text-primary me-2"></i>All BCA Classes
                            <small class="text-muted ms-2">Sorted by batch year (newest first)</small>
                        </h5>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary btn-sm" onclick="refreshClasses()">
                            <i class="bi bi-arrow-clockwise"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-0">
                <div id="classesTableContainer">
                    <!-- This will be loaded via AJAX on refresh -->
                    <?php if($classes_result->num_rows > 0): ?>
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
                                    // Get assigned teachers for this class
                                    $teachers_query = $connection->query("
                                        SELECT t.name 
                                        FROM teacher_class_assignments tca
                                        JOIN teacher t ON tca.teacher_id = t.teacher_id
                                        WHERE tca.class_id = {$class['class_id']}
                                        AND t.status = 'active'
                                        ORDER BY t.name
                                        LIMIT 3
                                    ");
                                    
                                    $teacher_count = $class['teacher_count'];
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
                                        <?php if($teacher_count > 0): ?>
                                            <div class="teacher-list">
                                                <?php while($teacher = $teachers_query->fetch_assoc()): ?>
                                                    <div class="mb-1">
                                                        <i class="bi bi-person-check text-success me-1"></i>
                                                        <?php echo $teacher['name']; ?>
                                                    </div>
                                                <?php endwhile; ?>
                                                <?php if($teacher_count > 3): ?>
                                                    <div class="text-muted">
                                                        <i class="bi bi-three-dots"></i> +<?php echo $teacher_count - 3; ?> more
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
                                    
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="assign_teachers.php?class_id=<?php echo $class['class_id']; ?>" 
                                               class="btn btn-outline-primary btn-sm action-btn" 
                                               title="Assign Teachers">
                                                <i class="bi bi-person-plus"></i>
                                            </a>
                                            
                                            <button onclick="editClass(<?php echo $class['class_id']; ?>)" 
                                                    class="btn btn-outline-warning btn-sm action-btn" 
                                                    title="Edit Class">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            
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
                                    Showing <span id="classesCount"><?php echo $total_classes; ?></span> BCA class<?php echo $total_classes != 1 ? 'es' : ''; ?>
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
                            <p class="text-muted mb-4">Get started by creating your first BCA class</p>
                            <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addClassModal">
                                <i class="bi bi-plus-circle"></i> Create First Class
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
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
        // DOM Elements
        const addClassForm = document.getElementById('addClassForm');
        const formMessage = document.getElementById('formMessage');
        const submitBtn = document.getElementById('submitBtn');
        const addClassModal = document.getElementById('addClassModal');
        const modal = bootstrap.Modal.getInstance(addClassModal) || new bootstrap.Modal(addClassModal);
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Toast instances
        const successToast = new bootstrap.Toast(document.getElementById('successToast'));
        const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
        
        // Show toast notification
        function showToast(message, type = 'success') {
            if(type === 'success') {
                document.getElementById('toastMessage').textContent = message;
                successToast.show();
            } else {
                document.getElementById('errorToastMessage').textContent = message;
                errorToast.show();
            }
        }
        
        // Show loading overlay
        function showLoading() {
            loadingOverlay.style.display = 'flex';
        }
        
        // Hide loading overlay
        function hideLoading() {
            loadingOverlay.style.display = 'none';
        }
        
        // Handle add class form submission
        addClassForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const semester = document.getElementById('semester').value;
            const batchYear = document.getElementById('batch_year').value;
            
            // Validate
            if(!semester || !batchYear) {
                showFormMessage('Please select both semester and batch year', 'danger');
                return;
            }
            
            // Show loading state
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';
            submitBtn.disabled = true;
            
            // Prepare form data
            const formData = new FormData();
            formData.append('ajax_add_class', 'true');
            formData.append('semester', semester);
            formData.append('batch_year', batchYear);
            
            // Send AJAX request
            fetch('admin_classes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    // Show success message
                    showFormMessage(data.message, 'success');
                    
                    // Show toast
                    showToast(data.message);
                    
                    // Reset form and close modal after delay
                    setTimeout(() => {
                        addClassForm.reset();
                        modal.hide();
                        
                        // Refresh the table via AJAX (NOT full page reload)
                        refreshClasses();
                    }, 1500);
                } else {
                    showFormMessage(data.message, 'danger');
                    showToast(data.message, 'error');
                }
            })
            .catch(error => {
                showFormMessage('Network error: ' + error.message, 'danger');
                showToast('Network error occurred', 'error');
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
        
        // Show form message
        function showFormMessage(message, type = 'info') {
            formMessage.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }
        
        // ================================================
        // FIXED: Refresh classes table via AJAX
        // ================================================
        function refreshClasses() {
            showLoading();
            
            // Fetch ONLY the table content (not the whole page)
            fetch('admin_classes.php?ajax_refresh=true')
                .then(response => response.text())
                .then(html => {
                    // Directly replace the table container content
                    document.getElementById('classesTableContainer').innerHTML = html;
                    
                    // Update stats by recalculating
                    updateStatsAfterRefresh();
                    
                    showToast('Classes refreshed successfully', 'success');
                    hideLoading();
                })
                .catch(error => {
                    console.error('Error refreshing:', error);
                    showToast('Error refreshing classes', 'error');
                    hideLoading();
                });
        }
        
        // Update stats after refresh
        function updateStatsAfterRefresh() {
            // Count rows in the table
            const classRows = document.querySelectorAll('#classesTableBody tr');
            const classCount = classRows.length;
            
            // Update displayed counts
            document.getElementById('totalClasses').textContent = classCount;
            document.getElementById('classesCount').textContent = classCount;
            
            // Count active classes (all rows with active badge)
            const activeCount = document.querySelectorAll('.badge.bg-success').length;
            document.getElementById('activeClasses').textContent = activeCount;
            
            // Count unassigned (rows with "No teachers" badge)
            const unassignedCount = document.querySelectorAll('.badge.bg-warning').length;
            document.getElementById('unassignedClasses').textContent = unassignedCount;
            
            // Count unique batch years
            const batchBadges = document.querySelectorAll('.batch-badge');
            const batchYears = new Set();
            batchBadges.forEach(badge => {
                const year = badge.textContent.match(/\d{4}/);
                if(year) batchYears.add(year[0]);
            });
            document.getElementById('batchYears').textContent = batchYears.size;
        }
        
        // Edit class function
        function editClass(classId) {
            showLoading();
            
            // In a real implementation, this would fetch class data and show edit modal
            setTimeout(() => {
                hideLoading();
                alert(`Edit class #${classId} - This would open an edit modal in production`);
            }, 500);
        }
        
        // Toggle class status (activate/deactivate)
        function toggleClassStatus(classId, action) {
            if(!confirm(`Are you sure you want to ${action} this class?`)) {
                return;
            }
            
            showLoading();
            
            // For now, just refresh the table after status change
            // In production, you'd make an AJAX call to update status
            setTimeout(() => {
                refreshClasses();
                showToast(`Class ${action}d successfully`, 'success');
            }, 1000);
        }
        
        // Handle modal events
        addClassModal.addEventListener('shown.bs.modal', function() {
            document.getElementById('semester').focus();
            formMessage.innerHTML = '';
        });
        
        addClassModal.addEventListener('hidden.bs.modal', function() {
            addClassForm.reset();
            formMessage.innerHTML = '';
        });
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Check for URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if(urlParams.has('success')) {
                showToast(urlParams.get('message') || 'Operation successful!');
            }
            
            if(urlParams.has('error')) {
                showToast(urlParams.get('message') || 'An error occurred!', 'error');
            }
        });
    </script>
</body>
</html>