<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$user_id = $_SESSION['user_id'];

$query = db_query($conn, "
SELECT
users.name,
students.attendance,
AVG(marks.total_marks) AS avg_marks
FROM users
INNER JOIN students ON users.id = students.user_id
LEFT JOIN marks ON students.student_id = marks.student_id
WHERE users.id = '$user_id'
GROUP BY users.id
");

$data = db_fetch_assoc($query);

$attendance = isset($data['attendance']) ? (float)$data['attendance'] : 0;
$marks = isset($data['avg_marks']) ? round($data['avg_marks']) : 0;

/* =========================================================
   RUN PYTHON PREDICTION
========================================================= */
$command = "python ../python/prediction.py $attendance $marks 2>&1";
$prediction = trim(shell_exec($command));
if(empty($prediction) || strpos($prediction, 'Error') !== false || strpos($prediction, 'Traceback') !== false) {
    // Fallback logic if python script outputs trace or unhandled string
    if($attendance >= 80 && $marks >= 75) $prediction = 'Excellent';
    elseif($attendance >= 70 && $marks >= 60) $prediction = 'Good';
    elseif($attendance >= 60 && $marks >= 45) $prediction = 'Average';
    else $prediction = 'Poor';
}

/* DERIVE RISK LEVEL */
if ($prediction === 'Excellent') {
    $risk_level = 'None';
    $badge_class = 'badge-pass';
} elseif ($prediction === 'Good') {
    $risk_level = 'Low';
    $badge_class = 'badge-info';
} elseif ($prediction === 'Average') {
    $risk_level = 'Medium';
    $badge_class = 'badge-warning';
} else {
    $risk_level = 'High';
    $badge_class = 'badge-fail';
}

/* GET STUDENT ID */
$student_query = db_query($conn, "SELECT student_id FROM students WHERE user_id = '$user_id'");
$student = db_fetch_assoc($student_query);
$student_id = $student['student_id'] ?? 0;

/* SAVE TO PREDICTION HISTORY (Schema Adaptive) */
if($student_id > 0) {
    $check_col = @db_query($conn, "SHOW COLUMNS FROM prediction_history LIKE 'prediction'");
    if($check_col && db_num_rows($check_col) > 0) {
        $stmt = @db_prepare($conn, "INSERT INTO prediction_history (student_id, attendance, marks, prediction) VALUES (?, ?, ?, ?)");
        if($stmt) {
            db_stmt_bind_param($stmt, "idds", $student_id, $attendance, $marks, $prediction);
            @db_stmt_execute($stmt);
            db_stmt_close($stmt);
        }
    } else {
        $stmt = @db_prepare($conn, "INSERT INTO prediction_history (student_id, predicted_score, result, risk_level) VALUES (?, ?, ?, ?)");
        if($stmt) {
            db_stmt_bind_param($stmt, "idss", $student_id, $marks, $prediction, $risk_level);
            @db_stmt_execute($stmt);
            db_stmt_close($stmt);
        }
    }
}


$page_title = "AI Performance Prediction";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Prediction | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- AI Banner Card -->
                <div class="card shadow-sm border-0 mb-4 text-center">
                    <div class="card-body p-4">
                        <div class="dashboard-icon icon-purple mx-auto mb-3" style="width:70px; height:70px; font-size:32px;">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3 class="fw-bold text-dark mb-1">AI Academic Standing Prediction</h3>
                        <p class="text-muted mb-3">Powered by EduNexAI Machine Learning Decision Tree Model</p>
                        
                        <div class="py-3 px-4 rounded bg-light d-inline-block border">
                            <span class="text-muted small d-block mb-1 text-uppercase fw-bold">Predicted Academic Performance</span>
                            <span class="badge-status <?php echo $badge_class; ?> fs-5 px-4 py-2">
                                <i class="fas fa-microchip me-2"></i> <?php echo htmlspecialchars($prediction); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Input Metrics -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center p-4">
                                <div class="dashboard-icon icon-blue mx-auto mb-2" style="width:50px; height:50px;">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <span class="text-muted small fw-bold text-uppercase">Attendance Input Rate</span>
                                <h2 class="fw-bold text-dark mt-1 mb-0"><?php echo number_format($attendance, 1); ?>%</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body text-center p-4">
                                <div class="dashboard-icon icon-green mx-auto mb-2" style="width:50px; height:50px;">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <span class="text-muted small fw-bold text-uppercase">Average Academic Score</span>
                                <h2 class="fw-bold text-dark mt-1 mb-0"><?php echo $marks; ?> / 100</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="text-center">
                    <a href="prediction_history.php" class="btn btn-primary px-4 me-2">
                        <i class="fas fa-history me-1"></i> View Prediction History
                    </a>
                    <a href="dashboard.php" class="btn btn-outline-secondary px-4">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
