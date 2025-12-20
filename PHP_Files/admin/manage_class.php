<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0"); 

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    exit("Unauthorized access.");
}
?>

<div class="card shadow">
  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="bi bi-kanban me-2"></i>Manage Classes</h5>
    <div>
      <a href="#" onclick="loadPage('add_class_form.php'); return false;" class="btn btn-light btn-sm">
        <i class="bi bi-plus-circle"></i> Add New Class
      </a>
    </div>
  </div>
  <div class="card-body">
    <!-- Filter Controls -->
    <div class="row mb-4">
      <div class="col-md-3">
        <select id="filterFaculty" class="form-select" onchange="filterClasses()">
          <option value="">All Faculties</option>
          <option value="BCA">BCA</option>
          <option value="BBM">BBM</option>
          <option value="BIM">BIM</option>
        </select>
      </div>
      <div class="col-md-3">
        <select id="filterSemester" class="form-select" onchange="filterClasses()">
          <option value="">All Semesters</option>
          <?php for($i=1; $i<=8; $i++): ?>
            <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-3">
        <select id="filterStatus" class="form-select" onchange="filterClasses()">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100" onclick="resetFilters()">
          <i class="bi bi-arrow-clockwise"></i> Reset Filters
        </button>
      </div>
    </div>
    
    <!-- Classes Table -->
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  loadClasses();
});

function loadClasses() {
  const container = document.getElementById('classes-container');
  
  fetch('../../php_files/admin/get_classes.php')
    .then(res => res.json())
    .then(classes => {
      if (classes.length === 0) {
        container.innerHTML = `
          <div class="alert alert-info text-center">
            <i class="bi bi-info-circle"></i> No classes found. 
            <a href="#" onclick="loadPage('add_class_form.php'); return false;" class="alert-link">
              Create your first class!
            </a>
          </div>
        `;
        return;
      }
      
      renderClassesTable(classes);
    })
    .catch(error => {
      container.innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle"></i> Error loading classes. Please try again.
        </div>
      `;
      console.error('Error:', error);
    });
}

function renderClassesTable(classes) {
  let html = `
    <div class="table-responsive">
      <table class="table table-hover table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Faculty</th>
            <th>Semester</th>
            <th>Teacher</th>
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
      <tr data-faculty="${cls.faculty}" data-semester="${cls.semester}" data-status="${cls.status}">
        <td>${cls.class_id}</td>
        <td><strong>${cls.faculty}</strong></td>
        <td>Semester ${cls.semester}</td>
        <td>${teacherInfo}</td>
        <td>${statusBadge}</td>
        <td>${new Date(cls.created_at).toLocaleDateString()}</td>
        <td>
          <div class="btn-group" role="group">
            <button class="btn btn-sm btn-outline-primary" onclick="editClass(${cls.class_id})" title="Edit">
              <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-outline-warning" onclick="toggleClassStatus(${cls.class_id}, '${cls.status}')" title="${cls.status === 'active' ? 'Deactivate' : 'Activate'}">
              <i class="bi bi-power"></i>
            </button>
            <button class="btn btn-sm btn-outline-danger" onclick="deleteClass(${cls.class_id})" title="Delete">
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
  
  document.getElementById('classes-container').innerHTML = html;
}

function filterClasses() {
  const facultyFilter = document.getElementById('filterFaculty').value;
  const semesterFilter = document.getElementById('filterSemester').value;
  const statusFilter = document.getElementById('filterStatus').value;
  
  const rows = document.querySelectorAll('#classes-container tbody tr');
  
  rows.forEach(row => {
    const faculty = row.getAttribute('data-faculty');
    const semester = row.getAttribute('data-semester');
    const status = row.getAttribute('data-status');
    
    let show = true;
    
    if (facultyFilter && faculty !== facultyFilter) show = false;
    if (semesterFilter && semester !== semesterFilter) show = false;
    if (statusFilter && status !== statusFilter) show = false;
    
    row.style.display = show ? '' : 'none';
  });
}

function resetFilters() {
  document.getElementById('filterFaculty').value = '';
  document.getElementById('filterSemester').value = '';
  document.getElementById('filterStatus').value = '';
  
  const rows = document.querySelectorAll('#classes-container tbody tr');
  rows.forEach(row => {
    row.style.display = '';
  });
}

function editClass(classId) {
  // You can implement edit functionality here
  alert('Edit class ' + classId + ' - Implement edit functionality');
}

function toggleClassStatus(classId, currentStatus) {
  const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
  const confirmMsg = currentStatus === 'active' 
    ? 'Are you sure you want to deactivate this class?' 
    : 'Are you sure you want to activate this class?';
  
  if (confirm(confirmMsg)) {
    fetch('../../php_files/admin/update_class_status.php', {
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
        loadClasses(); // Reload the list
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

function deleteClass(classId) {
  if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
    fetch('../../php_files/admin/delete_class.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        class_id: classId
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert(data.message);
        loadClasses(); // Reload the list
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      alert('Error deleting class');
      console.error('Error:', error);
    });
  }
}
</script>