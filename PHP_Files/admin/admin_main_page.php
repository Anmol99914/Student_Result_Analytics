<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0"); 

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    header("Location: admin_login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Result Analytics - Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
  body, html {
    height: 100%;
  }
  #main-content {
    background-color: #f9fafb;
    border-radius: 8px;
  }
  .nav-link.active {
    background-color: #0d6efd;
    color: white !important;
    border-radius: 5px;
  }
  .teacher-option.active {
    font-weight: bold;
  }
  .teacher-option.inactive {
    color: #6c757d;
    opacity: 0.7;
  }
</style>
</head>
<body class="d-flex flex-column min-vh-100" onload="noBack();">

<!-- Navbar -->
<nav class="navbar navbar-light bg-light">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <button class="btn btn-outline-primary d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
        <i class="bi bi-list"></i> Menu
      </button>
      <a class="navbar-brand mb-0">Student Result Analytics | Admin</a>
    </div>
    <form class="d-flex ms-auto" role="search" action="logout.php">
      <button class="btn btn-outline-danger" type="submit">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </form>
  </div>
</nav>

<!-- Main container -->
<div class="container-fluid flex-grow-1">
  <div class="row flex-md-nowrap flex-wrap">
    <!-- Desktop Sidebar -->
    <div class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3 d-none d-md-block vh-100 vh-md-auto">
      <h5>SRA | Admin</h5>
      <ul class="nav flex-column mt-4">
        <li class="nav-item mb-2">
          <a href="home.php" class="nav-link text-white ajax-link">
            <i class="bi bi-house"></i> Home/Dashboard
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#studentClassesMenu" class="nav-link text-white" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="studentClassesMenu">
            <i class="bi bi-table"></i> Student Classes
          </a>
          <div class="collapse" id="studentClassesMenu">
            <ul class="nav flex-column ms-3 mt-1">
              <li class="nav-item">
                <a href="#" onclick="showAddClassForm(); return false;" class="nav-link text-white">
                  <i class="bi bi-person-plus"></i> Add New Class
                </a>
              </li>
              <li class="nav-item">
                <a href="#" onclick="showManageClasses(); return false;" class="nav-link text-white">
                  <i class="bi bi-kanban"></i> Manage Class
                </a>
              </li>
            </ul>
          </div>
        </li>
        <!-- In the sidebar, add this after Student Classes -->
<li class="nav-item mb-2">
  <a href="#studentsMenu" class="nav-link text-white" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="studentsMenu">
    <i class="bi bi-people"></i> Students
  </a>
  <div class="collapse" id="studentsMenu">
    <ul class="nav flex-column ms-3 mt-1">
      <li class="nav-item">
        <a href="#" onclick="showAddStudentForm(); return false;" class="nav-link text-white">
          <i class="bi bi-person-plus"></i> Add New Student
        </a>
      </li>
      <li class="nav-item">
        <a href="#" onclick="showManageStudents(); return false;" class="nav-link text-white">
          <i class="bi bi-person-lines-fill"></i> Manage Students
        </a>
      </li>
    </ul>
  </div>
</li>
        <li class="nav-item mb-2">
          <a href="subjects.php" class="nav-link text-white ajax-link">
            <i class="bi bi-book"></i> Subjects
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="teachers.php" class="nav-link text-white ajax-link">
            <i class="bi bi-person-square"></i> Teachers
          </a>
        </li>
        <!-- <li class="nav-item mb-2">
          <a href="students.php" class="nav-link text-white ajax-link">
            <i class="bi bi-people"></i> Students
          </a>
        </li> -->
        <li class="nav-item mb-2">
          <a href="results.php" class="nav-link text-white ajax-link">
            <i class="bi bi-trophy"></i> Result
          </a>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4">
      <h1>Welcome to Student Result Analytics Admin Panel</h1>
      <p class="lead">Use the sidebar to navigate through sections like Students, Subjects, and Results.</p>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="mt-auto bg-light text-center py-2 border-top">
  © 2025 Student Result Analytics | Admin Panel
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Global function to load any page via AJAX
function loadPage(url) {
  const mainContent = document.getElementById('main-content');
  
  // Show loading
  mainContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading...</p></div>';
  
  fetch(url)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(html => {
      mainContent.innerHTML = html;
    })
    .catch(error => {
      console.error('Error loading page:', error);
      mainContent.innerHTML = '<div class="alert alert-danger">Error loading content. Please try again.</div>';
    });
}

