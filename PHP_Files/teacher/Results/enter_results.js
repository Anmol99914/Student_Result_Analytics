// Global variables to store selections
let selectedClass = null;
let selectedStudent = null;
let selectedSubject = null;

console.log("enter_results.js loaded successfully");

// Step navigation
function goToStep(stepNumber) {
    console.log(`goToStep(${stepNumber}) called`);
    
    // Hide all steps
    document.querySelectorAll('.step').forEach(step => {
        step.classList.remove('active');
    });
    
    // Show selected step
    const stepElement = document.getElementById('step' + stepNumber);
    if (stepElement) {
        stepElement.classList.add('active');
        console.log(`Step ${stepNumber} activated`);
    } else {
        console.error(`Step ${stepNumber} element not found!`);
    }
    
    // Update step indicator
    updateStepIndicator(stepNumber);
}

function updateStepIndicator(currentStep) {
    console.log(`updateStepIndicator(${currentStep}) called`);
    
    // Reset all circles and lines
    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById('step' + i + '-circle');
        if (circle) circle.classList.remove('active');
        
        if (i < 4) {
            const line = document.getElementById('step' + i + '-' + (i+1) + '-line');
            if (line) line.classList.remove('active');
        }
    }
    
    // Activate up to current step
    for (let i = 1; i <= currentStep; i++) {
        const circle = document.getElementById('step' + i + '-circle');
        if (circle) circle.classList.add('active');
        
        if (i < currentStep) {
            const line = document.getElementById('step' + i + '-' + (i+1) + '-line');
            if (line) line.classList.add('active');
        }
    }
    
    console.log(`Step indicator updated to step ${currentStep}`);
}

// Step 1: Select Class
function selectClass(classId, faculty, semester) {
    console.log("selectClass called with:", {classId, faculty, semester});
    
    if (!classId || !faculty || !semester) {
        console.error("Missing parameters in selectClass!");
        alert("Error: Missing class information");
        return;
    }
    
    selectedClass = {
        id: classId,
        faculty: faculty,
        semester: semester
    };
    
    console.log("Selected class:", selectedClass);
    
    // Update UI
    const selectedClassInfo = document.getElementById('selected-class-info');
    if (selectedClassInfo) {
        selectedClassInfo.innerHTML = `
            <span class="badge bg-primary">${faculty} - Semester ${semester}</span>
        `;
        console.log("Updated class info display");
    }
    
    // Load students for this class
    loadStudents(classId);
    
    // Go to step 2
    goToStep(2);
}

