<?php
// File: PHP_Files/student/pages/results.php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';

$student_id = $_SESSION['student_username'];
$student_name = $_SESSION['student_name'];

// Check payment status before showing results
$payment_check = checkPaymentStatus($student_id);

// Get all semesters for filter
$semesters = getStudentSemesters($student_id);

// Get selected semester
$selected_semester = $_GET['semester'] ?? $_SESSION['student_semester'] ?? 1;

// Fetch results
$results = getStudentResults($student_id, $selected_semester);
$stats = calculateResultStats($results);

// Helper Functions (in separate file later)
function checkPaymentStatus($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT payment_status, amount_paid, total_amount FROM payment WHERE student_id = ? ORDER BY payment_date DESC LIMIT 1");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: ['payment_status' => 'Unpaid', 'amount_paid' => 0, 'total_amount' => 0];
}

function getStudentSemesters($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT DISTINCT r.semester_id, s.semester_name FROM result r JOIN semester s ON r.semester_id = s.semester_id WHERE r.student_id = ? AND r.status = 'published' ORDER BY r.semester_id");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getStudentResults($student_id, $semester_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT r.marks_obtained, r.total_marks, r.percentage, r.grade, r.published_date, s.subject_name, s.subject_code, s.credits FROM result r JOIN subject s ON r.subject_id = s.subject_id WHERE r.student_id = ? AND r.semester_id = ? AND r.status = 'published' ORDER BY s.subject_code");
    $stmt->bind_param("si", $student_id, $semester_id);
    $stmt->execute();
    return $stmt->get_result();
}

