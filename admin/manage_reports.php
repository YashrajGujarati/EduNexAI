<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

/* Robust Counts */
$res_st = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$cnt_students = ($res_st && $row = mysqli_fetch_assoc($res_st)) ? (int)$row['total'] : 0;

$res_fc = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='faculty'");
$cnt_faculty = ($res_fc && $row = mysqli_fetch_assoc($res_fc)) ? (int)$row['total'] : 0;

$res_sb = mysqli_query($conn, "SELECT COUNT(*) AS total FROM subjects");
$cnt_subjects = ($res_sb && $row = mysqli_fetch_assoc($res_sb)) ? (int)$row['total'] : 0;

$res_mk = mysqli_query($conn, "SELECT COUNT(*) AS total FROM marks");
$cnt_marks = ($res_mk && $row = mysqli_fetch_assoc($res_mk)) ? (int)$row['total'] : 0;

/* Preview Recent Academic Records */
$preview_query = mysqli_query($conn, "
    SELECT
        s.student_id,
        u.name as student_name,
        s.class,
        s.roll_number,
        s.attendance,
        AVG(m.total_marks) as avg_score
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN marks m ON s.student_id = m.student_id
    GROUP BY s.student_id, u.name, s.class, s.roll_number, s.attendance
    ORDER BY s.student_id DESC
    LIMIT 5
");

$page_title = "Reports & Exports";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Exports | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-body p-4 card-header-flex">
                <div>
                    <h4 class="mb-1 fw-bold text-dark"><i class="fas fa-chart-column text-primary me-2"></i>Institutional Reports & Exports Hub</h4>
                    <p class="text-muted mb-0">Generate, inspect, and export institutional student academic records and performance metrics.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="export_excel.php" class="btn btn-success btn-sm px-3">
                        <i class="fas fa-file-excel me-1"></i> Export Excel
                    </a>
                    <a href="export_pdf.php" class="btn btn-danger btn-sm px-3">
                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Students</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($cnt_students); ?></h2>
                        </div>
                        <div class="dashboard-icon icon-blue">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Faculty Members</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($cnt_faculty); ?></h2>
                        </div>
                        <div class="dashboard-icon icon-green">
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Active Subjects</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($cnt_subjects); ?></h2>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-book-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Grade Entries</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($cnt_marks); ?></h2>
                        </div>
                        <div class="dashboard-icon icon-orange">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Specialized Report Modules Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 text-center p-3 card-hover-effect">
                    <div class="card-body">
                        <div class="dashboard-icon icon-blue mx-auto mb-3" style="width:60px; height:60px; font-size:26px;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <h5 class="fw-bold">Student Reports</h5>
                        <p class="text-muted small mb-3">View detailed individual student marksheets and attendance.</p>
                        <a href="student_report.php" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-eye me-1"></i> View Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 text-center p-3 card-hover-effect">
                    <div class="card-body">
                        <div class="dashboard-icon icon-purple mx-auto mb-3" style="width:60px; height:60px; font-size:26px;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h5 class="fw-bold">Subject Analysis</h5>
                        <p class="text-muted small mb-3">Subject-wise grade averages and pass percentage trends.</p>
                        <a href="subject_report.php" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-eye me-1"></i> View Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 text-center p-3 card-hover-effect">
                    <div class="card-body">
                        <div class="dashboard-icon icon-green mx-auto mb-3" style="width:60px; height:60px; font-size:26px;">
                            <i class="fas fa-trophy"></i>
                        </div>
                        <h5 class="fw-bold">Toppers List</h5>
                        <p class="text-muted small mb-3">Rank-wise high performers across all classes and subjects.</p>
                        <a href="topper_report.php" class="btn btn-outline-success btn-sm w-100"><i class="fas fa-eye me-1"></i> View Report</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="card h-100 shadow-sm border-0 text-center p-3 card-hover-effect">
                    <div class="card-body">
                        <div class="dashboard-icon icon-orange mx-auto mb-3" style="width:60px; height:60px; font-size:26px;">
                            <i class="fas fa-user-xmark"></i>
                        </div>
                        <h5 class="fw-bold">At-Risk Students</h5>
                        <p class="text-muted small mb-3">Students below attendance or score thresholds needing support.</p>
                        <a href="failed_students.php" class="btn btn-outline-danger btn-sm w-100"><i class="fas fa-eye me-1"></i> View Report</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Report Records Table -->
        <div class="card border-0 shadow-sm mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-table-list text-primary me-2"></i>Institutional Academic Records Overview</h5>
                    <small class="text-muted">Live report summary of registered student performance</small>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student ID</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Roll Number</th>
                                <th>Attendance Rate</th>
                                <th>Average Score</th>
                                <th class="text-end pe-4">Standing Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($preview_query && mysqli_num_rows($preview_query) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($preview_query)): 
                                $att = (float)($row['attendance'] ?? 0);
                                $avg = round((float)($row['avg_score'] ?? 0));
                                $badge_class = ($att >= 75 && $avg >= 40) ? 'badge-pass' : 'badge-fail';
                                $status_txt = ($att >= 75 && $avg >= 40) ? 'Passed' : 'Needs Review';
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $row['student_id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2" style="width:32px; height:32px; font-size:12px;">
                                            <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['class']); ?></span></td>
                                <td><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                <td>
                                    <span class="badge-status <?php echo ($att >= 75) ? 'badge-pass' : 'badge-fail'; ?>">
                                        <?php echo number_format($att, 1); ?>%
                                    </span>
                                </td>
                                <td class="fw-bold text-dark fs-6"><?php echo $avg; ?> / 100</td>
                                <td class="text-end pe-4">
                                    <span class="badge-status <?php echo $badge_class; ?>"><?php echo $status_txt; ?></span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-file-excel"></i>
                                        <h5>No Reports Data Logged</h5>
                                        <p>No student records available to generate report preview.</p>
                                    </div>
                                </td>
                            </tr>
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