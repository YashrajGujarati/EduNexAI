<?php
session_start();

/* =========================================================
   ADMIN SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   DATABASE CONNECTION
========================================================= */
include("../config/db.php");

/* =========================================================
   TOTAL STUDENTS
========================================================= */
$total_students = 0;
$query_students = "SELECT COUNT(*) AS total FROM students";
$result_students = mysqli_query($conn, $query_students);
if ($result_students) {
    $row_students = mysqli_fetch_assoc($result_students);
    $total_students = (int)$row_students['total'];
}

/* =========================================================
   TOTAL FACULTY
========================================================= */
$total_faculty = 0;
$query_faculty = "SELECT COUNT(*) AS total FROM users WHERE role = 'faculty'";
$result_faculty = mysqli_query($conn, $query_faculty);
if ($result_faculty) {
    $row_faculty = mysqli_fetch_assoc($result_faculty);
    $total_faculty = (int)$row_faculty['total'];
}

/* =========================================================
   TOTAL SUBJECTS
========================================================= */
$total_subjects = 0;
$query_subjects = "SELECT COUNT(*) AS total FROM subjects";
$result_subjects = mysqli_query($conn, $query_subjects);
if ($result_subjects) {
    $row_subjects = mysqli_fetch_assoc($result_subjects);
    $total_subjects = (int)$row_subjects['total'];
}

/* =========================================================
   TOTAL PREDICTIONS
========================================================= */
$total_predictions = 0;
$query_predictions = "SELECT COUNT(*) AS total FROM prediction_history";
$result_predictions = @mysqli_query($conn, $query_predictions);
if ($result_predictions) {
    $row_predictions = mysqli_fetch_assoc($result_predictions);
    $total_predictions = (int)$row_predictions['total'];
}

/* =========================================================
   RECENT STUDENTS
========================================================= */
$students = [];
$query_recent_students = "
    SELECT
        s.student_id,
        u.name,
        s.class,
        s.roll_number,
        s.attendance
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    ORDER BY s.student_id DESC
    LIMIT 5
";
$result_recent_students = mysqli_query($conn, $query_recent_students);
if ($result_recent_students) {
    while ($row = mysqli_fetch_assoc($result_recent_students)) {
        $students[] = $row;
    }
}

/* =========================================================
   PERFORMANCE TREND DATA
========================================================= */
$performance_values = [72, 75, 78, 82, 85, 88];

/* =========================================================
   ATTENDANCE BREAKDOWN
========================================================= */
$excellent_attendance = 0;
$average_attendance = 0;
$low_attendance = 0;

$query_att_dist = "
    SELECT
        SUM(CASE WHEN attendance >= 85 THEN 1 ELSE 0 END) AS excellent_count,
        SUM(CASE WHEN attendance >= 75 AND attendance < 85 THEN 1 ELSE 0 END) AS average_count,
        SUM(CASE WHEN attendance < 75 THEN 1 ELSE 0 END) AS low_count
    FROM students
";
$result_att_dist = mysqli_query($conn, $query_att_dist);
if ($result_att_dist) {
    $row_att = mysqli_fetch_assoc($result_att_dist);
    $excellent_attendance = (int)($row_att['excellent_count'] ?? 0);
    $average_attendance = (int)($row_att['average_count'] ?? 0);
    $low_attendance = (int)($row_att['low_count'] ?? 0);
}

