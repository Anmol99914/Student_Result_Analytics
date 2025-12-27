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
  /* Teacher Management Styles */
  .stats-card {
    transition: transform 0.2s;
    height: 100%;
    cursor: default;
  }
  .stats-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,.1);
  }
  .action-buttons {
    min-width: 220px;
  }
  .badge-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
  }
  .no-data {
    height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .action-dropdown {
    min-width: 200px;
  }
  /* Toast notifications */
.toast-container {
    z-index: 9999;
}

.toast {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

  .bg-success {
      background-color: #198754 !important;
  }

  .bg-danger {
      background-color: #dc3545 !important;
  }

  .bg-info {
      background-color: #0dcaf0 !important;
  }
  /* Modal cleanup fixes */
  .modal-backdrop {
    z-index: 1040;
  }

  .modal {
    z-index: 1050;
  }

  /* Ensure body scrolls properly */
  body.modal-open {
    overflow: auto !important;
    padding-right: 0 !important;
  }
  /* solution for tabs being invisible when selected:) */
#teacherTabs .nav-link {
    color: #6c757d !important;
    background-color: transparent !important;
    border: none !important;
    position: relative;
}

#teacherTabs .nav-link.active {
    color: #0d6efd !important;
    font-weight: 600 !important;
}

#teacherTabs .nav-link.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background-color: #0d6efd;
    border-radius: 3px 3px 0 0;
}

#teacherTabs .nav-link:hover {
    color: #0d6efd !important;
    background-color: rgba(13, 110, 253, 0.05) !important;
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
    <!-- SIMPLIFIED VERSION - Clean BCA Management -->
      <!-- UPDATED Sidebar - Fixed height and spacing -->
<div class="col-12 col-md-3 col-lg-2 bg-dark text-white p-3 d-none d-md-block" style="min-height: calc(100vh - 73px);">
  <h5>SRA | Admin</h5>
  <ul class="nav flex-column mt-4">
    <li class="nav-item mb-2">
      <a href="home.php" class="nav-link text-white ajax-link">
        <i class="bi bi-house"></i> Dashboard
      </a>
    </li>
    
    <!-- BCA Classes - FIXED LINK -->
    <li class="nav-item mb-2">
      <a href="#" onclick="loadBCAClasses(); return false;" class="nav-link text-white">
        <i class="bi bi-mortarboard-fill"></i> BCA Classes
      </a>
    </li>
    
    <!-- Teachers -->
    <li class="nav-item mb-2">
      <a href="teacher_list.php" class="nav-link text-white">
          <i class="bi bi-person-square"></i> Teachers
      </a>
    </li>
    
    <!-- Students -->
    <li class="nav-item mb-2">
      <a href="students_list.php" class="nav-link text-white">
          <i class="bi bi-people"></i> Students
      </a>
    </li>
    
    <!-- Subjects -->
    <li class="nav-item mb-2">
      <a href="subjects.php" class="nav-link text-white">
        <i class="bi bi-book"></i> Subjects
      </a>
    </li>
    
    <!-- Results -->
    <li class="nav-item mb-2">
      <a href="results.php" class="nav-link text-white">
        <i class="bi bi-trophy"></i> Results
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
      reinitializeScripts();
    })
    .catch(error => {
      console.error('Error loading page:', error);
      mainContent.innerHTML = '<div class="alert alert-danger">Error loading content. Please try again.</div>';
    });
}

// Function to reinitialize scripts
function reinitializeScripts() {
  // Reinitialize tooltips
  if (typeof $ !== 'undefined') {
    $('[title]').tooltip('dispose').tooltip({
      trigger: 'hover'
    });
  }
  
  // Reinitialize Bootstrap components
  const modals = document.querySelectorAll('.modal');
  modals.forEach(modal => {
    if (modal.id && !bootstrap.Modal.getInstance(modal)) {
      new bootstrap.Modal(modal);
    }
  });
  
  // Reinitialize dropdowns
  const dropdowns = document.querySelectorAll('.dropdown-toggle');
  dropdowns.forEach(dropdown => {
    if (!bootstrap.Dropdown.getInstance(dropdown)) {
      new bootstrap.Dropdown(dropdown);
    }
  });
}

