<?php
// assign_subjects.php - Assign subjects to a BCA class
session_start();
include('../../config.php');

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
    $subject_ids = $_POST['subject_ids'] ?? [];
    
    // Clear existing subjects for this class
    $clear_stmt = $connection->prepare("DELETE FROM class_subjects WHERE class_id = ?");
    $clear_stmt->bind_param("i", $class_id);
    $clear_stmt->execute();
    
    // Add new subjects
    if(!empty($subject_ids)) {
        $insert_stmt = $connection->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
        foreach($subject_ids as $subject_id) {
            $subject_id_int = intval($subject_id);
            if($subject_id_int > 0) {
                $insert_stmt->bind_param("ii", $class_id, $subject_id_int);
                $insert_stmt->execute();
            }
        }
        $success_message = "Subjects assigned successfully!";
    } else {
        $info_message = "No subjects selected. All subjects removed.";
    }
}

// Get subjects for this class's semester
$semester = $class['semester'];
$subjects_result = $connection->query("
    SELECT s.subject_id, s.subject_code, s.subject_name, s.credits, s.is_elective,
           EXISTS(SELECT 1 FROM class_subjects WHERE class_id = $class_id AND subject_id = s.subject_id) as is_assigned
    FROM subject s  -- CHANGED: subject (singular) not subjects
    WHERE s.semester = $semester AND s.status = 'active'
    ORDER BY s.subject_code
");

// Get currently assigned subjects
$assigned_subjects = $connection->query("
    SELECT s.subject_id, s.subject_code, s.subject_name, s.credits
    FROM class_subjects cs
    JOIN subject s ON cs.subject_id = s.subject_id  -- CHANGED: subject (singular)
    WHERE cs.class_id = $class_id
    ORDER BY s.subject_code
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .mandatory-checkbox {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .elective-checkbox {
            cursor: pointer;
        }
    </style>
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
                            <i class="bi bi-book me-2"></i>
                            Assign Subjects to BCA Class
                            <small class="float-end">
                                Semester <?php echo $class['semester']; ?>, 
                                <?php echo $class['batch_year']; ?> Batch
                            </small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> <?php echo $success_message; ?>
                            <div class="mt-2">
                                <a href="admin_classes.php" class="btn btn-sm btn-success">Back to Classes</a>
                                <a href="assign_subjects.php?class_id=<?php echo $class_id; ?>" class="btn btn-sm btn-primary">Continue Assigning</a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(isset($info_message)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i> <?php echo $info_message; ?>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <!-- <h6>Select Subjects for Semester <?php echo $semester; ?>:</h6> -->
                                
                                <?php if($subjects_result->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th width="50">✓</th>
                                                <th>Subject</th>
                                                <th>Code</th>
                                                <th>Credits</th>
                                                <th>Type</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($subject = $subjects_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" 
                                                           name="subject_ids[]" 
                                                           value="<?php echo $subject['subject_id']; ?>"
                                                           class="form-check-input <?php echo $subject['is_elective'] ? 'elective-checkbox' : 'mandatory-checkbox'; ?>"
                                                           <?php echo $subject['is_assigned'] ? 'checked' : ''; ?>
                                                           onclick="<?php echo !$subject['is_elective'] ? 'return false;' : ''; ?>">
                                                    <?php if(!$subject['is_elective']): ?>
                                                    <input type="hidden" 
                                                           name="subject_ids[]" 
                                                           value="<?php echo $subject['subject_id']; ?>">
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $subject['subject_name']; ?></td>
                                                <td><span class="badge bg-secondary"><?php echo $subject['subject_code']; ?></span></td>
                                                <td><?php echo $subject['credits']; ?></td>
                                                <td>
                                                    <?php if($subject['is_elective']): ?>
                                                    <span class="badge bg-warning">Elective</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-success">Mandatory</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="form-check mt-2">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                    <label class="form-check-label" for="selectAll">Select/Deselect All Subjects</label>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> No subjects found for Semester <?php echo $semester; ?>.
                                    <a href="setup_bca_subjects.php" class="btn btn-sm btn-success ms-2">
                                        <i class="bi bi-plus-circle"></i> Setup BCA Subjects First
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
                                            <?php if($assigned_subjects->num_rows > 0): ?>
                                                <ul class="list-group list-group-flush">
                                                <?php while($subject = $assigned_subjects->fetch_assoc()): ?>
                                                    <li class="list-group-item">
                                                        <i class="bi bi-book text-primary"></i>
                                                        <strong><?php echo $subject['subject_code']; ?></strong> - 
                                                        <?php echo $subject['subject_name']; ?>
                                                        <span class="badge bg-secondary float-end"><?php echo $subject['credits']; ?> cr</span>
                                                    </li>
                                                <?php endwhile; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">No subjects assigned yet.</p>
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
                                                <li><strong>Mandatory Subjects:</strong> Required for all students (auto-included)</li>
                                                <li><strong>Elective Subjects:</strong> Optional, choose based on requirements</li>
                                                <li>Each subject must be taught by at least one teacher</li>
                                                <li>Subjects are semester-specific</li>
                                                <li>Total credits: 20-24 per semester is standard</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4 d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Save Subjects
                                </button>
                                <a href="admin_classes.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <a href="assign_teachers.php?class_id=<?php echo $class_id; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-person-plus"></i> Assign Teachers
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
            const checkboxes = document.querySelectorAll('.elective-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Update select all when individual checkboxes change
        document.querySelectorAll('.elective-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.elective-checkbox');
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                document.getElementById('selectAll').checked = allChecked;
            });
        });
        
        // Initialize select all checkbox
        document.addEventListener('DOMContentLoaded', function() {
            const electiveCheckboxes = document.querySelectorAll('.elective-checkbox');
            if(electiveCheckboxes.length > 0) {
                const allChecked = Array.from(electiveCheckboxes).every(cb => cb.checked);
                document.getElementById('selectAll').checked = allChecked;
            }
        });
    </script>
</body>
</html>