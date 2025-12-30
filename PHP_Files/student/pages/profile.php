<?php
// File: PHP_Files/student/pages/profile.php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];

// Fetch student data with class/semester info
$stmt = $connection->prepare("
    SELECT s.*, c.class_name, sem.semester_name, 
           DATE_FORMAT(s.admission_date, '%d %M %Y') as formatted_date,
           DATE_FORMAT(s.date_of_birth, '%d %M %Y') as dob_formatted
    FROM student s
    LEFT JOIN class c ON s.class_id = c.class_id
    LEFT JOIN semester sem ON s.semester_id = sem.semester_id
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found!</div>';
    exit;
}
?>

<div class="container-fluid">
    <!-- Profile Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-lg bg-white bg-opacity-25 p-3 rounded-circle">
                                <i class="bi bi-person-circle fs-1"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h1 class="card-title mb-1"><?php echo htmlspecialchars($student['student_name']); ?></h1>
                            <p class="card-text opacity-75 mb-0">
                                <i class="bi bi-award me-1"></i>
                                <?php echo htmlspecialchars($student['class_name']); ?> • 
                                <?php echo htmlspecialchars($student['semester_name']); ?> Semester
                            </p>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-white text-primary fs-6 p-2">
                                ID: <?php echo htmlspecialchars($student_id); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Profile Content -->
    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-person-vcard text-primary me-2"></i>
                        Personal Information
                    </h5>
                    <button class="btn btn-sm btn-outline-primary" disabled>
                        <i class="bi bi-pencil me-1"></i> Edit (Contact Admin)
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Full Name</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($student['student_name']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Date of Birth</label>
                            <div class="form-control bg-light">
                                <?php echo $student['dob_formatted'] ?? 'Not specified'; ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Email Address</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($student['email']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Phone Number</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($student['phone']); ?></div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small">Address</label>
                            <div class="form-control bg-light" style="min-height: 80px;">
                                <?php echo htmlspecialchars($student['address']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Academic Information -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-mortarboard text-success me-2"></i>
                        Academic Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Student ID</span>
                            <strong><?php echo htmlspecialchars($student_id); ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Class</span>
                            <strong><?php echo htmlspecialchars($student['class_name']); ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Semester</span>
                            <strong><?php echo htmlspecialchars($student['semester_name']); ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Admission Date</span>
                            <strong><?php echo $student['formatted_date']; ?></strong>
                        </div>
                        <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                            <span class="text-muted">Status</span>
                            <span class="badge bg-<?php echo ($student['is_active'] == 1) ? 'success' : 'danger'; ?>">
                                <?php echo ($student['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Account Info -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="mb-3">
                            <i class="bi bi-shield-check text-warning me-2"></i>
                            Account Security
                        </h6>
                        <div class="alert alert-warning small">
                            <i class="bi bi-info-circle me-2"></i>
                            For password change or profile updates, please contact the administration office.
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="bi bi-key me-2"></i> Change Password
                            </button>
                            <a href="mailto:admin@college.edu" class="btn btn-outline-primary">
                                <i class="bi bi-envelope me-2"></i> Contact Admin
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Additional Info -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle text-info me-2"></i>
                        Additional Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card border-info">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar-check fs-1 text-info mb-3"></i>
                                    <h5>Attendance</h5>
                                    <div class="display-4 fw-bold text-info">85%</div>
                                    <small class="text-muted">Overall Attendance Rate</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-success">
                                <div class="card-body text-center">
                                    <i class="bi bi-graph-up fs-1 text-success mb-3"></i>
                                    <h5>Performance</h5>
                                    <div class="display-4 fw-bold text-success">3.8</div>
                                    <small class="text-muted">Current GPA</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card border-primary">
                                <div class="card-body text-center">
                                    <i class="bi bi-cash-coin fs-1 text-primary mb-3"></i>
                                    <h5>Fee Status</h5>
                                    <div class="display-4 fw-bold text-primary">Paid</div>
                                    <small class="text-muted">Last payment: 15 Nov 2024</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>