<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT student_id, attendance FROM students WHERE user_id='$user_id'");
$student = mysqli_fetch_assoc($query);
$student_id = $student['student_id'] ?? 0;
$overall_att = (float)($student['attendance'] ?? 0);

$attendance_query = mysqli_query($conn, "
    SELECT attendance.*, subjects.subject_name
    FROM attendance
    LEFT JOIN subjects ON attendance.subject_id = subjects.subject_id
    WHERE attendance.student_id='$student_id'
");

$page_title = "My Attendance Records";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Attendance | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Attendance Summary Widget -->
        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-body p-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Overall Class Attendance Rate</span>
                    <h2 class="fw-bold <?php echo ($overall_att >= 75) ? 'text-success' : 'text-danger'; ?> mb-0 mt-1">
                        <?php echo number_format($overall_att, 1); ?>% Overall
                    </h2>
                </div>
                <div>
                    <span class="badge-status <?php echo ($overall_att >= 75) ? 'badge-pass' : 'badge-fail'; ?> fs-6 px-4 py-2">
                        <i class="fas fa-check-circle me-1"></i> <?php echo ($overall_att >= 75) ? 'Eligible for Exams' : 'Low Attendance Alert'; ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-check text-primary me-2"></i>Subject Wise Attendance Roster</h4>
                    <small class="text-muted">Breakdown of attended sessions per enrolled subject</small>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject Name</th>
                                <th>Present Days</th>
                                <th>Total Sessions</th>
                                <th class="text-end pe-4">Attendance Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if ($attendance_query && mysqli_num_rows($attendance_query) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($attendance_query)): 
                                $p_days = (int)($row['present_days'] ?? 0);
                                $t_days = (int)($row['total_days'] ?? 100);
                                $pct = (float)($row['percentage'] ?? 0);
                                $badge_class = ($pct >= 75) ? 'badge-pass' : 'badge-fail';
                            ?>
                            <tr>
                                <td class="ps-4 fw-semibold text-dark">
                                    <i class="fas fa-book-bookmark text-primary me-2"></i><?php echo htmlspecialchars($row['subject_name'] ?? 'General Subject'); ?>
                                </td>
                                <td><?php echo $p_days; ?> Days Present</td>
                                <td><?php echo $t_days; ?> Total Days</td>
                                <td class="text-end pe-4">
                                    <span class="badge-status <?php echo $badge_class; ?>">
                                        <?php echo number_format($pct, 1); ?>%
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-calendar-days"></i>
                                        <h5>No Specific Attendance Log</h5>
                                        <p>Your overall attendance rate is <strong><?php echo number_format($overall_att, 1); ?>%</strong>. Subject breakdown will populate as faculty submits session logs.</p>
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