// Setup AJAX links
function setupAjaxLinks() {
  const links = document.querySelectorAll('.ajax-link');
  links.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Remove active class from all links
      document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
      
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

// Function to load teacher management
function loadTeacherManagement() {
    // Redirect to standalone teacher management page
    window.location.href = 'teacher_list.php';
}
// function loadTeacherManagement() {
//   const mainContent = document.getElementById('main-content');
  
//   // Clean up any existing modal remnants FIRST
//   cleanupModalBackdrops();
  
//   // Update active link
//   document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
//   const teacherLink = document.querySelector('a[href="#"][onclick*="loadTeacherManagement"]');
//   if (teacherLink) teacherLink.classList.add('active');
  
//   // Show loading
//   mainContent.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading Teacher Management...</p></div>';
  
//   // Close mobile offcanvas if open
//   const offcanvas = document.getElementById('offcanvasSidebar');
//   if (offcanvas) {
//     const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvas);
//     if (bsOffcanvas) bsOffcanvas.hide();
//   }
  
//   // Load teacher management content WITHOUT action parameter
//   fetch('admin_teachers_content.php')
//     .then(response => response.text())
//     .then(html => {
//       mainContent.innerHTML = html;
      
//       // Initialize teacher management functionality
//       initTeacherManagementFunctionality();
//     })
//     .catch(error => {
//       console.error('Error loading teacher management:', error);
//       mainContent.innerHTML = '<div class="alert alert-danger">Error loading teacher management. Please try again.</div>';
//     });
// }


// Initialize teacher management functionality
function initTeacherManagementFunctionality() {
  console.log('Initializing teacher management...');
  
  // Check if we have the tab container
  const teacherTabs = document.getElementById('teacherTabs');
  if (!teacherTabs) {
    console.error('Teacher tabs container not found!');
    return;
  }
  
  // Set up tab switching
  const tabLinks = teacherTabs.querySelectorAll('.nav-link');
  tabLinks.forEach(tab => {
    // Remove existing event listeners
    const newTab = tab.cloneNode(true);
    tab.parentNode.replaceChild(newTab, tab);
    
    // Add click event to the new tab
    newTab.addEventListener('click', function(e) {
      e.preventDefault();
      const tabName = this.getAttribute('data-tab');
      if (tabName) {
        console.log('Switching to tab:', tabName);
        loadTeacherTab(tabName);
        
        // Update active state
        tabLinks.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
      }
    });
  });
  
  // Load initial tab
  const activeTab = tabLinks.length > 0 ? 
    (teacherTabs.querySelector('.nav-link.active')?.getAttribute('data-tab') || 'active') : 
    'active';
  
  console.log('Loading initial tab:', activeTab);
  loadTeacherTab(activeTab);
}

// Function to load teacher tab - UPDATED
function loadTeacherTab(tab, page = 1, limit = 10) {
    const container = document.getElementById('teachers-table-container');
    
    if (!container) {
        console.error('Teachers table container not found!');
        return;
    }
    
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading teachers...</p></div>';
    
    // Load from admin_teachers_table.php with parameters
    fetch(`admin_teachers_table.php?tab=${tab}&page=${page}&limit=${limit}`)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
            
            // Reinitialize scripts for the new content
            initTeacherTableScripts();
            
            // Update active tab UI
            updateActiveTabUI(tab);
            
            // Update URL without reloading page
            updateURL(tab, page, limit);
        })
        .catch(error => {
            console.error('Error loading teacher tab:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading teachers. Please try again.</div>';
        });
}

// Function to refresh teacher stats in real-time
function refreshTeacherStats() {
    console.log('Refreshing teacher stats...');
    
    // Fetch updated stats
    fetch('get_teacher_stats.php')
        .then(response => response.json())
        .then(stats => {
            console.log('New stats received:', stats);
            
            // Update the dashboard stats if they exist
            const totalElement = document.querySelector('[class*="border-left-primary"] .h5');
            const activeElement = document.querySelector('[class*="border-left-success"] .h5');
            const inactiveElement = document.querySelector('[class*="border-left-secondary"] .h5');
            const unassignedElement = document.querySelector('[class*="border-left-info"] .h5');
            const teachingElement = document.querySelector('[class*="border-left-warning"] .h5');
            const assignedElement = document.querySelector('[class*="border-left-danger"] .h5');
            
            if (totalElement) totalElement.textContent = stats.total || 0;
            if (activeElement) activeElement.textContent = stats.active || 0;
            if (inactiveElement) inactiveElement.textContent = stats.inactive || 0;
            if (unassignedElement) unassignedElement.textContent = stats.unassigned || 0;
            if (teachingElement) teachingElement.textContent = stats.teaching || 0;
            if (assignedElement) assignedElement.textContent = (stats.assigned_percent || 0) + '%';
            
            // Show subtle animation to indicate update
            const statCards = document.querySelectorAll('.stats-card');
            statCards.forEach(card => {
                card.style.transition = 'all 0.3s';
                card.style.boxShadow = '0 0 15px rgba(0,123,255,0.3)';
                
                setTimeout(() => {
                    card.style.boxShadow = '';
                }, 800);
            });
        })
        .catch(error => {
            console.error('Error refreshing stats:', error);
        });
}