// Setup AJAX links
function setupAjaxLinks() {
  const links = document.querySelectorAll('.ajax-link');
  links.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Remove active class from all links
      links.forEach(l => l.classList.remove('active'));
      
      // Add active class to clicked link
      this.classList.add('active');
      
      // Load the page
      loadPage(this.getAttribute('href'));
      
      // Close mobile offcanvas if open
      const offcanvas = document.getElementById('offcanvasSidebar');
      if (offcanvas) {
        const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
        if (bsOffcanvas) bsOffcanvas.hide();
      }
    });
  });
}

// Show Add Class Form
function showAddClassForm() {
  const mainContent = document.getElementById('main-content');
  
  mainContent.innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add New Class</h5>
        <button type="button" class="btn btn-light btn-sm" onclick="showManageClasses()">
          <i class="bi bi-arrow-left"></i> Back to Classes
        </button>
      </div>
      <div class="card-body">
        <form id="addClassForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Faculty <span class="text-danger">*</span></label>
              <select name="faculty" class="form-select" required>
                <option value="">-- Select Faculty --</option>
                <option value="BCA">BCA</option>
                <option value="BBM">BBM</option>
                <option value="BIM">BIM</option>
                <option value="BBA">BBA</option>
                <option value="BIT">BIT</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Semester <span class="text-danger">*</span></label>
              <select name="semester" class="form-select" required>
                <option value="">-- Select Semester --</option>
                ${Array.from({length: 8}, (_, i) => `<option value="${i + 1}">Semester ${i + 1}</option>`).join('')}
              </select>
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label">Assign Teacher <span class="text-danger">*</span></label>
            <div class="alert alert-info alert-sm">
              <i class="bi bi-info-circle"></i> Only active teachers can be assigned. Suspended teachers are disabled.
            </div>
            <select name="teacher_id" id="teacherSelect" class="form-select" required>
              <option value="">-- Select Teacher --</option>
            </select>
            <small class="text-muted">Each class must have an assigned teacher.</small>
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success btn-lg">
              <i class="bi bi-check-circle"></i> Create Class
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('addClassForm').reset()">
              <i class="bi bi-x-circle"></i> Reset Form
            </button>
          </div>
        </form>

        <div id="add-class-msg" class="mt-4"></div>
      </div>
    </div>
  `;
  
  // Load teachers and setup form
  loadTeachers();
  setupAddClassForm();
}

// Load teachers for dropdown
function loadTeachers() {
  const teacherSelect = document.getElementById('teacherSelect');
  if (!teacherSelect) return;
  
  // Updated path for your structure
  fetch('get_teachers.php')
    .then(response => response.json())
    .then(teachers => {
      teacherSelect.innerHTML = '<option value="">-- Select Teacher --</option>';
      
      teachers.forEach(teacher => {
        const isActive = teacher.status === 'active';
        const option = document.createElement('option');
        option.value = teacher.teacher_id;
        option.textContent = `${teacher.name} (${teacher.email})`;
        option.className = isActive ? 'teacher-option active' : 'teacher-option inactive';
        option.disabled = !isActive;
        if (!isActive) {
          option.textContent += ' [SUSPENDED]';
        }
        teacherSelect.appendChild(option);
      });
    })
    .catch(error => {
      console.error('Error loading teachers:', error);
      teacherSelect.innerHTML = '<option value="">Error loading teachers</option>';
    });
}

// Setup Add Class Form submission
function setupAddClassForm() {
  const form = document.getElementById('addClassForm');
  if (!form) return;
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const msgDiv = document.getElementById('add-class-msg');
    
    // Show loading
    msgDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Creating class...</div>';
    
    // Submit the form - Updated path for your structure
    fetch('add_class.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        msgDiv.innerHTML = `
          <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> ${data.message}
            <div class="mt-2">
              <strong>Class Created Successfully:</strong><br>
              Faculty: ${formData.get('faculty')}<br>
              Semester: ${formData.get('semester')}<br>
              Assigned Teacher: ${data.teacher_name}
            </div>
          </div>
        `;
        
        // Reset form
        form.reset();
        
        // Reload teachers
        loadTeachers();
        
        // Auto-clear message after 5 seconds
        setTimeout(() => {
          msgDiv.innerHTML = '';
        }, 5000);
      } else {
        msgDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error creating class. Please try again.</div>';
    });
  });
}

// Show Manage Classes
function showManageClasses() {
  const mainContent = document.getElementById('main-content');
  
  mainContent.innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-kanban me-2"></i>Manage Classes</h5>
        <div>
          <button type="button" class="btn btn-light btn-sm" onclick="showAddClassForm()">
            <i class="bi bi-plus-circle"></i> Add New Class
          </button>
        </div>
      </div>
      <div class="card-body">
        <div id="classes-container">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading classes...</p>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Load classes
  loadAllClasses();
}

// Load all classes for management
function loadAllClasses() {
  const container = document.getElementById('classes-container');
  if (!container) return;
  
  // Updated path for your structure
  fetch('get_classes.php')
    .then(response => response.json())
    .then(classes => {
      if (classes.length === 0) {
        container.innerHTML = `
          <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No classes found. 
            <button class="btn btn-link alert-link" onclick="showAddClassForm()">
              Create your first class!
            </button>
          </div>
        `;
        return;
      }
      
      let html = `
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Class ID</th>
                <th>Faculty</th>
                <th>Semester</th>
                <th>Assigned Teacher</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
      `;
      
      classes.forEach(cls => {
        const statusBadge = cls.status === 'active' 
          ? '<span class="badge bg-success">Active</span>' 
          : '<span class="badge bg-secondary">Inactive</span>';
        
        const teacherInfo = cls.teacher_name 
          ? `${cls.teacher_name}<br><small class="text-muted">${cls.teacher_email}</small>`
          : '<span class="text-muted">Not assigned</span>';
        
        html += `
          <tr>
            <td>${cls.class_id}</td>
            <td><strong>${cls.faculty}</strong></td>
            <td>Semester ${cls.semester}</td>
            <td>${teacherInfo}</td>
            <td>${statusBadge}</td>
            <td>${new Date(cls.created_at).toLocaleDateString()}</td>
            <td>
              <button class="btn btn-sm btn-outline-primary me-1" onclick="editClass(${cls.class_id})" title="Edit">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="btn btn-sm btn-outline-warning" onclick="toggleClassStatus(${cls.class_id}, '${cls.status}')" title="${cls.status === 'active' ? 'Deactivate' : 'Activate'}">
                <i class="bi bi-power"></i>
              </button>
              <button class="btn btn-sm btn-outline-danger" onclick="deleteClass(${cls.class_id})" title="Delete">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        `;
      });
      
      html += `
            </tbody>
          </table>
        </div>
      `;
      
      container.innerHTML = html;
    })
    .catch(error => {
      console.error('Error loading classes:', error);
      container.innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> Error loading classes. Please try again.
        </div>
      `;
    });
}

