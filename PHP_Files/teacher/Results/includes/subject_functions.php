<?php
/**
 * Subject Helper Functions
 */

/**
 * Get subjects with status information
 */
function getSubjectsWithStatus($connection, $faculty, $semester, $class_id, $teacher_id) {
    // First, get the faculty_id from faculty name
    $faculty_sql = "SELECT faculty_id FROM faculty WHERE faculty_name = ?";
    $faculty_stmt = $connection->prepare($faculty_sql);
    $faculty_stmt->bind_param("s", $faculty);
    $faculty_stmt->execute();
    $faculty_result = $faculty_stmt->get_result();
    $faculty_data = $faculty_result->fetch_assoc();
    
    if (!$faculty_data) {
        return ['error' => 'Faculty not found'];
    }
    
    $faculty_id = $faculty_data['faculty_id'];
    
    // Get subjects for this faculty and semester
    $subject_sql = "SELECT * FROM subject 
                   WHERE faculty_id = ? 
                   AND semester = ? 
                   AND status = 'active'
                   ORDER BY subject_name";
    
    $subject_stmt = $connection->prepare($subject_sql);
    $subject_stmt->bind_param("ii", $faculty_id, $semester);
    $subject_stmt->execute();
    $subject_result = $subject_stmt->get_result();
    
    $subjects = [];
    while ($subject = $subject_result->fetch_assoc()) {
        // Get status information for each subject
        $subject['status_info'] = getSubjectStatusInfo($connection, $subject['subject_id'], $class_id, $semester, $teacher_id);
        $subjects[] = $subject;
    }
    
    return $subjects;
}

/**
 * Get status information for a specific subject
 */