// Update active tab UI - ADD THIS FUNCTION
function updateActiveTabUI(tab) {
    // Update tab navigation
    document.querySelectorAll('#teacherTabs .nav-link').forEach(navLink => {
        if (navLink.getAttribute('data-tab') === tab) {
            navLink.classList.add('active');
        } else {
            navLink.classList.remove('active');
        }
    });
}

// Update URL in address bar
function updateURL(tab, page, limit) {
  const url = new URL(window.location);
  url.searchParams.set('tab', tab);
  url.searchParams.set('page', page);
  url.searchParams.set('limit', limit);
  window.history.replaceState({}, '', url);
}

// Function to update active tab UI
function updateActiveTabUI(tab) {
  // Update tab navigation
  document.querySelectorAll('#teacherTabs .nav-link').forEach(navLink => {
    if (navLink.getAttribute('data-tab') === tab) {
      navLink.classList.add('active');
    } else {
      navLink.classList.remove('active');
    }
  });
  
  // Update tab content
  document.querySelectorAll('.tab-pane').forEach(tabPane => {
    if (tabPane.id === `${tab}-tab`) {
      tabPane.classList.add('show', 'active');
    } else {
      tabPane.classList.remove('show', 'active');
    }
  });
}

// Initialize teacher table scripts - UPDATED with better event delegation
function initTeacherTableScripts() {
  console.log('Initializing teacher table scripts...');
  
  // Use event delegation for the entire container
  const container = document.getElementById('teachers-table-container');
  if (!container) {
    console.error('Teachers table container not found in initTeacherTableScripts');
    return;
  }
  
  // Set up event delegation for the container
  container.addEventListener('click', handleTeacherTableClick);
  
  // Set up per page selector
  const perPageSelect = document.getElementById('perPage');
  if (perPageSelect) {
    perPageSelect.addEventListener('change', function() {
      const perPage = this.value;
      const activeTab = document.querySelector('#teacherTabs .nav-link.active')?.getAttribute('data-tab') || 'active';
      console.log('Changing records per page to:', perPage, 'for tab:', activeTab);
      loadTeacherTab(activeTab, 1, perPage);
    });
  }
}

