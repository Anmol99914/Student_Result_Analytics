<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal - Student Result Analytics</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <style>
        :root {
            --student-primary: #28a745;
            --student-secondary: #218838;
            --student-light: #e8f5e9;
        }
        
        body { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .login-wrapper {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(40, 167, 69, 0.12);
            border: 1px solid rgba(40, 167, 69, 0.08);
            overflow: hidden;
        }
        
        .student-icon {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, var(--student-primary) 0%, var(--student-secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -45px auto 25px;
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.25);
            border: 4px solid white;
            position: relative;
        }
        
        .student-icon:after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            background: linear-gradient(135deg, var(--student-primary) 0%, var(--student-secondary) 100%);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.12;
        }
        
        .form-control {
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            padding: 14px 18px;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            background: #f9f9f9;
        }
        
        .form-control:focus {
            border-color: var(--student-primary);
            box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.1);
            background: white;
            transform: translateY(-1px);
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .btn-student {
            background: linear-gradient(135deg, var(--student-primary) 0%, var(--student-secondary) 100%);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.2);
        }
        
        .btn-student:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(40, 167, 69, 0.3);
            background: linear-gradient(135deg, var(--student-secondary) 0%, var(--student-primary) 100%);
        }
        
        .btn-student:active {
            transform: translateY(-1px);
        }
        
        .floating-label {
            position: relative;
            margin-bottom: 25px;
        }
        
        .floating-label .form-control:focus ~ .floating-text,
        .floating-label .form-control:not(:placeholder-shown) ~ .floating-text {
            transform: translateY(-28px) scale(0.85);
            color: var(--student-primary);
            font-weight: 600;
            background: white;
            padding: 0 10px;
            left: 10px;
        }
        
        .floating-text {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: #f9f9f9;
            padding: 0 8px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            pointer-events: none;
            color: #64748b;
            font-size: 1.05rem;
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            padding: 6px 15px;
            border-radius: 25px;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.2);
        }
        
        .header-text {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header-text h2 {
            color: #1a202c;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--student-primary) 0%, var(--student-secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-text p {
            color: #718096;
            font-size: 1.1rem;
            font-weight: 400;
        }
        
        .form-check-input:checked {
            background-color: var(--student-primary);
            border-color: var(--student-primary);
        }
        
        .form-check-input:focus {
            border-color: var(--student-primary);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25);
        }
        
        .help-links {
            color: var(--student-primary);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .help-links:hover {
            color: var(--student-secondary);
            text-decoration: underline;
        }
        
        .student-notice {
            background: linear-gradient(135deg, var(--student-light) 0%, #f0f9f0 100%);
            border: 1px solid rgba(40, 167, 69, 0.12);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-top: 25px;
        }
        
        .copyright {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 35px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        
        .copyright a {
            color: var(--student-primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .copyright a:hover {
            color: var(--student-secondary);
            text-decoration: underline;
        }
        
        .back-home-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #e2e8f0;
            color: var(--student-primary);
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .back-home-btn:hover {
            background: white;
            border-color: var(--student-primary);
            color: var(--student-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.15);
        }
        
        .welcome-note {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 5px solid var(--student-primary);
        }
        
        @media (max-width: 768px) {
            .login-wrapper {
                max-width: 90%;
                padding: 0 15px;
            }
            
            .student-icon {
                width: 80px;
                height: 80px;
                margin-top: -40px;
            }
            
            .header-text h2 {
                font-size: 1.7rem;
            }
            
            .welcome-note {
                padding: 15px;
            }
        }
    </style>
</head>

<body class="d-flex flex-column align-items-center justify-content-center py-5">

    <!-- Back to Home -->
    <a href="../../index.html" class="btn back-home-btn position-fixed" 
       style="top: 25px; left: 25px; z-index: 1000;">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>

    <!-- Main Container -->
    <div class="login-wrapper">
        <div class="login-container p-5 position-relative">
            <!-- Status Badge -->
            <div class="status-badge">
                <i class="bi bi-mortarboard me-2"></i>Student Portal
            </div>
            
            <!-- Student Icon -->
            <div class="student-icon">
                <i class="bi bi-person-circle text-white fs-2"></i>
            </div>
            
            <!-- Page heading -->
            <div class="header-text">
                <h2>Student Portal</h2>
                <p>View Results & Academic Profile</p>
            </div>

            <!-- Welcome Note -->
            <div class="welcome-note">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill fs-5 me-3" style="color: var(--student-primary);"></i>
                    <div>
                        <h6 class="mb-2" style="color: var(--student-secondary);">Welcome Students!</h6>
                        <p class="mb-0 small">Access your academic results, view profile details, and check payment status securely.</p>
                    </div>
                </div>
            </div>

            <?php
            // Error messages
            $error_msg = '';
            if(isset($_GET['error'])){
                if($_GET['error'] === "invalid"){
                    $error_msg = "Invalid Student ID or password!";
                } elseif ($_GET['error'] === "inactive") {
                    $error_msg = "Your account is inactive. Please contact administration.";
                }
            }
            
            if($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                    <div>
                        <strong class="d-block">Login Failed</strong>
                        <span class="small"><?php echo $error_msg; ?></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="student_validation.php" method="POST" name="student_form" id="studentForm" class="needs-validation" novalidate>
                <div class="floating-label">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder=" " required autocomplete="username">
                    <label class="floating-text" for="username">
                        <i class="bi bi-person-badge me-2"></i>Student ID
                    </label>
                    <div class="invalid-feedback">
                        Please enter your Student ID.
                    </div>
                    <small class="form-text text-muted mt-1">e.g., BIT183, BCA413</small>
                </div>
                
                <div class="floating-label">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder=" " required autocomplete="current-password">
                    <label class="floating-text" for="password">
                        <i class="bi bi-key me-2"></i>Password
                    </label>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                        <label class="form-check-label text-muted" for="remember">
                            <i class="bi bi-clock-history me-1"></i>Remember me
                        </label>
                    </div>
                    <a href="#" class="help-links small">
                        <i class="bi bi-question-circle me-1"></i>Forgot password?
                    </a>
                </div>
                
                <button type="submit" class="btn btn-student w-100" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Access Student Dashboard
                </button>
            </form>
            
            <!-- Copyright -->
            <div class="copyright">
                <p class="mb-2">© 2025 Student Result Analytics System</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced form handling with floating labels
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('studentForm');
            const loginBtn = document.getElementById('loginBtn');
            
            // Initialize floating labels
            const floatingInputs = document.querySelectorAll('.floating-label .form-control');
            floatingInputs.forEach(input => {
                if (input.value.trim() !== '') {
                    input.nextElementSibling.classList.add('active');
                }
                
                input.addEventListener('focus', function() {
                    this.nextElementSibling.classList.add('active');
                });
                
                input.addEventListener('blur', function() {
                    if (this.value.trim() === '') {
                        this.nextElementSibling.classList.remove('active');
                    }
                });
            });
            
            // Auto-focus on Student ID field
            document.getElementById('username').focus();
            
            // Form validation
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!form.checkValidity()) {
                    e.stopPropagation();
                    form.classList.add('was-validated');
                    return;
                }
                
                // Show loading state
                const originalText = loginBtn.innerHTML;
                loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
                loginBtn.disabled = true;
                
                // Add loading animation to button
                loginBtn.style.opacity = '0.9';
                
                // Submit form
                const formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else {
                        return response.text();
                    }
                })
                .then(data => {
                    if (data) {
                        try {
                            const result = JSON.parse(data);
                            if (result.success) {
                                // Add success animation
                                loginBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Login Successful!';
                                loginBtn.style.background = 'linear-gradient(135deg, #20c997 0%, #198754 100%)';
                                
                                // Redirect after short delay
                                setTimeout(() => {
                                    window.location.href = 'student_dashboard.php';
                                }, 600);
                            } else {
                                window.location.href = 'student_login.php?error=invalid';
                            }
                        } catch (e) {
                            window.location.href = 'student_login.php?error=invalid';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    window.location.href = 'student_login.php?error=invalid';
                })
                .finally(() => {
                    // Reset button after 2 seconds if not redirected
                    setTimeout(() => {
                        loginBtn.innerHTML = originalText;
                        loginBtn.disabled = false;
                        loginBtn.style.opacity = '1';
                        loginBtn.style.background = '';
                    }, 2000);
                });
            });
            
            // Input animations
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
            
            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Enter to submit
                if (e.key === 'Enter' && !e.ctrlKey) {
                    if (document.activeElement.tagName !== 'BUTTON') {
                        form.dispatchEvent(new Event('submit'));
                    }
                }
            });
            
            // Add focus effect to button
            loginBtn.addEventListener('mouseenter', function() {
                if (!this.disabled) {
                    this.style.transform = 'translateY(-3px)';
                }
            });
            
            loginBtn.addEventListener('mouseleave', function() {
                if (!this.disabled) {
                    this.style.transform = 'translateY(0)';
                }
            });
            
            // Student ID format suggestion
            const studentIdInput = document.getElementById('username');
            studentIdInput.addEventListener('input', function() {
                const value = this.value.toUpperCase();
                if (value.length === 3 && /^[A-Z]{3}$/.test(value)) {
                    this.value = value;
                }
            });
        });
        
        // Handle back button cache
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>

</body>

</html>