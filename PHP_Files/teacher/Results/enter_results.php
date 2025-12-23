<?php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: ../teacher_login.php");
    exit();
}

$teacher_id = $_SESSION['teacher_id'];

// Get teacher's classes
$class_sql = "SELECT c.* FROM class c 
              WHERE c.teacher_id = ? 
              OR c.class_id = (SELECT assigned_class_id FROM teacher WHERE teacher_id = ?)";
$class_stmt = $connection->prepare($class_sql);
$class_stmt->bind_param("ii", $teacher_id, $teacher_id);
$class_stmt->execute();
$classes_result = $class_stmt->get_result();
$teacher_classes = $classes_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Results - Teacher Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .step {
            display: none;
            animation: fadeIn 0.3s ease;
        }
        .step.active {
            display: block;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-weight: bold;
        }
        .step-circle.active {
            background: #0d6efd;
            color: white;
        }
        .step-line {
            flex: 1;
            height: 3px;
            background: #e9ecef;
            align-self: center;
            margin: 0 -5px;
        }
        .step-line.active {
            background: #0d6efd;
        }
        
        .class-card, .student-card, .subject-card {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            height: 100%;
        }
        .class-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #0d6efd;
        }
        .class-card.selected {
            border-color: #0d6efd;
            background-color: #f0f8ff;
        }
        .student-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #17a2b8;
        }
        .student-card.selected {
            border-color: #17a2b8;
            background-color: #f0f8ff;
        }
        .subject-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #28a745;
        }
        .subject-card.selected {
            border-color: #28a745;
            background-color: #f0f8ff;
        }
        
        .marks-input {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: center;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Force pointer events */
        .clickable-card {
            cursor: pointer !important;
            pointer-events: auto !important;
        }
        .clickable-card * {
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">
                    <i class="bi bi-trophy text-warning"></i> Enter Results
                </h1>
                <p class="text-muted mb-0">Enter marks for your students</p>
            </div>
            <div>
                <button onclick="window.parent.showHome(); return false;" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </button>
            </div>
        </div>

        <?php if (empty($teacher_classes)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                No classes assigned to you. You need to have at least one class to enter results.
            </div>
        <?php else: ?>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-circle active" id="step1-circle">1</div>
            <div class="step-line" id="step1-2-line"></div>
            <div class="step-circle" id="step2-circle">2</div>
            <div class="step-line" id="step2-3-line"></div>
            <div class="step-circle" id="step3-circle">3</div>
            <div class="step-line" id="step3-4-line"></div>
            <div class="step-circle" id="step4-circle">4</div>
        </div>
        <div class="text-center mb-4">
            <small class="text-muted">
                <span id="step1-text">Select Class</span> → 
                <span id="step2-text">Select Student</span> → 
                <span id="step3-text">Select Subject</span> → 
                <span id="step4-text">Enter Marks</span>
            </small>
        </div>

        <!-- Step 1: Select Class -->
        <div class="step active" id="step1">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Step 1: Select Class</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Choose a class to enter results for:</p>
                    <div class="row" id="class-list">
                        <?php foreach ($teacher_classes as $class): 
                            // Count students in this class
                            $count_sql = "SELECT COUNT(*) as student_count FROM student WHERE class_id = ?";
                            $count_stmt = $connection->prepare($count_sql);
                            $count_stmt->bind_param("i", $class['class_id']);
                            $count_stmt->execute();
                            $count_result = $count_stmt->get_result();
                            $student_count = $count_result->fetch_assoc()['student_count'];
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="card student-card class-card clickable-card"
                                 data-class-id="<?php echo $class['class_id']; ?>"
                                 data-faculty="<?php echo htmlspecialchars($class['faculty']); ?>"
                                 data-semester="<?php echo $class['semester']; ?>"
                                 onclick="handleClassClick(this)">
                                <div class="card-body text-center">
                                    <h4 class="card-title"><?php echo htmlspecialchars($class['faculty']); ?></h4>
                                    <p class="card-text">Semester <?php echo $class['semester']; ?></p>
                                    <div class="d-flex justify-content-center">
                                        <span class="badge bg-primary me-2">
                                            <i class="bi bi-people"></i> <?php echo $student_count; ?> students
                                        </span>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Active
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Select Student -->
        <div class="step" id="step2">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Step 2: Select Student</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary btn-sm" onclick="goToStep(1)">
                            <i class="bi bi-arrow-left"></i> Back to Classes
                        </button>
                        <span class="ms-2" id="selected-class-info"></span>
                    </div>
                    <p class="text-muted mb-3">Select a student to enter results for:</p>
                    <div class="row" id="student-list">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading students...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Select Subject -->
        <div class="step" id="step3">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-book"></i> Step 3: Select Subject</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary btn-sm" onclick="goToStep(2)">
                            <i class="bi bi-arrow-left"></i> Back to Students
                        </button>
                        <span class="ms-2" id="selected-student-info"></span>
                    </div>
                    <p class="text-muted mb-3">Select a subject to enter marks for:</p>
                    <div class="row" id="subject-list">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading subjects...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Enter Marks -->
        <div class="step" id="step4">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Step 4: Enter Marks</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <button class="btn btn-outline-secondary btn-sm" onclick="goToStep(3)">
                            <i class="bi bi-arrow-left"></i> Back to Subjects
                        </button>
                        <span class="ms-2" id="selected-subject-info"></span>
                    </div>
                    
                    <form id="marks-form" onsubmit="submitMarks(event)">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Student Information</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Student ID:</th>
                                                <td id="detail-student-id">-</td>
                                            </tr>
                                            <tr>
                                                <th>Name:</th>
                                                <td id="detail-student-name">-</td>
                                            </tr>
                                            <tr>
                                                <th>Class:</th>
                                                <td id="detail-class">-</td>
                                            </tr>
                                            <tr>
                                                <th>Semester:</th>
                                                <td id="detail-semester">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Subject Information</h5>
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Subject:</th>
                                                <td id="detail-subject">-</td>
                                            </tr>
                                            <tr>
                                                <th>Subject Code:</th>
                                                <td id="detail-subject-code">-</td>
                                            </tr>
                                            <tr>
                                                <th>Previous Marks:</th>
                                                <td id="detail-previous-marks">
                                                    <span class="badge bg-secondary">No previous marks</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Enter Marks</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="marks-obtained" class="form-label">Marks Obtained *</label>
                                            <input type="number" class="form-control marks-input" 
                                                   id="marks-obtained" name="marks_obtained" 
                                                   min="0" max="100" step="0.01" required>
                                            <div class="form-text">Enter marks between 0-100</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="total-marks" class="form-label">Total Marks *</label>
                                            <input type="number" class="form-control marks-input" 
                                                   id="total-marks" name="total_marks" 
                                                   min="1" max="100" value="100" required>
                                            <div class="form-text">Usually 100 marks</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Auto-calculated fields -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="alert alert-info">
                                            <h6 class="alert-heading">Percentage</h6>
                                            <h3 id="percentage-calc">0%</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-warning">
                                            <h6 class="alert-heading">Grade</h6>
                                            <h3 id="grade-calc">-</h3>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="alert alert-success">
                                            <h6 class="alert-heading">Status</h6>
                                            <h3 id="status-calc">-</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="remarks" class="form-label">Remarks (Optional)</label>
                                    <textarea class="form-control" id="remarks" name="remarks" 
                                              rows="2" placeholder="Any additional remarks..."></textarea>
                                </div>

                                <input type="hidden" id="selected-class-id" name="class_id">
                                <input type="hidden" id="selected-student-id" name="student_id">
                                <input type="hidden" id="selected-subject-id" name="subject_id">
                                <input type="hidden" id="selected-semester-id" name="semester_id">

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="goToStep(3)">
                                        <i class="bi bi-arrow-left"></i> Back
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Save Results
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Simple inline script to handle clicks -->
    <script>
        // Simple function to handle class clicks
        function handleClassClick(card) {
            console.log("Class card clicked!");
            const classId = card.getAttribute('data-class-id');
            const faculty = card.getAttribute('data-faculty');
            const semester = card.getAttribute('data-semester');
            
            console.log("Data:", {classId, faculty, semester});
            
            // Visual feedback
            document.querySelectorAll('.class-card').forEach(c => {
                c.classList.remove('selected');
                c.style.borderColor = '';
            });
            card.classList.add('selected');
            card.style.borderColor = '#0d6efd';
            
            // Check if the main selectClass function exists
            if (typeof selectClass === 'function') {
                console.log("Calling selectClass function...");
                selectClass(classId, faculty, semester);
            } else {
                console.error("selectClass function not found!");
                // Fallback: show alert and load students directly
                alert("Would select: " + faculty + " Semester " + semester);
                
                // Update UI
                document.getElementById('selected-class-info').innerHTML = `
                    <span class="badge bg-primary">${faculty} - Semester ${semester}</span>
                `;
                
                // Load students
                fetch(`get_students.php?class_id=${classId}`)
                    .then(response => response.json())
                    .then(students => {
                        console.log("Students loaded:", students);
                        // Show step 2
                        document.getElementById('step1').classList.remove('active');
                        document.getElementById('step2').classList.add('active');
                        document.getElementById('step1-circle').classList.remove('active');
                        document.getElementById('step2-circle').classList.add('active');
                        document.getElementById('step1-2-line').classList.add('active');
                    });
            }
        }
        
        // Simple goToStep function
        function goToStep(stepNumber) {
            console.log("Going to step", stepNumber);
            // Hide all steps
            for (let i = 1; i <= 4; i++) {
                const step = document.getElementById('step' + i);
                const circle = document.getElementById('step' + i + '-circle');
                if (step) step.classList.remove('active');
                if (circle) circle.classList.remove('active');
            }
            
            // Show selected step
            const stepElement = document.getElementById('step' + stepNumber);
            const circleElement = document.getElementById('step' + stepNumber + '-circle');
            if (stepElement) stepElement.classList.add('active');
            if (circleElement) circleElement.classList.add('active');
            
            // Activate lines
            for (let i = 1; i < stepNumber; i++) {
                const line = document.getElementById('step' + i + '-' + (i+1) + '-line');
                if (line) line.classList.add('active');
            }
        }
        
        // Add hover effects to all class cards
        document.addEventListener('DOMContentLoaded', function() {
            console.log("Page loaded, adding hover effects...");
            
            const classCards = document.querySelectorAll('.class-card');
            console.log("Found", classCards.length, "class cards");
            
            classCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = 'translateY(-2px)';
                        this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                        this.style.borderColor = '#0d6efd';
                    }
                });
                
                card.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = '';
                        this.style.boxShadow = '';
                        this.style.borderColor = '';
                    }
                });
            });
        });
    </script>
    
    <!-- Load the main JavaScript file -->
    <script src="enter_results.js"></script>
</body>
</html>