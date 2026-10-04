<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "student") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$user_id = $_SESSION['user_id'];

/* Get Student ID */
$student_query = db_query($conn, "SELECT student_id, class, roll_number FROM students WHERE user_id = '$user_id' LIMIT 1");
if (!$student_query || db_num_rows($student_query) == 0) {
    die("Student record not found.");
}
$student = db_fetch_assoc($student_query);
$student_id = $student['student_id'];

/* Get User Info */
$user_query = db_query($conn, "SELECT name, email FROM users WHERE id = '$user_id' LIMIT 1");
$user = db_fetch_assoc($user_query);

/* Get Marks */
$marks_query = db_query($conn, "
    SELECT
        subjects.subject_name,
        marks.internal_marks,
        marks.external_marks,
        (COALESCE(marks.internal_marks,0) + COALESCE(marks.external_marks,0)) AS total_marks
    FROM marks
    INNER JOIN subjects ON marks.subject_id = subjects.subject_id
    WHERE marks.student_id = '$student_id'
    ORDER BY subjects.subject_name ASC
");

$marks_data = [];
$total_subjects = 0;
$total_marks_sum = 0;
$highest_marks = 0;

if ($marks_query) {
    while ($row = db_fetch_assoc($marks_query)) {
        $marks_data[] = $row;
        $t = (int)$row['total_marks'];
        $total_marks_sum += $t;
        if ($t > $highest_marks) $highest_marks = $t;
        $total_subjects++;
    }
}

$average_score = ($total_subjects > 0) ? round($total_marks_sum / $total_subjects) : 0;
$page_title = "My Academic Marks";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Marks | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Stat Summary Widgets -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Average Score</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $average_score; ?> / 100</h2>
                            <small class="text-primary fw-bold"><i class="fas fa-chart-line me-1"></i>GPA Average</small>
                        </div>
                        <div class="dashboard-icon icon-blue">
                            <i class="fas fa-award"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Highest Subject Score</span>
                            <h2 class="fw-bold text-success mb-0 mt-1"><?php echo $highest_marks; ?> / 100</h2>
                            <small class="text-success fw-bold"><i class="fas fa-arrow-up me-1"></i>Peak Performance</small>
                        </div>
                        <div class="dashboard-icon icon-green">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Evaluated Subjects</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $total_subjects; ?> Courses</h2>
                            <small class="text-info fw-bold"><i class="fas fa-book me-1"></i>Registered Marks</small>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marks Table Card -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-primary me-2"></i>Subject Wise Academic Score Sheet</h4>
                    <small class="text-muted">Detailed internal and external marks breakdown for all registered subjects</small>
                </div>
                <a href="result.php" class="btn btn-primary btn-sm"><i class="fas fa-file-invoice me-1"></i> View Result Summary</a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject Name</th>
                                <th>Internal Score (Max 40)</th>
                                <th>External Score (Max 60)</th>
                                <th>Total Combined Marks</th>
                                <th class="text-end pe-4">Grade Standing</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($marks_data) > 0): ?>
                            <?php foreach ($marks_data as $m): 
                                $total = (int)$m['total_marks'];
                                $badge_class = ($total >= 40) ? 'badge-pass' : 'badge-fail';
                                $status_text = ($total >= 40) ? 'Passed' : 'Needs Review';
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">
                                    <i class="fas fa-book text-primary me-2"></i><?php echo htmlspecialchars($m['subject_name']); ?>
                                </td>
                                <td><?php echo (int)$m['internal_marks']; ?> / 40</td>
                                <td><?php echo (int)$m['external_marks']; ?> / 60</td>
                                <td class="fw-bold text-dark fs-6"><?php echo $total; ?> / 100</td>
                                <td class="text-end pe-4">
                                    <span class="badge-status <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-chart-bar"></i>
                                        <h5>No Marks Evaluated Yet</h5>
                                        <p>No academic marks recorded in your account yet. Scores will appear once published by faculty.</p>
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