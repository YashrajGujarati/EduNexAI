<?php
session_start();

/* =========================================================
   ADMIN SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: ../login.php");
    exit();
}

require_once("../config/db.php");

$message = "";
$message_type = "";

/* Auto-sync users with role 'student' into students table if missing */
mysqli_query($conn, "
    INSERT IGNORE INTO students (user_id, class, roll_number) 
    SELECT id, 'BTech-CS', CONCAT('EN', LPAD(id, 4, '0')) 
    FROM users WHERE role = 'student' 
    AND id NOT IN (SELECT user_id FROM students)
");

/* Auto-initialize missing fee records for any students */
mysqli_query($conn, "
    INSERT IGNORE INTO student_fees (student_id, total_fee, paid_fee, due_date, status) 
    SELECT student_id, 50000.00, 0.00, DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY), 'pending' 
    FROM students 
    WHERE student_id NOT IN (SELECT student_id FROM student_fees)
");

/* =========================================================
   ACTION 1: UPDATE FEE STRUCTURE FOR A STUDENT
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fee'])) {
    $fee_id = (int)$_POST['fee_id'];
    $new_total_fee = (float)$_POST['total_fee'];
    $new_due_date = trim($_POST['due_date']);

    if ($new_total_fee <= 0) {
        $message = "Please enter a valid total fee amount.";
        $message_type = "danger";
    } else {
        // Fetch current paid fee to re-evaluate status
        $f_check = mysqli_query($conn, "SELECT paid_fee FROM student_fees WHERE fee_id = $fee_id LIMIT 1");
        if ($f_check && mysqli_num_rows($f_check) > 0) {
            $curr_paid = (float)mysqli_fetch_assoc($f_check)['paid_fee'];
            $new_status = ($curr_paid >= $new_total_fee) ? 'paid' : (($curr_paid > 0) ? 'partial' : 'pending');

            $stmt = mysqli_prepare($conn, "UPDATE student_fees SET total_fee = ?, due_date = ?, status = ? WHERE fee_id = ?");
            mysqli_stmt_bind_param($stmt, "dssi", $new_total_fee, $new_due_date, $new_status, $fee_id);
            if (mysqli_stmt_execute($stmt)) {
                $message = "Student fee structure updated successfully!";
                $message_type = "success";
            } else {
                $message = "Failed to update fee: " . mysqli_error($conn);
                $message_type = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

/* =========================================================
   ACTION 2: RECORD MANUAL OFFLINE PAYMENT BY ADMIN
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_manual_payment'])) {
    $fee_id = (int)$_POST['fee_id'];
    $student_id = (int)$_POST['student_id'];
    $amount_paid = (float)$_POST['amount_paid'];
    $payment_method = trim(mysqli_real_escape_string($conn, $_POST['payment_method']));

    // Get current fee status
    $f_query = mysqli_query($conn, "SELECT total_fee, paid_fee FROM student_fees WHERE fee_id = $fee_id LIMIT 1");
    if ($f_query && mysqli_num_rows($f_query) > 0) {
        $f_data = mysqli_fetch_assoc($f_query);
        $total_fee = (float)$f_data['total_fee'];
        $paid_fee = (float)$f_data['paid_fee'];
        $remaining = max(0, $total_fee - $paid_fee);

        if ($amount_paid <= 0) {
            $message = "Payment amount must be greater than ₹0.";
            $message_type = "danger";
        } else if ($amount_paid > $remaining) {
            $message = "Payment amount cannot exceed student's remaining balance of ₹" . number_format($remaining, 2) . ".";
            $message_type = "danger";
        } else {
            $txn_id = "ADM" . date('Ymd') . rand(10000, 99999);
            $receipt_no = "RCPT-ADM" . date('Ym') . rand(1000, 9999);

            $stmt = mysqli_prepare($conn, "INSERT INTO fee_payments (fee_id, student_id, amount_paid, payment_method, transaction_id, receipt_no, payment_status) VALUES (?, ?, ?, ?, ?, ?, 'success')");
            mysqli_stmt_bind_param($stmt, "iidsss", $fee_id, $student_id, $amount_paid, $payment_method, $txn_id, $receipt_no);

            if (mysqli_stmt_execute($stmt)) {
                $new_paid = $paid_fee + $amount_paid;
                $new_status = ($new_paid >= $total_fee) ? 'paid' : (($new_paid > 0) ? 'partial' : 'pending');

                $upd = mysqli_prepare($conn, "UPDATE student_fees SET paid_fee = ?, status = ? WHERE fee_id = ?");
                mysqli_stmt_bind_param($upd, "dsi", $new_paid, $new_status, $fee_id);
                mysqli_stmt_execute($upd);
                mysqli_stmt_close($upd);

                $message = "Manual payment of ₹" . number_format($amount_paid, 2) . " recorded successfully! Receipt #" . $receipt_no;
                $message_type = "success";
            } else {
                $message = "Error saving manual payment: " . mysqli_error($conn);
                $message_type = "danger";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

/* =========================================================
   COMPUTE FINANCIAL OVERVIEW STATISTICS
========================================================= */
$stats_q = mysqli_query($conn, "
    SELECT 
        COUNT(*) AS total_students,
        SUM(total_fee) AS total_expected,
        SUM(paid_fee) AS total_collected,
        SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
        SUM(CASE WHEN status = 'partial' THEN 1 ELSE 0 END) AS partial_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count
    FROM student_fees
");
$stats = mysqli_fetch_assoc($stats_q);

$total_students_count = (int)($stats['total_students'] ?? 0);
$total_expected = (float)($stats['total_expected'] ?? 0);
$total_collected = (float)($stats['total_collected'] ?? 0);
$total_pending_amount = max(0, $total_expected - $total_collected);

$paid_students_count = (int)($stats['paid_count'] ?? 0);
$partial_students_count = (int)($stats['partial_count'] ?? 0);
$unpaid_students_count = (int)($stats['pending_count'] ?? 0);
// Total students who still have fees to pay (Partial + Pending)
$students_with_pending_fees = $partial_students_count + $unpaid_students_count;

/* =========================================================
   FETCH STUDENT FEES LIST WITH FILTERS & SEARCH
========================================================= */
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
$search = isset($_GET['search']) ? trim(mysqli_real_escape_string($conn, $_GET['search'])) : '';

$where_clauses = [];
if ($filter_status === 'paid') {
    $where_clauses[] = "sf.status = 'paid'";
} else if ($filter_status === 'partial') {
    $where_clauses[] = "sf.status = 'partial'";
} else if ($filter_status === 'pending') {
    $where_clauses[] = "sf.status IN ('pending', 'partial')";
}

if (!empty($search)) {
    $where_clauses[] = "(u.name LIKE '%$search%' OR st.roll_number LIKE '%$search%' OR st.class LIKE '%$search%')";
}

$where_sql = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$list_sql = "
    SELECT 
        sf.fee_id,
        sf.student_id,
        sf.total_fee,
        sf.paid_fee,
        (sf.total_fee - sf.paid_fee) AS remaining_fee,
        sf.due_date,
        sf.status,
        u.name AS student_name,
        u.email AS student_email,
        st.roll_number,
        st.class
    FROM student_fees sf
    JOIN students st ON sf.student_id = st.student_id
    JOIN users u ON st.user_id = u.id
    $where_sql
    ORDER BY remaining_fee DESC, u.name ASC
";
$fees_list_res = mysqli_query($conn, $list_sql);

$page_title = "Fee Status Management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Status Management | EduNexAI Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
    <style>
        .fee-widget-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--bs-primary);
            height: 100%;
        }
        .fee-widget-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .search-box-wrapper {
            min-width: 320px;
            max-width: 420px;
        }
        .table-nowrap th, .table-nowrap td {
            white-space: nowrap;
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

        <!-- PAGE HEADER -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="fas fa-wallet text-primary me-2"></i>Fee Management & Student Payment Status</h3>
                <p class="text-muted mb-0">Track total revenue collected, monitor pending fee balances, and view individual student payment status.</p>
            </div>
        </div>

        <!-- ANALYTICAL STATS CARDS -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm fee-widget-card border-primary p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Total Fees Expected</small>
                            <h4 class="fw-bold text-dark mb-0 mt-1">₹<?php echo number_format($total_expected, 2); ?></h4>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm fee-widget-card border-success p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Total Revenue Collected</small>
                            <h4 class="fw-bold text-success mb-0 mt-1">₹<?php echo number_format($total_collected, 2); ?></h4>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-circle-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm fee-widget-card border-danger p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Outstanding / Pending Fees</small>
                            <h4 class="fw-bold text-danger mb-0 mt-1">₹<?php echo number_format($total_pending_amount, 2); ?></h4>
                        </div>
                        <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-hand-holding-dollar"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm fee-widget-card border-warning p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted fw-bold d-block text-uppercase">Pending Fee Students</small>
                            <h4 class="fw-bold text-warning mb-0 mt-1"><?php echo $students_with_pending_fees; ?> Students</h4>
                            <small class="text-muted d-block mt-1"><?php echo $paid_students_count; ?> / <?php echo $total_students_count; ?> Fully Paid</small>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:48px; height:48px; font-size:20px;">
                            <i class="fas fa-users-rectangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER TABS & SEARCH BAR -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <!-- Status Filter Pills -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="manage_fees.php?status=all" class="btn btn-sm rounded-pill px-3 <?php echo ($filter_status === 'all') ? 'btn-primary' : 'btn-light border'; ?>">
                        <i class="fas fa-list me-1"></i> All Students (<?php echo $total_students_count; ?>)
                    </a>
                    <a href="manage_fees.php?status=paid" class="btn btn-sm rounded-pill px-3 <?php echo ($filter_status === 'paid') ? 'btn-success' : 'btn-light border'; ?>">
                        <i class="fas fa-check-double me-1"></i> Fully Paid (<?php echo $paid_students_count; ?>)
                    </a>
                    <a href="manage_fees.php?status=partial" class="btn btn-sm rounded-pill px-3 <?php echo ($filter_status === 'partial') ? 'btn-warning text-dark' : 'btn-light border'; ?>">
                        <i class="fas fa-hourglass-half me-1"></i> Partially Paid (<?php echo $partial_students_count; ?>)
                    </a>
                    <a href="manage_fees.php?status=pending" class="btn btn-sm rounded-pill px-3 <?php echo ($filter_status === 'pending') ? 'btn-danger' : 'btn-light border'; ?>">
                        <i class="fas fa-clock me-1"></i> Pending Fees Baki (<?php echo $students_with_pending_fees; ?>)
                    </a>
                </div>

                <!-- Search Input -->
                <form method="GET" action="manage_fees.php" class="d-flex gap-2 ms-auto search-box-wrapper">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($filter_status); ?>">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control rounded-start-pill border ps-3" placeholder="Search student name, roll no..." value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary rounded-end-pill px-3" type="submit">
                            <i class="fas fa-search me-1"></i>
                        </button>
                    </div>
                    <?php if (!empty($search)): ?>
                        <a href="manage_fees.php?status=<?php echo htmlspecialchars($filter_status); ?>" class="btn btn-sm btn-outline-secondary rounded-pill d-flex align-items-center">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- STUDENT FEE STATUS TABLE -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-user-graduate text-info me-2"></i>Student Fee Payment Records</h5>
                <span class="badge bg-secondary rounded-pill px-3 py-2">
                    Showing: <?php echo mysqli_num_rows($fees_list_res); ?> Students
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student Info</th>
                                <th>Roll No / Class</th>
                                <th>Total Fee</th>
                                <th>Paid Amount</th>
                                <th>Pending Balance</th>
                                <th>Payment Progress</th>
                                <th>Status</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($fees_list_res) == 0): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fs-2 d-block mb-2"></i> No student fee records match your search query.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($fees_list_res)): ?>
                                    <?php 
                                        $tot = (float)$row['total_fee'];
                                        $pd = (float)$row['paid_fee'];
                                        $rem = max(0, $tot - $pd);
                                        $pct = ($tot > 0) ? min(100, round(($pd / $tot) * 100)) : 0;
                                        $st = $row['status'];

                                        // Fetch recent payments for this student
                                        $stu_id = (int)$row['student_id'];
                                        $p_history_q = mysqli_query($conn, "SELECT * FROM fee_payments WHERE student_id = $stu_id ORDER BY payment_date DESC");
                                        $p_logs = [];
                                        while ($pl = mysqli_fetch_assoc($p_history_q)) {
                                            $p_logs[] = $pl;
                                        }
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-2" style="width:36px; height:36px; font-size:14px;">
                                                    <i class="fas fa-user-graduate"></i>
                                                </div>
                                                <div>
                                                    <div><?php echo htmlspecialchars($row['student_name']); ?></div>
                                                    <small class="text-muted fw-normal"><?php echo htmlspecialchars($row['student_email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['roll_number'] ?? 'N/A'); ?></span>
                                            <small class="d-block text-muted"><?php echo htmlspecialchars($row['class'] ?? ''); ?></small>
                                        </td>
                                        <td class="fw-bold">₹<?php echo number_format($tot, 2); ?></td>
                                        <td class="fw-bold text-success">₹<?php echo number_format($pd, 2); ?></td>
                                        <td class="fw-bold <?php echo ($rem > 0) ? 'text-danger' : 'text-muted'; ?>">
                                            ₹<?php echo number_format($rem, 2); ?>
                                        </td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress w-100" style="height: 8px;">
                                                    <div class="progress-bar bg-<?php echo ($pct == 100) ? 'success' : (($pct > 0) ? 'warning' : 'danger'); ?>" style="width: <?php echo $pct; ?>%;"></div>
                                                </div>
                                                <small class="fw-bold text-muted"><?php echo $pct; ?>%</small>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($st === 'paid'): ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                    <i class="fas fa-check-double me-1"></i>Fully Paid
                                                </span>
                                            <?php elseif ($st === 'partial'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1">
                                                    <i class="fas fa-hourglass-half me-1"></i>Partial (Baki)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    <i class="fas fa-clock me-1"></i>Pending (Baki)
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="pe-4 text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border rounded-pill px-3 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    Manage
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                                    <?php if ($rem > 0): ?>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#payManualModal<?php echo $row['fee_id']; ?>">
                                                                <i class="fas fa-cash-register me-2"></i> Record Manual Payment
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>
                                                    <li>
                                                        <a class="dropdown-item text-primary" href="#" data-bs-toggle="modal" data-bs-target="#editFeeModal<?php echo $row['fee_id']; ?>">
                                                            <i class="fas fa-pen-to-square me-2"></i> Edit Total Fee Amount
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-info" href="#" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo $row['fee_id']; ?>">
                                                            <i class="fas fa-receipt me-2"></i> View Payment Logs (<?php echo count($p_logs); ?>)
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <!-- MODAL 1: EDIT FEE STRUCTURE -->
                                            <div class="modal fade text-start" id="editFeeModal<?php echo $row['fee_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <form method="POST" action="manage_fees.php">
                                                            <input type="hidden" name="fee_id" value="<?php echo $row['fee_id']; ?>">
                                                            <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                                                                <h5 class="modal-title fw-bold text-dark">
                                                                    <i class="fas fa-pen-to-square text-primary me-2"></i>Edit Fee Structure: <?php echo htmlspecialchars($row['student_name']); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Total Assigned Fee (₹)</label>
                                                                    <input type="number" name="total_fee" class="form-control rounded-3" step="0.01" value="<?php echo $row['total_fee']; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Fee Due Date</label>
                                                                    <input type="date" name="due_date" class="form-control rounded-3" value="<?php echo $row['due_date']; ?>" required>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                                                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="update_fee" class="btn btn-primary rounded-pill px-4">
                                                                    <i class="fas fa-save me-1"></i> Save Changes
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- MODAL 2: RECORD MANUAL OFFLINE PAYMENT -->
                                            <div class="modal fade text-start" id="payManualModal<?php echo $row['fee_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <form method="POST" action="manage_fees.php">
                                                            <input type="hidden" name="fee_id" value="<?php echo $row['fee_id']; ?>">
                                                            <input type="hidden" name="student_id" value="<?php echo $row['student_id']; ?>">
                                                            <div class="modal-header border-0 bg-success text-white rounded-top-4 py-3">
                                                                <h5 class="modal-title fw-bold">
                                                                    <i class="fas fa-cash-register me-2"></i>Record Manual Payment: <?php echo htmlspecialchars($row['student_name']); ?>
                                                                </h5>
                                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body p-4">
                                                                <div class="bg-light p-3 rounded-3 mb-3 border">
                                                                    <div class="d-flex justify-content-between small">
                                                                        <span>Total Fee: <strong>₹<?php echo number_format($tot, 2); ?></strong></span>
                                                                        <span>Already Paid: <strong class="text-success">₹<?php echo number_format($pd, 2); ?></strong></span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between small mt-1">
                                                                        <span>Remaining Balance:</span>
                                                                        <strong class="text-danger">₹<?php echo number_format($rem, 2); ?></strong>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Payment Amount (₹)</label>
                                                                    <input type="number" name="amount_paid" class="form-control rounded-3" step="0.01" max="<?php echo $rem; ?>" value="<?php echo $rem; ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label fw-bold">Payment Method</label>
                                                                    <select name="payment_method" class="form-select rounded-3">
                                                                        <option value="Cash">Cash Payment</option>
                                                                        <option value="Bank Transfer">Bank Transfer / NEFT</option>
                                                                        <option value="Cheque">Cheque</option>
                                                                        <option value="UPI / Online">UPI / Online</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                                                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="record_manual_payment" class="btn btn-success rounded-pill px-4">
                                                                    <i class="fas fa-check-circle me-1"></i> Submit Payment
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- MODAL 3: VIEW TRANSACTION LOGS -->
                                            <div class="modal fade text-start" id="historyModal<?php echo $row['fee_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                                    <div class="modal-content rounded-4 border-0 shadow">
                                                        <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                                                            <h5 class="modal-title fw-bold text-dark">
                                                                <i class="fas fa-receipt text-info me-2"></i>Payment History: <?php echo htmlspecialchars($row['student_name']); ?>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body p-4">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered align-middle mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Receipt #</th>
                                                                            <th>Txn ID</th>
                                                                            <th>Date</th>
                                                                            <th>Mode</th>
                                                                            <th>Amount</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php if (empty($p_logs)): ?>
                                                                            <tr>
                                                                                <td colspan="5" class="text-center text-muted py-3">No payments recorded for this student yet.</td>
                                                                            </tr>
                                                                        <?php else: ?>
                                                                            <?php foreach ($p_logs as $log): ?>
                                                                                <tr>
                                                                                    <td class="fw-bold text-primary"><?php echo $log['receipt_no']; ?></td>
                                                                                    <td class="font-monospace small"><?php echo $log['transaction_id']; ?></td>
                                                                                    <td><?php echo date('M d, Y h:i A', strtotime($log['payment_date'])); ?></td>
                                                                                    <td><span class="badge bg-light text-dark border"><?php echo $log['payment_method']; ?></span></td>
                                                                                    <td class="fw-bold text-success">₹<?php echo number_format($log['amount_paid'], 2); ?></td>
                                                                                </tr>
                                                                            <?php endforeach; ?>
                                                                        <?php endif; ?>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