// Handle teacher table clicks (activate/deactivate)
function handleTeacherTableClick(e) {
  const target = e.target;
  const container = document.getElementById('teachers-table-container');
  
  // Handle activate/deactivate buttons
  const statusBtn = target.closest('.deactivate-btn, .reactivate-btn');
  if (statusBtn) {
    e.preventDefault();
    e.stopPropagation();
    
    // Get confirmation message from onclick attribute
    // let confirmMsg = 'Are you sure?';
    // Check which button was clicked
    const isDeactivateBtn = statusBtn.classList.contains('deactivate-btn');
        
        // Set appropriate confirmation message
        let confirmMsg;
        if (isDeactivateBtn) {
            confirmMsg = 'Deactivate Teacher?\n\n' +
                         'This will:\n' +
                         '• Change status to INACTIVE\n' +
                         '• Remove from all classes\n' +
                         '• Disable login access';
        } else {
            confirmMsg = 'Activate Teacher?\n\n' +
                         'This will:\n' +
                         '• Change status to ACTIVE\n' +
                         '• Allow class assignments\n' +
                         '• Enable login access';
        }
    const onclickAttr = statusBtn.getAttribute('onclick');
    if (onclickAttr) {
      const match = onclickAttr.match(/confirm\('([^']+)'\)/);
      if (match && match[1]) {
        confirmMsg = match[1];
      }
    }
    
    // Show custom confirmation
    if (!confirm(confirmMsg)) {
      return;
    }
    
    const href = statusBtn.getAttribute('href');
    
    // Show loading
    if (container) {
      container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing...</p></div>';
    }
    
    // Parse parameters from href
    const url = new URL(href, window.location.origin);
    const teacherId = url.searchParams.get('deactivate') || url.searchParams.get('reactivate');
    const tab = url.searchParams.get('tab') || 'active';
    const page = url.searchParams.get('page') || 1;
    const limit = url.searchParams.get('limit') || 10;
    
    // Determine action
    const action = statusBtn.classList.contains('deactivate-btn') ? 'deactivate' : 'activate';
    
    console.log(`Processing ${action} for teacher ID: ${teacherId}, tab: ${tab}, page: ${page}`);
    
    // Send AJAX request
    fetch(`admin_teachers_content.php?action=${action}&id=${teacherId}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Reload the current tab
          loadTeacherTab(tab, page, limit);
          
          // Refresh dashboard stats
          refreshTeacherStats();
          
          // Show success message
          showAlert('success', data.message || `Teacher ${action}d successfully`);
        } else {
          throw new Error(data.message || `Failed to ${action} teacher`);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        if (container) {
          container.innerHTML = '<div class="alert alert-danger">Error processing request. Please try again.</div>';
        }
        showAlert('danger', 'Error: ' + error.message);
      });
    
    return;
  }
  
  // Handle edit button clicks
  if (target.closest('.edit-teacher-btn')) {
    e.preventDefault();
    const editBtn = target.closest('.edit-teacher-btn');
    const teacherId = editBtn.getAttribute('data-teacher-id');
    
    if (teacherId && teacherId !== '0') {
      showEditTeacherModal(teacherId);
    }
    return;
  }
  
  // Handle pagination links
  if (target.closest('.page-link')) {
    e.preventDefault();
    const pageLink = target.closest('.page-link');
    const href = pageLink.getAttribute('href');
    
    if (href && href !== '#') {
      const url = new URL(href, window.location.origin);
      const tab = url.searchParams.get('tab') || 'active';
      const page = url.searchParams.get('page') || 1;
      const limit = url.searchParams.get('limit') || 10;
      
      loadTeacherTab(tab, page, limit);
    }
    return;
  }
}


// Handle all clicks in the container
function handleContainerClick(e) {
  const target = e.target;
  
  // Handle edit button clicks
  const editButton = target.closest('.edit-teacher-btn');
  if (editButton) {
    e.preventDefault();
    e.stopPropagation();
    
    const teacherId = editButton.getAttribute('data-teacher-id');
    console.log('Edit button clicked, teacherId:', teacherId);
    
    if (!teacherId || teacherId === '0') {
      console.error('Invalid teacher ID from button:', teacherId);
      showAlert('danger', 'Invalid teacher ID');
      return;
    }
    
    showEditTeacherModal(teacherId);
    return;
  }
  
  // Handle pagination links
  const pageLink = target.closest('.page-link');
  if (pageLink) {
    e.preventDefault();
    const href = pageLink.getAttribute('href');
    const url = new URL(href, window.location.origin);
    const tab = url.searchParams.get('tab') || 'active';
    const page = url.searchParams.get('page') || 1;
    const limit = url.searchParams.get('limit') || 10;
    
    console.log('Loading page:', page, 'for tab:', tab);
    loadTeacherTab(tab, page, limit);
    return;
  }
  
  // Handle activate/deactivate buttons
  const statusButton = target.closest('.deactivate-btn, .reactivate-btn');
  if (statusButton) {
    e.preventDefault();
    
    // Get confirmation message
    let confirmMsg = 'Are you sure?';
    const onclickAttr = statusButton.getAttribute('onclick');
    if (onclickAttr) {
      const match = onclickAttr.match(/confirm\('([^']+)'/);
      if (match) confirmMsg = match[1];
    }
    
    if (!confirm(confirmMsg)) {
      return;
    }
    
    const href = statusButton.getAttribute('href');
    const container = document.getElementById('teachers-table-container');
    if (container) {
      container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing...</p></div>';
    }
    
    // Get current tab and page from URL
    const url = new URL(href, window.location.origin);
    const tab = url.searchParams.get('tab') || 'active';
    const page = url.searchParams.get('page') || 1;
    const limit = url.searchParams.get('limit') || 10;
    
    console.log('Updating teacher status, tab:', tab, 'page:', page);
    
    fetch(href)
      .then(response => response.text())
      .then(html => {
        // Reload the teacher tab
        loadTeacherTab(tab, page, limit);
        showAlert('success', statusButton.classList.contains('deactivate-btn') ? 'Teacher deactivated successfully' : 'Teacher activated successfully');
      })
      .catch(error => {
        console.error('Error:', error);
        if (container) {
          container.innerHTML = '<div class="alert alert-danger">Error processing request. Please try again.</div>';
        }
        showAlert('danger', 'Error processing request');
      });
    
    return;
  }
}

// Handle per page change
function handlePerPageChange() {
  const perPage = this.value;
  const activeTab = document.querySelector('#teacherTabs .nav-link.active')?.getAttribute('data-tab') || 'active';
  console.log('Changing per page to:', perPage, 'for tab:', activeTab);
  loadTeacherTab(activeTab, 1, perPage);
}

// Handle edit teacher click
function handleEditTeacherClick(e) {
  e.preventDefault();
  e.stopPropagation();
  
  const button = e.currentTarget;
  const teacherId = button.getAttribute('data-teacher-id');
  
  console.log('Edit button clicked, teacherId:', teacherId);
  
  if (!teacherId || teacherId === '0') {
    console.error('Invalid teacher ID from button:', teacherId);
    showAlert('danger', 'Invalid teacher ID');
    return;
  }
  
  showEditTeacherModal(teacherId);
}

// Toggle teacher status action
function toggleTeacherStatusAction(teacherId, action) {
  const container = document.getElementById('teachers-table-container');
  if (container) {
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing...</p></div>';
  }
  
  fetch(`admin_teachers_content.php?action=${action}&id=${teacherId}`)
    .then(response => response.text())
    .then(html => {
      // Reload the teacher management
      loadTeacherManagement();
      showAlert('success', action === 'activate' ? 'Teacher activated successfully' : 'Teacher deactivated successfully');
    })
    .catch(error => {
      console.error('Error:', error);
      if (container) {
        container.innerHTML = '<div class="alert alert-danger">Error processing request. Please try again.</div>';
      }
      showAlert('danger', 'Error processing request');
    });
}

// Show edit teacher modal
function showEditTeacherModal(teacherId) {
  console.log('Opening edit modal for teacher ID:', teacherId);
  
  // Clean up any existing modal first
  cleanupModalBackdrops();
  
  // Remove any existing modal
  const existingModal = document.getElementById('editTeacherModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  // Create modal HTML
  const modalHTML = `
    <div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="editTeacherModalLabel"><i class="bi bi-pencil me-2"></i>Edit Teacher</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="edit-teacher-loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading teacher details...</p>
            </div>
            <form id="editTeacherForm" style="display: none;">
              <input type="hidden" name="teacher_id" id="edit_teacher_id">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email <span class="text-danger">*</span></label>
                  <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" id="edit_status" class="form-select">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Change Password (Optional)</label>
                  <input type="text" name="new_password" class="form-control" placeholder="Leave blank to keep current password">
                  <small class="text-muted">Only enter if you want to change the password</small>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="editTeacherForm" class="btn btn-primary" id="saveTeacherBtn">
              <span id="saveTeacherSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
  
  // Add modal to body
  const modalContainer = document.createElement('div');
  modalContainer.innerHTML = modalHTML;
  document.body.appendChild(modalContainer.firstElementChild);
  
  // Show modal
  const modalElement = document.getElementById('editTeacherModal');
  const modal = new bootstrap.Modal(modalElement);
  
  // Add event listener to clean up when modal is hidden
  modalElement.addEventListener('hidden.bs.modal', function() {
    // Remove modal from DOM after a delay
    setTimeout(() => {
      if (modalElement.parentNode) {
        modalElement.remove();
      }
    }, 300);
  });
  
  modal.show();
  
  // Load teacher data
  loadTeacherDataForEdit(teacherId, modal);
}

// Load teacher data for editing - using admin_edit_teacher.php
function loadTeacherDataForEdit(teacherId, modal) {
  const loadingDiv = document.getElementById('edit-teacher-loading');
  const form = document.getElementById('editTeacherForm');
  
  console.log('Loading teacher data for ID:', teacherId);
  
  // Use fetch to get teacher data
  fetch(`get_teacher.php?id=${teacherId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Teacher data received:', data);
      
      if (data.error) {
        throw new Error(data.error);
      }
      
      // Populate form fields
      document.getElementById('edit_teacher_id').value = data.teacher_id || teacherId;
      document.getElementById('edit_name').value = data.name || '';
      document.getElementById('edit_email').value = data.email || '';
      document.getElementById('edit_status').value = data.status || 'active';
      
      // Hide loading, show form
      loadingDiv.style.display = 'none';
      form.style.display = 'block';
      
      // Set up form submission
      setupEditTeacherForm(modal);
    })
    .catch(error => {
      console.error('Error loading teacher data:', error);
      loadingDiv.innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> Error loading teacher data: ${error.message}
        </div>
      `;
    });
}

// Set up edit teacher form submission
function setupEditTeacherForm(modal) {
  const form = document.getElementById('editTeacherForm');
  if (!form) return;
  
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const saveBtn = document.getElementById('saveTeacherBtn');
    const spinner = document.getElementById('saveTeacherSpinner');
    const formData = new FormData(this);
    
    // Add action parameter
    formData.append('action', 'update_teacher');
    
    // Show loading
    saveBtn.disabled = true;
    spinner.classList.remove('d-none');
    
    // Submit to admin_teachers_content.php
    fetch('admin_teachers_content.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Show success message
        showAlert('success', data.message || 'Teacher updated successfully');

        //Refresh stats
        refreshTeacherStats();
        
        // Close modal
        setTimeout(() => {
          modal.hide();
          cleanupModalBackdrops();
          
          // Get current tab and reload
          const activeTab = document.querySelector('#teacherTabs .nav-link.active')?.getAttribute('data-tab') || 'active';
          setTimeout(() => {
            loadTeacherTab(activeTab);
          }, 500);
        }, 1000);
      } else {
        throw new Error(data.message || 'Failed to update teacher');
      }
    })
    .catch(error => {
      console.error('Error updating teacher:', error);
      showAlert('danger', 'Error: ' + error.message);
      
      // Reset button state
      saveBtn.disabled = false;
      spinner.classList.add('d-none');
    });
  });
}