// Edit class function
function editClass(classId) {
  alert('Edit functionality for class ID: ' + classId + ' will be implemented');
  // You can implement edit modal or redirect to edit form
}

// Toggle class status
function toggleClassStatus(classId, currentStatus) {
  const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
  const confirmMsg = currentStatus === 'active' 
    ? 'Are you sure you want to deactivate this class?' 
    : 'Are you sure you want to activate this class?';
  
  if (confirm(confirmMsg)) {
    fetch('update_class_status.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        class_id: classId,
        status: newStatus
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        loadAllClasses(); // Reload the list
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      alert('Error updating class status');
      console.error('Error:', error);
    });
  }
}

// Delete class function
function deleteClass(classId) {
  if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
    // You'll need to create delete_class.php
    alert('Delete functionality for class ID: ' + classId + ' will be implemented');
    // fetch('delete_class.php', {...})
  }
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
  setupAjaxLinks();
  
  // Make Home link active by default
  const homeLink = document.querySelector('.ajax-link[href="home.php"]');
  if (homeLink) {
    homeLink.classList.add('active');
  }
});
// Show Add Student Form
function showAddStudentForm() {
  const mainContent = document.getElementById('main-content');
  
  mainContent.innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Register New Student</h5>
        <button type="button" class="btn btn-light btn-sm" onclick="showManageStudents()">
          <i class="bi bi-arrow-left"></i> Back to Students
        </button>
      </div>
      <div class="card-body">
        <form id="addStudentForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Student Name <span class="text-danger">*</span></label>
              <input type="text" name="student_name" class="form-control" required 
                     placeholder="Enter full name">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" required 
                     placeholder="student@example.com">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone_number" class="form-control" 
                     placeholder="98XXXXXXXX" maxlength="10">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <input type="text" name="password" class="form-control" 
                     placeholder="Leave blank to auto-generate">
              <small class="text-muted">If left blank, a random password will be generated</small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Class <span class="text-danger">*</span></label>
              <select name="class_id" id="classSelect" class="form-select" required>
                <option value="">-- Select Class --</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Semester <span class="text-danger">*</span></label>
              <select name="semester_id" class="form-select" required>
                <option value="">-- Select Semester --</option>
                ${Array.from({length: 8}, (_, i) => `<option value="${i + 1}">Semester ${i + 1}</option>`).join('')}
              </select>
            </div>
          </div>

          <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> 
            Student ID will be auto-generated (STU-YYYY-XXXX format)
          </div>

          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-success btn-lg">
              <i class="bi bi-person-add"></i> Register Student
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="resetStudentForm()">
              <i class="bi bi-x-circle"></i> Reset Form
            </button>
          </div>
        </form>

        <div id="add-student-msg" class="mt-4"></div>
      </div>
    </div>
  `;
  
  // Load classes for dropdown
  loadClassesForStudents();
  setupAddStudentForm();
}

// Load classes for student registration
function loadClassesForStudents() {
  const classSelect = document.getElementById('classSelect');
  if (!classSelect) return;
  
  fetch('get_classes.php')
    .then(response => response.json())
    .then(classes => {
      classSelect.innerHTML = '<option value="">-- Select Class --</option>';
      
      classes.forEach(cls => {
        if (cls.status === 'active') {
          const option = document.createElement('option');
          option.value = cls.class_id;
          option.textContent = `${cls.faculty} - Semester ${cls.semester}`;
          classSelect.appendChild(option);
        }
      });
    })
    .catch(error => {
      console.error('Error loading classes:', error);
      classSelect.innerHTML = '<option value="">Error loading classes</option>';
    });
}

// Setup Add Student Form submission
function setupAddStudentForm() {
  const form = document.getElementById('addStudentForm');
  if (!form) return;
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const msgDiv = document.getElementById('add-student-msg');
    
    // Show loading
    msgDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Registering student...</div>';
    
    // Submit the form
    fetch('Students/add_student.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.status === 'success') {
        msgDiv.innerHTML = `
          <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> ${data.message}
            <div class="mt-3">
              <strong>Student Details:</strong>
              <div class="card mt-2">
                <div class="card-body">
                  <table class="table table-sm">
                    <tr>
                      <td><strong>Student ID:</strong></td>
                      <td><code>${data.student_id}</code></td>
                    </tr>
                    <tr>
                      <td><strong>Name:</strong></td>
                      <td>${data.student_name}</td>
                    </tr>
                    <tr>
                      <td><strong>Login Password:</strong></td>
                      <td><span class="text-danger fw-bold">${data.password}</span></td>
                    </tr>
                  </table>
                  <div class="alert alert-warning mt-2">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Important:</strong> Share the Student ID and Password with the student. 
                    They should change their password after first login.
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        
        // Reset form
        form.reset();
        
        // Auto-clear message after 10 seconds
        setTimeout(() => {
          msgDiv.innerHTML = '';
        }, 10000);
      } else {
        msgDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error registering student. Please try again.</div>';
    });
  });
}

