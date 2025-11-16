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
</style>
</head>
<body class="d-flex flex-column min-vh-100" onload="noBack();">

<!-- Navbar -->
<nav class="navbar navbar-light bg-light">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <!-- Left: Mobile menu + Title -->
    <div class="d-flex align-items-center">
      <!-- Mobile menu button -->
    <button class="btn btn-outline-primary d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
      <i class="bi bi-list"></i> Menu
    </button>
    <a class="navbar-brand mb-0">Student Result Analytics | Admin</a>
</div>
    <form class="d-flex ms-auto" role="search" action="logout.php">
      <button class="btn btn-outline-danger" type="submit">
        <i class="bi bi-box-arrow-right"></i> 
        Logout
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
            <i class="bi bi-house"></i> Home
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="dashboard.php" class="nav-link text-white ajax-link">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="#studentClassesMenu" class="nav-link text-white" data-bs-toggle = "collapse"
                  roll = "button" aria-expanded = "false" aria-controls = "studentClassesMenu">
            <i class="bi bi-table"></i> Student Classes
          </a>
            <div class = "collapse" id = "studentClassesMenu">
              <ul class = "nav flex-column ms-3 mt-1">
              <li class="nav-item">
                        <a href="add_class.php" class="nav-link text-white ajax-link">
                        <i class="bi bi-person-plus"></i>Add New Class
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage_class.php" class="nav-link text-white ajax-link">
                        <i class="bi bi-kanban"></i>Manage Class
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
          <a href="students.php" class="nav-link text-white ajax-link">
            <i class="bi bi-people"></i> Students
          </a>
        </li>
        <li class="nav-item mb-2">
          <a href="results.php" class="nav-link text-white ajax-link">
            <i class="bi bi-trophy"></i> Result
          </a>
        </li>
      </ul>
    </div>

    <!-- Mobile Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="offcanvasSidebar" data-bs-backdrop="false">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">SRA | Admin</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0">
        <ul class="nav flex-column mt-4">
          <li class="nav-item"><a href="home.php" class="nav-link text-white ajax-link"><i class="bi bi-house"></i> Home</a></li>
          <li class="nav-item"><a href="dashboard.php" class="nav-link text-white ajax-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li class="nav-item"><a href="student_classes.php" class="nav-link text-white ajax-link"><i class="bi bi-building"></i> Student Classes</a></li>
          <li class="nav-item"><a href="subjects.php" class="nav-link text-white ajax-link"><i class="bi bi-book"></i> Subjects</a></li>
          <li class="nav-item"><a href="students.php" class="nav-link text-white ajax-link"><i class="bi bi-people"></i> Students</a></li>
          <li class="nav-item"><a href="results.php" class="nav-link text-white ajax-link"><i class="bi bi-trophy"></i> Result</a></li>
        </ul>
      </div>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4">
      <h1>Welcome, Admin!</h1>
      <p>Select an option from the sidebar to get started.</p>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="mt-auto bg-light text-center py-2 border-top">
  © 2025 Student Result Analytics | Admin Panel
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadPage(url) {
  const mainContent = document.getElementById('main-content');
  fetch(url)
    .then(r => { if(!r.ok) throw new Error('Network error'); return r.text(); })
    .then(data => mainContent.innerHTML = data)
    .catch(e => { mainContent.innerHTML = "<p class='text-danger'>Error loading page.</p>"; console.error(e); });
}

function setupSidebarLinks() {
  const links = document.querySelectorAll('.ajax-link');
  links.forEach(link => {
    link.addEventListener('click', function(event) {
      event.preventDefault();
      links.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
      loadPage(this.getAttribute('href'));

      // Mobile offcanvas auto-close
      const offcanvasEl = document.getElementById('offcanvasSidebar');
      const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
      bsOffcanvas.hide();
    });
  });
}

document.addEventListener('DOMContentLoaded', function() {
  setupSidebarLinks();
  loadPage('home.php');
  document.querySelector('.ajax-link[href="home.php"]').classList.add('active');
});

window.history.forward();
function noBack() { window.history.forward(); }
</script>
</body>
</html>
