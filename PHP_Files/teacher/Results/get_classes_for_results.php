<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    die("Access denied");
}

$teacher_id = $_SESSION['teacher_id'];

// Get teacher's classes
$sql = "SELECT c.* FROM class c 
        WHERE c.teacher_id = ? 
        OR c.class_id = (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?)
        ORDER BY c.faculty, c.semester";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">No classes assigned to you yet.</div>';
    exit;
}
?>

<div class="row">
    <div class="col-12">
        <h4 class="mb-4">Select a Class to Enter Results</h4>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php while ($class = $result->fetch_assoc()): ?>
        <div class="col">
            <div class="card class-card h-100" 
                 data-class-id="<?php echo $class['class_id']; ?>"
                 data-faculty="<?php echo htmlspecialchars($class['faculty']); ?>"
                 data-semester="<?php echo $class['semester']; ?>">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?php echo htmlspecialchars($class['faculty']); ?></h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Semester <?php echo $class['semester']; ?></h6>
                    <p class="card-text">
                        <small>Class ID: <?php echo $class['class_id']; ?></small><br>
                        <small>Status: 
                            <span class="badge bg-<?php echo $class['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo $class['status']; ?>
                            </span>
                        </small>
                    </p>
                </div>
                <div class="card-footer bg-transparent border-top">
                    <button class="btn btn-primary w-100">
                        <i class="bi bi-arrow-right"></i> Select Class
                    </button>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>