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
    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Add New Class</h5>
    <a href="#" onclick="loadPage('student_classes.php'); return false;" class="btn btn-light btn-sm">
      <i class="bi bi-arrow-left"></i> Back to Classes
    </a>
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
            <?php for($i=1; $i<=8; $i++): ?>
              <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
            <?php endfor; ?>
          </select>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label">Assign Teacher (Optional)</label>
        <div class="alert alert-info alert-sm">
          <i class="bi bi-info-circle"></i> Only active teachers can be assigned. Suspended teachers are disabled.
        </div>
        <select name="teacher_id" id="teacherSelect" class="form-select">
          <option value="">-- Loading teachers... --</option>
        </select>
        <small class="text-muted">You can assign a teacher later if not decided yet.</small>
      </div>

      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-success btn-lg">
          <i class="bi bi-check-circle"></i> Create Class
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="resetAddClassForm()">
          <i class="bi bi-x-circle"></i> Reset Form
        </button>
      </div>
    </form>

    <div id="add-class-msg" class="mt-4"></div>
  </div>
</div>

<script>
// Load teachers when this form is loaded
document.addEventListener('DOMContentLoaded', function() {
  loadTeachers();
  
  // Setup form submission
  const form = document.getElementById('addClassForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      submitAddClassForm(this);
    });
  }
});

function loadTeachers() {
  const teacherSelect = document.getElementById('teacherSelect');
  if (!teacherSelect) return;
  
  fetch('../../php_files/admin/get_teachers.php')
    .then(res => res.json())
    .then(teachers => {
      teacherSelect.innerHTML = '<option value="">-- Select Teacher (Optional) --</option>';
      
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
      teacherSelect.innerHTML = '<option value="">Error loading teachers</option>';
      console.error('Error loading teachers:', error);
    });
}

function submitAddClassForm(form) {
  const formData = new FormData(form);
  const msgDiv = document.getElementById('add-class-msg');
  
  // Show loading
  msgDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Creating class...</div>';
  
  fetch('../../php_files/admin/add_class.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      msgDiv.innerHTML = `
        <div class="alert alert-success">
          <i class="bi bi-check-circle"></i> ${data.message}
          <div class="mt-2">
            <strong>Class Details:</strong><br>
            Faculty: ${formData.get('faculty')}<br>
            Semester: ${formData.get('semester')}<br>
            ${data.teacher_assigned ? `Assigned Teacher: ${data.teacher_name}` : 'No teacher assigned'}
          </div>
        </div>
      `;
      
      // Reset form
      form.reset();
      loadTeachers(); // Reload teachers
      
      // Auto-hide success message after 5 seconds
      setTimeout(() => {
        msgDiv.innerHTML = '';
      }, 5000);
    } else {
      msgDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.message}</div>`;
    }
  })
  .catch(error => {
    msgDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error creating class. Please try again.</div>';
    console.error('Error:', error);
  });
}

function resetAddClassForm() {
  const form = document.getElementById('addClassForm');
  if (form) {
    form.reset();
    document.getElementById('add-class-msg').innerHTML = '';
  }
}
</script>