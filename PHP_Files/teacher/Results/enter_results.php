<!-- require_once '../../../config.php'; -->
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Please login first</div>';
    exit();
}

require_once '../../../config.php';

$teacher_id = $_SESSION['teacher_id'];
$teacher_name = $_SESSION['teacher_name'];
?>
<div class="container-fluid">
    <!-- Results Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-trophy me-2"></i> Enter Results</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">Select a class to enter marks for students. Marks will be sent for admin verification.</p>
                    
                    <!-- Classes Container -->
                    <div id="classes-container">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading your classes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// This runs when the page is loaded into #main-content via AJAX
console.log('Enter Results page loaded');

// Set teacher data
window.TEACHER_DATA = {
    id: <?php echo $teacher_id; ?>,
    name: '<?php echo addslashes($teacher_name); ?>'
};

// Function to load classes
function loadTeacherClasses() {
    console.log('Loading classes for teacher:', window.TEACHER_DATA.id);
    const container = document.getElementById('classes-container');
    
    if (!container) {
        console.error('classes-container not found!');
        return;
    }
    
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading your classes...</p>
        </div>
    `;
    
    // Fetch classes
    fetch('Results/get_classes_for_results.php?teacher_id=' + window.TEACHER_DATA.id)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            
            // Add click handlers to SELECT buttons (not whole card)
            addClassButtonListeners();
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Classes</h5>
                    <p>${error.message}</p>
                    <button class="btn btn-sm btn-primary" onclick="loadTeacherClasses()">
                        Try Again
                    </button>
                </div>
            `;
        });
}

// Add click handlers to SELECT CLASS buttons
function addClassButtonListeners() {
    const buttons = document.querySelectorAll('.select-class-btn');
    console.log('Found', buttons.length, 'class buttons');
    
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            const faculty = this.getAttribute('data-faculty');
            const semester = this.getAttribute('data-semester');
            
            console.log('Class selected:', classId, faculty, semester);
            
            // Load subjects for this class
            loadClassSubjects(classId, faculty, semester);
        });
    });
}

