<!-- my_students.php -->
<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: ../teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];

// Get teacher's assigned classes first
$class_sql = "SELECT c.class_id, c.faculty, c.semester FROM class c 
              WHERE c.teacher_id = ? 
              OR c.class_id = (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?)";
$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("ii", $teacher_id, $teacher_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();
$teacher_classes = $class_result->fetch_all(MYSQLI_ASSOC);

// Get class IDs for query
$class_ids = array_column($teacher_classes, 'class_id');

// Get students from these classes
if (!empty($class_ids)) {
    // Create placeholders for IN clause
    $placeholders = str_repeat('?,', count($class_ids) - 1) . '?';
    
    $student_sql = "SELECT s.*, 
                           c.faculty, 
                           c.semester,
                           se.semester_name
                    FROM student s
                    JOIN class c ON s.class_id = c.class_id
                    JOIN semester se ON s.semester_id = se.semester_id
                    WHERE s.class_id IN ($placeholders)
                    ORDER BY s.class_id, s.student_name";
    
    $student_stmt = $connection->prepare($student_sql);
    
    // Bind parameters dynamically
    $types = str_repeat('i', count($class_ids));
    $student_stmt->bind_param($types, ...$class_ids);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    $students = $student_result->fetch_all(MYSQLI_ASSOC);
} else {
    $students = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - Teacher Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .status-active { color: #198754; }
        .status-inactive { color: #6c757d; }
        .table-hover tbody tr:hover { background-color: rgba(0, 0, 0, 0.075); }
        .badge-faculty { background-color: #6f42c1; }
        .badge-semester { background-color: #fd7e14; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="bi bi-people text-primary"></i> My Students
                </h1>
                <p class="text-muted mb-0">
                    Teacher: <?php echo htmlspecialchars($teacher_name); ?> 
                    | Total Students: <?php echo count($students); ?>
                </p>
            </div>
            <div>
                <!-- <a href="add_student.php" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Add New Student
                </a> -->
                <button onclick="window.parent.showAddStudentForm(); return false;" class="btn btn-success">
    <i class="bi bi-person-plus"></i> Add New Student
</button>
                <button onclick="window.parent.showHome(); return false;" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <?php if (!empty($teacher_classes)): ?>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-mortarboard"></i> Classes
                        </h5>
                        <h2 class="display-6"><?php echo count($teacher_classes); ?></h2>
                        <p class="card-text">Assigned to you</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success">
                            <i class="bi bi-people"></i> Total Students
                        </h5>
                        <h2 class="display-6"><?php echo count($students); ?></h2>
                        <p class="card-text">Across all classes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info">
                            <i class="bi bi-check-circle"></i> Active
                        </h5>
                        <h2 class="display-6">
                            <?php echo count(array_filter($students, function($s) { return $s['is_active'] == 1; })); ?>
                        </h2>
                        <p class="card-text">Active students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning">
                    <div class="card-body">
                        <h5 class="card-title text-warning">
                            <i class="bi bi-clock-history"></i> Recent
                        </h5>
                        <h2 class="display-6">
                            <?php 
                            $recent = array_filter($students, function($s) {
                                return strtotime($s['created_at']) > strtotime('-7 days');
                            });
                            echo count($recent);
                            ?>
                        </h2>
                        <p class="card-text">Added last 7 days</p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Class Filter -->
        <?php if (!empty($teacher_classes)): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="bi bi-funnel"></i> Filter by Class</h6>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="all">
                        All Classes (<?php echo count($students); ?>)
                    </button>
                    <?php foreach ($teacher_classes as $class): 
                        $class_students = array_filter($students, function($s) use ($class) {
                            return $s['class_id'] == $class['class_id'];
                        });
                    ?>
                    <button type="button" class="btn btn-outline-secondary" 
                            data-filter="class-<?php echo $class['class_id']; ?>">
                        <?php echo htmlspecialchars($class['faculty']); ?> Sem <?php echo $class['semester']; ?>
                        <span class="badge bg-secondary ms-1"><?php echo count($class_students); ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Students Table -->
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul"></i> Student List
                    <span class="badge bg-primary ms-2" id="student-count"><?php echo count($students); ?></span>
                </h5>
                <div>
                    <input type="text" class="form-control form-control-sm" id="search-student" 
                           placeholder="Search by name, ID, or email..." style="width: 250px;">
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($teacher_classes)): ?>
                    <div class="text-center py-5">
                        <div class="alert alert-warning m-4">
                            <i class="bi bi-exclamation-triangle display-4"></i>
                            <h4 class="mt-3">No Classes Assigned</h4>
                            <p>You don't have any classes assigned to you yet.</p>
                            <p class="text-muted">Contact administrator to get classes assigned.</p>
                        </div>
                    </div>
                <?php elseif (empty($students)): ?>
                    <div class="text-center py-5">
                        <div class="alert alert-info m-4">
                            <i class="bi bi-people display-4"></i>
                            <h4 class="mt-3">No Students Found</h4>
                            <p>There are no students in your classes yet.</p>
                            <a href="add_student.php" class="btn btn-primary mt-2">
                                <i class="bi bi-person-plus"></i> Add Your First Student
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="students-table">
                            <thead class="table-light">
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Class</th>
                                    <th>Semester</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    $status_class = $student['is_active'] == 1 ? 'status-active' : 'status-inactive';
                                    $status_text = $student['is_active'] == 1 ? 'Active' : 'Inactive';
                                    $status_icon = $student['is_active'] == 1 ? 'bi-check-circle' : 'bi-x-circle';
                                ?>
                                <tr data-class="class-<?php echo $student['class_id']; ?>">
                                    <td>
                                        <strong class="text-primary"><?php echo htmlspecialchars($student['student_id']); ?></strong>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-circle me-1"></i>
                                        <?php echo htmlspecialchars($student['student_name']); ?>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($student['email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-faculty text-white">
                                            <?php echo htmlspecialchars($student['faculty']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-semester text-white">
                                            Sem <?php echo $student['semester']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student['phone_number'] ?: 'N/A'); ?>
                                    </td>
                                    <td>
                                        <i class="bi <?php echo $status_icon; ?> <?php echo $status_class; ?> me-1"></i>
                                        <?php echo $status_text; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($student['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-info" 
                                            onclick="window.parent.viewStudentDetail('<?php echo $student['student_id']; ?>')"
                                            title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                            <button class="btn btn-outline-warning"
                                                    onclick="window.parent.showAddResultForm()"
                                                    title="Enter Results">
                                                <i class="bi bi-trophy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($students)): ?>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing <span id="showing-count"><?php echo count($students); ?></span> of <?php echo count($students); ?> students
                    </small>
                    <small class="text-muted">
                        Last updated: <?php echo date('F j, Y h:i A'); ?>
                    </small>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filter by class
        document.querySelectorAll('[data-filter]').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active button
                document.querySelectorAll('[data-filter]').forEach(btn => {
                    btn.classList.remove('active', 'btn-primary');
                    btn.classList.add('btn-outline-secondary');
                });
                this.classList.add('active', 'btn-primary');
                this.classList.remove('btn-outline-secondary');
                
                // Filter rows
                const rows = document.querySelectorAll('#students-table tbody tr');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-class') === filter) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Update counts
                document.getElementById('showing-count').textContent = visibleCount;
                document.getElementById('student-count').textContent = visibleCount;
            });
        });
        
        // Search functionality
        document.getElementById('search-student').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#students-table tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update counts
            document.getElementById('showing-count').textContent = visibleCount;
        });
        
        function viewStudent(studentId) {
            alert('View student details for: ' + studentId + '\n\nNext: Create student detail view page');
            // window.parent.location.href = 'view_student.php?id=' + studentId;
        }
    </script>
</body>
</html>