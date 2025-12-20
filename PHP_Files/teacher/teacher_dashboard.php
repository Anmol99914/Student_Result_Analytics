<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    header("Location: teacher_login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teacher Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
  body, html { height: 100%; }
  #main-content { background-color: #f9fafb; border-radius: 8px; }
  .nav-link.active { 
    background-color: #0d6efd; 
    color: white !important; 
    border-radius: 8px;
  }
  .sidebar { 
    background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  }
  .sidebar .nav-link { 
    color: #ecf0f1; 
    border-radius: 8px;
    margin-bottom: 5px;
    transition: all 0.3s;
  }
  .sidebar .nav-link:hover { 
    background-color: rgba(255,255,255,0.1); 
    color: white;
  }
</style>
</head>
<body class="d-flex flex-column min-vh-100" onload="noBack();">

<!-- Navbar -->
<nav class="navbar navbar-light bg-light shadow-sm">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
      <button class="btn btn-outline-primary d-md-none me-2" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i> Menu
      </button>
      <a class="navbar-brand mb-0 fw-bold">
        <i class="bi bi-person-badge text-primary"></i> Teacher Dashboard
      </a>
      <span class="ms-3 text-muted d-none d-md-block">
        Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?>
      </span>
    </div>
    <form class="d-flex ms-auto" action="teacher_logout.php">
      <button class="btn btn-outline-danger" type="submit">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </form>
  </div>
</nav>

<!-- Mobile Sidebar (Offcanvas) -->
<div class="offcanvas offcanvas-start sidebar text-white" tabindex="-1" id="offcanvasSidebar">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">
      <i class="bi bi-person-badge"></i> Teacher Menu
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="nav flex-column mt-3 p-3">
      <li class="nav-item mb-2">
        <a href="#" onclick="showHome(); return false;" class="nav-link text-white">
          <i class="bi bi-house"></i> Dashboard
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="#" onclick="showMyClasses(); return false;" class="nav-link text-white">
          <i class="bi bi-table"></i> My Classes
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="#" onclick="showAddStudentForm(); return false;" class="nav-link text-white">
          <i class="bi bi-person-plus"></i> Add Student
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="#" onclick="showMyStudents(); return false;" class="nav-link text-white">
          <i class="bi bi-people"></i> My Students
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="#" onclick="showAddResultForm(); return false;" class="nav-link text-white">
          <i class="bi bi-trophy"></i> Enter Results
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="#" onclick="showProfile(); return false;" class="nav-link text-white">
          <i class="bi bi-person"></i> My Profile
        </a>
      </li>
    </ul>
  </div>
</div>

<!-- Main container -->
<div class="container-fluid flex-grow-1">
  <div class="row flex-md-nowrap flex-wrap">
    <!-- Desktop Sidebar -->
    <div class="col-12 col-md-3 col-lg-2 sidebar text-white p-3 d-none d-md-block vh-100">
      <h5 class="mb-4">
        <i class="bi bi-person-badge"></i> Teacher Panel
      </h5>
      <ul class="nav flex-column mt-2">
        <li class="nav-item mb-2">
          <a href="#" onclick="showHome(); return false;" class="nav-link text-white active">
            <i class="bi bi-house"></i> Dashboard
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" onclick="showMyClasses(); return false;" class="nav-link text-white">
            <i class="bi bi-table"></i> My Classes
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" onclick="showAddStudentForm(); return false;" class="nav-link text-white">
            <i class="bi bi-person-plus"></i> Add Student
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" onclick="showMyStudents(); return false;" class="nav-link text-white">
            <i class="bi bi-people"></i> My Students
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" onclick="showAddResultForm(); return false;" class="nav-link text-white">
            <i class="bi bi-trophy"></i> Enter Results
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#" onclick="showProfile(); return false;" class="nav-link text-white">
            <i class="bi bi-person"></i> My Profile
          </a>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4">
      <!-- Default Home Content -->
      <div class="text-center py-5">
        <h1 class="display-5 mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?>! 👨‍🏫</h1>
        <p class="lead text-muted mb-5">Manage your classes, students, and results from this dashboard.</p>
        
        <div class="row justify-content-center g-4">
          <div class="col-md-4">
            <div class="card border-primary shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-table display-4 text-primary mb-3"></i>
                <h5 class="card-title">My Classes</h5>
                <p class="card-text">View classes assigned to you</p>
                <button class="btn btn-outline-primary" onclick="showMyClasses()">
                  View Classes
                </button>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-person-plus display-4 text-success mb-3"></i>
                <h5 class="card-title">Add Student</h5>
                <p class="card-text">Register new students to your class</p>
                <button class="btn btn-outline-success" onclick="showAddStudentForm()">
                  Add Student
                </button>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card border-warning shadow-sm h-100">
              <div class="card-body text-center">
                <i class="bi bi-trophy display-4 text-warning mb-3"></i>
                <h5 class="card-title">Enter Results</h5>
                <p class="card-text">Enter marks for your students</p>
                <button class="btn btn-outline-warning" onclick="showAddResultForm()">
                  Enter Results
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="mt-auto bg-light text-center py-2 border-top">
  <small class="text-muted">
    © 2025 Student Result Analytics | Teacher Panel
  </small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Functions for navigation
function showHome() {
  setActiveLink('home');
  document.getElementById('main-content').innerHTML = `
    <div class="text-center py-5">
      <h1 class="display-5 mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['teacher_name']); ?>! 👨‍🏫</h1>
      <p class="lead text-muted mb-5">Manage your classes, students, and results from this dashboard.</p>
      
      <div class="row justify-content-center g-4">
        <div class="col-md-4">
          <div class="card border-primary shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-table display-4 text-primary mb-3"></i>
              <h5 class="card-title">My Classes</h5>
              <p class="card-text">View classes assigned to you</p>
              <button class="btn btn-outline-primary" onclick="showMyClasses()">
                View Classes
              </button>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card border-success shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-person-plus display-4 text-success mb-3"></i>
              <h5 class="card-title">Add Student</h5>
              <p class="card-text">Register new students to your class</p>
              <button class="btn btn-outline-success" onclick="showAddStudentForm()">
                Add Student
              </button>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card border-warning shadow-sm h-100">
            <div class="card-body text-center">
              <i class="bi bi-trophy display-4 text-warning mb-3"></i>
              <h5 class="card-title">Enter Results</h5>
              <p class="card-text">Enter marks for your students</p>
              <button class="btn btn-outline-warning" onclick="showAddResultForm()">
                Enter Results
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  `;
}

function showMyClasses() {
  setActiveLink('classes');
  document.getElementById('main-content').innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table me-2"></i> My Assigned Classes</h5>
      </div>
      <div class="card-body">
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
  `;
  
  // We'll implement this function next
  loadTeacherClasses();
}

function showAddStudentForm() {
  setActiveLink('addStudent');
  
  let html = `
    <div class="card shadow">
      <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i> Add New Student</h5>
        <button class="btn btn-sm btn-light" onclick="closeIframeView()">
          <i class="bi bi-x-lg"></i> Close
        </button>
      </div>
      <div class="card-body p-0">
        <iframe id="addStudentFrame" src="Students/add_student.php" 
                style="width: 100%; height: 600px; border: none;"></iframe>
      </div>
    </div>
  `;
  
  document.getElementById('main-content').innerHTML = html;
}

// Add this function to close iframe view
function closeIframeView() {
  showHome();
}

// Add this function to refresh the iframe (useful for after adding student)
function refreshAddStudentFrame() {
  const frame = document.getElementById('addStudentFrame');
  if (frame) {
    frame.src = frame.src; // Reload the iframe
  }
}

function showMyStudents() {
  setActiveLink('students');
  
  fetch('Students/my_students.php')
    .then(response => response.text())
    .then(html => {
      document.getElementById('main-content').innerHTML = `
        <div class="card shadow">
          <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i> My Students</h5>
            <button class="btn btn-sm btn-light" onclick="showHome()">
              <i class="bi bi-arrow-left"></i> Back
            </button>
          </div>
          <div class="card-body p-0">
            ${html}
          </div>
        </div>
      `;
    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = `
        <div class="alert alert-danger">
          Error loading students: ${error}
        </div>
      `;
    });
}

function showAddResultForm() {
  setActiveLink('results');
  document.getElementById('main-content').innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-trophy me-2"></i> Enter Results</h5>
      </div>
      <div class="card-body">
        <div class="text-center py-5">
          <h4><i class="bi bi-hourglass-split text-warning"></i></h4>
          <p class="lead">Result entry feature</p>
          <p class="text-muted">Will allow you to enter marks for students in your subjects.</p>
        </div>
      </div>
    </div>
  `;
}

function showProfile() {
  setActiveLink('profile');
  document.getElementById('main-content').innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person me-2"></i> My Profile</h5>
      </div>
      <div class="card-body">
        <div class="text-center py-5">
          <h4><i class="bi bi-hourglass-split text-secondary"></i></h4>
          <p class="lead">Profile management feature</p>
          <p class="text-muted">Will show your teacher profile and allow updates.</p>
        </div>
      </div>
    </div>
  `;
}

function setActiveLink(linkName) {
  // Remove active class from all links
  document.querySelectorAll('.sidebar .nav-link').forEach(link => {
    link.classList.remove('active');
  });
  
  // Add active class to clicked link (simplified for now)
  // We'll implement proper highlighting when we add the actual content
}

function loadTeacherClasses() {
  const container = document.getElementById('classes-container');
  
  // Show loading
  container.innerHTML = `
    <div class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-2">Loading your classes...</p>
    </div>
  `;
  
  // Fetch teacher's classes
  fetch('get_teacher_classes.php')
    .then(response => response.json())
    .then(classes => {
      if(classes.length === 0) {
        container.innerHTML = `
          <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i>
            No classes assigned to you yet.
            <br><small>Contact admin to get classes assigned.</small>
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
                <th>Students</th>
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
        
        html += `
          <tr>
            <td>${cls.class_id}</td>
            <td><strong>${cls.faculty}</strong></td>
            <td>Semester ${cls.semester}</td>
            <td>
              <span class="badge bg-primary">${cls.student_count} students</span>
            </td>
            <td>${statusBadge}</td>
            <td>${new Date(cls.created_at).toLocaleDateString()}</td>
            <td>
              <button class="btn btn-sm btn-outline-primary" onclick="viewClassStudents(${cls.class_id})">
                <i class="bi bi-people"></i> View Students
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
          <i class="bi bi-exclamation-triangle"></i>
          Error loading classes. Please try again.
        </div>
      `;
    });
}