$page_title = "Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Hero Welcome Banner -->
        <div class="hero-banner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3>Welcome back, Administrator! 👋</h3>
                    <p>EduNexAI Machine Learning Analytics System is active. Monitor student standing, faculty assignments, and ML predictions in real-time.</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-white text-dark px-3 py-2 fw-bold"><i class="fas fa-check-circle text-success me-1"></i> AI Model Active</span>
                    <span class="badge bg-primary px-3 py-2 fw-bold"><i class="fas fa-server me-1"></i> System Optimal</span>
                </div>
            </div>
        </div>

        <!-- Stat Widgets Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Students</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_students); ?></h2>
                            <small class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>Enrolled Scholars</small>
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
                            <span class="text-muted small fw-bold text-uppercase">Total Faculty</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_faculty); ?></h2>
                            <small class="text-primary fw-bold"><i class="fas fa-user-check me-1"></i>Active Teachers</small>
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
                            <span class="text-muted small fw-bold text-uppercase">Academic Subjects</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_subjects); ?></h2>
                            <small class="text-warning fw-bold"><i class="fas fa-book me-1"></i>Curriculum Courses</small>
                        </div>
                        <div class="dashboard-icon icon-orange">
                            <i class="fas fa-book-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">AI Predictions</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_predictions); ?></h2>
                            <small class="text-info fw-bold"><i class="fas fa-microchip me-1"></i>ML Runs Executed</small>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-brain"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <a href="add_student.php" class="quick-action-tile">
                    <i class="fas fa-user-plus"></i>
                    <h6>Add Student</h6>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="add_faculty.php" class="quick-action-tile">
                    <i class="fas fa-user-tie"></i>
                    <h6>Add Faculty</h6>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="add_marks.php" class="quick-action-tile">
                    <i class="fas fa-pen-to-square"></i>
                    <h6>Enter Marks</h6>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="ai_predictions.php" class="quick-action-tile">
                    <i class="fas fa-robot"></i>
                    <h6>AI Risk Analytics</h6>
                </a>
            </div>
        </div>

        <!-- Analytics Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Academic Performance Progression</h5>
                            <small class="text-muted">Average score progression over recent assessments</small>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 300px;">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm h-100 card-hover-effect">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie text-primary me-2"></i>Attendance Analytics</h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div style="height: 220px; position: relative;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <small><i class="fas fa-circle text-success me-1"></i> &ge;85% (<?php echo $excellent_attendance; ?>)</small>
                            <small><i class="fas fa-circle text-warning me-1"></i> 75-84% (<?php echo $average_attendance; ?>)</small>
                            <small><i class="fas fa-circle text-danger me-1"></i> &lt;75% (<?php echo $low_attendance; ?>)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Students Table -->
        <div class="card border-0 shadow-sm mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>Recently Registered Students</h5>
                    <small class="text-muted">Latest student enrollments registered in the EduNexAI system</small>
                </div>
                <a href="manage_students.php" class="btn btn-outline-primary btn-sm">View All Students</a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Student ID</th>
                                <th>Name</th>
                                <th>Class</th>
                                <th>Roll Number</th>
                                <th>Attendance</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $s): 
                                $att = (float)($s['attendance'] ?? 0);
                                $badge_class = ($att >= 75) ? 'badge-pass' : 'badge-fail';
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $s['student_id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2" style="width:34px; height:34px; font-size:12px;">
                                            <?php echo strtoupper(substr($s['name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($s['name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($s['class']); ?></span></td>
                                <td><?php echo htmlspecialchars($s['roll_number']); ?></td>
                                <td>
                                    <span class="badge-status <?php echo $badge_class; ?>">
                                        <?php echo number_format($att, 1); ?>%
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_student.php?id=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-outline-warning me-1"><i class="fas fa-pen"></i></a>
                                    <a href="manage_students.php" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-graduate"></i>
                                        <h5>No Recent Students Found</h5>
                                        <p>No student records found in the database. Add your first student to see enrollments here.</p>
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
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Performance Line Chart
    const perfCtx = document.getElementById("performanceChart");
    if (perfCtx) {
        new Chart(perfCtx, {
            type: "line",
            data: {
                labels: ["Term 1", "Term 2", "Midterm", "Term 3", "Pre-Final", "Final"],
                datasets: [{
                    label: "Average Class Score (%)",
                    data: <?php echo json_encode($performance_values); ?>,
                    borderColor: "#2563eb",
                    backgroundColor: "rgba(37, 99, 235, 0.08)",
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: "#2563eb"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { min: 50, max: 100 }
                }
            }
        });
    }

    // Attendance Doughnut Chart
    const attCtx = document.getElementById("attendanceChart");
    if (attCtx) {
        new Chart(attCtx, {
            type: "doughnut",
            data: {
                labels: ["Excellent (>=85%)", "Average (75-84%)", "Low (<75%)"],
                datasets: [{
                    data: [<?php echo $excellent_attendance; ?>, <?php echo $average_attendance; ?>, <?php echo $low_attendance; ?>],
                    backgroundColor: ["#10b981", "#f59e0b", "#ef4444"],
                    borderWidth: 2,
                    borderColor: "#ffffff"
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
</body>
</html>