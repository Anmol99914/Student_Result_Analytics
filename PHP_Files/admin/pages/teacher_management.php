<?php
// teacher_management.php - Teacher Management Page
// include('../../../config.php');
include(__DIR__ . '/../../../config.php');

?>
<div class="teacher-management-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-person-square me-2"></i>Teacher Management</h2>
            <p class="text-muted mb-0">Manage all teachers and their assignments</p>
        </div>
        <div>
            <button class="btn btn-primary" id="addTeacherBtn">
                <i class="bi bi-person-plus"></i> Add New Teacher
            </button>
        </div>
    </div>
    
    <!-- Teacher Tabs -->
    <ul class="nav nav-tabs mb-4" id="teacherTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" 
                    data-bs-target="#active-teachers" type="button" role="tab">
                <i class="bi bi-person-check"></i> Active Teachers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inactive-tab" data-bs-toggle="tab" 
                    data-bs-target="#inactive-teachers" type="button" role="tab">
                <i class="bi bi-person-x"></i> Inactive Teachers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="all-tab" data-bs-toggle="tab" 
                    data-bs-target="#all-teachers" type="button" role="tab">
                <i class="bi bi-people"></i> All Teachers
            </button>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="teacherTabContent">
        <!-- Active Teachers Tab -->
        <div class="tab-pane fade show active" id="active-teachers" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div id="active-teachers-container">
                        <!-- Content loaded via JavaScript -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading active teachers...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Inactive Teachers Tab -->
        <div class="tab-pane fade" id="inactive-teachers" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div id="inactive-teachers-container">
                        <!-- Content loaded via JavaScript -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading inactive teachers...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- All Teachers Tab -->
        <div class="tab-pane fade" id="all-teachers" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div id="all-teachers-container">
                        <!-- Content loaded via JavaScript -->
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading all teachers...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load the separate JavaScript file -->
<script src="../../js/admin/teacher-management.js"></script>
<script>
// Simple check to ensure initialization
setTimeout(function() {
    if (typeof TeacherManager !== 'undefined' && !window.teacherManager) {
        console.log('Direct initialization from teacher_management.php');
        window.teacherManager = new TeacherManager();
    }
}, 500);
</script>