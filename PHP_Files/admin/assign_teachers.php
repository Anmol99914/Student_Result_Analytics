<?php
// assign_teachers.php - Assign teachers to a BCA class
session_start();
include('../config.php');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

$class_id = intval($_GET['class_id'] ?? 0);

// Get class details
$class_query = $connection->prepare("SELECT class_id, semester, batch_year FROM class WHERE class_id = ?");
$class_query->bind_param("i", $class_id);
$class_query->execute();
$class = $class_query->get_result()->fetch_assoc();

if(!$class) {
    die("Class not found!");
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacher_ids = $_POST['teacher_ids'] ?? [];
    
    // Clear existing assignments for this class
    $clear_stmt = $connection->prepare("DELETE FROM teacher_class_assignments WHERE class_id = ?");
    $clear_stmt->bind_param("i", $class_id);
    $clear_stmt->execute();
    
    // Add new assignments
    if(!empty($teacher_ids)) {
        $insert_stmt = $connection->prepare("INSERT INTO teacher_class_assignments (teacher_id, class_id) VALUES (?, ?)");
        foreach($teacher_ids as $teacher_id) {
            $teacher_id_int = intval($teacher_id);
            if($teacher_id_int > 0) {
                $insert_stmt->bind_param("ii", $teacher_id_int, $class_id);
                $insert_stmt->execute();
            }
        }
        $success = "Teachers assigned successfully!";
    } else {
        $info = "No teachers selected. All assignments removed.";
    }
}

// Get all active teachers
$teachers_result = $connection->query("
    SELECT t.teacher_id, t.name, t.email, 
           (SELECT COUNT(*) FROM teacher_class_assignments 
            WHERE teacher_id = t.teacher_id AND class_id = $class_id) as is_assigned
    FROM teacher t 
    WHERE t.status = 'active'
    ORDER BY t.name
");

// Get currently assigned teachers
$assigned_teachers = $connection->query("
    SELECT t.teacher_id, t.name 
    FROM teacher_class_assignments tca
    JOIN teacher t ON tca.teacher_id = t.teacher_id
    WHERE tca.class_id = $class_id
    ORDER BY t.name
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_classes.php">
                <i class="bi bi-arrow-left"></i> Back to Classes
            </a>
            <div>
                <a href="admin_main_page.php" class="btn btn-outline-primary">
                    <i class="bi bi-house"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            Assign Teachers to BCA Class
                            <small class="float-end">
                                Semester <?php echo $class['semester']; ?>, 
                                <?php echo $class['batch_year']; ?> Batch
                            </small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
                            <div class="mt-2">
                                <a href="admin_classes.php" class="btn btn-sm btn-success">Back to Classes</a>
                                <a href="assign_teachers.php?class_id=<?php echo $class_id; ?>" class="btn btn-sm btn-primary">Continue Assigning</a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(isset($info)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i> <?php echo $info; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                            
                            <div class="mb-4">
                                <h6>Select Teachers for this Class:</h6>
                                <p class="text-muted">All selected teachers will have access to manage this class.</p>
                                
                                <?php if($teachers_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">✓</th>
                                                <th>Teacher</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($teacher = $teachers_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" 
                                                           name="teacher_ids[]" 
                                                           value="<?php echo $teacher['teacher_id']; ?>"
                                                           class="form-check-input teacher-checkbox"
                                                           <?php echo $teacher['is_assigned'] > 0 ? 'checked' : ''; ?>>
                                                </td>
                                                <td><?php echo $teacher['name']; ?></td>
                                                <td><?php echo $teacher['email']; ?></td>
                                                <td>
                                                    <?php if($teacher['is_assigned'] > 0): ?>
                                                    <span class="badge bg-success">Assigned</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">Not Assigned</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                    <label class="form-check-label" for="selectAll">Select/Deselect All Teachers</label>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No active teachers found.
                                    <a href="admin_add_teacher.php" class="btn btn-sm btn-success ms-2">
                                        <i class="bi bi-person-plus"></i> Add Teachers First
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bi bi-list-check"></i> Currently Assigned</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php if($assigned_teachers->num_rows > 0): ?>
                                                <ul class="list-group list-group-flush">
                                                <?php while($teacher = $assigned_teachers->fetch_assoc()): ?>
                                                    <li class="list-group-item">
                                                        <i class="bi bi-person-check text-success"></i>
                                                        <?php echo $teacher['name']; ?>
                                                    </li>
                                                <?php endwhile; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">No teachers assigned yet.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-0">
                                                <li>All assigned teachers can:
                                                    <ul>
                                                        <li>Add/remove students</li>
                                                        <li>Enter marks and results</li>
                                                        <li>View class reports</li>
                                                    </ul>
                                                </li>
                                                <li>Teachers can be unassigned anytime</li>
                                                <li>Deactivated teachers are automatically unassigned</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Save Assignments
                                </button>
                                <a href="admin_classes.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <a href="admin_add_teacher.php" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus"></i> Add New Teacher
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all checkboxes
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.teacher-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Update select all when individual checkboxes change
        document.querySelectorAll('.teacher-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.teacher-checkbox');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                document.getElementById('selectAll').checked = allChecked;
            });
        });
    </script>
</body>
</html>