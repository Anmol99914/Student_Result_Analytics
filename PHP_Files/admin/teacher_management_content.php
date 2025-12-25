<?php
// teacher_management_content.php - FIXED VERSION
include_once("../../config.php");

// Get teacher statistics - FIXED CALCULATIONS
$total_teachers = $connection->query("SELECT COUNT(*) as count FROM teacher")->fetch_assoc()['count'];
$active_teachers = $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status = 'active'")->fetch_assoc()['count'];
$inactive_teachers = $connection->query("SELECT COUNT(*) as count FROM teacher WHERE status = 'inactive'")->fetch_assoc()['count'];

// FIX 1: Get assigned teachers CORRECTLY (teachers with ANY class in class table)
$assigned_teachers = $connection->query("SELECT COUNT(DISTINCT t.teacher_id) as count FROM teacher t INNER JOIN class c ON t.teacher_id = c.teacher_id WHERE t.status = 'active'")->fetch_assoc()['count'];

// FIX 2: Get unassigned teachers DIRECTLY from database (NOT by subtraction)
$unassigned_query = "SELECT COUNT(*) as count FROM teacher 
                     WHERE status = 'active' 
                     AND (assigned_class_id IS NULL OR assigned_class_id = 0 OR assigned_class_id = '')";
$unassigned_result = $connection->query($unassigned_query);
$unassigned_teachers = $unassigned_result->fetch_assoc()['count'];

// FIX 3: Calculate percentage based on ACTUAL assigned count
$assigned_percentage = $active_teachers > 0 ? round(($assigned_teachers / $active_teachers) * 100) : 0;
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">Teacher Management</h1>
            <p class="text-muted">Manage all teachers, their status, and assignments</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Teachers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_teachers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-people-fill fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $active_teachers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-toggle-on fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Inactive</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $inactive_teachers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-toggle-off fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Unassigned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="unassigned-count">
                                <?php echo $unassigned_teachers; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-x fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Teaching</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $assigned_teachers; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-danger shadow h-100 py-2 stats-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Assigned</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $assigned_percentage; ?>%</div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-percent fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">All Teachers</h6>
            <a href="javascript:void(0)" onclick="showAddTeacherForm()" class="btn btn-success">
                <i class="bi bi-person-plus"></i> Add New Teacher
            </a>
        </div>
        <div class="card-body">
            <!-- Tabs navigation -->
            <ul class="nav nav-tabs" id="teacherTabs">
                <li class="nav-item">
                    <a class="nav-link active" data-tab="active" href="javascript:void(0)">
                        <i class="bi bi-person-check"></i> Active Teachers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-tab="inactive" href="javascript:void(0)">
                        <i class="bi bi-person-x"></i> Inactive Teachers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-tab="unassigned" href="javascript:void(0)">
                        <i class="bi bi-person-dash"></i> Unassigned Teachers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-tab="all" href="javascript:void(0)">
                        <i class="bi bi-people"></i> All Teachers
                    </a>
                </li>
            </ul>

            <!-- Tab Content Container -->
            <div id="teachers-table-container" class="mt-3">
                <!-- Content loaded via AJAX here -->
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

<!-- Add this JavaScript to update stats when teachers are added/edited -->
<script>
// Function to refresh teacher stats
function refreshTeacherStats() {
    fetch('get_teacher_stats.php')
        .then(response => response.json())
        .then(stats => {
            // Update the unassigned count on the dashboard
            const unassignedElement = document.getElementById('unassigned-count');
            if (unassignedElement) {
                unassignedElement.textContent = stats.unassigned;
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

// Call this after adding/editing teachers
// Example: In your add teacher success handler:
function onTeacherAddedSuccess() {
    // ... existing success code ...
    refreshTeacherStats(); // Add this line
    // ... rest of success handling ...
}
</script>