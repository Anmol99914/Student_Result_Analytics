<?php
// teacher_management.php - SIMPLE TEMPLATE
?>
<div class="container-fluid p-4">
    <div class="teacher-management-page">
        <!-- Container for JavaScript -->
        <div id="teachers-container">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading teacher management system...</p>
            </div>
        </div>
    </div>
</div>

<!-- Load teacher management JS -->
<script src="../../js/admin/teacher-management.js?v=<?php echo time(); ?>"></script>

<!-- Simple initialization -->
<script>
console.log('Teacher management page loaded');

// Check if teacherManager exists
function initTeacherManager() {
    if (window.teacherManager && typeof window.teacherManager.init === 'function') {
        console.log('teacherManager found, initializing...');
        window.teacherManager.init();
        return true;
    }
    console.log('teacherManager not ready yet');
    return false;
}

// Try to initialize
if (!initTeacherManager()) {
    // Single retry after short delay
    setTimeout(initTeacherManager, 300);
}
</script>