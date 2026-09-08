<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$faculty_id = (int)$_SESSION['user_id'];

/* Fetch Metrics & Distribution Data for Faculty's Assigned Subjects */
$stu_res = mysqli_query($conn, "SELECT COUNT(DISTINCT student_id) as count FROM students");
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

/* Subject Performance Averages for Faculty */
$sub_chart_query = mysqli_query($conn, "
    SELECT s.subject_name, ROUND(AVG(m.total_marks)) as avg_score
    FROM subjects s
    LEFT JOIN marks m ON s.subject_id = m.subject_id
    WHERE s.faculty_id = '$faculty_id'
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

$page_title = "Faculty Analytics Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Analytics | EduNexAI</title>
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

        <!-- Hero Header -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-body p-4 card-header-flex">
                <div>
                    <h4 class="fw-bold text-dark mb-1"><i class="fas fa-chart-pie text-primary me-2"></i>Faculty Visual Analytics & Grading Hub</h4>
                    <p class="text-muted mb-0">Visual performance analytics and course grade distributions for assigned teaching subjects.</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button onclick="window.location.reload();" class="btn btn-outline-primary btn-sm px-3">
                        <i class="fas fa-rotate me-1"></i> Refresh Analytics
                    </button>
                    <?php if(file_exists("../python/output/student_report.csv")): ?>
                        <a href="../python/output/student_report.csv" class="btn btn-success btn-sm px-3">
                            <i class="fas fa-download me-1"></i> Export CSV Report
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Metric Stat Widgets Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Scholars</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_students); ?></h2>
                            <small class="text-primary fw-bold"><i class="fas fa-user-graduate me-1"></i>Enrolled Roster</small>
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
                            <span class="text-muted small fw-bold text-uppercase">Subject Average Score</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $overall_avg; ?> / 100</h2>
                            <small class="text-success fw-bold"><i class="fas fa-chart-line me-1"></i>Class GPA Average</small>
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
                            <span class="text-muted small fw-bold text-uppercase">High Achievers</span>
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
                            <span class="text-muted small fw-bold text-uppercase">Needs Support</span>
                            <h2 class="fw-bold text-danger mb-0 mt-1"><?php echo number_format($cnt_risk); ?></h2>
                            <small class="text-danger fw-bold"><i class="fas fa-user-xmark me-1"></i>Score &lt; 50%</small>
                        </div>
                        <div class="dashboard-icon icon-red">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interactive Chart.js Visualizations Row -->
        <div class="row g-4 mb-4">
            <!-- Interactive Subject Bar Chart -->
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-column text-primary me-2"></i>My Course Marks Breakdown</h5>
                        <span class="badge bg-primary-subtle text-primary border rounded-pill">Interactive</span>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 320px;">
                            <canvas id="facultyBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Interactive Grade Doughnut Chart -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100 card-hover-effect">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-pie text-success me-2"></i>Grade Ratio Breakdown</h5>
                        <span class="badge bg-success-subtle text-success border rounded-pill">Distribution</span>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div style="height: 320px; position: relative;">
                            <canvas id="facultyPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Python Matplotlib Models Section -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="fab fa-python text-primary me-2"></i>Python AI Generated Analytical Models</h5>
                    <small class="text-muted">Automated graphical reports generated by Seaborn & Matplotlib analytics modules</small>
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
                                    <h5>Bar Chart Pending</h5>
                                    <p>Python script will update this graph automatically.</p>
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
                                    <h5>Pie Chart Pending</h5>
                                    <p>Python script will update this graph automatically.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-fire text-danger me-1"></i> Attendance vs Score Correlation Heatmap</h6>
                            <?php if(file_exists("../python/output/heatmap.png")): ?>
                                <img src="../python/output/heatmap.png?t=<?php echo time(); ?>" class="img-fluid rounded border shadow-sm" style="max-height:420px;" alt="Heatmap">
                            <?php else: ?>
                                <div class="empty-state py-4">
                                    <i class="fas fa-border-all"></i>
                                    <h5>Correlation Heatmap Pending</h5>
                                    <p>Python correlation matrix will display after execution.</p>
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
    // 1. Faculty Bar Chart
    const ctxBar = document.getElementById('facultyBarChart');
    if (ctxBar) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(count($subject_names) > 0 ? $subject_names : ['Course A', 'Course B']); ?>,
                datasets: [{
                    label: 'Subject Average (%)',
                    data: <?php echo json_encode(count($subject_scores) > 0 ? $subject_scores : [85, 78]); ?>,
                    backgroundColor: ['#2563eb', '#10b981', '#8b5cf6', '#f59e0b'],
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

    // 2. Faculty Pie Chart
    const ctxPie = document.getElementById('facultyPieChart');
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
});
</script>
</body>
</html>