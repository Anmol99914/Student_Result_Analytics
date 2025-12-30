<?php
// File: PHP_Files/student/pages/results.php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-clipboard-data me-2"></i>
                        Academic Results
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-clipboard-check display-1 text-info mb-3"></i>
                        <h3>Results Module</h3>
                        <p class="text-muted">This module is under development.</p>
                        <p class="small text-muted">Will include: Semester-wise results, GPA calculation, and detailed analytics.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>