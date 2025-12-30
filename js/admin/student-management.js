// student-management.js - FIXED TIMING
console.log('=== student-management.js START ===');

// DELETE any existing StudentManager to avoid conflicts
if (window.StudentManager) {
    console.log('Clearing existing StudentManager...');
    delete window.StudentManager;
}

// DEFINE StudentManager IMMEDIATELY
window.StudentManager = class StudentManager {
    constructor() {
        console.log('🎯 StudentManager CONSTRUCTOR called');
        this.init();
    }
    
    init() {
        console.log('📊 StudentManager INIT called');
        
        // Load data immediately
        this.loadStudents();
        this.loadStats();
        
        // Setup events
        setTimeout(() => this.setupEventListeners(), 100);
    }
    
    setupEventListeners() {
        console.log('🔗 Setting up event listeners');
        
        document.getElementById('addStudentBtn')?.addEventListener('click', () => {
            alert('Add student would open here');
        });
        
        document.getElementById('searchBtn')?.addEventListener('click', () => {
            this.loadStudents();
        });
    }
    
    async loadStudents() {
        console.log('🔄 Loading students...');
        
        const container = document.getElementById('studentsContainer');
        if (!container) {
            console.error('❌ studentsContainer not found!');
            return;
        }
        
        // Show loading
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Loading student data...</p>
            </div>
        `;
        
        try {
            // CORRECT PATHS with /admin/
            const paths = [
                '../admin/students/get_students.php',    // FROM: pages/manage_students.php → admin/students/
                '../../admin/students/get_students.php', // Alternative
                'admin/students/get_students.php',       // Relative
                '/Student_result_analytics/PHP_Files/admin/students/get_students.php'  // Absolute
            ];
            
            let response, students;
            
            for (const path of paths) {
                try {
                    console.log(`Trying path: ${path}`);
                    response = await fetch(path);
                    if (response.ok) {
                        students = await response.json();
                        console.log(`✅ Success with: ${path}`);
                        console.log(`📊 Found ${students.length} students`);
                        break;
                    }
                } catch (e) {
                    console.log(`❌ Failed: ${path} - ${e.message}`);
                }
            }
            
            if (students) {
                this.renderStudents(students);
            } else {
                throw new Error('All API paths failed');
            }
            
        } catch (error) {
            console.error('❌ Error loading students:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Error: ${error.message}
                    <button class="btn btn-sm btn-danger mt-2" onclick="window.studentManager.loadStudents()">
                        Retry
                    </button>
                </div>
            `;
        }
    }
    
    renderStudents(students) {
        const container = document.getElementById('studentsContainer');
        if (!container) return;
        
        console.log(`🎨 Rendering ${students.length} students`);
        
        if (students.length === 0) {
            container.innerHTML = '<div class="alert alert-info">No students found</div>';
            return;
        }
        
        let html = '<table class="table table-sm"><thead><tr>';
        html += '<th>ID</th><th>Name</th><th>Email</th><th>Faculty</th><th>Status</th></tr></thead><tbody>';
        
        students.forEach(s => {
            html += `<tr>
                <td><strong>${s.student_id}</strong></td>
                <td>${s.student_name}</td>
                <td>${s.email}</td>
                <td>${s.faculty || 'N/A'}</td>
                <td><span class="badge bg-${s.is_active == 1 ? 'success' : 'danger'}">
                    ${s.is_active == 1 ? 'Active' : 'Inactive'}
                </span></td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    async loadStats() {
        console.log('📈 Loading stats...');
        try {
            // CORRECT PATH with /admin/
            const response = await fetch('../admin/students/get_students.php?stats=true');
            const stats = await response.json();
            
            // Update stat cards
            ['totalStudents', 'activeStudents', 'pendingStudents', 'recentStudents'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.textContent = stats[id] || 0;
            });
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }
};

console.log('✅ StudentManager class defined');

// WAIT FOR DOM TO BE READY BEFORE INITIALIZING
function initializeStudentManager() {
    console.log('🔍 Checking if we should initialize...');
    console.log('studentsContainer exists:', !!document.getElementById('studentsContainer'));
    console.log('StudentManager defined:', typeof StudentManager);
    console.log('studentManager exists:', !!window.studentManager);
    
    if (document.getElementById('studentsContainer')) {
        console.log('🚀 Auto-creating studentManager instance...');
        if (!window.studentManager && window.StudentManager) {
            window.studentManager = new window.StudentManager();
            console.log('✅ studentManager created');
        }
    } else {
        console.log('⏳ studentsContainer not found yet, checking again in 300ms...');
        setTimeout(initializeStudentManager, 300);
    }
}

// Start initialization process
if (document.readyState === 'loading') {
    // DOM not ready yet, wait for it
    document.addEventListener('DOMContentLoaded', initializeStudentManager);
} else {
    // DOM already ready
    initializeStudentManager();
}

// ===== GUARANTEED INITIALIZATION =====
console.log('🔄 Setting up guaranteed initialization...');

function guaranteedInit() {
    console.log('🔍 Checking for studentsContainer...');
    
    if (document.getElementById('studentsContainer')) {
        console.log('🎯 Found studentsContainer');
        
        if (!window.studentManager && window.StudentManager) {
            console.log('🚀 Creating studentManager instance');
            window.studentManager = new window.StudentManager();
            console.log('✅ studentManager created');
            return true;
        } else if (window.studentManager) {
            console.log('✅ studentManager already exists');
            return true;
        }
    }
    
    return false;
}

// Try immediately
if (!guaranteedInit()) {
    // Try every 200ms for 3 seconds
    let attempts = 0;
    const interval = setInterval(() => {
        attempts++;
        console.log(`Attempt ${attempts} to initialize...`);
        
        if (guaranteedInit() || attempts >= 15) {
            clearInterval(interval);
            console.log(attempts >= 15 ? '❌ Failed after 15 attempts' : '✅ Initialized successfully');
        }
    }, 200);
}

console.log('🏁 student-management.js execution complete');

