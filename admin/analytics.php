<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

/* Fetch Metrics & Distribution Data */
$stu_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
$total_students = mysqli_fetch_assoc($stu_res)['count'] ?? 0;

$avg_res = mysqli_query($conn, "SELECT AVG(total_marks) as avg_marks FROM marks");
$overall_avg = round(mysqli_fetch_assoc($avg_res)['avg_marks'] ?? 0);

/* Grade Distribution */
$high_res = mysqli_query($conn, "SELECT COUNT(DISTINCT student_id) as cnt FROM marks WHERE total_marks >= 75");
$cnt_high = mysqli_fetch_assoc($high_res)['cnt'] ?? 0;

$avg_perf_res = mysqli_query($conn, "SELECT COUNT(DISTINCT student_id) as cnt FROM marks WHERE total_marks >= 50 AND total_marks < 75");
$cnt_avg = mysqli_fetch_assoc($avg_perf_res)['cnt'] ?? 0;

$risk_res = mysqli_query($conn, "SELECT COUNT(DISTINCT student_id) as cnt FROM marks WHERE total_marks < 50");
$cnt_risk = mysqli_fetch_assoc($risk_res)['cnt'] ?? 0;

/* Subject Performance Averages */
$sub_chart_query = mysqli_query($conn, "
    SELECT s.subject_name, ROUND(AVG(m.total_marks)) as avg_score
    FROM subjects s
    LEFT JOIN marks m ON s.subject_id = m.subject_id
    GROUP BY s.subject_id, s.subject_name
");
$subject_names = [];
$subject_scores = [];
if ($sub_chart_query) {
    while ($row = mysqli_fetch_assoc($sub_chart_query)) {
        $subject_names[] = $row['subject_name'];
        $subject_scores[] = (int)($row['avg_score'] ?? 0);
    }
}

/* Attendance Distribution */
$att_res_1 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM students WHERE attendance < 60");
$att_low = mysqli_fetch_assoc($att_res_1)['cnt'] ?? 0;

$att_res_2 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM students WHERE attendance >= 60 AND attendance < 75");
$att_mid = mysqli_fetch_assoc($att_res_2)['cnt'] ?? 0;

$att_res_3 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM students WHERE attendance >= 75 AND attendance < 90");
$att_good = mysqli_fetch_assoc($att_res_3)['cnt'] ?? 0;

$att_res_4 = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM students WHERE attendance >= 90");
$att_high = mysqli_fetch_assoc($att_res_4)['cnt'] ?? 0;

$page_title = "Analytics Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual Analytics Dashboard | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Hero Header -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-body p-4 card-header-flex">
                <div>
                    <h4 class="fw-bold text-dark mb-1"><i class="fas fa-chart-pie text-primary me-2"></i>Institutional AI Visual Analytics Suite</h4>
                    <p class="text-muted mb-0">Real-time interactive performance analytics, grade distributions, and Python machine learning visualization models.</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button onclick="window.location.reload();" class="btn btn-outline-primary btn-sm px-3">
                        <i class="fas fa-rotate me-1"></i> Refresh Analytics
                    </button>
                    <a href="ai_predictions.php" class="btn btn-primary btn-sm px-3">
                        <i class="fas fa-microchip me-1"></i> Run AI Model
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Widgets Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Enrolled</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_students); ?></h2>
                            <small class="text-primary fw-bold"><i class="fas fa-user-graduate me-1"></i>Active Students</small>
                        </div>
                        <div class="dashboard-icon icon-blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Average Class GPA</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $overall_avg; ?> / 100</h2>
                            <small class="text-success fw-bold"><i class="fas fa-chart-line me-1"></i>Institutional Score</small>
                        </div>
                        <div class="dashboard-icon icon-green">
                            <i class="fas fa-award"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">High Performers</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?php echo number_format($cnt_high); ?></h2>
                            <small class="text-success fw-bold"><i class="fas fa-star me-1"></i>Score &gt;= 75%</small>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-trophy"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">At-Risk Scholars</span>
                            <h2 class="fw-bold text-danger mb-0 mt-1"><?php echo number_format($cnt_risk); ?></h2>
                            <small class="text-danger fw-bold"><i class="fas fa-triangle-exclamation me-1"></i>Score &lt; 50%</small>
                        </div>
                        <div class="dashboard-icon icon-red">
                            <i class="fas fa-user-xmark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Chart.js Visualizations Row 1 -->
        <div class="row g-4 mb-4">
            <!-- Interactive Subject Bar Chart -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-column text-primary me-2"></i>Subject Wise Average Performance</h5>
                        <span class="badge bg-primary-subtle text-primary border rounded-pill">Live Interactive</span>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 320px;">
                            <canvas id="liveBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Grade Doughnut Chart -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie text-success me-2"></i>Academic Standing Breakdown</h5>
                        <span class="badge bg-success-subtle text-success border rounded-pill">Ratio Model</span>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div style="height: 320px; position: relative;">
                            <canvas id="livePieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Chart.js Visualizations Row 2 -->
        <div class="row g-4 mb-4">
            <!-- Attendance Distribution Chart -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-check text-info me-2"></i>Attendance Rate Distribution</h5>
                        <span class="badge bg-info-subtle text-info border rounded-pill">Roster Analytics</span>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 300px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Radar / Subject Standing Chart -->
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-bullseye text-warning me-2"></i>Curriculum Strength Radar</h5>
                        <span class="badge bg-warning-subtle text-warning border rounded-pill">Multi-Axis</span>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 300px;">
                            <canvas id="radarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Python Matplotlib Models Section -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="fab fa-python text-primary me-2"></i>Python Seaborn & Matplotlib Generated Graphical Reports</h5>
                    <small class="text-muted">High-resolution static data visualizations output by backend Python ML analytics scripts</small>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-image text-primary me-1"></i> Performance Distribution (Bar Model)</h6>
                            <?php if(file_exists("../python/output/bar_chart.png")): ?>
                                <img src="../python/output/bar_chart.png?t=<?php echo time(); ?>" class="img-fluid rounded border shadow-sm" style="max-height:350px;" alt="Bar Chart">
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="fas fa-chart-simple"></i>
                                    <h5>Bar Chart Model Pending</h5>
                                    <p>Run Python AI script to update graphical reports.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-chart-pie text-success me-1"></i> Grade Ratio Breakdown (Pie Model)</h6>
                            <?php if(file_exists("../python/output/pie_chart.png")): ?>
                                <img src="../python/output/pie_chart.png?t=<?php echo time(); ?>" class="img-fluid rounded border shadow-sm" style="max-height:350px;" alt="Pie Chart">
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="fas fa-chart-pie"></i>
                                    <h5>Pie Chart Model Pending</h5>
                                    <p>Run Python AI script to update graphical reports.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-fire text-danger me-1"></i> Attendance vs Score Correlation Heatmap Matrix</h6>
                            <?php if(file_exists("../python/output/heatmap.png")): ?>
                                <img src="../python/output/heatmap.png?t=<?php echo time(); ?>" class="img-fluid rounded border shadow-sm" style="max-height:420px;" alt="Heatmap">
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="fas fa-border-all"></i>
                                    <h5>Correlation Heatmap Pending</h5>
                                    <p>Run Python AI script to render correlation matrix.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Live Bar Chart
    const ctxBar = document.getElementById('liveBarChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(count($subject_names) > 0 ? $subject_names : ['Maths', 'Python', 'DBMS', 'Web Tech']); ?>,
                datasets: [{
                    label: 'Subject Average Score (%)',
                    data: <?php echo json_encode(count($subject_scores) > 0 ? $subject_scores : [82, 75, 88, 79]); ?>,
                    backgroundColor: ['#2563eb', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4'],
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

    // 2. Live Pie / Doughnut Chart
    const ctxPie = document.getElementById('livePieChart');
    if (ctxPie) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['High Performers (>=75%)', 'Average Performers (50-74%)', 'At-Risk Scholars (<50%)'],
                datasets: [{
                    data: [<?php echo $cnt_high; ?>, <?php echo $cnt_avg; ?>, <?php echo $cnt_risk; ?>],
                    backgroundColor: ['#10b981', '#3b82f6', '#ef4444'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // 3. Attendance Distribution Chart
    const ctxAtt = document.getElementById('attendanceChart');
    if (ctxAtt) {
        new Chart(ctxAtt, {
            type: 'bar',
            data: {
                labels: ['< 60% (Low)', '60-74% (Borderline)', '75-89% (Good)', '90-100% (Excellent)'],
                datasets: [{
                    label: 'Number of Students',
                    data: [<?php echo $att_low; ?>, <?php echo $att_mid; ?>, <?php echo $att_good; ?>, <?php echo $att_high; ?>],
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }

    // 4. Radar Chart
    const ctxRadar = document.getElementById('radarChart');
    if (ctxRadar) {
        new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: <?php echo json_encode(count($subject_names) > 0 ? $subject_names : ['Maths', 'Python', 'DBMS', 'Web Tech']); ?>,
                datasets: [{
                    label: 'Class Average',
                    data: <?php echo json_encode(count($subject_scores) > 0 ? $subject_scores : [82, 75, 88, 79]); ?>,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.2)',
                    borderColor: '#2563eb',
                    pointBackgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { r: { min: 0, max: 100 } }
            }
        });
    }
});
</script>
</body>
</html>