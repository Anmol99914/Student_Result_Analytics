<?php
// setup_bca_subjects.php - Run this ONCE to setup BCA subjects
session_start();
include('../../config.php');

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true) {
    die("Access denied!");
}

echo "<h3>Setting up BCA Subjects</h3>";

// Fixed BCA subjects for all 8 semesters
$bca_subjects = [
    // Semester 1
    ['BCA101', 'Computer Fundamentals and Applications', 1, 3, 0],
    ['BCA102', 'Society and Technology', 1, 3, 0],
    ['BCA103', 'English I', 1, 4, 0],
    ['BCA104', 'Mathematics I', 1, 2, 0],
    ['BCA105', 'Digital Logic', 1, 2, 0],
    
    // Semester 2
    ['BCA201', 'C Programming', 2, 4, 0],
    ['BCA202', 'Financial Accounting', 2, 4, 0],
    ['BCA203', 'English II', 2, 4, 0],
    ['BCA204', 'Mathematics II', 2, 2, 0],
    ['BCA205', 'MicroProcessor and Architecture', 2, 3, 0],
    
    // Semester 3
    ['BCA301', 'Data Structures and Algorithms', 3, 4, 0],
    ['BCA302', 'Probability and Statistics', 3, 4, 0],
    ['BCA303', 'System Analysis and Design', 3, 3, 0],
    ['BCA304', 'OOP in Java', 3, 2, 0],
    ['BCA305', 'Web Technology', 3, 3, 0],
    
    // Semester 4
    ['BCA401', 'Operating Systems', 4, 4, 0],
    ['BCA402', 'Numerical Methods', 4, 4, 0],
    ['BCA403', 'Software Engineering', 4, 3, 0],
    ['BCA404', 'Scripting Language', 4, 3, 0],
    ['BCA405', 'Database Management System', 4, 3, 0],
    
    // Semester 5
    ['BCA501', 'MIS and E-Business', 5, 4, 0],
    ['BCA502', 'DotNet Technology', 5, 4, 0],
    ['BCA503', 'Computer Networking', 5, 3, 0],
    ['BCA504', 'Introduction to Management', 5, 3, 0],
    ['BCA505', 'Computer Graphics and Animation', 5, 3, 0],
    
    // Semester 6
    ['BCA601', 'Mobile Programming', 6, 4, 0],
    ['BCA602', 'Distributed System', 6, 4, 0],
    ['BCA603', 'Applied Economics', 6, 3, 0],
    ['BCA604', 'Advanced Java Programming', 6, 3, 0],
    ['BCA605', 'Network Programming', 6, 4, 0],
    
    // Semester 7
    ['BCA701', 'Cyber Law and Professional Ethics', 7, 4, 0],
    ['BCA702', 'Cloud Computing', 7, 4, 0],
    ['BCA703', 'Internet of Things', 7, 3, 0],
    ['BCA704', 'Cyber Security', 7, 3, 0],
    ['BCA705', 'Data Mining', 7, 4, 0],
    
    // Semester 8
    ['BCA801', 'Machine Learning', 8, 4, 0],
    ['BCA802', 'Big Data Analytics', 8, 4, 0],
    ['BCA803', 'Blockchain Technology', 8, 3, 0],
    ['BCA804', 'Image Processing', 8, 5, 0],
    ['BCA805', 'Network Administration', 8, 2, 0],
];

// Check if subjects already exist
$check = $connection->query("SELECT COUNT(*) as count FROM subject WHERE subject_code LIKE 'BCA%'");
$existing = $check->fetch_assoc()['count'];

if($existing > 0) {
    echo "<div class='alert alert-warning'>⚠️ BCA subjects already exist ($existing found). Skipping setup.</div>";
    echo "<a href='admin_main_page.php' class='btn btn-primary'>Back to Dashboard</a>";
    exit();
}

// Insert subjects
$stmt = $connection->prepare("INSERT INTO subject (subject_code, subject_name, semester, credits, is_elective) VALUES (?, ?, ?, ?, ?)");
$inserted = 0;

foreach($bca_subjects as $subject) {
    try {
        $stmt->bind_param("ssiii", $subject[0], $subject[1], $subject[2], $subject[3], $subject[4]);
        $stmt->execute();
        $inserted++;
        echo "✅ Added: {$subject[1]} (Code: {$subject[0]}, Semester: {$subject[2]})<br>";
    } catch(Exception $e) {
        echo "❌ Error adding {$subject[1]}: " . $e->getMessage() . "<br>";
    }
}

echo "<div class='alert alert-success mt-3'><h4>✅ Setup Complete!</h4>";
echo "Inserted $inserted BCA subjects for all 8 semesters.</div>";
echo "<a href='admin_main_page.php' class='btn btn-success'>Back to Dashboard</a>";
?>