// Step 2: Load Students
function loadStudents(classId) {
    console.log(`loadStudents called for class: ${classId}`);
    const studentList = document.getElementById('student-list');
    
    if (!studentList) {
        console.error("Student list element not found!");
        return;
    }
    
    // Show loading
    studentList.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading students...</p>
        </div>
    `;
    
    fetch(`get_students.php?class_id=${classId}`)
        .then(response => {
            console.log("Fetch response status:", response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(students => {
            console.log("Students loaded:", students);
            
            if (students.error) {
                studentList.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Error: ${students.error}
                        </div>
                    </div>
                `;
                return;
            }
            
            if (students.length === 0) {
                studentList.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            No students found in this class.
                            <a href="../Students/add_student.php" target="_blank" class="alert-link">
                                Add students first
                            </a>
                        </div>
                    </div>
                `;
                return;
            }
            
            let html = '';
            students.forEach(student => {
                html += `
                    <div class="col-md-4 mb-3">
                        <div class="card student-card student-item clickable-card"
                             data-student-id="${student.student_id}"
                             data-student-name="${escapeHtml(student.student_name)}"
                             onclick="selectStudent('${student.student_id}', '${escapeHtml(student.student_name)}')">
                            <div class="card-body text-center">
                                <h5 class="card-title">${student.student_name}</h5>
                                <p class="card-text text-muted">${student.student_id}</p>
                                <p class="card-text">
                                    <small class="text-muted">
                                        <i class="bi bi-envelope"></i> ${student.email || 'No email'}
                                    </small>
                                </p>
                                <span class="badge ${student.is_active == 1 ? 'bg-success' : 'bg-secondary'}">
                                    ${student.is_active == 1 ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            studentList.innerHTML = html;
            console.log(`Displayed ${students.length} students`);
            
        })
        .catch(error => {
            console.error("Error loading students:", error);
            studentList.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error loading students: ${error.message}
                    <br><small>Check console for details</small>
                </div>
            `;
        });
}

// Step 2: Select Student
function selectStudent(studentId, studentName) {
    console.log("selectStudent called with:", {studentId, studentName});
    
    if (!studentId || !studentName) {
        console.error("Missing student information!");
        return;
    }
    
    selectedStudent = {
        id: studentId,
        name: studentName
    };
    
    console.log("Selected student:", selectedStudent);
    
    // Update UI
    const selectedStudentInfo = document.getElementById('selected-student-info');
    if (selectedStudentInfo) {
        selectedStudentInfo.innerHTML = `
            <span class="badge bg-info">${studentName} (${studentId})</span>
        `;
    }
    
    // Load subjects
    loadSubjects();
    
    // Go to step 3
    goToStep(3);
}

// Step 3: Load Subjects
function loadSubjects() {
    console.log("loadSubjects called");
    const subjectList = document.getElementById('subject-list');
    
    if (!subjectList) {
        console.error("Subject list element not found!");
        return;
    }
    
    // Show loading
    subjectList.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading subjects...</p>
        </div>
    `;
    
    fetch(`get_subjects.php`)
        .then(response => {
            console.log("Subjects fetch response:", response.status);
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(subjects => {
            console.log("Subjects loaded:", subjects);
            
            if (subjects.error) {
                subjectList.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            Error: ${subjects.error}
                        </div>
                    </div>
                `;
                return;
            }
            
            if (subjects.length === 0) {
                subjectList.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            No subjects found in database.
                            <br><small>Contact admin to add subjects.</small>
                        </div>
                    </div>
                `;
                return;
            }
            
            let html = '';
            subjects.forEach(subject => {
                html += `
                    <div class="col-md-4 mb-3">
                        <div class="card subject-card subject-item clickable-card"
                             data-subject-id="${subject.subject_id}"
                             data-subject-name="${escapeHtml(subject.subject_name)}"
                             onclick="selectSubject('${subject.subject_id}', '${escapeHtml(subject.subject_name)}')">
                            <div class="card-body text-center">
                                <h4 class="card-title">${subject.subject_name}</h4>
                                <p class="card-text text-muted">Subject ID: ${subject.subject_id}</p>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            subjectList.innerHTML = html;
            console.log(`Displayed ${subjects.length} subjects`);
        })
        .catch(error => {
            console.error("Error loading subjects:", error);
            subjectList.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error loading subjects: ${error.message}
                </div>
            `;
        });
}

// Step 3: Select Subject
function selectSubject(subjectId, subjectName) {
    console.log("selectSubject called with:", {subjectId, subjectName});
    
    if (!subjectId || !subjectName) {
        console.error("Missing subject information!");
        return;
    }
    
    selectedSubject = {
        id: subjectId,
        name: subjectName
    };
    
    console.log("Selected subject:", selectedSubject);
    
    // Update UI
    const selectedSubjectInfo = document.getElementById('selected-subject-info');
    if (selectedSubjectInfo) {
        selectedSubjectInfo.innerHTML = `
            <span class="badge bg-success">${subjectName}</span>
        `;
    }
    
    // Update form details
    document.getElementById('detail-student-id').textContent = selectedStudent.id;
    document.getElementById('detail-student-name').textContent = selectedStudent.name;
    document.getElementById('detail-class').textContent = selectedClass.faculty;
    document.getElementById('detail-semester').textContent = `Semester ${selectedClass.semester}`;
    document.getElementById('detail-subject').textContent = subjectName;
    document.getElementById('detail-subject-code').textContent = `SUB${subjectId.toString().padStart(3, '0')}`;
    
    // Set hidden fields
    document.getElementById('selected-class-id').value = selectedClass.id;
    document.getElementById('selected-student-id').value = selectedStudent.id;
    document.getElementById('selected-subject-id').value = subjectId;
    document.getElementById('selected-semester-id').value = selectedClass.semester;
    
    // Check for previous marks
    checkPreviousMarks(selectedStudent.id, subjectId);
    
    // Go to step 4
    goToStep(4);
}

// Check if student already has marks for this subject
function checkPreviousMarks(studentId, subjectId) {
    console.log(`checkPreviousMarks for student ${studentId}, subject ${subjectId}`);
    
    fetch(`check_previous_marks.php?student_id=${studentId}&subject_id=${subjectId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log("Previous marks check:", data);
            
            const detailElement = document.getElementById('detail-previous-marks');
            if (!detailElement) return;
            
            if (data.exists) {
                detailElement.innerHTML = `
                    <span class="badge bg-warning text-dark">
                        Previous: ${data.marks_obtained}/${data.total_marks}
                        (${data.percentage}%)
                    </span>
                    <br>
                    <small class="text-muted">This will update the existing record</small>
                `;
            } else {
                detailElement.innerHTML = `
                    <span class="badge bg-secondary">No previous marks</span>
                `;
            }
        })
        .catch(error => {
            console.error("Error checking previous marks:", error);
        });
}

// Auto-calculate percentage, grade, and status
function calculateResults() {
    console.log("calculateResults called");
    
    const marksObtained = parseFloat(document.getElementById('marks-obtained').value) || 0;
    const totalMarks = parseFloat(document.getElementById('total-marks').value) || 100;
    
    console.log(`Marks: ${marksObtained}/${totalMarks}`);
    
    if (totalMarks > 0) {
        const percentage = (marksObtained / totalMarks) * 100;
        const percentageElement = document.getElementById('percentage-calc');
        if (percentageElement) {
            percentageElement.textContent = percentage.toFixed(1) + '%';
        }
        
        // Calculate grade
        let grade = 'F';
        let status = 'Fail';
        
        if (percentage >= 90) { grade = 'A+'; status = 'Distinction'; }
        else if (percentage >= 80) { grade = 'A'; status = 'Excellent'; }
        else if (percentage >= 70) { grade = 'B+'; status = 'Very Good'; }
        else if (percentage >= 60) { grade = 'B'; status = 'Good'; }
        else if (percentage >= 50) { grade = 'C+'; status = 'Satisfactory'; }
        else if (percentage >= 40) { grade = 'C'; status = 'Pass'; }
        else { grade = 'F'; status = 'Fail'; }
        
        const gradeElement = document.getElementById('grade-calc');
        const statusElement = document.getElementById('status-calc');
        
        if (gradeElement) gradeElement.textContent = grade;
        if (statusElement) statusElement.textContent = status;
        
        console.log(`Calculated: ${percentage.toFixed(1)}%, Grade: ${grade}, Status: ${status}`);
    }
}

// Step 4: Submit Marks
function submitMarks(event) {
    event.preventDefault();
    console.log("submitMarks called");
    
    const formData = new FormData(document.getElementById('marks-form'));
    
    console.log("Submitting form data:");
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    fetch('save_results.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log("Save response status:", response.status);
        return response.json();
    })
    .then(data => {
        console.log("Save response data:", data);
        
        if (data.success) {
            alert('✅ Results saved successfully!');
            console.log("Results saved, resetting form...");
            // Reset and go back to step 1
            resetForm();
            goToStep(1);
        } else {
            alert('❌ Error: ' + data.message);
            console.error("Save error:", data.message);
        }
    })
    .catch(error => {
        console.error("Error saving results:", error);
        alert('❌ Error saving results: ' + error.message);
    });
}

function resetForm() {
    console.log("resetForm called");
    
    selectedClass = null;
    selectedStudent = null;
    selectedSubject = null;
    
    const marksForm = document.getElementById('marks-form');
    if (marksForm) marksForm.reset();
    
    // Clear info displays
    document.getElementById('selected-class-info').innerHTML = '';
    document.getElementById('selected-student-info').innerHTML = '';
    document.getElementById('selected-subject-info').innerHTML = '';
    
    // Reset detail displays
    document.getElementById('detail-student-id').textContent = '-';
    document.getElementById('detail-student-name').textContent = '-';
    document.getElementById('detail-class').textContent = '-';
    document.getElementById('detail-semester').textContent = '-';
    document.getElementById('detail-subject').textContent = '-';
    document.getElementById('detail-subject-code').textContent = '-';
    document.getElementById('detail-previous-marks').innerHTML = `
        <span class="badge bg-secondary">No previous marks</span>
    `;
    
    // Reset calculated fields
    document.getElementById('percentage-calc').textContent = '0%';
    document.getElementById('grade-calc').textContent = '-';
    document.getElementById('status-calc').textContent = '-';
    
    // Reset class cards
    document.querySelectorAll('.class-card').forEach(card => {
        card.classList.remove('selected');
        card.style.borderColor = '';
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log("enter_results.js initialized");
    
    // Set up input listeners for marks calculation
    const marksObtained = document.getElementById('marks-obtained');
    const totalMarks = document.getElementById('total-marks');
    
    if (marksObtained) {
        marksObtained.addEventListener('input', calculateResults);
    }
    if (totalMarks) {
        totalMarks.addEventListener('input', calculateResults);
    }
});

// Make functions available globally
window.selectClass = selectClass;
window.selectStudent = selectStudent;
window.selectSubject = selectSubject;
window.goToStep = goToStep;
window.submitMarks = submitMarks;