<?php
session_start();
include('config.php');

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit();
}

// Fetch student info
$student_id = $_SESSION['student_username'];
$stmt = $conn->prepare("SELECT * FROM student WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<div class="container-fluid">

    <h2 class="mb-4">
        <i class="bi bi-person"></i> My Profile
    </h2>

    <div class="card shadow-sm p-4 mb-4">
        <h5 class="card-title mb-3">Student Details</h5>
        <hr>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Student ID:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['student_id']); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Name:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['student_name']); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Email:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['email']); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Class:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['class_id']); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Semester:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['semester_id']); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Phone:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['phone_number']); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-3"><strong>Old Numeric ID:</strong></div>
            <div class="col-md-9"><?php echo htmlspecialchars($student['old_numeric_id']); ?></div>
        </div>

    </div>

</div>
