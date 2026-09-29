<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

// Dynamic schema column detection to handle 'result' or 'prediction' column names
$check_col = @mysqli_query($conn, "SHOW COLUMNS FROM prediction_history LIKE 'prediction'");
$pred_col = ($check_col && mysqli_num_rows($check_col) > 0) ? "prediction" : "result";

$excellent = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prediction_history WHERE `$pred_col`='Excellent'"));
$good      = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prediction_history WHERE `$pred_col`='Good'"));
$average   = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prediction_history WHERE `$pred_col`='Average'"));
$poor      = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prediction_history WHERE `$pred_col`='Poor'"));

$query = mysqli_query($conn, "
SELECT
prediction_history.*,
users.name as student_name
FROM prediction_history
INNER JOIN students ON prediction_history.student_id = students.student_id
INNER JOIN users ON students.user_id = users.id
ORDER BY prediction_history.created_at DESC
");

/* Handle Batch AI Prediction Trigger */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_batch_predictions'])) {
    $stus = mysqli_query($conn, "SELECT s.student_id, s.attendance, AVG(m.total_marks) as avg_marks FROM students s LEFT JOIN marks m ON s.student_id = m.student_id GROUP BY s.student_id");
    if ($stus) {
        while ($st = mysqli_fetch_assoc($stus)) {
            $sid = (int)$st['student_id'];
            $att = (float)$st['attendance'];
            $mrk = (float)($st['avg_marks'] ?? 70);

            if ($att >= 85 && $mrk >= 80) { $pred = 'Excellent'; $risk = 'None'; }
            elseif ($att >= 75 && $mrk >= 65) { $pred = 'Good'; $risk = 'Low'; }
            elseif ($att >= 60 && $mrk >= 45) { $pred = 'Average'; $risk = 'Medium'; }
            else { $pred = 'Poor'; $risk = 'High'; }

            @mysqli_query($conn, "INSERT INTO prediction_history (student_id, predicted_score, result, risk_level) VALUES ($sid, $mrk, '$pred', '$risk')");
            @mysqli_query($conn, "INSERT INTO predictions (student_id, predicted_score, result, risk_level) VALUES ($sid, $mrk, '$pred', '$risk') ON DUPLICATE KEY UPDATE predicted_score=$mrk, result='$pred', risk_level='$risk'");
        }
        $batch_msg = "Batch AI Model evaluation executed successfully for all enrolled students!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Predictions | EduNexAI</title>
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

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="fw-bold text-dark mb-1"><i class="fas fa-brain text-primary me-2"></i>AI Machine Learning Academic Predictions</h4>
                    <p class="text-muted mb-0">Overview of student risk categories predicted by the Decision Tree Machine Learning model.</p>
                </div>
                <form method="POST">
                    <button type="submit" name="run_batch_predictions" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-microchip me-2"></i> Run Batch AI Predictions
                    </button>
                </form>
            </div>
        </div>

        <?php if(!empty($batch_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($batch_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Visual Chart Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-primary me-2"></i>Academic Risk Distribution Chart</h5>
            </div>
            <div class="card-body p-4" style="height: 300px;">
                <canvas id="predictionChart"></canvas>
            </div>
        </div>

        <!-- Prediction Category Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-widget text-center border-start border-success border-4">
                    <span class="text-muted small text-uppercase fw-bold">Excellent Standing</span>
                    <h2 class="text-success fw-bold my-1"><?php echo $excellent; ?></h2>
                    <span class="badge-status badge-pass"><i class="fas fa-star me-1"></i> High Performers</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-widget text-center border-start border-primary border-4">
                    <span class="text-muted small text-uppercase fw-bold">Good Standing</span>
                    <h2 class="text-primary fw-bold my-1"><?php echo $good; ?></h2>
                    <span class="badge-status badge-info"><i class="fas fa-thumbs-up me-1"></i> Stable Students</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-widget text-center border-start border-warning border-4">
                    <span class="text-muted small text-uppercase fw-bold">Average Risk</span>
                    <h2 class="text-warning fw-bold my-1"><?php echo $average; ?></h2>
                    <span class="badge-status badge-warning"><i class="fas fa-exclamation-triangle me-1"></i> Moderate Risk</span>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stat-widget text-center border-start border-danger border-4">
                    <span class="text-muted small text-uppercase fw-bold">Poor Risk</span>
                    <h2 class="text-danger fw-bold my-1"><?php echo $poor; ?></h2>
                    <span class="badge-status badge-fail"><i class="fas fa-triangle-exclamation me-1"></i> High Intervention</span>
                </div>
            </div>
        </div>

        <!-- Prediction History Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-history text-primary me-2"></i>Prediction Log & Risk Classification</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Log ID</th>
                                <th>Student Name</th>
                                <th>Attendance Score</th>
                                <th>Academic Marks</th>
                                <th>AI Predicted Level</th>
                                <th class="text-end pe-4">Prediction Date</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($query && mysqli_num_rows($query) > 0) {
                            while($row = mysqli_fetch_assoc($query)) {
                                $pred = !empty($row['prediction']) ? $row['prediction'] : (!empty($row['result']) ? $row['result'] : 'N/A');
                                $att = isset($row['attendance']) ? number_format((float)$row['attendance'], 1).'%' : 'N/A';
                                $marks_val = isset($row['marks']) ? $row['marks'] : (isset($row['predicted_score']) ? $row['predicted_score'] : 'N/A');
                                $badge = 'badge-info';
                                if($pred == 'Excellent') $badge = 'badge-pass';
                                elseif($pred == 'Good') $badge = 'badge-info';
                                elseif($pred == 'Average') $badge = 'badge-warning';
                                elseif($pred == 'Poor') $badge = 'badge-fail';
                                $log_id = isset($row['id']) ? $row['id'] : (isset($row['prediction_id']) ? $row['prediction_id'] : 0);
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $log_id; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2" style="width:32px; height:32px; font-size:12px;">
                                            <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="fw-semibold"><?php echo $att; ?></span></td>
                                <td><span class="fw-semibold"><?php echo $marks_val; ?></span></td>
                                <td><span class="badge-status <?php echo $badge; ?>"><?php echo htmlspecialchars($pred); ?></span></td>
                                <td class="text-end pe-4 text-muted small"><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                        <?php } 

                        } else { ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-robot"></i>
                                        <h5>No AI Predictions Generated Yet</h5>
                                        <p>Predictions will automatically populate as students generate performance analytics.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
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
<script>
const ctx = document.getElementById('predictionChart');
if (ctx) {
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Excellent Standing', 'Good Standing', 'Average Risk', 'Poor Risk'],
            datasets: [{
                label: 'Students Count',
                data: [
                    <?php echo $excellent; ?>,
                    <?php echo $good; ?>,
                    <?php echo $average; ?>,
                    <?php echo $poor; ?>
                ],
                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
}
</script>
</body>
</html>