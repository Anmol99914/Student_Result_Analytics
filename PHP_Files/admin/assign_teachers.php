<?php
// assign_teachers.php - Assign teachers to subjects in a class
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

$class_id = intval($_GET['class_id'] ?? 0);

// Get class details
$class_query = $connection->prepare("
    SELECT c.class_id, c.faculty, c.semester, c.batch_year,
           f.faculty_name
    FROM class c
    LEFT JOIN faculty f ON c.faculty = f.faculty_code
    WHERE c.class_id = ?
");
$class_query->bind_param("i", $class_id);
$class_query->execute();
$class = $class_query->get_result()->fetch_assoc();

if(!$class) {
    die("Class not found!");
}

// Get all faculties for dropdown
$faculties_result = $connection->query("
    SELECT faculty_code, faculty_name 
    FROM faculty 
    WHERE status = 'active' 
    ORDER BY faculty_name
");

// Get semesters (1-8)
$semesters = range(1, 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Teachers to Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
</head>
<body data-class-id="<?php echo $class_id; ?>"
      data-faculty="<?php echo htmlspecialchars($class['faculty']); ?>"
      data-semester="<?php echo $class['semester']; ?>">
    
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
        <!-- Alerts will be inserted here by JavaScript -->
        <div id="alertContainer"></div>
        
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            Assign Teachers to Subjects
                            <small class="float-end" id="classInfo">
                                <?php echo $class['faculty_name']; ?> 
                                Semester <?php echo $class['semester']; ?>, 
                                <?php echo $class['batch_year']; ?> Batch
                            </small>
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <!-- Loading Spinner -->
                        <div id="loadingSpinner" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading data...</p>
                        </div>

                        <!-- Step 1: Select Faculty and Semester -->
                        <div class="mb-4">
                            <h6><i class="bi bi-1-circle"></i> Select Faculty & Semester</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Faculty</label>
                                    <select class="form-select" id="facultySelect">
                                        <?php while($faculty = $faculties_result->fetch_assoc()): ?>
                                            <option value="<?php echo $faculty['faculty_code']; ?>"
                                                <?php echo $faculty['faculty_code'] == $class['faculty'] ? 'selected' : ''; ?>>
                                                <?php echo $faculty['faculty_name']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Semester</label>
                                    <select class="form-select" id="semesterSelect">
                                        <?php foreach($semesters as $sem): ?>
                                            <option value="<?php echo $sem; ?>"
                                                <?php echo $sem == $class['semester'] ? 'selected' : ''; ?>>
                                                Semester <?php echo $sem; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Select Subject -->
                        <div class="mb-4">
                            <h6><i class="bi bi-2-circle"></i> Select Subject</h6>
                            <div class="mb-2">
                                <label class="form-label">Choose a subject to assign teachers</label>
                                <select class="form-select" id="subjectSelect">
                                    <option value="">Select a faculty and semester first</option>
                                </select>
                                <div class="form-text">
                                    <span id="subjectCount">0 subjects found</span> • 
                                    ✓ indicates subject already has teacher(s)
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Select Teachers -->
                        <div class="mb-4">
                            <h6><i class="bi bi-3-circle"></i> Select Teachers</h6>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> 
                                Select teachers to assign them to the chosen subject. 
                                <span id="selectedCount">0</span> teacher(s) selected.
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover" id="teachersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="form-check-input" id="selectAllTeachers">
                                            </th>
                                            <th>Teacher Name</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Current Workload</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                Select a subject to view available teachers
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllTeachersBottom">
                                    <label class="form-check-label" for="selectAllTeachersBottom">
                                        Select/Deselect All
                                    </label>
                                </div>
                                <button id="saveBtn" class="btn btn-success" disabled>
                                    <i class="bi bi-save"></i> Assign Selected Teachers
                                </button>
                            </div>
                        </div>

                        <!-- Current Assignments -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card border-primary">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <i class="bi bi-list-check text-primary"></i> 
                                            Current Teacher Assignments for This Class
                                        </h6>
                                    </div>
                                    <div class="card-body" id="assignmentsList">
                                        <div class="text-center">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <span class="ms-2">Loading current assignments...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Information & Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-info-circle"></i> How It Works</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li>Teachers can be assigned to <strong>multiple subjects</strong></li>
                                            <li>Subjects can have <strong>multiple teachers</strong> (co-teaching)</li>
                                            <li>When teachers leave, assignments are marked as completed (history preserved)</li>
                                            <li>Each teacher-subject assignment is tracked separately</li>
                                            <li>Assignments are for the current academic year (2025)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Recommendations</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="mb-0">
                                            <li>For core subjects, assign <strong>1-2 teachers</strong></li>
                                            <li>For lab/practical subjects, assign <strong>2-3 teachers</strong></li>
                                            <li>Check teacher workload before assigning</li>
                                            <li>Consider teacher expertise in subject area</li>
                                            <li>Balance assignments across all teachers</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 d-flex gap-2">
                            <button id="saveBtnBottom" class="btn btn-success" disabled>
                                <i class="bi bi-save"></i> Save Assignments
                            </button>
                            <a href="admin_classes.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Back to Classes
                            </a>
                            <a href="admin_add_teacher.php" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Add New Teacher
                            </a>
                            <button id="refreshBtn" class="btn btn-outline-info">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Our JavaScript Files -->
    <script src="../../js/admin/common.js"></script>
    <script src="../../js/admin/assign-teachers.js"></script>
    
    <script>
        // Link the two select-all checkboxes
        document.getElementById('selectAllTeachers').addEventListener('change', function() {
            document.getElementById('selectAllTeachersBottom').checked = this.checked;
        });
        
        document.getElementById('selectAllTeachersBottom').addEventListener('change', function() {
            document.getElementById('selectAllTeachers').checked = this.checked;
        });
        
        // Link the two save buttons
        document.getElementById('saveBtnBottom').addEventListener('click', function() {
            document.getElementById('saveBtn').click();
        });
        
        // Refresh button
        document.getElementById('refreshBtn').addEventListener('click', function() {
            const jsApp = window.assignTeachersApp;
            if(jsApp && typeof jsApp.loadSubjects === 'function') {
                jsApp.loadSubjects();
                jsApp.loadCurrentAssignments();
                AdminUtils.showToast('Data refreshed', 'info');
            }
        });
        
        // Make app available globally for debugging
        window.assignTeachersApp = {
            loadSubjects: function() {
                const faculty = document.getElementById('facultySelect').value;
                const semester = document.getElementById('semesterSelect').value;
                // This will be implemented in assign-teachers.js
                console.log('Refresh triggered for:', faculty, semester);
            },
            loadCurrentAssignments: function() {
                console.log('Refreshing assignments...');
            }
        };
    </script>
</body>
</html>