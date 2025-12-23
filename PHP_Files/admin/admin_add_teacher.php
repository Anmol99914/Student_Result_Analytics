<?php
// admin_add_teacher.php
session_start();
include('../../config.php');

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $assigned_class_id = !empty($_POST['assigned_class_id']) ? intval($_POST['assigned_class_id']) : NULL;
    
    // Validation
    if(empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } else {
        // Check if email already exists
        $check = $connection->prepare("SELECT teacher_id FROM teacher WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        
        if($check->get_result()->num_rows > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert teacher
            $stmt = $connection->prepare("INSERT INTO teacher (name, email, password, assigned_class_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $assigned_class_id);
            
            if($stmt->execute()) {
                $teacher_id = $stmt->insert_id;
                
                // Also add to users table for login
                $user_stmt = $connection->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher')");
                $user_stmt->bind_param("ss", $email, $hashed_password);
                $user_stmt->execute();
                
                $success = "Teacher added successfully! Teacher ID: #$teacher_id";
                // Clear form
                $_POST = array();
            } else {
                $error = "Error adding teacher: " . $connection->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Add New Teacher</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo $_POST['name'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo $_POST['email'] ?? ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Assign to Class (Optional)</label>
                                <select class="form-select" name="assigned_class_id">
                                    <option value="">-- Not Assigned --</option>
                                    <?php
                                    $classes = $connection->query("SELECT class_id, faculty, semester FROM class WHERE status='active'");
                                    while($class = $classes->fetch_assoc()): 
                                        $selected = ($_POST['assigned_class_id'] ?? '') == $class['class_id'] ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $class['class_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $class['faculty'] . " - Semester " . $class['semester']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <small class="text-muted">You can assign a class later</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="admin_teachers.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Add Teacher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Password Requirements Note -->
            <div class="alert alert-info mt-3">
                <h6><i class="bi bi-info-circle"></i> Notes:</h6>
                <ul class="mb-0">
                    <li>Teacher will use their email and password to login</li>
                    <li>Make sure to provide a strong password</li>
                    <li>Class assignment can be done later</li>
                    <li>Default status will be "active"</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>