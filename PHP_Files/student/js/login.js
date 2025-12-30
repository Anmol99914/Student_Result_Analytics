// File: PHP_Files/student/js/login.js
// Student Login Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('studentForm');
    const loginBtn = document.getElementById('loginBtn');
    const rememberCheckbox = document.getElementById('remember');
    
    if (!form) return;
    
    // Auto-focus on Student ID field
    document.getElementById('username').focus();
    
       // Floating labels - Improved version
       const floatingInputs = document.querySelectorAll('.floating-label .form-control');
       floatingInputs.forEach(input => {
           // Set placeholder to empty string for floating labels
           input.setAttribute('placeholder', ' ');
           
           // Check if input has value on page load
           if (input.value.trim() !== '') {
               input.parentElement.classList.add('has-value');
               input.nextElementSibling.classList.add('active');
           }
           
           input.addEventListener('focus', function() {
               this.parentElement.classList.add('focused');
               this.nextElementSibling.classList.add('active');
           });
           
           input.addEventListener('blur', function() {
               this.parentElement.classList.remove('focused');
               if (this.value.trim() === '') {
                   this.nextElementSibling.classList.remove('active');
                   this.parentElement.classList.remove('has-value');
               } else {
                   this.parentElement.classList.add('has-value');
               }
           });
           
           // Auto-uppercase for Student ID
           if (input.id === 'username') {
               input.addEventListener('input', function() {
                   this.value = this.value.toUpperCase();
                   if (this.value.trim() !== '') {
                       this.nextElementSibling.classList.add('active');
                       this.parentElement.classList.add('has-value');
                   }
               });
           }
       });   
    
    // Student ID format validation
    const studentIdInput = document.getElementById('username');
    studentIdInput.addEventListener('input', function() {
        // Auto-uppercase
        this.value = this.value.toUpperCase();
        
        // Validate format: BCA001, BBM001, etc.
        const isValid = /^[A-Z]{3}\d{3}$/.test(this.value);
        if (this.value.length > 0 && !isValid) {
            this.setCustomValidity('Format: ABC123 (3 letters + 3 numbers)');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Form submission
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Basic validation
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add('was-validated');
            return;
        }
        
        // Show loading state
        const originalText = loginBtn.innerHTML;
        loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Logging in...';
        loginBtn.disabled = true;
        loginBtn.style.opacity = '0.8';
        
        try {
            const formData = new FormData(form);
            
            // Make login request
            const response = await fetch('../api/login_validate.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            
            if (response.redirected) {
                // Redirect to dashboard
                window.location.href = response.url;
            } else {
                const result = await response.text();
                
                // Check if login was successful
                if (result.includes('Location:')) {
                    // PHP redirected - follow it
                    window.location.href = '../pages/dashboard.php';
                } else {
                    // Show error
                    window.location.href = 'login.php?error=invalid';
                }
            }
        } catch (error) {
            console.error('Login error:', error);
            window.location.href = 'login.php?error=network';
        } finally {
            // Reset button after delay
            setTimeout(() => {
                loginBtn.innerHTML = originalText;
                loginBtn.disabled = false;
                loginBtn.style.opacity = '1';
            }, 2000);
        }
    });
    
    // Button hover effects
    loginBtn.addEventListener('mouseenter', function() {
        if (!this.disabled) {
            this.style.transform = 'translateY(-2px)';
        }
    });
    
    loginBtn.addEventListener('mouseleave', function() {
        if (!this.disabled) {
            this.style.transform = 'translateY(0)';
        }
    });
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Enter to submit (unless in textarea)
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
            if (!e.ctrlKey && !e.shiftKey) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
    
    // Remember me functionality
    if (rememberCheckbox) {
        // Check if credentials are saved
        const savedUsername = localStorage.getItem('student_username');
        const savedRemember = localStorage.getItem('student_remember') === 'true';
        
        if (savedRemember && savedUsername) {
            studentIdInput.value = savedUsername;
            rememberCheckbox.checked = true;
            studentIdInput.nextElementSibling.classList.add('active');
        }
        
        // Save on change
        rememberCheckbox.addEventListener('change', function() {
            if (this.checked && studentIdInput.value) {
                localStorage.setItem('student_username', studentIdInput.value);
                localStorage.setItem('student_remember', 'true');
            } else {
                localStorage.removeItem('student_username');
                localStorage.removeItem('student_remember');
            }
        });
    }
});