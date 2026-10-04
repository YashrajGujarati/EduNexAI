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
prediction_history.*
FROM prediction_history
INNER JOIN students ON prediction_history.student_id = students.student_id
WHERE students.user_id = '$user_id'
ORDER BY prediction_history.created_at DESC
");

$page_title = "Prediction History";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediction History | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-clock-rotate-left text-primary me-2"></i>My AI Prediction History</h4>
                    <small class="text-muted">Historical log of all Machine Learning risk analysis runs for your account</small>
                </div>
                <a href="ai_prediction.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-brain me-1"></i> Run New AI Analysis
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Date & Time</th>
                                <th>Attendance Rate</th>
                                <th>Average Score</th>
                                <th>AI Standing Result</th>
                                <th class="text-end pe-4">Calculated Risk</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if($query && db_num_rows($query) > 0) {
                            while ($row = db_fetch_assoc($query)) {
                                $res = !empty($row['result']) ? $row['result'] : ($row['prediction'] ?? 'N/A');
                                $score = isset($row['predicted_score']) ? $row['predicted_score'] : ($row['marks'] ?? 'N/A');
                                $att = isset($row['attendance']) ? number_format((float)$row['attendance'], 1).'%' : 'N/A';
                                $risk = !empty($row['risk_level']) ? $row['risk_level'] : 'N/A';
                                
                                $badge_class = 'badge-info';
                                if($res == 'Excellent') $badge_class = 'badge-pass';
                                elseif($res == 'Good') $badge_class = 'badge-info';
                                elseif($res == 'Average') $badge_class = 'badge-warning';
                                elseif($res == 'Poor') $badge_class = 'badge-fail';
                        ?>
                            <tr>
                                <td class="ps-4 text-muted small"><i class="fas fa-calendar-day text-primary me-2"></i><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td class="fw-semibold"><?php echo $att; ?></td>
                                <td class="fw-semibold text-dark"><?php echo $score; ?></td>
                                <td><span class="badge-status <?php echo $badge_class; ?>"><?php echo htmlspecialchars($res); ?></span></td>
                                <td class="text-end pe-4">
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($risk); ?></span>
                                </td>
                            </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-clock-rotate-left"></i>
                                        <h5>No Prediction History Available</h5>
                                        <p>Run your first AI Academic Prediction to log your performance analysis.</p>
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