<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    die("Access denied");
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$faculty = isset($_GET['faculty']) ? $_GET['faculty'] : '';
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

if (!$class_id || !$faculty) {
    die("Invalid request");
}

// First, let's see ALL subjects in the database
$sql = "SELECT * FROM subject LIMIT 50";
$result = $connection->query($sql);

if (!$result) {
    die("Database error: " . $connection->error);
}

// Check if we have any subjects at all
if ($result->num_rows === 0) {
    echo '<div class="alert alert-danger">';
    echo '<h4>No Subjects Found in Database</h4>';
    echo '<p>The subject table is empty or does not exist.</p>';
    echo '</div>';
    exit;
}

// Get all column names from the first row
$first_subject = $result->fetch_assoc();
$column_names = array_keys($first_subject);

// Reset pointer back to beginning
$result->data_seek(0);
?>

<div class="row">
    <div class="col-12">
        <h4>Select Subject for <?php echo htmlspecialchars($faculty); ?> - Semester <?php echo $semester; ?></h4>
        <p class="text-muted">Choose a subject to enter marks for students.</p>
        
        <div class="alert alert-info">
            <h5>Database Info:</h5>
            <p>Class: <?php echo htmlspecialchars($faculty); ?> (ID: <?php echo $class_id; ?>)</p>
            <p>Semester: <?php echo $semester; ?></p>
            <p>Total subjects in database: <?php echo $result->num_rows; ?></p>
            <p>Subject table columns: <?php echo implode(', ', $column_names); ?></p>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 g-4 mt-3">
    <?php while ($subject = $result->fetch_assoc()): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <?php 
                        // Try different possible name fields
                        $subject_name = 'Subject';
                        if (isset($subject['subject_name'])) {
                            $subject_name = $subject['subject_name'];
                        } elseif (isset($subject['name'])) {
                            $subject_name = $subject['name'];
                        } elseif (isset($subject['title'])) {
                            $subject_name = $subject['title'];
                        }
                        echo htmlspecialchars($subject_name);
                        ?>
                    </h5>
                </div>
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">
                        <?php if (isset($subject['subject_code'])): ?>
                            Code: <?php echo $subject['subject_code']; ?>
                        <?php elseif (isset($subject['code'])): ?>
                            Code: <?php echo $subject['code']; ?>
                        <?php else: ?>
                            ID: <?php echo $subject['subject_id']; ?>
                        <?php endif; ?>
                    </h6>
                    
                    <div class="small text-muted mb-2">
                        <?php foreach ($subject as $key => $value): ?>
                            <?php if (!in_array($key, ['subject_name', 'name', 'title', 'subject_code', 'code']) && $value): ?>
                                <div><strong><?php echo $key; ?>:</strong> <?php echo htmlspecialchars($value); ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top">
                    <button class="btn btn-success w-100" 
                            onclick="enterMarksForSubject(
                                <?php echo $class_id; ?>, 
                                <?php echo $subject['subject_id']; ?>, 
                                '<?php echo addslashes($subject_name); ?>'
                            )">
                        <i class="bi bi-pencil-square"></i> Enter Marks
                    </button>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <h5>Next Steps:</h5>
            <p>Since your subject table structure is different than expected, we need to:</p>
            <ol>
                <li>Check how subjects are linked to classes/faculties/semesters</li>
                <li>Create a proper database query to get the right subjects</li>
                <li>You might need to add a "semester" column to the subject table</li>
                <li>Or subjects might be linked through a different table</li>
            </ol>
            <button class="btn btn-primary" onclick="showAddResultForm()">
                <i class="bi bi-arrow-left"></i> Back to Classes
            </button>
        </div>
    </div>
</div>

<script>
function enterMarksForSubject(classId, subjectId, subjectName) {
    // Show a simple form for now
    document.getElementById('main-content').innerHTML = `
        <div class="card shadow">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i> Enter Marks</h5>
                <div>
                    <button class="btn btn-sm btn-light me-2" onclick="showAddResultForm()">
                        <i class="bi bi-arrow-left"></i> Back
                    </button>
                    <button class="btn btn-sm btn-light" onclick="showHome()">
                        <i class="bi bi-house"></i> Dashboard
                    </button>
                </div>
            </div>
            <div class="card-body">
                <h4>${subjectName}</h4>
                <p class="text-muted">Class ID: ${classId} | Subject ID: ${subjectId}</p>
                
                <div class="alert alert-info">
                    <h5>Feature Under Development</h5>
                    <p>The marks entry feature is being set up. Your database structure needs to be checked first.</p>
                    <p>For now, you can see that the subject selection works!</p>
                </div>
                
                <div class="mt-4">
                    <h5>What we know:</h5>
                    <ul>
                        <li>Class ID: ${classId}</li>
                        <li>Subject ID: ${subjectId}</li>
                        <li>Subject Name: ${subjectName}</li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <button class="btn btn-primary" onclick="showAddResultForm()">
                        <i class="bi bi-arrow-left"></i> Back to Subjects
                    </button>
                </div>
            </div>
        </div>
    `;
}
</script>