<?php
// File: PHP_Files/student/pages/payments.php
require_once '../includes/auth_check.php';
require_student_login();

// Include root config
require_once '../../../config.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>
                        Fee Payments
                    </h4>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="bi bi-credit-card-2-back display-1 text-success mb-3"></i>
                        <h3>Payments Module</h3>
                        <p class="text-muted">This module is under development.</p>
                        <p class="small text-muted">Will include: Payment history, online payment gateway, and fee status tracking.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>