function viewClassStudents(classId) {
  // We'll implement this in the next step
  alert('View students of class ID: ' + classId + '\nNext step: Implement student list for this class');
}

function viewStudentDetail(studentId) {
  setActiveLink('students');
  
  fetch('Students/view_student.php?id=' + studentId)
    .then(response => response.text())
    .then(html => {
      document.getElementById('main-content').innerHTML = `
        <div class="card shadow">
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i> Student Details</h5>
            <button class="btn btn-sm btn-light" onclick="showMyStudents()">
              <i class="bi bi-arrow-left"></i> Back to Students
            </button>
          </div>
          <div class="card-body p-0">
            ${html}
          </div>
        </div>
      `;
    })
    .catch(error => {
      document.getElementById('main-content').innerHTML = `
        <div class="alert alert-danger">
          Error loading student details: ${error}
        </div>
      `;
    });
}

function showAddResultForStudent(studentId) {
  setActiveLink('results');
  
  document.getElementById('main-content').innerHTML = `
    <div class="card shadow">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-trophy me-2"></i> Enter Results for Student</h5>
        <button class="btn btn-sm btn-light" onclick="viewStudentDetail('${studentId}')">
          <i class="bi bi-arrow-left"></i> Back to Student
        </button>
      </div>
      <div class="card-body">
        <div class="text-center py-5">
          <h4>Enter Results for Student ID: ${studentId}</h4>
          <p class="text-muted">This feature will be implemented next.</p>
          <div class="mt-4">
            <button class="btn btn-primary me-2" onclick="viewStudentDetail('${studentId}')">
              <i class="bi bi-arrow-left"></i> Back to Student Details
            </button>
            <button class="btn btn-outline-secondary" onclick="showHome()">
              <i class="bi bi-house"></i> Dashboard
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
  // Set home as active by default
  const homeLink = document.querySelector('.sidebar .nav-link');
  if(homeLink) homeLink.classList.add('active');
});

// Prevent back button
window.history.forward();
function noBack() { window.history.forward(); }

// Handle page refresh
window.addEventListener('pageshow', function(event) {
  if(event.persisted){
    window.location.reload();
  }
});
</script>
</body>
</html>