<?php
session_start();

/* =========================================================
   STUDENT SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   DATABASE CONNECTION
========================================================= */
require_once(__DIR__ . '/../config/db.php');

$user_id = (int)$_SESSION['user_id'];

/* Student Profile Info */
$query = "
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
$stmt = db_prepare($conn, $query);
db_stmt_bind_param($stmt, "i", $user_id);
db_stmt_execute($stmt);
$result = db_stmt_get_result($stmt);

if (!$result || db_num_rows($result) == 0) {
    die("Student information not found.");
}
$student = db_fetch_assoc($result);
db_stmt_close($stmt);

$student_name = $student['name'];
$student_email = $student['email'];
$student_id = (int)$student['student_id'];
$student_class = $student['class'];
$roll_number = $student['roll_number'];
$attendance = (float)($student['attendance'] ?? 0);

/* Subject Marks */
$marks_query = "
    SELECT
        sub.subject_name,
        m.internal_marks,
        m.external_marks,
        (COALESCE(m.internal_marks,0) + COALESCE(m.external_marks,0)) AS total_marks
    FROM marks m
    INNER JOIN subjects sub ON m.subject_id = sub.subject_id
    WHERE m.student_id = ?
";
$stmt = db_prepare($conn, $marks_query);
db_stmt_bind_param($stmt, "i", $student_id);
db_stmt_execute($stmt);
$marks_result = db_stmt_get_result($stmt);

$subject_marks = [];
$total_score_sum = 0;
$marks_count = 0;

if ($marks_result) {
    while ($row = db_fetch_assoc($marks_result)) {
        $subject_marks[] = $row;
        $total_score_sum += (int)$row['total_marks'];
        $marks_count++;
    }
}
db_stmt_close($stmt);

$average_marks = ($marks_count > 0) ? round($total_score_sum / $marks_count) : 0;
$total_subjects = count($subject_marks);

/* Latest AI Prediction */
$latest_prediction = "Pending Run";
$pred_query = db_query($conn, "SELECT * FROM prediction_history WHERE student_id = '$student_id' ORDER BY created_at DESC LIMIT 1");
if ($pred_query && db_num_rows($pred_query) > 0) {
    $pred_row = db_fetch_assoc($pred_query);
    $latest_prediction = !empty($pred_row['result']) ? $pred_row['result'] : ($pred_row['prediction'] ?? 'N/A');
}