// For activate/deactivate actions, update to use your endpoint:
function handleStatusChange(teacherId, action) {
  const confirmMsg = action === 'deactivate' ? 
    'Deactivate this teacher? They will be removed from all classes.' : 
    'Reactivate this teacher?';
  
  if (!confirm(confirmMsg)) return;
  
  const container = document.getElementById('teachers-table-container');
  if (container) {
    container.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Processing...</p></div>';
  }
  
  // Use your admin_teachers_content.php endpoint
  fetch(`admin_teachers_content.php?action=${action}&id=${teacherId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Get current tab and reload
        const activeTab = document.querySelector('#teacherTabs .nav-link.active')?.getAttribute('data-tab') || 'active';
        const currentPage = new URL(window.location).searchParams.get('page') || 1;
        const currentLimit = new URL(window.location).searchParams.get('limit') || 10;
        
        loadTeacherTab(activeTab, currentPage, currentLimit);
        showAlert('success', data.message || `Teacher ${action}d successfully`);
      } else {
        throw new Error(data.message || `Failed to ${action} teacher`);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      if (container) {
        container.innerHTML = '<div class="alert alert-danger">Error processing request. Please try again.</div>';
      }
      showAlert('danger', error.message);
    });
    
}
// Clean up modal backdrops
function cleanupModalBackdrops() {
  // Remove all modal backdrops
  const backdrops = document.querySelectorAll('.modal-backdrop');
  backdrops.forEach(backdrop => backdrop.remove());
  
  // Remove modal-open class
  document.body.classList.remove('modal-open');
  document.body.style.overflow = '';
  document.body.style.paddingRight = '';
}

// Show alert message
function showAlert(type, message) {
  // Remove existing alerts
  const existingAlerts = document.querySelectorAll('.alert-dismissible');
  existingAlerts.forEach(alert => alert.remove());
  
  // Create new alert
  const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
  const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
  
  const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 1060; min-width: 300px;">
      <i class="bi bi-${icon} me-2"></i>
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  `;
  
  // Add alert to body
  const alertContainer = document.createElement('div');
  alertContainer.innerHTML = alertHtml;
  document.body.appendChild(alertContainer.firstElementChild);
  
  // Auto-remove after 5 seconds
  setTimeout(() => {
    const alert = document.querySelector('.alert-dismissible');
    if (alert) {
      alert.remove();
    }
  }, 5000);
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

// Setup Add Teacher Form submission
function setupAddTeacherForm() {
    const form = document.getElementById('addTeacherForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const msgDiv = document.getElementById('add-teacher-msg');
        
        // Clear previous messages
        msgDiv.innerHTML = '';
        
        // Basic validation
        const password = formData.get('password');
        const confirmPassword = formData.get('confirm_password');
        
        if (password !== confirmPassword) {
            msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Passwords do not match!</div>';
            return;
        }
        
        if (password.length < 6) {
            msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Password must be at least 6 characters!</div>';
            return;
        }
        
        // Show loading
        msgDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Adding teacher...</div>';
        
        // Submit via AJAX
        fetch('admin_add_teacher.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success message
                msgDiv.innerHTML = `
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> ${data.message}
                        <div class="mt-3">
                            <strong>Teacher Details:</strong>
                            <div class="card mt-2">
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Name:</strong></td>
                                            <td>${formData.get('name')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>${formData.get('email')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>${formData.get('status')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Teacher ID:</strong></td>
                                            <td>#${data.teacher_id}</td>
                                        </tr>
                                    </table>
                                    <div class="alert alert-warning mt-2">
                                        <i class="bi bi-exclamation-triangle"></i>
                                        <strong>Important:</strong> Teacher can login using their email and the password you set.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Reset form
                form.reset();

                //Refresh stats
                refreshTeacherStats();
                
                // Add buttons to go back or add another
                setTimeout(() => {
                    msgDiv.innerHTML += `
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-primary" onclick="loadTeacherManagement()">
                                <i class="bi bi-arrow-left"></i> Back to Teacher List
                            </button>
                            <button class="btn btn-outline-secondary" onclick="resetAddTeacherForm()">
                                <i class="bi bi-plus-circle"></i> Add Another Teacher
                            </button>
                        </div>
                    `;
                }, 500);
                
            } else {
                // Error message
                msgDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Network error. Please try again.</div>';
        });
    });
}
// Reset add teacher form
function resetAddTeacherForm() {
  const form = document.getElementById('addTeacherForm');
  if (form) {
    form.reset();
    document.getElementById('add-teacher-msg').innerHTML = '';
  }
}

// Load classes for teacher assignment dropdown
function loadClassesForTeacherAssignment() {
    const classSelect = document.getElementById('addTeacherClassSelect');
    if (!classSelect) return;
    
    fetch('get_classes.php')
        .then(response => response.json())
        .then(classes => {
            classSelect.innerHTML = '<option value="">-- Not Assigned --</option>';
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
        loadAllClasses();
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
    alert('Delete functionality for class ID: ' + classId + ' will be implemented');
  }
}

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

// Show Add Teacher Form - UPDATED with status field
function showAddTeacherForm() {
    const mainContent = document.getElementById('main-content');
    
    mainContent.innerHTML = `
        <div class="card shadow">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add New Teacher</h5>
                <button type="button" class="btn btn-light btn-sm" onclick="loadTeacherManagement()">
                    <i class="bi bi-arrow-left"></i> Back to Teachers
                </button>
            </div>
            <div class="card-body">
                <form id="addTeacherForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required 
                                   placeholder="Enter full name">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required 
                                   placeholder="teacher@example.com">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required 
                                   minlength="6" placeholder="Minimum 6 characters">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" required 
                                   placeholder="Retype password">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Assign to Class (Optional)</label>
                            <select name="assigned_class_id" id="addTeacherClassSelect" class="form-select">
                                <option value="">-- Not Assigned --</option>
                            </select>
                            <small class="text-muted">You can assign a class later</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Notes:</h6>
                        <ul class="mb-0">
                            <li>Teacher will use their email and password to login</li>
                            <li>Make sure to provide a strong password</li>
                            <li>Class assignment can be done later</li>
                            <li>Default status is "active"</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Add Teacher
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetAddTeacherForm()">
                            <i class="bi bi-x-circle"></i> Reset Form
                        </button>
                    </div>
                </form>
                <div id="add-teacher-msg" class="mt-4"></div>
            </div>
        </div>
    `;
    
    // Load classes for assignment dropdown
    loadClassesForTeacherAssignment();
    
    // Setup form submission
    setupAddTeacherForm();
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
        loadAllStudents();
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
      loadAllStudents();
    })
    .catch(error => {
      alert('Error deleting student');
      console.error('Error:', error);
    });
  }
}


// Debug: Check page structure
function debugPageStructure() {
  console.log('=== PAGE STRUCTURE DEBUG ===');
  
  // Check tabs
  const teacherTabs = document.getElementById('teacherTabs');
  console.log('Teacher tabs exists:', !!teacherTabs);
  if (teacherTabs) {
    const tabLinks = teacherTabs.querySelectorAll('.nav-link');
    console.log('Number of tab links:', tabLinks.length);
    tabLinks.forEach((link, i) => {
      console.log(`Tab ${i}:`, {
        text: link.textContent.trim(),
        dataTab: link.getAttribute('data-tab'),
        isActive: link.classList.contains('active')
      });
    });
  }
  
  // Check container
  const container = document.getElementById('teachers-table-container');
  console.log('Table container exists:', !!container);
  
  // Check for edit buttons
  const editButtons = document.querySelectorAll('.edit-teacher-btn');
  console.log('Number of edit buttons:', editButtons.length);
  if (editButtons.length > 0) {
    console.log('First edit button data:', {
      dataTeacherId: editButtons[0].getAttribute('data-teacher-id'),
      onclick: editButtons[0].getAttribute('onclick')
    });
  }
  
  console.log('=== END DEBUG ===');
}

// Call after loading: debugPageStructure();

// To see if tables are at least being found:
  function switchTab(tabName) {
    console.log(`=== SWITCHING TO TAB: ${tabName} ===`);
    
    // Hide all tab contents
    $('.tab-content').each(function() {
        console.log(`Hiding: ${this.id}`);
        $(this).hide();
    });
    
    // Show selected tab
    const tabContentId = `${tabName}-teachers`;
    const $tabContent = $(`#${tabContentId}`);
    
    if ($tabContent.length === 0) {
        console.error(`❌ Tab content #${tabContentId} not found!`);
        return;
    }
    
    console.log(`✅ Showing: ${tabContentId}`);
    $tabContent.show();
    
    // Check if table exists
    const tableId = `${tabName}-teachers-table`;
    const $table = $(`#${tableId}`);
    
    if ($table.length === 0) {
        console.error(`❌ Table #${tableId} not found in tab ${tabName}!`);
        
        // List all tables on page
        console.log("Available tables on page:");
        $('table').each(function() {
            console.log(`- ${this.id || 'No ID'}`);
        });
    } else {
        console.log(`✅ Table ${tableId} found, initializing...`);
    }
    
    // Initialize table
    initializeTeacherTable(tabName);
}

// BCA 
// Add to your existing JavaScript
function loadBCAClassManagement() {
    $('#main-content').load('admin_classes.php');
}

function showAddBCAClass() {
    $('#main-content').load('admin_add_class.php');
}

function showBatchReports() {
    $('#main-content').html(`
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Batch Performance Reports</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Select Batch Year</label>
                        <select class="form-select" id="batchYear">
                            <option value="">All Batches</option>
                            ${getBatchYears()}
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Select Semester</label>
                        <select class="form-select" id="semester">
                            <option value="">All Semesters</option>
                            ${Array.from({length: 8}, (_, i) => `<option value="${i+1}">Semester ${i+1}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" onclick="generateBatchReport()">
                            <i class="bi bi-file-earmark-text"></i> Generate Report
                        </button>
                    </div>
                </div>
                <div id="report-result"></div>
            </div>
        </div>
    `);
}

// Add this function to handle BCA Classes navigation
function loadBCAClasses() {
    // Clear main content and show loading
    $('#main-content').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading BCA Classes Management...</p>
        </div>
    `);
    
    // Load admin_classes.php
    $.ajax({
        url: 'admin_classes.php',
        type: 'GET',
        success: function(data) {
            $('#main-content').html(data);
        },
        error: function() {
            $('#main-content').html(`
                <div class="alert alert-danger">
                    <h5><i class="bi bi-exclamation-triangle"></i> Error Loading BCA Classes</h5>
                    <p>Could not load the BCA Classes page. Please check:</p>
                    <ul>
                        <li>File <code>admin_classes.php</code> exists in admin folder</li>
                        <li>File permissions are correct</li>
                    </ul>
                    <a href="admin_main_page.php" class="btn btn-primary">Return to Dashboard</a>
                </div>
            `);
        }
    });
}

// Update the BCA Classes link to use this function
function updateBCAClassesLink() {
    $('a[href="admin_classes.php"]').attr('href', '#');
    $('a[href="admin_classes.php"]').attr('onclick', 'loadBCAClasses(); return false;');
}

function getBatchYears() {
    // This would fetch from your database
    const currentYear = new Date().getFullYear();
    let options = '';
    for(let year = currentYear - 2; year <= currentYear + 1; year++) {
        options += `<option value="${year}">${year} Batch</option>`;
    }
    return options;
}

// Also, check if tables exist on page load
$(document).ready(function() {
    console.log("=== PAGE LOADED ===");
    console.log("All tab-content elements:");
    $('.tab-content').each(function() {
        console.log(`- ${this.id} (display: ${$(this).css('display')})`);
    });
    
    console.log("All teacher tables:");
    $('[id$="-teachers-table"]').each(function() {
        console.log(`- ${this.id}`);
    });
});

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
  setupAjaxLinks();
  cleanupModalBackdrops();
  
  // Make Home link active by default
  const homeLink = document.querySelector('.ajax-link[href="home.php"]');
  if (homeLink) {
    homeLink.classList.add('active');
  }
  
  // Set up teacher link
  const teacherLink = document.querySelector('a[href="#"][onclick*="loadTeacherManagement"]');
  if (teacherLink) {
    teacherLink.addEventListener('click', function(e) {
      e.preventDefault();
      loadTeacherManagement();
      
      // Update active state
      document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
      this.classList.add('active');
    });
  }
});

// Prevent back button
window.history.forward();
function noBack() { 
  window.history.forward(); 
}
</script>

</body>
</html>