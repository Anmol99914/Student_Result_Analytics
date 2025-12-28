<?php
// add_teacher_content.php - LOADS INSIDE admin_main_page.php
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in'])) {
    die("Access denied!");
}

$message = '';
$message_type = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    // Validation
    if(empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required!";
        $message_type = 'danger';
    } elseif($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $message_type = 'danger';
    } elseif(strlen($password) < 6) {
        $message = "Password must be at least 6 characters!";
        $message_type = 'danger';
    } else {
        // Check if email exists
        $check = $connection->prepare("SELECT teacher_id FROM teacher WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        if($check->get_result()->num_rows > 0) {
            $message = "Email already registered!";
            $message_type = 'danger';
        } else {
            // Add teacher
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $connection->prepare("INSERT INTO teacher (name, email, password, status) VALUES (?, ?, ?, 'active')");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if($stmt->execute()) {
                $teacher_id = $stmt->insert_id;
                
                // Add to users table
                $user_stmt = $connection->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'teacher', ?)");
                $user_status = 'active'; // Default status for new teachers
                $user_stmt->bind_param("sss", $email, $hashed_password, $user_status);
                $user_stmt->execute();
                
                $message = "✅ Teacher added successfully! Teacher ID: #$teacher_id";
                $message_type = 'success';
                
                // Clear form on success
                $_POST = array();
            } else {
                $message = "Error adding teacher: " . $connection->error;
                $message_type = 'danger';
            }
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add New Teacher</h4>
            <p class="text-muted mb-0">Create a new faculty account</p>
        </div>
        <a href="#" onclick="loadTeacherManagement(); return false;" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Teachers
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i> Teacher Registration</h5>
                </div>
                <div class="card-body">
                    <?php if($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                <div class="form-text">Teacher will use this email to login</div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required minlength="6">
                                <div class="form-text">Minimum 6 characters</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Teacher will be created with "Active" status and can login immediately.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="#" onclick="loadTeacherManagement(); return false;" class="btn btn-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Add Teacher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>