// Reset student form
function resetStudentForm() {
  const form = document.getElementById('addStudentForm');
  if (form) {
    form.reset();
    document.getElementById('add-student-msg').innerHTML = '';
  }
}

// Show Manage Students
function showManageStudents() {
  const mainContent = document.getElementById('main-content');
  
  mainContent.innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-lines-fill me-2"></i>Manage Students</h5>
        <div>
          <button type="button" class="btn btn-light btn-sm" onclick="showAddStudentForm()">
            <i class="bi bi-person-plus"></i> Add New Student
          </button>
        </div>
      </div>
      <div class="card-body">
        <!-- Filter Controls -->
        <div class="row mb-4">
          <div class="col-md-3">
            <select id="filterClass" class="form-select" onchange="filterStudents()">
              <option value="">All Classes</option>
            </select>
          </div>
          <div class="col-md-3">
            <select id="filterSemester" class="form-select" onchange="filterStudents()">
              <option value="">All Semesters</option>
              ${Array.from({length: 8}, (_, i) => `<option value="${i + 1}">Semester ${i + 1}</option>`).join('')}
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" id="searchStudent" class="form-control" placeholder="Search by name or ID" onkeyup="searchStudents()">
          </div>
          <div class="col-md-3">
            <button class="btn btn-outline-secondary w-100" onclick="resetStudentFilters()">
              <i class="bi bi-arrow-clockwise"></i> Reset Filters
            </button>
          </div>
        </div>
        
        <!-- Students Table -->
        <div id="students-container">
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading students...</p>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Load classes for filter
  loadClassesForFilter();
  // Load students
  loadAllStudents();
}

// Load classes for filter dropdown
function loadClassesForFilter() {
  const filterClass = document.getElementById('filterClass');
  if (!filterClass) return;
  
  fetch('get_classes.php')
    .then(response => response.json())
    .then(classes => {
      classes.forEach(cls => {
        if (cls.status === 'active') {
          const option = document.createElement('option');
          option.value = cls.class_id;
          option.textContent = `${cls.faculty} - Sem ${cls.semester}`;
          filterClass.appendChild(option);
        }
      });
    });
}

