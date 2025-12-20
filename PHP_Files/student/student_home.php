<?php
session_start();
include('../../config.php');

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit();
}

// Get student details from database
$student_id = $_SESSION['student_username'];

$stmt = $connection->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<div class="container-fluid">

    <!-- Welcome Section -->
    <div class="mb-4">
        <h2>Welcome, <?php echo htmlspecialchars($student['student_name']); ?> 👋</h2>
        <p class="text-muted">
            This dashboard provides a quick overview of your academic and payment status.
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4">

        <!-- Academic Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-mortarboard"></i> Academic Summary
                    </h5>
                    <hr>
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($student['class_id']); ?></p>
                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($student['semester_id']); ?></p>
                    <p><strong>Result Status:</strong> <span class="text-success">Published</span></p>
                </div>
            </div>
        </div>

        <!-- Payment Summary (hardcoded for now, can fetch from payment table later) -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-credit-card"></i> Payment Summary
                    </h5>
                    <hr>
                    <p><strong>Total Fee:</strong> NPR 45,000</p>
                    <p><strong>Paid Amount:</strong> NPR 45,000</p>
                    <p>
                        <strong>Status:</strong>
                        <span class="badge bg-success">Fully Paid</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Summary -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="bi bi-person-circle"></i> Profile Summary
                    </h5>
                    <hr>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($student['student_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone_number']); ?></p>
                </div>
            </div>
        </div>

    </div>

    <!-- Important Note -->
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i>
        Detailed academic results are accessible only after full payment confirmation.
    </div>

</div>
