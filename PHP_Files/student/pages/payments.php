<?php
// File: PHP_Files/student/pages/payments.php
require_once '../includes/auth_check.php';
require_student_login();

require_once '../../../config.php';

$student_id = $_SESSION['student_username'];

// Fetch payment data
$payments = getPaymentHistory($student_id);
$summary = calculatePaymentSummary($payments);

function getPaymentHistory($student_id) {
    global $connection;
    $stmt = $connection->prepare("SELECT payment_id, total_amount, amount_paid, due_amount, payment_status, payment_date, DATE_FORMAT(payment_date, '%M %d, %Y') as formatted_date FROM payment WHERE student_id = ? ORDER BY payment_date DESC");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

function calculatePaymentSummary($payments) {
    $summary = ['total_amount' => 0, 'total_paid' => 0, 'total_due' => 0, 'last_payment_date' => null, 'payment_count' => 0, 'status' => 'Unpaid'];
    
    if ($payments->num_rows > 0) {
        while($row = $payments->fetch_assoc()) {
            $summary['total_amount'] += $row['total_amount'];
            $summary['total_paid'] += $row['amount_paid'];
            $summary['total_due'] += $row['due_amount'];
            $summary['payment_count']++;
            if (!$summary['last_payment_date'] || $row['payment_date'] > $summary['last_payment_date']) {
                $summary['last_payment_date'] = $row['payment_date'];
            }
        }
        $payments->data_seek(0);
        $summary['status'] = $summary['total_due'] <= 0 ? 'Paid' : ($summary['total_paid'] > 0 ? 'Partial' : 'Unpaid');
    }
    return $summary;
}
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div><h4 class="mb-0"><i class="bi bi-credit-card me-2"></i>Fee Payments</h4><small class="opacity-75">View payment history</small></div>
                        <span class="badge bg-<?php echo $summary['status'] === 'Paid' ? 'light text-success' : ($summary['status'] === 'Partial' ? 'warning' : 'danger'); ?> fs-6">
                            <i class="bi bi-<?php echo $summary['status'] === 'Paid' ? 'check-circle' : ($summary['status'] === 'Partial' ? 'exclamation-triangle' : 'x-circle'); ?> me-1"></i>
                            <?php echo $summary['status']; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3"><div class="card border-primary"><div class="card-body"><div class="d-flex align-items-center"><div class="avatar bg-primary bg-opacity-10 p-3 rounded me-3"><i class="bi bi-cash-stack text-primary fs-4"></i></div><div><h6 class="text-muted mb-1">Total Fees</h6><h4 class="mb-0">NPR <?php echo number_format($summary['total_amount'], 2); ?></h4></div></div></div></div></div>
        <div class="col-md-3 col-6 mb-3"><div class="card border-success"><div class="card-body"><div class="d-flex align-items-center"><div class="avatar bg-success bg-opacity-10 p-3 rounded me-3"><i class="bi bi-check-circle text-success fs-4"></i></div><div><h6 class="text-muted mb-1">Paid</h6><h4 class="mb-0">NPR <?php echo number_format($summary['total_paid'], 2); ?></h4></div></div></div></div></div>
        <div class="col-md-3 col-6 mb-3"><div class="card border-<?php echo $summary['total_due'] > 0 ? 'warning' : 'info'; ?>"><div class="card-body"><div class="d-flex align-items-center"><div class="avatar bg-<?php echo $summary['total_due'] > 0 ? 'warning' : 'info'; ?> bg-opacity-10 p-3 rounded me-3"><i class="bi bi-clock text-<?php echo $summary['total_due'] > 0 ? 'warning' : 'info'; ?> fs-4"></i></div><div><h6 class="text-muted mb-1">Due</h6><h4 class="mb-0">NPR <?php echo number_format($summary['total_due'], 2); ?></h4></div></div></div></div></div>
        <div class="col-md-3 col-6 mb-3"><div class="card border-secondary"><div class="card-body"><div class="d-flex align-items-center"><div class="avatar bg-secondary bg-opacity-10 p-3 rounded me-3"><i class="bi bi-receipt text-secondary fs-4"></i></div><div><h6 class="text-muted mb-1">Transactions</h6><h4 class="mb-0"><?php echo $summary['payment_count']; ?></h4><?php if($summary['last_payment_date']): ?><small class="text-muted">Last: <?php echo date('M d, Y', strtotime($summary['last_payment_date'])); ?></small><?php endif; ?></div></div></div></div></div>
    </div>
    
    <!-- Payment Options -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-lightning-charge text-primary me-2"></i>Quick Payment</h5></div>
                <div class="card-body">
                    <?php if($summary['total_due'] > 0): ?>
                    <div class="alert alert-info"><i class="bi bi-info-circle-fill me-2"></i>Pay your due amount securely through our payment gateway.</div>
                    <div class="row g-3">
                        <div class="col-md-4"><div class="card payment-method border-primary text-center"><div class="card-body"><i class="bi bi-credit-card-2-front display-4 text-primary mb-3"></i><h5>Card</h5><p class="text-muted small">Visa/Mastercard</p><button class="btn btn-outline-primary w-100" onclick="initiatePayment('card')">Pay Now</button></div></div></div>
                        <div class="col-md-4"><div class="card payment-method border-success text-center"><div class="card-body"><i class="bi bi-phone display-4 text-success mb-3"></i><h5>Mobile</h5><p class="text-muted small">eSewa/Khalti</p><button class="btn btn-outline-success w-100" onclick="initiatePayment('mobile')">Pay Now</button></div></div></div>
                        <div class="col-md-4"><div class="card payment-method border-warning text-center"><div class="card-body"><i class="bi bi-bank display-4 text-warning mb-3"></i><h5>Bank</h5><p class="text-muted small">Transfer</p><button class="btn btn-outline-warning w-100" onclick="showBankDetails()">View</button></div></div></div>
                    </div>
                    <div class="mt-4 p-4 bg-light rounded">
                        <h6 class="mb-3">Payment Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Amount Due</label><div class="input-group"><span class="input-group-text">NPR</span><input type="text" class="form-control" value="<?php echo number_format($summary['total_due'], 2); ?>" readonly></div></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Pay Amount</label><div class="input-group"><span class="input-group-text">NPR</span><input type="number" class="form-control" id="payAmount" min="100" max="<?php echo $summary['total_due']; ?>" value="<?php echo $summary['total_due']; ?>"></div></div>
                        </div>
                        <div class="d-grid"><button class="btn btn-primary btn-lg" onclick="processPayment()"><i class="bi bi-lock me-2"></i>Proceed to Payment</button></div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4"><i class="bi bi-check-circle display-1 text-success mb-3"></i><h4>All Fees Paid</h4><p class="text-muted">No pending fees.</p><button class="btn btn-outline-primary" onclick="downloadReceipt()"><i class="bi bi-download me-2"></i>Download Receipt</button></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Guidelines -->
        <div class="col-lg-4">
            <div class="card"><div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-info-circle text-info me-2"></i>Guidelines</h5></div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-0 border-0"><div class="d-flex"><i class="bi bi-1-circle text-primary me-3"></i><div><h6 class="mb-1">Deadlines</h6><p class="mb-0 small text-muted">Pay before exam period.</p></div></div></div>
                    <div class="list-group-item px-0 border-0"><div class="d-flex"><i class="bi bi-2-circle text-primary me-3"></i><div><h6 class="mb-1">Late Payment</h6><p class="mb-0 small text-muted">2% monthly charge.</p></div></div></div>
                    <div class="list-group-item px-0 border-0"><div class="d-flex"><i class="bi bi-3-circle text-primary me-3"></i><div><h6 class="mb-1">Confirmation</h6><p class="mb-0 small text-muted">24-48 hours processing.</p></div></div></div>
                </div>
                <div class="mt-4 pt-3 border-top"><h6 class="mb-3"><i class="bi bi-headset text-warning me-2"></i>Help?</h6><div class="alert alert-warning small"><p class="mb-2"><strong>Accounts</strong></p><p class="mb-1"><i class="bi bi-telephone me-2"></i> +977 1-1234567</p><p class="mb-0"><i class="bi bi-envelope me-2"></i> accounts@college.edu</p></div></div>
            </div></div>
        </div>
    </div>
    
    <!-- Payment History -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Payment History</h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshPayments()"><i class="bi bi-arrow-clockwise me-1"></i> Refresh</button>
                </div>
                <div class="card-body">
                    <?php if($payments->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light"><tr><th>#</th><th>Date</th><th>ID</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th><th>Status</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php $counter = 1; while($row = $payments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo $row['formatted_date']; ?></td>
                                    <td><code>PAY-<?php echo str_pad($row['payment_id'], 6, '0', STR_PAD_LEFT); ?></code></td>
                                    <td class="text-end">NPR <?php echo number_format($row['total_amount'], 2); ?></td>
                                    <td class="text-end">NPR <?php echo number_format($row['amount_paid'], 2); ?></td>
                                    <td class="text-end">NPR <?php echo number_format($row['due_amount'], 2); ?></td>
                                    <td><span class="badge bg-<?php echo $row['payment_status'] === 'Paid' ? 'success' : ($row['payment_status'] === 'Partial' ? 'warning' : 'danger'); ?>"><?php echo $row['payment_status']; ?></span></td>
                                    <td><button class="btn btn-sm btn-outline-primary" onclick="viewReceipt(<?php echo $row['payment_id']; ?>)"><i class="bi bi-receipt me-1"></i> Receipt</button></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5"><i class="bi bi-cash-coin display-1 text-muted mb-3"></i><h4>No Payments</h4><p class="text-muted">No payment records found.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/payments.js"></script>