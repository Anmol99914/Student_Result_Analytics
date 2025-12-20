function showAddClassForm() {
  const container = document.getElementById('main-content');

  container.innerHTML = `
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h5>Add New Class</h5>
    </div>
    <div class="card-body">
      <form id="addClassForm">

        <div class="mb-3">
          <label class="form-label">Faculty</label>
          <select name="faculty" class="form-select" required>
            <option value="">-- Select --</option>
            <option value="BCA">BCA</option>
            <option value="BBM">BBM</option>
            <option value="BIM">BIM</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Semester</label>
          <select name="semester" class="form-select" required>
            <option value="">-- Select --</option>
            ${Array.from({length:8}, (_,i)=>`<option value="${i+1}">Semester ${i+1}</option>`).join('')}
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Teacher</label>
          <select name="teacher_id" id="teacherSelect" class="form-select"></select>
        </div>

        <button class="btn btn-success w-100">Create Class</button>
      </form>

      <div id="add-class-msg" class="mt-3"></div>
    </div>
  </div>
  `;

  loadTeachers();
  submitAddClass();
}

function loadTeachers(){
  fetch('../../php_files/admin/get_teachers.php')
    .then(res => res.json())
    .then(data => {
      const sel = document.getElementById('teacherSelect');
      sel.innerHTML = '<option value="">-- Select Teacher --</option>';

      data.forEach(t => {
        const disabled = t.status !== 'active' ? 'disabled' : '';
        sel.innerHTML += `<option value="${t.teacher_id}" ${disabled}>${t.name}</option>`;
      });
    });
}

function submitAddClass(){
  document.getElementById('addClassForm').addEventListener('submit', e => {
    e.preventDefault();
    const fd = new FormData(e.target);

    fetch('../../php_files/admin/add_class.php', {
      method:'POST',
      body:fd
    })
    .then(res => res.json())
    .then(data => {
      document.getElementById('add-class-msg').innerHTML = data.message;
      if(data.status === 'success') e.target.reset();
    });
  });
}
