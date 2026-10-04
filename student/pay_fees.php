<?php
session_start();

/* =========================================================
   STUDENT SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$user_id = (int)$_SESSION['user_id'];

/* Get Student Info */
$student_q = db_query($conn, "
    SELECT s.student_id, u.name, u.email, s.class, s.roll_number 
    FROM users u 
    LEFT JOIN students s ON u.id = s.user_id 
    WHERE u.id = $user_id LIMIT 1
");

if (!$student_q || db_num_rows($student_q) == 0) {
    die("Student profile not found.");
}
$student = db_fetch_assoc($student_q);

// Ensure student record exists in students table
if (empty($student['student_id'])) {
    $ins_stu = db_query($conn, "INSERT INTO students (user_id, class, roll_number) VALUES ($user_id, 'FY-BTech', 'CS" . rand(100, 999) . "')");
    $student_id = db_insert_id($conn);
    $student['student_id'] = $student_id;
    $student['class'] = 'FY-BTech';
} else {
    $student_id = (int)$student['student_id'];
}

/* Ensure Fee record exists for student */
$fee_q = db_query($conn, "SELECT * FROM student_fees WHERE student_id = $student_id LIMIT 1");
if (!$fee_q || db_num_rows($fee_q) == 0) {
    $default_due = date('Y-m-d', strtotime('+30 days'));
    db_query($conn, "INSERT INTO student_fees (student_id, total_fee, paid_fee, due_date, status) VALUES ($student_id, 50000.00, 0.00, '$default_due', 'pending')");
    $fee_q = db_query($conn, "SELECT * FROM student_fees WHERE student_id = $student_id LIMIT 1");
}
$fee_record = db_fetch_assoc($fee_q);
$fee_id = (int)$fee_record['fee_id'];

$total_fee = (float)$fee_record['total_fee'];
$paid_fee = (float)$fee_record['paid_fee'];
$remaining_fee = max(0, $total_fee - $paid_fee);
$fee_status = $fee_record['status'];
$due_date = $fee_record['due_date'];

$message = "";
$message_type = "";
$new_receipt_id = 0;

/* =========================================================
   HANDLE ONLINE FEE PAYMENT
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $amount_paid = (float)$_POST['amount_paid'];
    $payment_method = trim(db_real_escape_string($conn, $_POST['payment_method']));

    if ($amount_paid <= 0) {
        $message = "Please enter a valid payment amount greater than ₹0.";
        $message_type = "danger";
    } else if ($amount_paid > $remaining_fee) {
        $message = "Payment amount cannot exceed the remaining balance of ₹" . number_format($remaining_fee, 2) . ".";
        $message_type = "danger";
    } else {
        $txn_id = "TXN" . date('Ymd') . rand(10000, 99999);
        $receipt_no = "RCPT-" . date('Ym') . rand(1000, 9999);

        // Insert transaction record
        $stmt = db_prepare($conn, "INSERT INTO fee_payments (fee_id, student_id, amount_paid, payment_method, transaction_id, receipt_no, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'success')");
        db_stmt_bind_param($stmt, "iidsss", $fee_id, $student_id, $amount_paid, $payment_method, $txn_id, $receipt_no);
        
        if (db_stmt_execute($stmt)) {
            $new_receipt_id = db_insert_id($conn);

            // Update student_fees table
            $new_paid = $paid_fee + $amount_paid;
            $new_status = ($new_paid >= $total_fee) ? 'paid' : (($new_paid > 0) ? 'partial' : 'pending');

            $upd = db_prepare($conn, "UPDATE student_fees SET paid_fee = ?, status = ? WHERE fee_id = ?");
            db_stmt_bind_param($upd, "dsi", $new_paid, $new_status, $fee_id);
            db_stmt_execute($upd);
            db_stmt_close($upd);

            // Refresh values
            $paid_fee = $new_paid;
            $remaining_fee = max(0, $total_fee - $paid_fee);
            $fee_status = $new_status;

            $message = "Payment of ₹" . number_format($amount_paid, 2) . " processed successfully! Receipt #" . $receipt_no . " generated.";
            $message_type = "success";
        } else {
            $message = "Payment failed to record: " . db_error($conn);
            $message_type = "danger";
        }
        db_stmt_close($stmt);
    }
}

/* =========================================================
   FETCH TRANSACTION HISTORY
========================================================= */
$history_q = db_query($conn, "SELECT * FROM fee_payments WHERE student_id = $student_id ORDER BY payment_date DESC");
$payment_history = [];
while ($h = db_fetch_assoc($history_q)) {
    $payment_history[] = $h;
}

$paid_percentage = ($total_fee > 0) ? min(100, round(($paid_fee / $total_fee) * 100)) : 0;
$page_title = "Online Fee Payment";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Fees Online | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
    <style>
        .fee-card {
            transition: all 0.3s ease;
            border-radius: 1rem;
        }
        .fee-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
        }
        .payment-method-card {
            border: 2px solid #e9ecef;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .payment-method-card:hover, .payment-method-card.active {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .qr-placeholder {
            background: #fff;
            border: 2px dashed #0d6efd;
            border-radius: 12px;
            padding: 15px;
            display: inline-block;
        }
        @media print {
            body * { visibility: hidden; }
            #printableReceiptModal, #printableReceiptModal * { visibility: visible; }
            #printableReceiptModal { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content p-4">
        <?php include("header.php"); ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- HEADER & ACTIONS -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="fas fa-credit-card text-primary me-2"></i>Online Student Fee Payment</h3>
                <p class="text-muted mb-0">View your current fee statement, make instant online payments, and download official payment receipts.</p>
            </div>
            <div>
                <?php if ($remaining_fee > 0): ?>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm py-2" data-bs-toggle="modal" data-bs-target="#payModal">
                        <i class="fas fa-wallet me-2"></i>Pay Fees Online Now
                    </button>
                <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-4 py-2 fs-6">
                        <i class="fas fa-check-double me-2"></i>Full Fees Paid
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- STATS WIDGETS ROW -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm fee-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Total Academic Fee</small>
                            <h4 class="fw-bold text-dark mb-0 mt-1">₹<?php echo number_format($total_fee, 2); ?></h4>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm fee-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Amount Paid</small>
                            <h4 class="fw-bold text-success mb-0 mt-1">₹<?php echo number_format($paid_fee, 2); ?></h4>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm fee-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Remaining Balance</small>
                            <h4 class="fw-bold <?php echo ($remaining_fee > 0) ? 'text-danger' : 'text-muted'; ?> mb-0 mt-1">
                                ₹<?php echo number_format($remaining_fee, 2); ?>
                            </h4>
                        </div>
                        <div class="rounded-circle bg-<?php echo ($remaining_fee > 0) ? 'danger' : 'secondary'; ?>-subtle text-<?php echo ($remaining_fee > 0) ? 'danger' : 'secondary'; ?> d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-0 shadow-sm fee-card p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Status & Due Date</small>
                            <div class="mt-1">
                                <?php if ($fee_status === 'paid'): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-1">Fully Paid</span>
                                <?php elseif ($fee_status === 'partial'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Partially Paid</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1">Pending</span>
                                <?php endif; ?>
                            </div>
                            <small class="text-muted d-block mt-1">Due: <?php echo date('M d, Y', strtotime($due_date)); ?></small>
                        </div>
                        <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-calendar-day"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FEE PROGRESS BAR CARD -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-line text-primary me-2"></i>Fee Payment Progress</h6>
                <span class="fw-bold text-primary"><?php echo $paid_percentage; ?>% Completed</span>
            </div>
            <div class="progress rounded-pill" style="height: 14px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: <?php echo $paid_percentage; ?>%;" aria-valuenow="<?php echo $paid_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <!-- PAYMENT TRANSACTION HISTORY TABLE -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history text-info me-2"></i>Payment History & Digital Receipts</h5>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                    Total Transactions: <?php echo count($payment_history); ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Receipt No</th>
                                <th>Transaction ID</th>
                                <th>Payment Date</th>
                                <th>Payment Method</th>
                                <th>Amount Paid</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payment_history)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-info-circle me-1"></i> No fee payments recorded yet. Click "Pay Fees Online Now" to make your first payment.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payment_history as $ph): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">
                                            <i class="fas fa-receipt me-1 text-secondary"></i><?php echo htmlspecialchars($ph['receipt_no']); ?>
                                        </td>
                                        <td class="font-monospace small"><?php echo htmlspecialchars($ph['transaction_id']); ?></td>
                                        <td><?php echo date('M d, Y - h:i A', strtotime($ph['payment_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">
                                                <i class="fas fa-credit-card me-1 text-info"></i><?php echo htmlspecialchars($ph['payment_method']); ?>
                                            </span>
                                        </td>
                                        <td class="fw-bold text-success">₹<?php echo number_format($ph['amount_paid'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-check me-1"></i>Successful
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#receiptModal<?php echo $ph['payment_id']; ?>">
                                                <i class="fas fa-print me-1"></i> View Receipt
                                            </button>

                                            <!-- DIGITAL RECEIPT MODAL -->
                                            <div class="modal fade text-start" id="receiptModal<?php echo $ph['payment_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-body p-4" id="receiptContent<?php echo $ph['payment_id']; ?>">
                                                            <!-- RECEIPT HEADER -->
                                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                                <div>
                                                                    <h4 class="fw-bold text-primary mb-1"><i class="fas fa-graduation-cap me-2"></i>EduNexAI Academy</h4>
                                                                    <small class="text-muted d-block">Official Fee Payment Receipt & Transaction Statement</small>
                                                                </div>
                                                                <div class="text-end">
                                                                    <span class="badge bg-success rounded-pill px-3 py-2 fs-6">PAID RECEIPT</span>
                                                                    <small class="d-block text-muted mt-1">Receipt #: <strong><?php echo $ph['receipt_no']; ?></strong></small>
                                                                </div>
                                                            </div>

                                                            <!-- STUDENT & TRANSACTION DETAILS -->
                                                            <div class="row g-3 mb-4">
                                                                <div class="col-6">
                                                                    <div class="bg-light p-3 rounded-3 border">
                                                                        <small class="text-muted d-block uppercase fw-bold">Student Information</small>
                                                                        <strong class="d-block text-dark fs-6 mt-1"><?php echo htmlspecialchars($student['name']); ?></strong>
                                                                        <small class="text-muted d-block">Roll No: <?php echo htmlspecialchars($student['roll_number']); ?></small>
                                                                        <small class="text-muted d-block">Class: <?php echo htmlspecialchars($student['class']); ?></small>
                                                                        <small class="text-muted d-block">Email: <?php echo htmlspecialchars($student['email']); ?></small>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="bg-light p-3 rounded-3 border">
                                                                        <small class="text-muted d-block uppercase fw-bold">Payment Summary</small>
                                                                        <small class="d-block text-muted mt-1">Date: <strong><?php echo date('F d, Y h:i A', strtotime($ph['payment_date'])); ?></strong></small>
                                                                        <small class="d-block text-muted">Transaction ID: <strong><?php echo $ph['transaction_id']; ?></strong></small>
                                                                        <small class="d-block text-muted">Payment Mode: <strong><?php echo $ph['payment_method']; ?></strong></small>
                                                                        <small class="d-block text-muted">Status: <strong class="text-success">SUCCESSFUL</strong></small>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- TABLE BREAKDOWN -->
                                                            <table class="table table-bordered mb-4">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Description</th>
                                                                        <th class="text-end">Amount (INR)</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <td>Academic Tuition & Course Fee Payment</td>
                                                                        <td class="text-end fw-bold">₹<?php echo number_format($ph['amount_paid'], 2); ?></td>
                                                                    </tr>
                                                                    <tr class="table-light">
                                                                        <td class="fw-bold text-end">Total Paid Amount:</td>
                                                                        <td class="text-end fw-bold text-success fs-5">₹<?php echo number_format($ph['amount_paid'], 2); ?></td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

                                                            <div class="d-flex justify-content-between align-items-end pt-2 border-top">
                                                                <small class="text-muted">This is a computer-generated official receipt. No physical signature required.</small>
                                                                <div class="text-end">
                                                                    <small class="d-block fw-bold text-primary">EduNexAI Finance Department</small>
                                                                    <small class="text-muted">Verified & Stamp Approved</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                                            <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                            <button type="button" class="btn btn-primary rounded-pill px-4" onclick="printReceipt('receiptContent<?php echo $ph['payment_id']; ?>')">
                                                                <i class="fas fa-print me-1"></i> Print / Download PDF
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ONLINE PAYMENT CHECKOUT MODAL -->
        <div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow rounded-4">
                    <form method="POST" action="pay_fees.php" id="paymentForm">
                        <div class="modal-header border-0 bg-primary text-white rounded-top-4 py-3">
                            <h5 class="modal-title fw-bold"><i class="fas fa-lock me-2"></i>EduNexAI Online Payment Gateway</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="bg-primary-subtle p-3 rounded-3 mb-4 border border-primary-subtle d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-primary d-block fw-bold">Paying For Student</small>
                                    <h6 class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($student['name']); ?> (Roll: <?php echo htmlspecialchars($student['roll_number']); ?>)</h6>
                                </div>
                                <div class="text-end">
                                    <small class="text-primary d-block fw-bold">Remaining Balance</small>
                                    <h5 class="fw-bold text-danger mb-0">₹<?php echo number_format($remaining_fee, 2); ?></h5>
                                </div>
                            </div>

                            <!-- ENTER AMOUNT -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Enter Payment Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light fw-bold">₹</span>
                                    <input type="number" name="amount_paid" id="payAmountInput" class="form-control rounded-end-3 fw-bold fs-4 text-primary" step="0.01" min="1" max="<?php echo $remaining_fee; ?>" value="<?php echo $remaining_fee; ?>" required>
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="setAmount(<?php echo $remaining_fee; ?>)">Full Amount (₹<?php echo number_format($remaining_fee, 2); ?>)</button>
                                    <?php if ($remaining_fee > 5000): ?>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="setAmount(<?php echo round($remaining_fee / 2); ?>)">Half Amount (₹<?php echo number_format(round($remaining_fee / 2), 2); ?>)</button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- SELECT PAYMENT METHOD -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Payment Gateway Method <span class="text-danger">*</span></label>
                                <input type="hidden" name="payment_method" id="selectedPaymentMethod" value="UPI / GPay">
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="payment-method-card p-3 text-center active" id="methodUPI" onclick="selectMethod('UPI / GPay', 'methodUPI')">
                                            <i class="fab fa-google-pay text-primary fs-2 d-block mb-1"></i>
                                            <small class="fw-bold d-block text-dark">UPI / GPay / PhonePe</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="payment-method-card p-3 text-center" id="methodCard" onclick="selectMethod('Credit / Debit Card', 'methodCard')">
                                            <i class="fas fa-credit-card text-success fs-2 d-block mb-1"></i>
                                            <small class="fw-bold d-block text-dark">Credit / Debit Card</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="payment-method-card p-3 text-center" id="methodNet" onclick="selectMethod('Net Banking', 'methodNet')">
                                            <i class="fas fa-building-columns text-warning fs-2 d-block mb-1"></i>
                                            <small class="fw-bold d-block text-dark">Net Banking</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DYNAMIC METHOD DETAILS -->
                            <div id="upiSection" class="p-3 bg-light rounded-3 border text-center mb-3">
                                <small class="text-muted d-block fw-bold mb-2">Scan QR Code or Use UPI ID to Pay</small>
                                <div class="qr-placeholder mb-2">
                                    <i class="fas fa-qrcode fs-1 text-primary"></i>
                                    <small class="d-block fw-bold text-dark mt-1">edunexai@pay</small>
                                </div>
                                <small class="d-block text-muted">Supports Google Pay, PhonePe, Paytm, BHIM & Any UPI App</small>
                            </div>

                            <div id="cardSection" class="p-3 bg-light rounded-3 border mb-3 d-none">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Card Number</label>
                                        <input type="text" class="form-control" placeholder="4532 •••• •••• 8921" maxlength="19">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">Expiry Date</label>
                                        <input type="text" class="form-control" placeholder="MM/YY" maxlength="5">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold">CVV Code</label>
                                        <input type="password" class="form-control" placeholder="•••" maxlength="3">
                                    </div>
                                </div>
                            </div>

                            <div id="netSection" class="p-3 bg-light rounded-3 border mb-3 d-none">
                                <label class="form-label small fw-bold">Choose Bank</label>
                                <select class="form-select">
                                    <option>HDFC Bank</option>
                                    <option>State Bank of India (SBI)</option>
                                    <option>ICICI Bank</option>
                                    <option>Axis Bank</option>
                                    <option>Bank of Baroda</option>
                                    <option>Kotak Mahindra Bank</option>
                                </select>
                            </div>

                            <div class="d-flex align-items-center text-muted small">
                                <i class="fas fa-shield-halved text-success fs-5 me-2"></i>
                                <span>256-Bit Bank Grade SSL Encrypted Payment Processing.</span>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                            <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="process_payment" class="btn btn-success rounded-pill px-4 py-2 fs-6 fw-bold">
                                <i class="fas fa-check-circle me-1"></i> Confirm & Pay Online
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function setAmount(val) {
        document.getElementById('payAmountInput').value = val;
    }

    function selectMethod(methodName, elementId) {
        document.getElementById('selectedPaymentMethod').value = methodName;
        document.querySelectorAll('.payment-method-card').forEach(el => el.classList.remove('active'));
        document.getElementById(elementId).classList.add('active');

        document.getElementById('upiSection').classList.add('d-none');
        document.getElementById('cardSection').classList.add('d-none');
        document.getElementById('netSection').classList.add('d-none');

        if (elementId === 'methodUPI') {
            document.getElementById('upiSection').classList.remove('d-none');
        } else if (elementId === 'methodCard') {
            document.getElementById('cardSection').classList.remove('d-none');
        } else if (elementId === 'methodNet') {
            document.getElementById('netSection').classList.remove('d-none');
        }
    }

    function printReceipt(divId) {
        var printContents = document.getElementById(divId).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        window.location.reload();
    }
</script>
</body>
</html>
