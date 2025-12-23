<?php
// admin_edit_teacher.php
session_start();
include('../../config.php');

$teacher_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($teacher_id == 0) {
    header("Location: admin_teachers.php");
    exit();
}

// Fetch teacher data
$stmt = $connection->prepare("SELECT * FROM teacher WHERE teacher_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();

if(!$teacher) {
    header("Location: admin_teachers.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $assigned_class_id = !empty($_POST['assigned_class_id']) ? intval($_POST['assigned_class_id']) : NULL;
    $status = $_POST['status'];
    
    // Check if email changed and exists
    if($email != $teacher['email']) {
        $check = $connection->prepare("SELECT teacher_id FROM teacher WHERE email = ? AND teacher_id != ?");
        $check->bind_param("si", $email, $teacher_id);
        $check->execute();
        if($check->get_result()->num_rows > 0) {
            $error = "Email already registered to another teacher!";
        }
    }
    
    if(!$error) {
        // Update teacher
        $update = $connection->prepare("UPDATE teacher SET name=?, email=?, assigned_class_id=?, status=? WHERE teacher_id=?");
        $update->bind_param("ssisi", $name, $email, $assigned_class_id, $status, $teacher_id);
        
        if($update->execute()) {
            // Also update users table if email changed
            if($email != $teacher['email']) {
                $user_update = $connection->prepare("UPDATE users SET username=? WHERE username=? AND role='teacher'");
                $user_update->bind_param("ss", $email, $teacher['email']);
                $user_update->execute();
            }
            
            $success = "Teacher updated successfully!";
            // Refresh teacher data
            $stmt->execute();
            $teacher = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Error updating teacher: " . $connection->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0"><i class="bi bi-pencil"></i> Edit Teacher</h4>
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
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign to Class</label>
                                <select class="form-select" name="assigned_class_id">
                                    <option value="">-- Not Assigned --</option>
                                    <?php
                                    $classes = $connection->query("SELECT class_id, faculty, semester FROM class WHERE status='active'");
                                    while($class = $classes->fetch_assoc()): 
                                        $selected = ($teacher['assigned_class_id'] == $class['class_id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $class['class_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $class['faculty'] . " - Semester " . $class['semester']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo ($teacher['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($teacher['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <div class="card">
                                    <div class="card-body bg-light">
                                        <h6>Teacher Information</h6>
                                        <p class="mb-1"><strong>Teacher ID:</strong> #<?php echo $teacher['teacher_id']; ?></p>
                                        <p class="mb-1"><strong>Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($teacher['created_at'])); ?></p>
                                        <p class="mb-0"><strong>Last Updated:</strong> <?php echo date('F j, Y, g:i a', strtotime($teacher['updated_at'] ?? $teacher['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="admin_teachers.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <div>
                                <a href="admin_reset_password.php?id=<?php echo $teacher_id; ?>" 
                                   class="btn btn-outline-warning me-2">
                                    <i class="bi bi-key"></i> Reset Password
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Update Teacher
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>