/* Pending Assignments Check */
$pending_assign_query = db_query($conn, "
    SELECT 
        a.assignment_id,
        a.title,
        a.due_date,
        sub.subject_name
    FROM assignments a
    JOIN subjects sub ON a.subject_id = sub.subject_id
    LEFT JOIN assignment_submissions subm 
        ON a.assignment_id = subm.assignment_id AND subm.student_id = '$student_id'
    WHERE subm.submission_id IS NULL
    ORDER BY a.due_date ASC
");

$pending_assignments_count = ($pending_assign_query) ? db_num_rows($pending_assign_query) : 0;
$urgent_due_assignments = [];
$now_ts = time();
if ($pending_assign_query) {
    while ($p_row = db_fetch_assoc($pending_assign_query)) {
        $due_ts = strtotime($p_row['due_date']);
        if ($due_ts > $now_ts && ($due_ts - $now_ts <= 48 * 3600)) {
            $urgent_due_assignments[] = $p_row;
        }
    }
}

$page_title = "Student Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Hero Welcome Banner -->
        <div class="hero-banner mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3>Welcome back, <?php echo htmlspecialchars($student_name); ?>! 🎓</h3>
                    <p>EduNexAI Scholar Portal. Track your subject marks, attendance rate, and Machine Learning performance prediction.</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-white text-dark px-3 py-2 fw-bold"><i class="fas fa-graduation-cap text-primary me-1"></i> Class: <?php echo htmlspecialchars($student_class); ?></span>
                    <span class="badge bg-primary px-3 py-2 fw-bold"><i class="fas fa-hashtag me-1"></i> Roll: <?php echo htmlspecialchars($roll_number); ?></span>
                </div>
            </div>
        </div>

        <!-- Pending Assignment Notification Banner -->
        <?php if (!empty($urgent_due_assignments)): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-3" style="width:46px; height:46px; font-size:20px;">
                        <i class="fas fa-bell fa-bounce"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">Urgent Assignment Due Date Warning!</h6>
                        <p class="mb-0 small text-dark">
                            You have <strong><?php echo count($urgent_due_assignments); ?> pending assignment(s)</strong> due in the next 48 hours (e.g. <em><?php echo htmlspecialchars($urgent_due_assignments[0]['title']); ?></em>). Submit before the deadline!
                        </p>
                    </div>
                </div>
                <a href="assignments.php" class="btn btn-danger rounded-pill px-4 btn-sm">Complete Now</a>
            </div>
        <?php elseif ($pending_assignments_count > 0): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width:42px; height:42px; font-size:18px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Pending Coursework Notification</h6>
                        <p class="mb-0 small text-muted">You have <strong><?php echo $pending_assignments_count; ?> assignment(s)</strong> awaiting completion.</p>
                    </div>
                </div>
                <a href="assignments.php" class="btn btn-outline-dark rounded-pill px-4 btn-sm">View Assignments</a>
            </div>
        <?php endif; ?>

        <!-- Stat Widgets Row -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Overall Attendance</span>
                            <h2 class="fw-bold <?php echo ($attendance >= 75) ? 'text-success' : 'text-danger'; ?> mb-0 mt-1"><?php echo number_format($attendance, 1); ?>%</h2>
                            <small class="text-muted"><i class="fas fa-chart-pie me-1"></i>Classes Attended</small>
                        </div>
                        <div class="dashboard-icon icon-blue">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Average Score</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $average_marks; ?> / 100</h2>
                            <small class="text-primary fw-bold"><i class="fas fa-award me-1"></i>Overall GPA Standing</small>
                        </div>
                        <div class="dashboard-icon icon-green">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Enrolled Subjects</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_subjects); ?></h2>
                            <small class="text-warning fw-bold"><i class="fas fa-book me-1"></i>Registered Courses</small>
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
                            <span class="text-muted small fw-bold text-uppercase">AI Standing Prediction</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1" style="font-size: 20px; margin-top: 4px;"><?php echo htmlspecialchars($latest_prediction); ?></h2>
                            <small class="text-info fw-bold"><i class="fas fa-microchip me-1"></i>ML Risk Model</small>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-brain"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <a href="subjects.php" class="quick-action-tile">
                    <i class="fas fa-book-open"></i>
                    <h6>My Subjects</h6>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="marks.php" class="quick-action-tile">
                    <i class="fas fa-chart-bar"></i>
                    <h6>View Marks</h6>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="ai_prediction.php" class="quick-action-tile">
                    <i class="fas fa-robot"></i>
                    <h6>AI Risk Analysis</h6>
                </a>
            </div>
            <div class="col-md-3 col-6">
                <a href="profile.php" class="quick-action-tile">
                    <i class="fas fa-user-graduate"></i>
                    <h6>My Profile</h6>
                </a>
            </div>
        </div>

        <!-- My Marks Overview Card -->
        <div class="card border-0 shadow-sm mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-bar text-primary me-2"></i>My Academic Subject Performance</h5>
                    <small class="text-muted">Summary of internal and external scores across registered courses</small>
                </div>
                <a href="marks.php" class="btn btn-outline-primary btn-sm">Full Report</a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject Name</th>
                                <th>Internal Marks</th>
                                <th>External Marks</th>
                                <th>Total Marks</th>
                                <th class="text-end pe-4">Grade Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($subject_marks) > 0): ?>
                            <?php foreach ($subject_marks as $m): 
                                $total = (int)$m['total_marks'];
                                $badge_class = ($total >= 40) ? 'badge-pass' : 'badge-fail';
                                $status_text = ($total >= 40) ? 'Passed' : 'Needs Review';
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-semibold text-dark"><i class="fas fa-book text-primary me-2"></i><?php echo htmlspecialchars($m['subject_name']); ?></span>
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
                                        <h5>No Marks Recorded Yet</h5>
                                        <p>Your academic marks will appear here once entered by your course faculty.</p>
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