<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$user_id = (int)$_SESSION['user_id'];

/* Fetch Student Info */
$student_sql = "
    SELECT
        u.name,
        u.email,
        s.student_id,
        s.class,
        s.roll_number,
        s.attendance
    FROM users u
    INNER JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
    LIMIT 1
";
$stmt = mysqli_prepare($conn, $student_sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$student_result = mysqli_stmt_get_result($stmt);

if (!$student_result || mysqli_num_rows($student_result) == 0) {
    die("Student information not found.");
}
$student = mysqli_fetch_assoc($student_result);
mysqli_stmt_close($stmt);

$student_id = (int)$student['student_id'];
$attendance = (float)($student['attendance'] ?? 0);

/* Fetch Marks */
$marks_sql = "
    SELECT
        sub.subject_name,
        m.internal_marks,
        m.external_marks,
        (COALESCE(m.internal_marks,0) + COALESCE(m.external_marks,0)) AS total_marks
    FROM marks m
    INNER JOIN subjects sub ON m.subject_id = sub.subject_id
    WHERE m.student_id = ?
";
$stmt = mysqli_prepare($conn, $marks_sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$marks_result = mysqli_stmt_get_result($stmt);

$subject_marks = [];
$total_marks_sum = 0;

if ($marks_result) {
    while ($row = mysqli_fetch_assoc($marks_result)) {
        $subject_marks[] = $row;
        $total_marks_sum += (int)$row['total_marks'];
    }
}
mysqli_stmt_close($stmt);

$subject_count = count($subject_marks);
$average_marks = ($subject_count > 0) ? round($total_marks_sum / $subject_count) : 0;

/* Derive Standing Category */
$standing_category = "Average";
$badge_class = "badge-warning";
if ($attendance >= 80 && $average_marks >= 75) {
    $standing_category = "Excellent";
    $badge_class = "badge-pass";
} elseif ($attendance >= 70 && $average_marks >= 60) {
    $standing_category = "Good Standing";
    $badge_class = "badge-info";
} elseif ($attendance < 60 || $average_marks < 40) {
    $standing_category = "At Risk";
    $badge_class = "badge-fail";
}

$page_title = "Performance Analysis";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Analysis | EduNexAI</title>
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

        <!-- Performance Category Banner Card -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Academic Standing & Risk Analytics</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">Overall Performance: <?php echo $standing_category; ?></h3>
                    <p class="text-muted small mb-0">Based on evaluated subject marks and cumulative attendance records.</p>
                </div>
                <div>
                    <span class="badge-status <?php echo $badge_class; ?> fs-5 px-4 py-2">
                        <i class="fas fa-chart-line me-2"></i> <?php echo $standing_category; ?> Category
                    </span>
                </div>
            </div>
        </div>

        <!-- Metrics Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Attendance Rate</span>
                            <h2 class="fw-bold <?php echo ($attendance >= 75) ? 'text-success' : 'text-danger'; ?> mb-0 mt-1"><?php echo number_format($attendance, 1); ?>%</h2>
                            <small class="text-muted">Total Recorded Sessions</small>
                        </div>
                        <div class="dashboard-icon icon-blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Cumulative Average Score</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $average_marks; ?> / 100</h2>
                            <small class="text-primary fw-bold">GPA Performance Score</small>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-award"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Chart -->
        <div class="card border-0 shadow-sm mb-4 card-hover-effect">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-column text-primary me-2"></i>Subject Performance Breakdown</h5>
            </div>
            <div class="card-body p-4">
                <div style="height: 300px;">
                    <canvas id="subjectChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById("subjectChart");
    if (ctx) {
        new Chart(ctx, {
            type: "bar",
            data: {
                labels: <?php echo json_encode(array_column($subject_marks, 'subject_name')); ?>,
                datasets: [{
                    label: "Subject Total Score (%)",
                    data: <?php echo json_encode(array_map('intval', array_column($subject_marks, 'total_marks'))); ?>,
                    backgroundColor: "#2563eb",
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { min: 0, max: 100 } }
            }
        });
    }
});
</script>
</body>
</html>