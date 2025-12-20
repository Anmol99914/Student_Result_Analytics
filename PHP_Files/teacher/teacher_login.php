<?php
session_start();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teacher Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<style>
body { background-color: #f8f9fa; }
.card { border-radius: 12px; border: none; }
.form-control { border-radius: 8px; }
button[type="submit"] { border-radius: 8px; font-weight: 500; }
</style>
</head>
<body class="d-flex flex-column align-items-center justify-content-center vh-100">

<a href="../../index.html" class="btn btn-danger position-absolute" style="top:20px; left:20px; z-index:10;">
<i class="bi bi-house-heart"></i> Back To Home</a>

<div class="col-md-4 col-sm-10">
<div class="card shadow-sm p-4">
<h3 class="text-center mb-4">Teacher Login</h3>

<?php
if(isset($_GET['error'])){
    $error = $_GET['error'];
    $message = '';
    
    if($error === 'invalid') $message = 'Invalid email or password!';
    elseif($error === 'inactive') $message = 'Your account is inactive!';
    elseif($error === 'empty') $message = 'Please fill all fields!';
    
    if(!empty($message)){
        echo '<div class="alert alert-danger alert-dismissible fade show">';
        echo '<i class="bi bi-exclamation-triangle me-2"></i>' . $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}
?>

<form id="loginForm" method="POST">
  <div class="mb-3">
    <label class="form-label">Email:</label>
    <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Password:</label>
    <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
  </div>
  <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
</form>

<!-- Login Options -->
<div class="text-center mt-3">
    <small class="text-muted">
        <a href="../student/student_login.php" class="text-decoration-none">Student Login</a> | 
        <a href="../admin/admin_login.php" class="text-decoration-none">Admin Login</a>
    </small>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('loginForm').addEventListener('submit', function(e){
    e.preventDefault();
    
    const email = this.querySelector('[name="email"]').value.trim();
    const password = this.querySelector('[name="password"]').value.trim();
    
    if(!email || !password){
        window.location.href = 'teacher_login.php?error=empty';
        return;
    }
    
    const formData = new FormData(this);
    
    // Show loading
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
    submitBtn.disabled = true;

    fetch('teacher_validation.php', { 
        method: 'POST', 
        body: formData 
    })
    .then(res => {
        if(res.redirected){
            window.location.href = res.url;
            return;
        }
        return res.text();
    })
    .then(data => {
        if(data){
            try {
                const result = JSON.parse(data);
                if(result.status === 'success'){
                    window.location.href = 'teacher_dashboard.php';
                } else {
                    window.location.href = 'teacher_login.php?error=invalid';
                }
            } catch(e) {
                console.error('JSON parse error:', e);
                window.location.href = 'teacher_login.php?error=invalid';
            }
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        window.location.href = 'teacher_login.php?error=invalid';
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Handle back button
window.addEventListener('pageshow', function(event) {
    if(event.persisted){
        window.location.reload();
    }
});
</script>
</body>
</html>