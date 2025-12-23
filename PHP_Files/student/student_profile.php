<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit();
}

// Fetch student info with class, semester, and teacher details
$student_id = $_SESSION['student_username'];
$stmt = $connection->prepare("
    SELECT 
        s.*, 
        c.faculty, 
        c.semester as class_semester,
        c.teacher_id as class_teacher_id,
        sem.semester_name,
        t.name as teacher_name,
        t.email as teacher_email
    FROM student s 
    LEFT JOIN class c ON s.class_id = c.class_id 
    LEFT JOIN semester sem ON s.semester_id = sem.semester_id 
    LEFT JOIN teacher t ON c.teacher_id = t.teacher_id 
    WHERE s.student_id = ?
");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Format dates nicely
$created_date = date("F j, Y", strtotime($student['created_at']));
$updated_date = date("F j, Y, g:i a", strtotime($student['updated_at']));
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">
                <i class="bi bi-person-circle me-2"></i>My Profile
            </h2>
            <p class="text-muted mb-0">View and manage your personal information</p>
        </div>
        <span class="badge bg-<?php echo ($student['is_active'] == 1) ? 'success' : 'danger'; ?> fs-6 py-2 px-3">
            <i class="bi bi-circle-fill me-1"></i>
            <?php echo ($student['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
        </span>
    </div>

    <!-- Student Basic Info Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-primary"><?php echo htmlspecialchars($student['student_name']); ?></h4>
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <div>
                            <small class="text-muted">Student ID</small>
                            <div class="fw-bold"><?php echo htmlspecialchars($student['student_id']); ?></div>
                        </div>
                        <div>
                            <small class="text-muted">Faculty</small>
                            <div class="fw-bold">
                                <?php 
                                echo !empty($student['faculty']) 
                                    ? htmlspecialchars($student['faculty']) 
                                    : '<span class="text-muted">Not assigned</span>';
                                ?>
                            </div>
                        </div>
                        <div>
                            <small class="text-muted">Semester</small>
                            <div class="fw-bold">
                                <?php 
                                echo !empty($student['semester_name']) 
                                    ? htmlspecialchars($student['semester_name']) 
                                    : '<span class="text-muted">Not assigned</span>';
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <div class="d-inline-block text-center p-3 rounded bg-light">
                        <i class="bi bi-person-bounding-box fs-1 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Personal Information -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2 text-primary"></i>Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 40%" class="text-muted">Full Name</th>
                                <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Student ID</th>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo htmlspecialchars($student['student_id']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Email Address</th>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                                       class="text-decoration-none">
                                        <i class="bi bi-envelope me-1"></i>
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Phone Number</th>
                                <td>
                                    <i class="bi bi-phone me-1"></i>
                                    <?php echo !empty($student['phone_number']) 
                                        ? htmlspecialchars($student['phone_number']) 
                                        : '<span class="text-muted">Not provided</span>'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Account Status</th>
                                <td>
                                    <span class="badge bg-<?php echo ($student['is_active'] == 1) ? 'success' : 'danger'; ?>">
                                        <?php echo ($student['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Academic Information -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-mortarboard me-2 text-primary"></i>Academic Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 40%" class="text-muted">Faculty</th>
                                <td>
                                    <?php 
                                    if(!empty($student['faculty'])) {
                                        echo '<span class="fw-bold">' . htmlspecialchars($student['faculty']) . '</span>';
                                    } else {
                                        echo '<span class="text-muted">Not assigned</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Semester</th>
                                <td>
                                    <?php 
                                    if(!empty($student['semester_name'])) {
                                        echo htmlspecialchars($student['semester_name']) . 
                                             ' <small class="text-muted">(Semester ' . htmlspecialchars($student['semester_id']) . ')</small>';
                                    } else {
                                        echo 'Semester ' . htmlspecialchars($student['semester_id']);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Class Teacher</th>
                                <td>
                                    <?php 
                                    if(!empty($student['teacher_name'])) {
                                        echo '<div class="fw-bold">' . htmlspecialchars($student['teacher_name']) . '</div>';
                                        if(!empty($student['teacher_email'])) {
                                            echo '<small class="text-muted">' . htmlspecialchars($student['teacher_email']) . '</small>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">Not assigned</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Joined Date</th>
                                <td>
                                    <i class="bi bi-calendar-check me-1"></i>
                                    <?php echo $created_date; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted">Last Updated</th>
                                <td>
                                    <i class="bi bi-clock-history me-1"></i>
                                    <?php echo $updated_date; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-2">
                        <i class="bi bi-award fs-1"></i>
                    </div>
                    <h4 class="mb-1">--</h4>
                    <p class="text-muted mb-0">Overall GPA</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-2">
                        <i class="bi bi-check-circle fs-1"></i>
                    </div>
                    <h4 class="mb-1">--</h4>
                    <p class="text-muted mb-0">Completed Subjects</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-2">
                        <i class="bi bi-credit-card fs-1"></i>
                    </div>
                    <h4 class="mb-1">Fully Paid</h4>
                    <p class="text-muted mb-0">Fee Status</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Important Note -->
    <div class="alert alert-info border-0 shadow-sm">
        <div class="d-flex">
            <div class="me-3">
                <i class="bi bi-shield-check fs-4"></i>
            </div>
            <div>
                <h6 class="alert-heading mb-1">Privacy & Security</h6>
                <p class="mb-0 small">
                    Your personal information is secure. If you need to update any details, 
                    please contact the administration office. For security reasons, passwords 
                    cannot be viewed or changed from this interface.
                </p>
            </div>
        </div>
    </div>

</div>

<style>
.card {
    border-radius: 10px;
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-2px);
}
.table th {
    font-weight: 500;
}
.badge {
    font-weight: 500;
}
.text-muted {
    color: #6c757d !important;
}
</style>