<?php
// edit_teacher.php - STANDALONE EDIT TEACHER PAGE
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$teacher_id = intval($_GET['teacher_id'] ?? 0);
if($teacher_id == 0) {
    die("Invalid teacher ID!");
}

// Get teacher data
$teacher = $connection->query("SELECT * FROM teacher WHERE teacher_id = $teacher_id")->fetch_assoc();
if(!$teacher) {
    die("Teacher not found!");
}

$message = '';
$message_type = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    // Check if email changed and exists
    $error = '';
    if($email != $teacher['email']) {
        $check = $connection->query("SELECT teacher_id FROM teacher WHERE email = '$email' AND teacher_id != $teacher_id");
        if($check->num_rows > 0) {
            $error = "Email already registered to another teacher!";
        }
    }
    
    if(!$error) {
        // Update teacher
        $stmt = $connection->prepare("UPDATE teacher SET name=?, email=?, status=? WHERE teacher_id=?");
        $stmt->bind_param("sssi", $name, $email, $status, $teacher_id);
        
        if($stmt->execute()) {
            // Update users table if email changed
            if($email != $teacher['email']) {
                $connection->query("UPDATE users SET username='$email' WHERE username='{$teacher['email']}' AND role='teacher'");
            }
            
            $message = "✅ Teacher updated successfully!";
            $message_type = 'success';
            
            // Update current teacher data
            $teacher['name'] = $name;
            $teacher['email'] = $email;
            $teacher['status'] = $status;
        } else {
            $message = "Error updating teacher: " . $connection->error;
            $message_type = 'danger';
        }
    } else {
        $message = $error;
        $message_type = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; }
        .card { border: none; box-shadow: 0 0 20px rgba(0,0,0,.1); }
        .teacher-info { background: #f8f9fa; border-radius: 8px; padding: 15px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <a href="teacher_list.php" class="navbar-brand">
                <i class="bi bi-arrow-left"></i> Back to Teachers List
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i> Edit Teacher: <?php echo htmlspecialchars($teacher['name']); ?></h5>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="teacher-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Teacher ID:</strong> #<?php echo $teacher['teacher_id']; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Created:</strong> <?php echo date('M d, Y', strtotime($teacher['created_at'])); ?>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active" <?php echo $teacher['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $teacher['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="teacher_list.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Update Teacher
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="alert alert-info mt-3">
            <h6><i class="bi bi-info-circle"></i> Note:</h6>
            <ul class="mb-0">
                <li>Changing email will also update the teacher's login username</li>
                <li>Password cannot be changed here for security reasons</li>
                <li>To assign teachers to classes, go to Teachers List page</li>
            </ul>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>