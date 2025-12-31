<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$subject_name = isset($_GET['subject_name']) ? $_GET['subject_name'] : 'Subject';

if (!$class_id || !$subject_id) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit();
}

// Get subject details
$subject_sql = "SELECT * FROM subject WHERE subject_id = ?";
$subject_stmt = $connection->prepare($subject_sql);
$subject_stmt->bind_param("i", $subject_id);
$subject_stmt->execute();
$subject_result = $subject_stmt->get_result();
$subject_data = $subject_result->fetch_assoc();

if (!$subject_data) {
    echo '<div class="alert alert-danger">Subject not found</div>';
    exit();
}

// Get class details
$class_sql = "SELECT * FROM class WHERE class_id = ?";
$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("i", $class_id);
$class_stmt->execute();
$class_result = $class_stmt->get_result();
$class_data = $class_result->fetch_assoc();

// Get students in this class
$students_sql = "SELECT student_id, student_name, class_id FROM student WHERE class_id = ? ORDER BY student_id";
$students_stmt = $connection->prepare($students_sql);
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();

if ($students_result->num_rows === 0) {
    echo '<div class="alert alert-warning">No students found in this class</div>';
    exit();
}

// Check for existing marks
$existing_marks = [];
$marks_sql = "SELECT student_id, marks_obtained, total_marks, percentage, grade, verification_status 
              FROM result 
              WHERE subject_id = ? AND class_id = ? AND entered_by_teacher_id = ?";
$marks_stmt = $connection->prepare($marks_sql);
$marks_stmt->bind_param("iii", $subject_id, $class_id, $_SESSION['teacher_id']);
$marks_stmt->execute();
$marks_result = $marks_stmt->get_result();

while ($row = $marks_result->fetch_assoc()) {
    $existing_marks[$row['student_id']] = $row;
}

// Start output
echo '<div class="card">';
echo '<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">';
echo '<div>';
echo '<h5 class="mb-0"><i class="bi bi-pencil-square"></i> Enter Marks</h5>';
echo '<small class="opacity-75">' . htmlspecialchars($subject_name) . ' | ' . 
     htmlspecialchars($class_data['faculty'] ?? 'Class') . ' - Semester ' . ($class_data['semester'] ?? '') . '</small>';
echo '</div>';
echo '<button class="btn btn-sm btn-light" onclick="loadClassSubjects(' . $class_id . ', \'' . htmlspecialchars($class_data['faculty'] ?? '') . '\', ' . ($class_data['semester'] ?? 0) . ')">';
echo '<i class="bi bi-arrow-left"></i> Back to Subjects';
echo '</button>';
echo '</div>';

echo '<div class="card-body">';

// Instructions
echo '<div class="alert alert-info mb-4">';
echo '<h6><i class="bi bi-info-circle"></i> Instructions:</h6>';
echo '<ul class="mb-0">';
echo '<li>Enter marks for each student (0-100)</li>';
echo '<li>Grade will be calculated automatically</li>';
echo '<li>Click "Save Marks" to submit for verification</li>';
echo '<li>Status colors: <span class="badge bg-warning">Pending</span> <span class="badge bg-success">Verified</span> <span class="badge bg-danger">Rejected</span></li>';
echo '</ul>';
echo '</div>';

// Marks form
echo '<form id="marksForm" onsubmit="return saveAllMarks(event, ' . $class_id . ', ' . $subject_id . ', \'' . htmlspecialchars($subject_name) . '\')">';
echo '<input type="hidden" name="class_id" value="' . $class_id . '">';
echo '<input type="hidden" name="subject_id" value="' . $subject_id . '">';
echo '<input type="hidden" name="teacher_id" value="' . $_SESSION['teacher_id'] . '">';

echo '<div class="table-responsive">';
echo '<table class="table table-bordered table-hover">';
echo '<thead class="table-light">';
echo '<tr>';
echo '<th width="50">#</th>';
echo '<th>Student ID</th>';
echo '<th>Student Name</th>';
echo '<th width="150">Marks (0-100)</th>';
echo '<th width="100">Grade</th>';
echo '<th width="120">Status</th>';
echo '<th width="100">Actions</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