function calculateResultStats($results) {
    $stats = ['total_credits' => 0, 'earned_credits' => 0, 'total_grade_points' => 0, 'total_subjects' => 0, 'passed_subjects' => 0, 'failed_subjects' => 0];
    $grade_points = ['A+' => 4.0, 'A' => 3.7, 'B+' => 3.3, 'B' => 3.0, 'C+' => 2.7, 'C' => 2.3, 'F' => 0.0];
    
    if ($results->num_rows > 0) {
        while($row = $results->fetch_assoc()) {
            $stats['total_subjects']++;
            $stats['total_credits'] += $row['credits'];
            if ($row['grade'] !== 'F') {
                $stats['passed_subjects']++;
                $stats['earned_credits'] += $row['credits'];
                $stats['total_grade_points'] += ($grade_points[$row['grade']] ?? 0) * $row['credits'];
            } else $stats['failed_subjects']++;
        }
        $results->data_seek(0);
    }
    $stats['gpa'] = $stats['earned_credits'] > 0 ? $stats['total_grade_points'] / $stats['earned_credits'] : 0;
    return $stats;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <div><h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Academic Results</h4><small class="opacity-75">View your semester-wise results</small></div>
                    <span class="badge bg-<?php echo $payment_check['payment_status'] === 'Paid' ? 'success' : 'warning'; ?> fs-6">
                        <i class="bi bi-<?php echo $payment_check['payment_status'] === 'Paid' ? 'check-circle' : 'exclamation-triangle'; ?> me-1"></i>
                        <?php echo $payment_check['payment_status']; ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <?php if($payment_check['payment_status'] !== 'Paid'): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-2">Fee Payment Required</h5>
                        <p class="mb-2">Your results are restricted due to pending fees.</p>
                        <div class="d-flex align-items-center">
                            <div class="me-4"><small class="text-muted">Paid:</small><div class="h5 mb-0">NPR <?php echo number_format($payment_check['amount_paid'], 2); ?></div></div>
                            <div class="me-4"><small class="text-muted">Total:</small><div class="h5 mb-0">NPR <?php echo number_format($payment_check['total_amount'], 2); ?></div></div>
                            <div class="ms-auto"><a href="payments.php" class="btn btn-primary"><i class="bi bi-credit-card me-1"></i>Pay Now</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Semester Filter -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-filter me-2"></i>Filter by Semester</h5></div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <?php while($sem = $semesters->fetch_assoc()): ?>
                        <a href="?semester=<?php echo $sem['semester_id']; ?>" class="btn btn-outline-primary <?php echo $selected_semester == $sem['semester_id'] ? 'active' : ''; ?>">
                            <?php echo $sem['semester_name']; ?>
                        </a>
                        <?php endwhile; ?>
                        <?php if($semesters->num_rows === 0): ?>
                        <div class="alert alert-info w-100 mb-0"><i class="bi bi-info-circle me-2"></i>No published results found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="text-white-50 mb-2">Current GPA</h6>
                    <div class="display-4 fw-bold mb-1"><?php echo number_format($stats['gpa'], 2); ?></div>
                    <small>Out of 4.0</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Result Details - Semester <?php echo $selected_semester; ?></h5>
                    <div class="d-flex gap-2">
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i> Print</button>
                        <button onclick="downloadResultPDF()" class="btn btn-outline-success btn-sm"><i class="bi bi-download me-1"></i> Download</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if($payment_check['payment_status'] === 'Paid'): ?>
                        <?php if($results->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr><th>#</th><th>Subject Code</th><th>Subject Name</th><th class="text-center">Credits</th><th class="text-center">Obtained</th><th class="text-center">Total</th><th class="text-center">%</th><th class="text-center">Grade</th><th class="text-center">Status</th></tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; while($row = $results->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td><strong><?php echo htmlspecialchars($row['subject_code']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($row['subject_name']); ?></td>
                                        <td class="text-center"><?php echo $row['credits']; ?></td>
                                        <td class="text-center"><strong><?php echo $row['marks_obtained']; ?></strong></td>
                                        <td class="text-center"><?php echo $row['total_marks']; ?></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $row['percentage'] >= 80 ? 'success' : ($row['percentage'] >= 60 ? 'warning' : 'danger'); ?>"><?php echo number_format($row['percentage'], 2); ?>%</span></td>
                                        <td class="text-center"><span class="badge bg-<?php echo in_array($row['grade'], ['A+','A']) ? 'success' : (in_array($row['grade'], ['B+','B']) ? 'primary' : ($row['grade'] === 'F' ? 'danger' : 'warning')); ?> fs-6"><?php echo $row['grade']; ?></span></td>
                                        <td class="text-center"><span class="badge bg-<?php echo $row['grade'] === 'F' ? 'danger' : 'success'; ?>"><?php echo $row['grade'] === 'F' ? 'Failed' : 'Passed'; ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Totals:</strong></td>
                                        <td class="text-center"><strong><?php echo $stats['total_credits']; ?></strong></td>
                                        <td colspan="2" class="text-center"><strong><?php echo $stats['passed_subjects']; ?> Passed / <?php echo $stats['failed_subjects']; ?> Failed</strong></td>
                                        <td class="text-center"><strong>GPA: <?php echo number_format($stats['gpa'], 2); ?></strong></td>
                                        <td colspan="2" class="text-center"><strong>Credits: <?php echo $stats['earned_credits']; ?>/<?php echo $stats['total_credits']; ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Summary Cards -->
                        <div class="row mt-4">
                            <div class="col-md-3 col-6 mb-3"><div class="card border-success"><div class="card-body text-center"><h6 class="text-muted">GPA</h6><div class="display-5 fw-bold text-success"><?php echo number_format($stats['gpa'], 2); ?></div><small>Out of 4.0</small></div></div></div>
                            <div class="col-md-3 col-6 mb-3"><div class="card border-primary"><div class="card-body text-center"><h6 class="text-muted">Pass Rate</h6><div class="display-5 fw-bold text-primary"><?php echo $stats['total_subjects'] > 0 ? round(($stats['passed_subjects']/$stats['total_subjects'])*100) : 0; ?>%</div><small>Subjects Passed</small></div></div></div>
                            <div class="col-md-3 col-6 mb-3"><div class="card border-info"><div class="card-body text-center"><h6 class="text-muted">Credits</h6><div class="display-5 fw-bold text-info"><?php echo $stats['earned_credits']; ?></div><small>Earned</small></div></div></div>
                            <div class="col-md-3 col-6 mb-3"><div class="card border-<?php echo $stats['failed_subjects'] > 0 ? 'warning' : 'success'; ?>"><div class="card-body text-center"><h6 class="text-muted">Status</h6><div class="display-5 fw-bold text-<?php echo $stats['failed_subjects'] > 0 ? 'warning' : 'success'; ?>"><?php echo $stats['failed_subjects'] > 0 ? 'Improve' : 'Excellent'; ?></div><small><?php echo $stats['failed_subjects']; ?> to improve</small></div></div></div>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-5"><i class="bi bi-clipboard-x display-1 text-muted mb-3"></i><h4>No Results Found</h4><p class="text-muted">No published results available.</p></div>
                        <?php endif; ?>
                    <?php else: ?>
                    <div class="text-center py-5"><i class="bi bi-lock display-1 text-warning mb-3"></i><h4>Results Locked</h4><p class="text-muted">Complete fee payment to view results.</p><div class="mt-4"><a href="payments.php" class="btn btn-primary btn-lg"><i class="bi bi-credit-card me-2"></i>Go to Payments</a></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/results.js"></script>