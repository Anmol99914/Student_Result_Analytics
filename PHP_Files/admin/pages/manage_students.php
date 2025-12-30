<?php
// manage_students.php - Student Management Page
include(__DIR__ . '/../../../config.php');
?>

<div class="student-management-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-people-fill me-2"></i>Student Management</h2>
            <p class="text-muted mb-0">Manage student records and enrollment</p>
        </div>
        <div>
            <button class="btn btn-primary" id="addStudentBtn">
                <i class="bi bi-plus-circle"></i> Add New Student
            </button>
            <button class="btn btn-outline-success" id="exportStudentsBtn">
                <i class="bi bi-download"></i> Export
            </button>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <select class="form-select" id="facultyFilter">
                <option value="">All Faculties</option>
                <option value="BCA">BCA</option>
                <option value="BBM">BBM</option>
                <option value="BIM">BIM</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="semesterFilter">
                <option value="">All Semesters</option>
                <?php for($i = 1; $i <= 8; $i++): ?>
                    <option value="<?php echo $i; ?>">Semester <?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select" id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <input type="text" class="form-control" id="searchInput" placeholder="Search by name or ID">
                <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100" id="resetFiltersBtn">
                <i class="bi bi-arrow-clockwise"></i> Reset
            </button>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-people text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Students</h6>
                            <h4 class="mb-0" id="totalStudents">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-check-circle text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Active</h6>
                            <h4 class="mb-0" id="activeStudents">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-clock text-warning fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Fee Pending</h6>
                            <h4 class="mb-0" id="pendingStudents">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                            <i class="bi bi-mortarboard text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Recent (7 days)</h6>
                            <h4 class="mb-0" id="recentStudents">0</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Students Table Container -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check me-2"></i>
                Students List
            </h5>
        </div>
        <div class="card-body">
            <div id="studentsContainer">
                <!-- Students table will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading students data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// RUN ONLY ONCE - check if already executed
if (!window.studentPageInitialized) {
    window.studentPageInitialized = true;
    
    console.log('📦 Loading student management system...');
    
    // 1. Remove any existing studentManager
    if (window.studentManager) {
        console.log('🔄 Clearing existing studentManager');
        delete window.studentManager;
    }
    
    // 2. Load the script file
    const studentManagementScript = document.createElement('script');
    studentManagementScript.src = '/Student_result_analytics/js/admin/student-management.js';
    studentManagementScript.async = false;
    
    // 3. After script loads
    studentManagementScript.onload = function() {
        console.log('✅ student-management.js loaded');
        console.log('StudentManager defined:', typeof StudentManager);
        
        setTimeout(() => {
            if (document.getElementById('studentsContainer')) {
                console.log('🎯 On student management page, initializing...');
                
                if (typeof StudentManager !== 'undefined') {
                    if (!window.studentManager) {
                        console.log('🚀 Creating StudentManager instance');
                        window.studentManager = new StudentManager();
                        console.log('✅ studentManager created:', !!window.studentManager);
                    }
                }
            }
        }, 300);
    };
    
    // 4. Handle errors
    studentManagementScript.onerror = function(error) {
        console.error('❌ Failed to load student-management.js:', error);
    };
    
    // 5. Add to DOM
    document.head.appendChild(studentManagementScript);
    
    // 6. Safety check
    setTimeout(() => {
        console.log('🕒 Safety check:');
        console.log('StudentManager:', typeof StudentManager);
        console.log('studentManager:', !!window.studentManager);
    }, 2000);
} else {
    console.log('⚠️ Student page already initialized, skipping');
}
</script>