// Load all students
function loadAllStudents() {
  const container = document.getElementById('students-container');
  if (!container) return;
  
  fetch('Students/get_students.php')
    .then(response => response.json())
    .then(students => {
      if (students.length === 0) {
        container.innerHTML = `
          <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No students found. 
            <button class="btn btn-link alert-link" onclick="showAddStudentForm()">
              Register your first student!
            </button>
          </div>
        `;
        return;
      }
      
      renderStudentsTable(students);
    })
    .catch(error => {
      console.error('Error loading students:', error);
      container.innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> Error loading students. Please try again.
        </div>
      `;
    });
}

// Render students table
function renderStudentsTable(students) {
  let html = `
    <div class="table-responsive">
      <table class="table table-hover table-striped">
        <thead class="table-dark">
          <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Class</th>
            <th>Semester</th>
            <th>Phone</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
  `;
  
  students.forEach(student => {
    const statusBadge = student.is_active == 1 
      ? '<span class="badge bg-success">Active</span>' 
      : '<span class="badge bg-secondary">Inactive</span>';
    
    html += `
      <tr data-class="${student.class_id}" data-semester="${student.semester_id}" 
          data-name="${student.student_name.toLowerCase()}" data-id="${student.student_id.toLowerCase()}">
        <td><strong>${student.student_id}</strong></td>
        <td>${student.student_name}</td>
        <td>${student.email}</td>
        <td>${student.faculty || 'N/A'}</td>
        <td>Semester ${student.semester_id}</td>
        <td>${student.phone_number || 'N/A'}</td>
        <td>${statusBadge}</td>
        <td>
          <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="viewStudent('${student.student_id}')" title="View">
              <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="toggleStudentStatus('${student.student_id}', ${student.is_active})" 
                    title="${student.is_active == 1 ? 'Deactivate' : 'Activate'}">
              <i class="bi bi-power"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent('${student.student_id}')" title="Delete">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  });
  
  html += `
        </tbody>
      </table>
    </div>
  `;
  
  document.getElementById('students-container').innerHTML = html;
}

// Filter students
function filterStudents() {
  const classFilter = document.getElementById('filterClass').value;
  const semesterFilter = document.getElementById('filterSemester').value;
  
  const rows = document.querySelectorAll('#students-container tbody tr');
  
  rows.forEach(row => {
    const classValue = row.getAttribute('data-class');
    const semesterValue = row.getAttribute('data-semester');
    
    let show = true;
    
    if (classFilter && classValue !== classFilter) show = false;
    if (semesterFilter && semesterValue !== semesterFilter) show = false;
    
    row.style.display = show ? '' : 'none';
  });
}

// Search students
function searchStudents() {
  const searchTerm = document.getElementById('searchStudent').value.toLowerCase();
  const rows = document.querySelectorAll('#students-container tbody tr');
  
  rows.forEach(row => {
    const name = row.getAttribute('data-name');
    const id = row.getAttribute('data-id');
    
    if (name.includes(searchTerm) || id.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  });
}

// Reset filters
function resetStudentFilters() {
  document.getElementById('filterClass').value = '';
  document.getElementById('filterSemester').value = '';
  document.getElementById('searchStudent').value = '';
  
  const rows = document.querySelectorAll('#students-container tbody tr');
  rows.forEach(row => {
    row.style.display = '';
  });
}

// View student details
function viewStudent(studentId) {
  // Will implement later
  alert('View student: ' + studentId);
}

// Toggle student status
function toggleStudentStatus(studentId, currentStatus) {
  const newStatus = currentStatus == 1 ? 0 : 1;
  const confirmMsg = currentStatus == 1 
    ? 'Are you sure you want to deactivate this student?' 
    : 'Are you sure you want to activate this student?';
  
  if (confirm(confirmMsg)) {
    fetch('Students/update_student_status.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        student_id: studentId,
        status: newStatus
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        loadAllStudents(); // Reload the list
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      alert('Error updating student status');
      console.error('Error:', error);
    });
  }
}

// Delete student
function deleteStudent(studentId) {
  if (confirm('Are you sure you want to delete this student? If the student has results, they will be deactivated instead.')) {
    fetch('Students/delete_student.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        student_id: studentId
      })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.message);
      loadAllStudents(); // Reload the list
    })
    .catch(error => {
      alert('Error deleting student');
      console.error('Error:', error);
    });
  }
}

// Prevent back button
window.history.forward();
function noBack() { 
  window.history.forward(); 
}
</script>
</body>
</html>