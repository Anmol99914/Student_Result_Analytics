<!-- add_student.php -->
<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: ../teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Get teacher's assigned classes
$sql = "SELECT c.* FROM class c 
        WHERE c.teacher_id = ? 
        OR c.class_id = (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?)";
$stmt = $connection->prepare($sql);
$stmt->bind_param("ii", $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher_classes = $result->fetch_all(MYSQLI_ASSOC);

// Get semesters
$semester_result = $connection->query("SELECT * FROM semester");
$semesters = $semester_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student - Teacher Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
    <!-- ADD THIS SCRIPT IN HEAD TO DEFINE FUNCTIONS EARLY -->
    <script>
        // Define the function BEFORE the form loads
        function handleFormSubmit(event) {
            console.log("Form submission intercepted - TEST");
            event.preventDefault(); // This stops normal form submission
            
            // Show we're intercepting
            alert("Form is being handled by JavaScript - This should work!");
            
            // Don't submit the form normally
            return false;
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="form-container">
            <h2 class="mb-4 text-center">📝 Add New Student</h2>
            
            <?php if (empty($teacher_classes)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    No classes assigned to you. You need to have at least one class to add students.
                </div>
            <?php else: ?>
            
            <!-- SIMPLE FORM WITH NO ACTION - JavaScript will handle it -->
            <form id="addStudentForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="student_name" class="form-label">Student Name *</label>
                        <input type="text" class="form-control" id="student_name" name="student_name" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" pattern="[0-9]{10}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="class_id" class="form-label">Select Class *</label>
                        <select class="form-select" id="class_id" name="class_id" required>
                            <option value="">-- Select Class --</option>
                            <?php foreach ($teacher_classes as $class): ?>
                                <option value="<?= $class['class_id'] ?>">
                                    <?= htmlspecialchars($class['faculty']) ?> - Semester <?= $class['semester'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="semester_id" class="form-label">Semester *</label>
                        <select class="form-select" id="semester_id" name="semester_id" required>
                            <option value="">-- Select Semester --</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?= $semester['semester_id'] ?>">
                                    <?= htmlspecialchars($semester['semester_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="student_id" class="form-label">Student ID (Optional)</label>
                    <input type="text" class="form-control" id="student_id" name="student_id" placeholder="e.g., BIT001">
                    <small class="text-muted">Leave blank to auto-generate</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="button" id="submitBtn" class="btn btn-primary">Add Student</button>
                </div>
            </form>
            
            <?php endif; ?>
            
            <div class="mt-4 d-flex justify-content-between">
                <div>
                    <button id="addAnotherBtn" class="btn btn-outline-success">
                        <i class="bi bi-plus-circle"></i> Add Another Student
                    </button>
                </div>
                <div>
                    <button onclick="window.parent.showHome(); return false;" class="btn btn-outline-dark">
                        ← Back to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Page loaded - initializing form");
            
            // 1. Setup auto-generate student ID
            document.getElementById('class_id').addEventListener('change', function() {
                const classId = this.value;
                const studentIdField = document.getElementById('student_id');
                
                if (classId && !studentIdField.value) {
                    fetch('get_class_details.php?class_id=' + classId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.faculty) {
                                const random = Math.floor(100 + Math.random() * 900);
                                const facultyCode = data.faculty.substring(0, 3).toUpperCase();
                                studentIdField.value = facultyCode + random;
                            }
                        });
                }
            });
            
            // 2. Setup submit button (NOT a submit type button)
            document.getElementById('submitBtn').addEventListener('click', function() {
                console.log("Submit button clicked");
                submitStudentForm();
            });
            
            // 3. Setup Add Another button
            document.getElementById('addAnotherBtn').addEventListener('click', function() {
                document.getElementById('addStudentForm').reset();
                document.getElementById('student_name').focus();
            });
            
            // 4. Also prevent form submission via Enter key
            document.getElementById('addStudentForm').addEventListener('submit', function(event) {
                console.log("Form submit event - preventing default");
                event.preventDefault();
                return false;
            });
            
            console.log("Form initialized successfully");
        });
        
        // Function to submit the form via AJAX
        function submitStudentForm() {
            console.log("Submitting form via AJAX");
            
            // Get form data
            const form = document.getElementById('addStudentForm');
            const formData = new FormData(form);
            
            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...';
            submitBtn.disabled = true;
            
            // Submit via fetch
            fetch('Students/process_add_student.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("Got response");
                return response.json();
            })
            .then(data => {
                console.log("Parsed JSON:", data);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    // Show success message
                    alert("✅ Student added successfully!\n\nStudent ID: " + data.student_id + "\nPassword: " + data.password);
                    
                    // Reset form
                    form.reset();
                    
                    // Focus on first field
                    document.getElementById('student_name').focus();
                } else {
                    // Show error
                    alert("❌ Error: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                alert("❌ Network error: " + error.message);
            });
        }
    </script>
</body>
</html>