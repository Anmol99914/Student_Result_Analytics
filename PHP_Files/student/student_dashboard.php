<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] != true) {
    header("Location: student_login.php");
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Student Result Analytics | Student</title>

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
    <div class="d-flex align-items-center">
      <button class="btn btn-outline-primary d-md-none me-2" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
        <i class="bi bi-list"></i> Menu
      </button>
      <a class="navbar-brand mb-0">Student Result Analytics | Student</a>
    </div>

    <form class="d-flex ms-auto" action="student_logout.php">
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
    <div class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3 d-none d-md-block vh-100">
      <h5>SRA | Student</h5>

      <ul class="nav flex-column mt-4">
        <li class="nav-item mb-2">
          <a href="student_home.php" class="nav-link text-white ajax-link">
            <i class="bi bi-house"></i> Home
          </a>
        </li>

        <li class="nav-item mb-2">
          <a href="student_profile.php" class="nav-link text-white ajax-link">
            <i class="bi bi-person"></i> My Profile
          </a>
        </li>

        <li class="nav-item mb-2">
          <a href="student_results.php" class="nav-link text-white ajax-link">
            <i class="bi bi-trophy"></i> My Results
          </a>
        </li>

        <li class="nav-item mb-2">
          <a href="student_payments.php" class="nav-link text-white ajax-link">
            <i class="bi bi-credit-card"></i> Payments
          </a>
        </li>
      </ul>
    </div>

    <!-- Mobile Sidebar -->
    <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">SRA | Student</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
      </div>

      <div class="offcanvas-body p-0">
        <ul class="nav flex-column mt-4">
          <li class="nav-item">
            <a href="student_home.php" class="nav-link text-white ajax-link">
              <i class="bi bi-house"></i> Home
            </a>
          </li>
          <li class="nav-item">
            <a href="student_profile.php" class="nav-link text-white ajax-link">
              <i class="bi bi-person"></i> My Profile
            </a>
          </li>
          <li class="nav-item">
            <a href="student_results.php" class="nav-link text-white ajax-link">
              <i class="bi bi-trophy"></i> My Results
            </a>
          </li>
          <li class="nav-item">
            <a href="student_payments.php" class="nav-link text-white ajax-link">
              <i class="bi bi-credit-card"></i> Payments
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- Main Content -->
    <div id="main-content" class="col-12 col-md-9 col-lg-10 p-4">
      <h1>Welcome, <?php echo $_SESSION['student_username']; ?>!</h1>
      <p>Use the sidebar to view your profile, results, and payment status.</p>
    </div>

  </div>
</div>

<!-- Footer -->
<footer class="mt-auto bg-light text-center py-2 border-top">
  © 2025 Student Result Analytics | Student Panel
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

<script>
function loadPage(url) {
  fetch(url)
    .then(r => r.text())
    .then(data => document.getElementById('main-content').innerHTML = data)
    .catch(() => document.getElementById('main-content').innerHTML =
      "<p class='text-danger'>Error loading page.</p>");
}

function setupLinks() {
  const links = document.querySelectorAll('.ajax-link');
  links.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      links.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
      loadPage(this.getAttribute('href'));

      const offcanvas = bootstrap.Offcanvas.getOrCreateInstance(
        document.getElementById('offcanvasSidebar')
      );
      offcanvas.hide();
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  setupLinks();
  loadPage('student_home.php');
  document.querySelector('.ajax-link[href="student_home.php"]').classList.add('active');
});

window.history.forward();
function noBack() { window.history.forward(); }

window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});

</script>

</body>
</html>