// Load subjects for selected class
function loadClassSubjects(classId, faculty, semester) {
    const container = document.getElementById('classes-container');
    
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading subjects for ${faculty} - Semester ${semester}...</p>
        </div>
    `;
    
    // Fetch subjects from PHP
    fetch(`Results/get_subjects_for_class.php?class_id=${classId}&faculty=${encodeURIComponent(faculty)}&semester=${semester}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            container.innerHTML = html;
            
            // Add click handlers to subject buttons
            addSubjectButtonListeners();
        })
        .catch(error => {
            console.error('Error:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Subjects</h5>
                    <p>${error.message}</p>
                    <button class="btn btn-sm btn-primary" onclick="loadClassSubjects(${classId}, '${faculty}', ${semester})">
                        Try Again
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="loadTeacherClasses()">
                        Back to Classes
                    </button>
                </div>
            `;
        });
}

// Add click handlers to subject buttons
function addSubjectButtonListeners() {
    // Enter marks buttons
    document.querySelectorAll('.enter-marks-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            const faculty = this.getAttribute('data-faculty');
            const semester = this.getAttribute('data-semester');
            
            console.log('Enter marks for:', subjectName);
            loadMarksEntryForm(classId, subjectId, subjectName, faculty, semester);
        });
    });
    
    // View marks buttons
    document.querySelectorAll('.view-marks-btn').forEach(button => {
        button.addEventListener('click', function() {
            const classId = this.getAttribute('data-class-id');
            const subjectId = this.getAttribute('data-subject-id');
            const subjectName = this.getAttribute('data-subject-name');
            const faculty = this.getAttribute('data-faculty');
            const semester = this.getAttribute('data-semester');
            
            console.log('View marks for:', subjectName);
            loadMarksEntryForm(classId, subjectId, subjectName, faculty, semester, true);
        });
    });
}

// Load marks entry form for a subject
function loadMarksEntryForm(classId, subjectId, subjectName, faculty, semester, isViewMode = false) {
    const container = document.getElementById('classes-container');
    
    container.innerHTML = `
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square"></i> ${isViewMode ? 'View/Edit' : 'Enter'} Marks
                    </h5>
                    <small class="text-muted">${subjectName} - ${faculty} (Sem ${semester})</small>
                </div>
                <button class="btn btn-sm btn-outline-secondary" onclick="loadClassSubjects(${classId}, '${faculty}', ${semester})">
                    <i class="bi bi-arrow-left"></i> Back to Subjects
                </button>
            </div>
            <div class="card-body">
                <div id="marks-entry-container">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading student list...</p>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Load actual students for marks entry
    fetch(`Results/get_students_for_marks.php?class_id=${classId}&subject_id=${subjectId}&subject_name=${encodeURIComponent(subjectName)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }
            return response.text();
        })
        .then(html => {
            const marksContainer = document.getElementById('marks-entry-container');
            marksContainer.innerHTML = html;
            
            // Reinitialize any JavaScript after content loads
            setTimeout(() => {
                console.log('Marks form loaded, checking JavaScript...');
                
                // Test if calculateGrade function exists
                if (typeof calculateGrade === 'function') {
                    console.log('✓ calculateGrade function is available');
                } else {
                    console.error('✗ calculateGrade function NOT found');
                }
                
                // Test if event listeners are attached
                const inputs = document.querySelectorAll('.marks-input');
                console.log('Found', inputs.length, 'marks inputs');
                
                // Add a test button if not exists
                if (!document.querySelector('.js-test-btn')) {
                    const testBtn = document.createElement('button');
                    testBtn.className = 'btn btn-sm btn-outline-info js-test-btn mt-2';
                    testBtn.innerHTML = '<i class="bi bi-code"></i> Debug JS';
                    testBtn.onclick = () => {
                        alert('JavaScript loaded. Functions available:\n' +
                              '- calculateGrade: ' + (typeof calculateGrade) + '\n' +
                              '- getGradeFromMarks: ' + (typeof getGradeFromMarks) + '\n' +
                              '- saveAllMarks: ' + (typeof saveAllMarks));
                    };
                    marksContainer.appendChild(testBtn);
                }
            }, 100);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('marks-entry-container').innerHTML = `
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error Loading Students</h5>
                    <p>${error.message}</p>
                    <button class="btn btn-sm btn-primary" onclick="loadMarksEntryForm(${classId}, ${subjectId}, '${subjectName}', '${faculty}', ${semester}, ${isViewMode})">
                        Try Again
                    </button>
                </div>
            `;
        });
}

// Load students for marks entry (temporary demo)
function loadStudentsForMarks(classId, subjectId, subjectName, faculty, semester, isViewMode) {
    const container = document.getElementById('marks-entry-container');
    
    // For now, show a demo - you'll implement actual student loading
    container.innerHTML = `
        <div class="alert alert-info">
            <h5><i class="bi bi-info-circle"></i> Marks Entry Form - Coming Soon</h5>
            <p><strong>Subject:</strong> ${subjectName}</p>
            <p><strong>Class:</strong> ${faculty} - Semester ${semester}</p>
            <p><strong>Mode:</strong> ${isViewMode ? 'View/Edit Existing Marks' : 'Enter New Marks'}</p>
            
            <hr>
            <p>Next steps to implement:</p>
            <ol>
                <li>Fetch students from class ID: ${classId}</li>
                <li>Display students in a table with marks input</li>
                <li>Add validation for marks (0-100)</li>
                <li>Calculate grades automatically</li>
                <li>Save to database with "pending" status</li>
            </ol>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6>Demo Student List</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Marks</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>S001</td>
                            <td>John Doe</td>
                            <td><input type="number" class="form-control form-control-sm" value="85" min="0" max="100" style="width: 80px;"></td>
                            <td><span class="badge bg-success">A</span></td>
                        </tr>
                        <tr>
                            <td>S002</td>
                            <td>Jane Smith</td>
                            <td><input type="number" class="form-control form-control-sm" value="72" min="0" max="100" style="width: 80px;"></td>
                            <td><span class="badge bg-warning">B</span></td>
                        </tr>
                        <tr>
                            <td>S003</td>
                            <td>Bob Johnson</td>
                            <td><input type="number" class="form-control form-control-sm" value="91" min="0" max="100" style="width: 80px;"></td>
                            <td><span class="badge bg-success">A+</span></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="mt-3">
                    <button class="btn btn-success" onclick="saveMarks(${classId}, ${subjectId})">
                        <i class="bi bi-check-circle"></i> Save Marks
                    </button>
                    <button class="btn btn-secondary ms-2" onclick="loadClassSubjects(${classId}, '${faculty}', ${semester})">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
}

// Demo save function
function saveMarks(classId, subjectId) {
    alert(`Marks would be saved for:\nClass ID: ${classId}\nSubject ID: ${subjectId}\n\nStatus: Pending Verification\n\nThis will be implemented next.`);
}

// Initialize when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadTeacherClasses);
} else {
    // DOM already loaded
    setTimeout(loadTeacherClasses, 100);
}
</script>