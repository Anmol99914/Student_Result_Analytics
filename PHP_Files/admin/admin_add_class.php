<?php
// admin_add_class.php - BCA-Only Add Class Form (NO TEACHER SELECTION)
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    header("Location: admin_login.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $semester = intval($_POST['semester']);
    $batch_year = intval($_POST['batch_year']);
    
    // ENFORCE BCA ONLY
    $faculty = 'BCA';
    $status = 'active';
    
    // Validation
    if($semester < 1 || $semester > 8) {
        $error = "Invalid semester (1-8 only)";
    } elseif($batch_year < 2000 || $batch_year > 2030) {
        $error = "Invalid batch year (2000-2030)";
    } else {
        // Check if BCA class already exists
        $check = $connection->prepare("SELECT class_id FROM class WHERE faculty = ? AND semester = ? AND batch_year = ?");
        $check->bind_param("sii", $faculty, $semester, $batch_year);
        $check->execute();
        
        if($check->get_result()->num_rows > 0) {
            $error = "BCA Semester $semester for $batch_year batch already exists!";
        } else {
            // Insert new BCA class (NO TEACHER ID)
            $stmt = $connection->prepare("INSERT INTO class (faculty, semester, batch_year, status) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siis", $faculty, $semester, $batch_year, $status);
            
            if($stmt->execute()) {
                $class_id = $stmt->insert_id;
                $success = "✅ BCA Class created successfully! Class ID: #$class_id";
                $success .= "<br><small>Now you can assign teachers to this class.</small>";
                
                // Clear form
                $_POST = array();
            } else {
                $error = "Error creating class: " . $connection->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add BCA Class</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }
        .step.active {
            background: #0d6efd;
        }
        .step.completed {
            background: #198754;
        }
        .step-label {
            margin-top: 5px;
            font-size: 0.8rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_main_page.php">
                <i class="bi bi-arrow-left"></i> Back to Admin
            </a>
            <div>
                <a href="admin_classes.php" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul"></i> View All Classes
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step active">1</div>
                    <div class="step">2</div>
                    <div class="step-label">Create Class</div>
                    <div class="step-label">Assign Teachers</div>
                </div>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New BCA Class</h5>
                    </div>
                    <div class="card-body">
                        <?php if($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill"></i> 
                            <div><?php echo $success; ?></div>
                            <div class="mt-3">
                                <a href="assign_teachers.php?class_id=<?php echo $stmt->insert_id ?? ''; ?>" class="btn btn-success">
                                    <i class="bi bi-person-plus"></i> Assign Teachers Now
                                </a>
                                <a href="admin_add_class.php" class="btn btn-outline-primary">Add Another Class</a>
                                <a href="admin_classes.php" class="btn btn-outline-secondary">View All Classes</a>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="addClassForm">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Faculty</label>
                                    <input type="text" class="form-control" value="BCA" readonly disabled>
                                    <small class="text-muted">System is BCA-only</small>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select name="semester" class="form-select" required 
                                            value="<?php echo $_POST['semester'] ?? ''; ?>">
                                        <option value="">-- Select Semester --</option>
                                        <?php for($i = 1; $i <= 8; $i++): ?>
                                            <option value="<?php echo $i; ?>" 
                                                <?php echo (isset($_POST['semester']) && $_POST['semester'] == $i) ? 'selected' : ''; ?>>
                                                Semester <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Batch Year <span class="text-danger">*</span></label>
                                    <?php 
                                    $currentYear = date('Y');
                                    $selectedYear = $_POST['batch_year'] ?? $currentYear;
                                    ?>
                                    <select name="batch_year" class="form-select" required>
                                        <option value="">-- Select Batch Year --</option>
                                        <?php for($year = $currentYear - 2; $year <= $currentYear + 2; $year++): ?>
                                            <option value="<?php echo $year; ?>"
                                                <?php echo $selectedYear == $year ? 'selected' : ''; ?>>
                                                <?php echo $year; ?> Batch
                                                <?php echo $year == $currentYear ? ' (Current)' : ''; ?>
                                                <?php echo $year == $currentYear + 1 ? ' (Next)' : ''; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- NO TEACHER SELECTION SECTION -->
                            
                            <div class="alert alert-info">
                                <h6><i class="bi bi-info-circle"></i> How it works:</h6>
                                <ul class="mb-0">
                                    <li><strong>Step 1:</strong> Create class (you're here)</li>
                                    <li><strong>Step 2:</strong> Assign teachers to this class</li>
                                    <li><strong>Step 3:</strong> Teachers manage students & subjects</li>
                                    <li><strong>Step 4:</strong> Admin can activate/deactivate any teacher</li>
                                </ul>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Create BCA Class
                                </button>
                                <a href="admin_classes.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="bi bi-lightning"></i> Quick Actions</h6>
                        <div class="d-flex gap-2">
                            <a href="admin_classes.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-list-ul"></i> Manage Classes
                            </a>
                            <a href="assign_teachers_bulk.php" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-people"></i> Bulk Assign Teachers
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('addClassForm').addEventListener('submit', function(e) {
            const semester = this.querySelector('[name="semester"]').value;
            const batchYear = this.querySelector('[name="batch_year"]').value;
            
            if(!semester || !batchYear) {
                e.preventDefault();
                alert('Please select both semester and batch year');
                return false;
            }
            
            // Show loading
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Creating...';
            submitBtn.disabled = true;
            
            // Re-enable button after 5 seconds (safety)
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        });
    </script>
</body>
</html>