$counter = 1;
while ($student = $students_result->fetch_assoc()) {
    $existing = $existing_marks[$student['student_id']] ?? null;
    
    echo '<tr>';
    echo '<td>' . $counter++ . '</td>';
    echo '<td><strong>' . htmlspecialchars($student['student_id']) . '</strong></td>';
    echo '<td>' . htmlspecialchars($student['student_name']) . '</td>';  // Changed from full_name to student_name
    
    // Marks input
    echo '<td>';
    echo '<div class="input-group">';
    echo '<input type="number" 
                 class="form-control marks-input" 
                 name="marks[' . $student['student_id'] . ']" 
                 value="' . ($existing['marks_obtained'] ?? '') . '" 
                 min="0" max="100" 
                 step="0.01"
                 onchange="calculateGrade(this)" 
                 required>';
    echo '<span class="input-group-text">/100</span>';
    echo '</div>';
    echo '<div class="form-text"><small>Enter marks (0-100)</small></div>';
    echo '</td>';
    
    // Grade display
    echo '<td>';
    if ($existing) {
        echo '<span class="badge grade-badge grade-' . str_replace('+', 'plus', $existing['grade']) . '">' . $existing['grade'] . '</span>';
    } else {
        echo '<span class="badge bg-secondary" id="grade-' . $student['student_id'] . '">--</span>';
    }
    echo '</td>';
    
    // Status
    echo '<td>';
    if ($existing) {
        $status_class = '';
        if ($existing['verification_status'] == 'verified') $status_class = 'bg-success';
        elseif ($existing['verification_status'] == 'rejected') $status_class = 'bg-danger';
        else $status_class = 'bg-warning';
        
        echo '<span class="badge ' . $status_class . '">';
        echo ucfirst($existing['verification_status']);
        echo '</span>';
    } else {
        echo '<span class="badge bg-light text-dark">Not Entered</span>';
    }
    echo '</td>';
    
    // Actions
    echo '<td>';
    if ($existing) {
        echo '<button type="button" class="btn btn-sm btn-outline-info" 
                onclick="viewStudentMarks(' . $student['student_id'] . ', ' . $subject_id . ')">
                <i class="bi bi-eye"></i>
              </button>';
    }
    echo '</td>';
    
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';

// Action buttons
echo '<div class="mt-4 border-top pt-3">';
echo '<div class="d-flex justify-content-between">';
echo '<div>';
echo '<button type="button" class="btn btn-secondary" 
        onclick="loadClassSubjects(' . $class_id . ', \'' . htmlspecialchars($class_data['faculty'] ?? '') . '\', ' . ($class_data['semester'] ?? 0) . ')">
        <i class="bi bi-x-circle"></i> Cancel
      </button>';
echo '</div>';
echo '<div>';
echo '<button type="button" class="btn btn-warning me-2" onclick="calculateAllGrades()">
        <i class="bi bi-calculator"></i> Calculate All Grades
      </button>';
echo '<button type="submit" class="btn btn-success" id="saveBtn">
        <i class="bi bi-check-circle"></i> Save All Marks
      </button>';
echo '</div>';
echo '</div>';
echo '</div>';

echo '</form>';
echo '</div>'; // card-body
echo '</div>'; // card

// Include JavaScript functions
?>
<script>
// Calculate grade based on marks
// Calculate grade based on marks
function calculateGrade(input) {
    const marks = parseFloat(input.value);
    if (isNaN(marks) || marks < 0 || marks > 100) return;
    
    const studentId = input.name.match(/\[(.*?)\]/)[1];
    const grade = getGradeFromMarks(marks);
    
    // Update grade display
    const gradeBadge = document.getElementById('grade-' + studentId);
    if (gradeBadge) {
        gradeBadge.textContent = grade;
        
        // Remove all grade classes and add the correct one
        gradeBadge.className = 'badge grade-badge';
        const gradeClass = 'grade-' + grade.replace('+', 'plus');
        gradeBadge.classList.add(gradeClass);
    }
}

// Get grade from marks
function getGradeFromMarks(marks) {
    if (marks >= 90) return 'A+';
    if (marks >= 80) return 'A';
    if (marks >= 70) return 'B+';
    if (marks >= 60) return 'B';
    if (marks >= 50) return 'C+';
    if (marks >= 40) return 'C';
    return 'F';
}

// Calculate all grades
function calculateAllGrades() {
    document.querySelectorAll('.marks-input').forEach(input => {
        if (input.value) calculateGrade(input);
    });
}

// View student marks details
function viewStudentMarks(studentId, subjectId) {
    alert('Student ID: ' + studentId + '\nSubject ID: ' + subjectId + '\n\nView feature coming soon...');
}

// Save all marks
// Save all marks - ADD return false
function saveAllMarks(event, classId, subjectId, subjectName) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    
    // Disable button and show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Saving...';
    
    // Validate all marks
    let isValid = true;
    document.querySelectorAll('.marks-input').forEach(input => {
        const marks = parseFloat(input.value);
        if (isNaN(marks) || marks < 0 || marks > 100) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        alert('Please enter valid marks (0-100) for all students.');
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
        return false; // ADD THIS
    }
    
    // Submit via AJAX
    fetch('save_marks.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Marks saved successfully! Status: ' + data.status);
            
            // Update status badges
            document.querySelectorAll('[id^="grade-"]').forEach(badge => {
                if (badge.textContent !== '--') {
                    badge.parentElement.nextElementSibling.innerHTML = 
                        '<span class="badge bg-warning">Pending</span>';
                }
            });
            
            // Reload after delay
            setTimeout(() => {
                loadClassSubjects(classId, '<?php echo htmlspecialchars($class_data['faculty'] ?? ''); ?>', <?php echo $class_data['semester'] ?? 0; ?>);
            }, 2000);
        } else {
            showAlert('danger', 'Error: ' + data.message);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        showAlert('danger', 'Network error: ' + error.message);
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    });
    
    return false; // ADD THIS - prevents form submission
}

// Show alert
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const cardBody = document.querySelector('.card-body');
    if (cardBody) {
        cardBody.insertBefore(alertDiv, cardBody.firstChild);
    }
}
</script>

// Add this CSS right before the closing </script> tag in get_students_for_marks.php
?>
<style>
.grade-badge {
    font-size: 0.85rem;
    padding: 4px 10px;
    border-radius: 4px;
}
.grade-Aplus { background-color: #28a745; color: white; }
.grade-A { background-color: #20c997; color: white; }
.grade-Bplus { background-color: #ffc107; color: black; }
.grade-B { background-color: #fd7e14; color: white; }
.grade-Cplus { background-color: #6f42c1; color: white; }
.grade-C { background-color: #e83e8c; color: white; }
.grade-F { background-color: #dc3545; color: white; }
</style>