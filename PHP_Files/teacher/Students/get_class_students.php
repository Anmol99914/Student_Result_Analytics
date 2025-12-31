<?php
// Students/get_class_students.php
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$teacher_id = $_SESSION['teacher_id'];

if ($class_id <= 0) {
    echo '<div class="alert alert-danger">Invalid class ID</div>';
    exit();
}

try {
    // Verify teacher has access to this class
    $verify_sql = "SELECT c.* FROM class c 
                   INNER JOIN teacher_class_assignments tca ON c.class_id = tca.class_id
                   WHERE c.class_id = ? AND tca.teacher_id = ?";
    $verify_stmt = $connection->prepare($verify_sql);
    $verify_stmt->bind_param("ii", $class_id, $teacher_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $class_data = $verify_result->fetch_assoc();
    
    if (!$class_data) {
        echo '<div class="alert alert-danger">Access denied to this class</div>';
        exit();
    }
    
    // First, let's check what columns exist in the student table
    $check_columns = $connection->query("DESCRIBE student");
    $columns = [];
    while ($col = $check_columns->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
    
    // Build SQL query based on available columns
    $select_fields = [
        's.student_id',
        's.student_name',
        's.email',
        's.created_at'
    ];
    
    // Add optional fields if they exist
    if (in_array('phone', $columns)) {
        $select_fields[] = 's.phone';
    }
    
    if (in_array('gender', $columns)) {
        $select_fields[] = 's.gender';
    }
    
    if (in_array('status', $columns)) {
        $select_fields[] = 's.status';
    }
    
    if (in_array('phone_number', $columns)) {
        $select_fields[] = 's.phone_number';
    }
    
    if (in_array('contact', $columns)) {
        $select_fields[] = 's.contact';
    }
    
    // Build the query
    $sql = "SELECT " . implode(', ', $select_fields) . ",
                   (SELECT COUNT(*) FROM result WHERE student_id = s.student_id) as result_count
            FROM student s
            WHERE s.class_id = ?
            ORDER BY s.student_name";
    
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
    
    $class_name = $class_data['faculty'] . ' - Semester ' . $class_data['semester'];
    
    echo '<div class="class-header mb-4">
            <h5 class="text-primary"><i class="bi bi-people-fill me-2"></i>' . htmlspecialchars($class_name) . '</h5>
            <p class="text-muted mb-0">Class ID: ' . $class_id . ' | ' . count($students) . ' students</p>
          </div>';
    
    if (empty($students)) {
        echo '<div class="text-center py-5">
                <i class="bi bi-people display-4 text-muted mb-3"></i>
                <h5 class="text-muted">No Students Found</h5>
                <p class="text-muted">This class has no students enrolled yet.</p>
                <button class="btn btn-primary mt-3" onclick="loadAddStudentForm()">
                    <i class="bi bi-person-plus me-1"></i> Add Students
                </button>
              </div>';
    } else {
        // Determine what columns we have
        $has_phone = in_array('phone', $columns) || in_array('phone_number', $columns) || in_array('contact', $columns);
        $has_gender = in_array('gender', $columns);
        $has_status = in_array('status', $columns);
        
        echo '<div class="table-responsive student-table">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="120">Student ID</th>
                            <th>Name</th>
                            <th width="150">Email</th>';
        
        if ($has_phone) {
            echo '<th width="120">Contact</th>';
        }
        
        echo '<th width="100">Results</th>';
        
        if ($has_status) {
            echo '<th width="120">Status</th>';
        }
        
        echo '<th width="200">Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($students as $student) {
            // Safely get values with defaults
            $status = '';
            if ($has_status && isset($student['status']) && $student['status']) {
                $status = $student['status'];
            }
            
            $gender = '';
            if ($has_gender && isset($student['gender']) && $student['gender']) {
                $gender = $student['gender'];
            }
            
            // Get phone from any possible column
            $phone = 'N/A';
            if ($has_phone) {
                if (isset($student['phone']) && $student['phone']) {
                    $phone = $student['phone'];
                } elseif (isset($student['phone_number']) && $student['phone_number']) {
                    $phone = $student['phone_number'];
                } elseif (isset($student['contact']) && $student['contact']) {
                    $phone = $student['contact'];
                }
            }
            
            $result_count = isset($student['result_count']) ? intval($student['result_count']) : 0;
            
            // Status badge
            $status_badge = '';
            if ($has_status) {
                $status_badge = ($status === 'active' || $status === 'Active' || $status === '') 
                    ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Active</span>'
                    : '<span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> ' . htmlspecialchars($status) . '</span>';
            }
            
            // Result badge
            $result_badge = $result_count > 0 
                ? '<span class="badge bg-info"><i class="bi bi-trophy me-1"></i> ' . $result_count . '</span>'
                : '<span class="badge bg-secondary">No results</span>';
            
            // Gender display
            $gender_display = '';
            if ($has_gender && $gender) {
                $gender_icon = ($gender === 'male' || $gender === 'Male') ? 'bi-gender-male' : 
                              (($gender === 'female' || $gender === 'Female') ? 'bi-gender-female' : 'bi-gender-ambiguous');
                $gender_display = '<br><small class="text-muted"><i class="bi ' . $gender_icon . ' me-1"></i>' . ucfirst($gender) . '</small>';
            }
            
            echo '<tr>
                    <td><span class="badge bg-dark">' . htmlspecialchars($student['student_id']) . '</span></td>
                    <td>
                        <strong>' . htmlspecialchars($student['student_name']) . '</strong>
                        ' . $gender_display . '
                    </td>
                    <td><small>' . htmlspecialchars($student['email'] ?? 'N/A') . '</small></td>';
            
            if ($has_phone) {
                echo '<td>' . htmlspecialchars($phone) . '</td>';
            }
            
            echo '<td>' . $result_badge . '</td>';
            
            if ($has_status) {
                echo '<td>' . $status_badge . '</td>';
            }
            
            echo '<td class="student-actions">
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="viewStudentDetails(\'' . $student['student_id'] . '\')" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning me-1" onclick="editStudent(\'' . $student['student_id'] . '\')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteStudent(\'' . $student['student_id'] . '\', \'' . htmlspecialchars(addslashes($student['student_name'])) . '\')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                  </tr>';
        }
        
        echo '</tbody></table></div>';
        
        // Summary
        echo '<div class="alert alert-success mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Total:</strong> ' . count($students) . ' students in this class
                    </div>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="loadMyClasses()">
                            <i class="bi bi-arrow-left me-1"></i> Back to Classes
                        </button>
                        <button class="btn btn-sm btn-success" onclick="loadAddStudentForm()">
                            <i class="bi bi-person-plus me-1"></i> Add Student
                        </button>
                    </div>
                </div>
              </div>';
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Error loading students: ' . htmlspecialchars($e->getMessage()) . '
          </div>';
}
?>