function getSubjectStatusInfo($connection, $subject_id, $class_id, $semester, $teacher_id) {
    $status_info = [
        'marked_students' => 0,
        'total_students' => 0,
        'status_counts' => [
            'pending' => 0,
            'verified' => 0,
            'rejected' => 0
        ],
        'overall_status' => 'not_started'
    ];
    
    // Get total students in class
    $total_sql = "SELECT COUNT(*) as total FROM student WHERE class_id = ?";
    $total_stmt = $connection->prepare($total_sql);
    $total_stmt->bind_param("i", $class_id);
    $total_stmt->execute();
    $total_result = $total_stmt->get_result();
    $total_data = $total_result->fetch_assoc();
    $status_info['total_students'] = $total_data['total'] ?? 0;
    
    // Get marked students count
    $marked_sql = "SELECT COUNT(*) as marked FROM result r
                  JOIN student s ON r.student_id = s.student_id
                  WHERE r.subject_id = ? 
                  AND s.class_id = ?
                  AND r.semester_id = ?
                  AND r.entered_by_teacher_id = ?";
    
    $marked_stmt = $connection->prepare($marked_sql);
    $marked_stmt->bind_param("iiii", $subject_id, $class_id, $semester, $teacher_id);
    $marked_stmt->execute();
    $marked_result = $marked_stmt->get_result();
    $marked_data = $marked_result->fetch_assoc();
    $status_info['marked_students'] = $marked_data['marked'] ?? 0;
    
    // Get verification status counts
    $status_sql = "SELECT r.verification_status, COUNT(*) as count 
                  FROM result r
                  JOIN student s ON r.student_id = s.student_id
                  WHERE r.subject_id = ? 
                  AND s.class_id = ?
                  AND r.semester_id = ?
                  AND r.entered_by_teacher_id = ?
                  GROUP BY r.verification_status";
    
    $status_stmt = $connection->prepare($status_sql);
    $status_stmt->bind_param("iiii", $subject_id, $class_id, $semester, $teacher_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    
    while ($status_row = $status_result->fetch_assoc()) {
        $status_info['status_counts'][$status_row['verification_status']] = $status_row['count'];
    }
    
    // Determine overall status
    if ($status_info['marked_students'] > 0) {
        if ($status_info['status_counts']['verified'] > 0 && 
            $status_info['status_counts']['pending'] == 0 && 
            $status_info['status_counts']['rejected'] == 0) {
            $status_info['overall_status'] = 'verified';
        } elseif ($status_info['status_counts']['rejected'] > 0) {
            $status_info['overall_status'] = 'rejected';
        } elseif ($status_info['status_counts']['pending'] > 0) {
            $status_info['overall_status'] = 'pending';
        } else {
            $status_info['overall_status'] = 'mixed';
        }
    }
    
    // Calculate progress percentage
    $status_info['progress_percent'] = $status_info['total_students'] > 0 ? 
        round(($status_info['marked_students'] / $status_info['total_students']) * 100) : 0;
    
    return $status_info;
}

/**
 * Get student count for a class
 */
function getStudentCount($connection, $class_id) {
    $sql = "SELECT COUNT(*) as total FROM student WHERE class_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    return $data['total'] ?? 0;
}

/**
 * Get badge HTML for status
 */
function getStatusBadgeHTML($status, $count = 0) {
    $badges = [
        'pending' => '<span class="badge bg-warning status-badge pending-badge">
                      <i class="bi bi-clock"></i> Pending' . ($count > 0 ? " ({$count})" : '') . '
                     </span>',
        'verified' => '<span class="badge bg-success status-badge verified-badge">
                       <i class="bi bi-check-circle"></i> Verified' . ($count > 0 ? " ({$count})" : '') . '
                      </span>',
        'rejected' => '<span class="badge bg-danger status-badge rejected-badge">
                       <i class="bi bi-x-circle"></i> Rejected' . ($count > 0 ? " ({$count})" : '') . '
                      </span>',
        'not_started' => '<span class="badge bg-secondary status-badge">Not Started</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary status-badge">Unknown</span>';
}

/**
 * Get action button HTML based on status
 */
function getActionButtonHTML($subject, $status_info, $class_data, $teacher_id) {
    $subject_id = $subject['subject_id'];
    $subject_name = htmlspecialchars($subject['subject_name']);
    $class_id = $class_data['class_id'];
    $faculty = $class_data['faculty'];
    $semester = $class_data['semester'];
    
    $buttons = '';
    
    switch ($status_info['overall_status']) {
        case 'not_started':
            $buttons = '
                <button class="btn btn-primary btn-sm subject-action-btn" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-faculty="' . $faculty . '"
                        data-semester="' . $semester . '"
                        data-action="enter">
                    <i class="bi bi-pencil-square"></i> Enter Marks
                </button>';
            break;
            
        case 'rejected':
            $buttons = '
                <button class="btn btn-danger btn-sm subject-action-btn" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-action="view-pending">
                    <i class="bi bi-exclamation-triangle"></i> Review Rejected
                </button>
                <button class="btn btn-outline-primary btn-sm subject-action-btn mt-2" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-faculty="' . $faculty . '"
                        data-semester="' . $semester . '"
                        data-action="enter">
                    <i class="bi bi-pencil"></i> Edit Marks
                </button>';
            break;
            
        case 'pending':
            $buttons = '
                <button class="btn btn-warning btn-sm subject-action-btn" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-action="view-pending">
                    <i class="bi bi-clock-history"></i> View Pending
                </button>
                <button class="btn btn-outline-primary btn-sm subject-action-btn mt-2" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-faculty="' . $faculty . '"
                        data-semester="' . $semester . '"
                        data-action="enter">
                    <i class="bi bi-plus-circle"></i> Add More
                </button>';
            break;
            
        case 'verified':
            $buttons = '
                <button class="btn btn-success btn-sm subject-action-btn" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-action="view-verified">
                    <i class="bi bi-check-circle"></i> View Verified
                </button>
                <button class="btn btn-outline-secondary btn-sm subject-action-btn mt-2 disabled" disabled>
                    <i class="bi bi-lock"></i> Verified
                </button>';
            break;
            
        default: // mixed
            $buttons = '
                <button class="btn btn-info btn-sm subject-action-btn" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-action="view-all">
                    <i class="bi bi-eye"></i> View All
                </button>
                <button class="btn btn-outline-primary btn-sm subject-action-btn mt-2" 
                        data-subject-id="' . $subject_id . '"
                        data-subject-name="' . $subject_name . '"
                        data-class-id="' . $class_id . '"
                        data-faculty="' . $faculty . '"
                        data-semester="' . $semester . '"
                        data-action="enter">
                    <i class="bi bi-pencil-square"></i> Manage Marks
                </button>';
            break;
    }
    
    return $buttons;
}
?>