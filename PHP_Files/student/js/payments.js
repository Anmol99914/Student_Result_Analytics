// File: PHP_Files/student/js/payments.js
// Student Payments Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    console.log('Payments page loaded');
    initPaymentsPage();
});

function initPaymentsPage() {
    // Add hover effects to payment method cards
    document.querySelectorAll('.payment-method').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
            this.style.transition = 'all 0.3s ease';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
    
    // Initialize tooltips
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(t => new bootstrap.Tooltip(t));
}

function initiatePayment(method) {
    const amount = document.getElementById('payAmount').value;
    const due = parseFloat('<?php echo $summary["total_due"]; ?>');
    
    if (!amount || amount <= 0) {
        showToast('Enter valid amount', 'warning');
        return;
    }
    
    if (parseFloat(amount) > due) {
        showToast('Amount exceeds due', 'warning');
        return;
    }
    
    showToast(`Processing ${method} payment...`, 'info');
    
    // Simulate redirect
    setTimeout(() => {
        const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
        modal.show();
    }, 1000);
}

function processPayment() {
    const amount = document.getElementById('payAmount').value;
    const btn = event.target;
    const originalText = btn.innerHTML;
    
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    btn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        showToast('Payment of NPR ' + amount + ' successful!', 'success');
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        // Reload after 2 seconds
        setTimeout(() => location.reload(), 2000);
    }, 3000);
}

function showBankDetails() {
    const bankInfo = `
        <div class="bank-details">
            <h5>Bank Details</h5>
            <p><strong>Bank:</strong> Global Bank Nepal</p>
            <p><strong>Account:</strong> College Name</p>
            <p><strong>Account No:</strong> 1234567890123456</p>
            <p><strong>Reference:</strong> Student ID - <?php echo $_SESSION['student_username']; ?></p>
            <div class="alert alert-info mt-3"><i class="bi bi-info-circle me-2"></i>Include Student ID in reference</div>
        </div>
    `;
    
    showModal('Bank Transfer', bankInfo);
}

function viewReceipt(paymentId) {
    showToast('Generating receipt...', 'info');
    // window.location.href = '../api/generate_receipt.php?id=' + paymentId;
}

function downloadReceipt() {
    showToast('Downloading receipt...', 'info');
    setTimeout(() => showToast('Downloaded', 'success'), 1500);
}

function refreshPayments() {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Refreshing...';
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        showToast('Refreshed', 'success');
    }, 1000);
}

function showModal(title, content) {
    const modalId = 'customModal';
    let modal = document.getElementById(modalId);
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">${title}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">${content}</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    } else {
        modal.querySelector('.modal-title').textContent = title;
        modal.querySelector('.modal-body').innerHTML = content;
    }
    
    new bootstrap.Modal(modal).show();
}

// Create payment modal dynamically
const paymentModal = document.createElement('div');
paymentModal.id = 'paymentModal';
paymentModal.className = 'modal fade';
paymentModal.innerHTML = `
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Payment Gateway</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3"></div>
                <h5>Redirecting...</h5>
                <p class="text-muted">Secure payment page loading...</p>
            </div>
        </div>
    </div>
`;
document.body.appendChild(paymentModal);

// Make functions global
window.initiatePayment = initiatePayment;
window.processPayment = processPayment;
window.showBankDetails = showBankDetails;
window.viewReceipt = viewReceipt;
window.downloadReceipt = downloadReceipt;
window.refreshPayments = refreshPayments;