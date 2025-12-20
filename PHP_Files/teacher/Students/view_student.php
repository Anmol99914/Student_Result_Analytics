<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: ../teacher_login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Student ID not provided");
}

$student_id = $_GET['id'];
$teacher_id = $_SESSION['teacher_id'];

// Check if teacher has access to this student
$sql = "SELECT s.*, 
               c.faculty, 
               c.semester, 
               se.semester_name,
               t.name as teacher_name
        FROM student s
        JOIN class c ON s.class_id = c.class_id
        JOIN semester se ON s.semester_id = se.semester_id
        JOIN teacher t ON c.teacher_id = t.teacher_id
        WHERE s.student_id = ? 
        AND (c.teacher_id = ? OR c.class_id = 
            (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?))";
        
$stmt = $connection->prepare($sql);
$stmt->bind_param("sii", $student_id, $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo '<div class="alert alert-danger">Student not found or you don\'t have access.</div>';
    exit();
}

// Get student's results if any
$result_sql = "SELECT r.*, sub.subject_name 
               FROM result r 
               JOIN subject sub ON r.subject_id = sub.subject_id 
               WHERE r.student_id = ? 
               ORDER BY r.semester_id";
$result_stmt = $connection->prepare($result_sql);
$result_stmt->bind_param("s", $student_id);
$result_stmt->execute();
$results = $result_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate statistics
$total_subjects = count($results);
$total_marks = 0;
$obtained_marks = 0;
foreach ($results as $result) {
    $total_marks += $result['total_marks'];
    $obtained_marks += $result['marks_obtained'];
}
$percentage = $total_marks > 0 ? ($obtained_marks / $total_marks) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - <?php echo htmlspecialchars($student['student_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px 10px 0 0;
        }
        .info-card {
            border-left: 4px solid #0d6efd;
            padding-left: 1rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 10px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .grade-a { background-color: #d4edda; color: #155724; }
        .grade-b { background-color: #fff3cd; color: #856404; }
        .grade-c { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container-fluid mt-3">
        <!-- Header with back button -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button onclick="window.parent.showStudentManagement('list'); return false;" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Students List
            </button>
            <button onclick="window.parent.showHome(); return false;" class="btn btn-outline-dark">
                <i class="bi bi-house"></i> Dashboard
            </button>
        </div>

        <!-- Student Profile Header -->
        <div class="card shadow mb-4">
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <div style="width: 120px; height: 120px; background: rgba(255,255,255,0.2); 
                                    border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                                    margin: 0 auto; font-size: 3rem;">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h1 class="display-6"><?php echo htmlspecialchars($student['student_name']); ?></h1>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <p class="mb-1"><i class="bi bi-person-badge"></i> Student ID</p>
                                <h5><span class="badge bg-light text-dark"><?php echo htmlspecialchars($student['student_id']); ?></span></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><i class="bi bi-mortarboard"></i> Faculty</p>
                                <h5><span class="badge bg-info"><?php echo htmlspecialchars($student['faculty']); ?></span></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="mb-1"><i class="bi bi-calendar"></i> Semester</p>
                                <h5><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($student['semester_name']); ?></span></h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="status-badge">
                            <?php if ($student['is_active'] == 1): ?>
                                <span class="badge bg-success" style="font-size: 1rem;">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary" style="font-size: 1rem;">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Student Details -->
            <div class="card-body">
                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4><i class="bi bi-person-lines-fill text-primary"></i> Personal Information</h4>
                            <table class="table">
                                <tr>
                                    <th width="40%">Full Name:</th>
                                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Student ID:</th>
                                    <td>
                                        <code><?php echo htmlspecialchars($student['student_id']); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Email Address:</th>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>">
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($student['email']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Phone Number:</th>
                                    <td>
                                        <?php if ($student['phone_number']): ?>
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($student['phone_number']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not provided</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Account Status:</th>
                                    <td>
                                        <?php if ($student['is_active'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Academic Information -->
                    <div class="col-md-6">
                        <div class="info-card">
                            <h4><i class="bi bi-mortarboard-fill text-success"></i> Academic Information</h4>
                            <table class="table">
                                <tr>
                                    <th width="40%">Faculty:</th>
                                    <td><?php echo htmlspecialchars($student['faculty']); ?></td>
                                </tr>
                                <tr>
                                    <th>Semester:</th>
                                    <td>
                                        <?php echo htmlspecialchars($student['semester_name']); ?> 
                                        (Semester <?php echo $student['semester']; ?>)
                                    </td>
                                </tr>
                                <tr>
                                    <th>Class Teacher:</th>
                                    <td><?php echo htmlspecialchars($student['teacher_name']); ?></td>
                                </tr>
                                <tr>
                                    <th>Joined Date:</th>
                                    <td><?php echo date('F j, Y', strtotime($student['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Last Updated:</th>
                                    <td><?php echo date('F j, Y, g:i a', strtotime($student['updated_at'])); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Results Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="info-card">
                            <h4><i class="bi bi-trophy-fill text-warning"></i> Academic Results</h4>
                            
                            <?php if (empty($results)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> No results recorded yet for this student.
                                </div>
                            <?php else: ?>
                                
                                <!-- Statistics Cards -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="stat-card grade-a">
                                            <h3><?php echo $total_subjects; ?></h3>
                                            <p>Subjects</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card grade-b">
                                            <h3><?php echo number_format($percentage, 1); ?>%</h3>
                                            <p>Percentage</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card">
                                            <h3><?php echo $obtained_marks; ?>/<?php echo $total_marks; ?></h3>
                                            <p>Total Marks</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="stat-card <?php echo $percentage >= 80 ? 'grade-a' : ($percentage >= 60 ? 'grade-b' : 'grade-c'); ?>">
                                            <h3>
                                                <?php 
                                                if ($percentage >= 80) echo 'A';
                                                elseif ($percentage >= 60) echo 'B';
                                                elseif ($percentage >= 40) echo 'C';
                                                else echo 'F';
                                                ?>
                                            </h3>
                                            <p>Grade</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Results Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Semester</th>
                                                <th>Marks Obtained</th>
                                                <th>Total Marks</th>
                                                <th>Percentage</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($results as $result): 
                                                $subject_percentage = ($result['marks_obtained'] / $result['total_marks']) * 100;
                                                $grade = getGrade($subject_percentage);
                                                $grade_class = getGradeClass($subject_percentage);
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                                <td>Sem <?php echo $result['semester_id']; ?></td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $result['marks_obtained']; ?></span>
                                                </td>
                                                <td><?php echo $result['total_marks']; ?></td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar <?php echo $grade_class; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $subject_percentage; ?>%">
                                                            <?php echo number_format($subject_percentage, 1); ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $grade_class; ?>">
                                                        <?php echo $grade; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="mt-4">
                                <button onclick="window.parent.showAddResultForStudent('<?php echo $student_id; ?>'); return false;" 
                                        class="btn btn-primary me-2">
                                    <i class="bi bi-trophy"></i> Enter Results
                                </button>
                                <button onclick="window.parent.showStudentManagement('list'); return false;" 
                                        class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to List
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Helper functions for grades
    function getGrade($percentage) {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        return 'F';
    }
    
    function getGradeClass($percentage) {
        if ($percentage >= 80) return 'bg-success';
        if ($percentage >= 60) return 'bg-warning text-dark';
        if ($percentage >= 40) return 'bg-info';
        return 'bg-danger';
    }
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>