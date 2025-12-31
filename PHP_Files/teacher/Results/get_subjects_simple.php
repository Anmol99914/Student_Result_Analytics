<?php
// Results/get_subjects_simple.php - Simplified version for AJAX loading
session_start();
require_once '../../../config.php';

if (!isset($_SESSION['teacher_logged_in']) || $_SESSION['teacher_logged_in'] != true) {
    echo '<div class="alert alert-danger">Access denied</div>';
    exit();
}

$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$faculty = isset($_GET['faculty']) ? $_GET['faculty'] : '';
$semester = isset($_GET['semester']) ? intval($_GET['semester']) : 0;

if (!$class_id || !$faculty) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit();
}

// Get subjects for this faculty/semester
$sql = "SELECT s.* FROM subject s
        WHERE s.faculty_id = (SELECT faculty_id FROM faculty WHERE faculty_name = ?)
        AND s.semester = ?
        AND s.status = 'active'
        ORDER BY s.subject_name";

$stmt = $connection->prepare($sql);
$stmt->bind_param("si", $faculty, $semester);
$stmt->execute();
$result = $stmt->get_result();

echo '<div class="row mb-3">';
echo '<div class="col-12">';
echo '<button class="btn btn-outline-secondary mb-3" onclick="resultsSystem.loadClasses()">';
echo '<i class="bi bi-arrow-left"></i> Back to Classes';
echo '</button>';
echo '<h5>' . htmlspecialchars($faculty) . ' - Semester ' . $semester . '</h5>';
echo '<p class="text-muted">Select a subject to enter marks:</p>';
echo '</div>';
echo '</div>';

echo '<div class="row">';

if ($result->num_rows === 0) {
    echo '<div class="col-12">';
    echo '<div class="alert alert-warning">No subjects found for this class.</div>';
    echo '</div>';
} else {
    while ($subject = $result->fetch_assoc()) {
        echo '<div class="col-md-4 mb-4">';
        echo '<div class="card subject-card" 
              data-subject-id="' . $subject['subject_id'] . '"
              data-subject-name="' . htmlspecialchars($subject['subject_name']) . '">';
        
        echo '<div class="card-header bg-light">';
        echo '<h6 class="mb-0">' . htmlspecialchars($subject['subject_name']) . '</h6>';
        echo '<span class="badge bg-secondary">' . htmlspecialchars($subject['subject_code']) . '</span>';
        echo '</div>';
        
        echo '<div class="card-body">';
        echo '<div class="small text-muted">';
        echo '<div class="mb-2">';
        echo '<i class="bi bi-journal-text"></i> Credits: ' . $subject['credits'];
        echo '</div>';
        
        if ($subject['is_elective']) {
            echo '<div class="mb-2">';
            echo '<i class="bi bi-star"></i> <span class="badge bg-info">Elective</span>';
            echo '</div>';
        }
        
        if (!empty($subject['description']) && $subject['description'] != '0') {
            echo '<div class="mb-2">';
            echo '<i class="bi bi-info-circle"></i> ' . htmlspecialchars(substr($subject['description'], 0, 60)) . '...';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>';
        
        echo '<div class="card-footer bg-transparent">';
        echo '<button class="btn btn-primary btn-sm w-100" 
                onclick="resultsSystem.showMarksForm(' . $class_id . ', ' . $subject['subject_id'] . ', \'' . htmlspecialchars($subject['subject_name']) . '\', \'' . htmlspecialchars($faculty) . '\', ' . $semester . ')">
                <i class="bi bi-pencil-square me-1"></i> Enter Marks
              </button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
}

echo '</div>';
?>