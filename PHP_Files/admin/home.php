<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] != true){
    exit("Unauthorized");
}
?>

<div class="text-center py-5">
    <h1 class="display-4">Welcome to Student Result Analytics Admin Panel</h1>
    <p class="lead mt-3">Manage students, teachers, classes, and results from here.</p>
    
    <div class="row mt-5">
        <div class="col-md-4 mb-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-people display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Students</h5>
                    <p class="card-text">Manage student records and information</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-person-square display-4 text-success"></i>
                    <h5 class="card-title mt-3">Teachers</h5>
                    <p class="card-text">Manage teacher accounts and assignments</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-trophy display-4 text-info"></i>
                    <h5 class="card-title mt-3">Results</h5>
                    <p class="card-text">View and manage student results</p>
                </div>
            </div>
        </div>
    </div>
</div>