<?php
session_start();

/* =========================================================
   FACULTY SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != "faculty") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$faculty_id = (int)$_SESSION['user_id'];

/* Fetch Student Performance Overview for Faculty's Subjects */
$student_query = mysqli_query($conn, "
    SELECT
        s.student_id,
        s.roll_number,
        s.class,
        s.attendance,
        u.name,
        u.email,
        AVG(COALESCE(m.internal_marks, 0) + COALESCE(m.external_marks, 0)) AS average_marks,
        COUNT(m.mark_id) AS total_entries
    FROM students s
    INNER JOIN users u ON s.user_id = u.id
    LEFT JOIN marks m ON s.student_id = m.student_id
    GROUP BY s.student_id, s.roll_number, s.class, s.attendance, u.name, u.email
    ORDER BY average_marks DESC
");

$students = [];
if ($student_query) {
    while ($row = mysqli_fetch_assoc($student_query)) {
        $students[] = $row;
    }
}

$page_title = "Student Performance Analysis";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="card shadow-sm border-0 mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-line text-primary me-2"></i>Class Student Performance Matrix</h4>
                    <small class="text-muted">Ranked overview of student attendance and average academic score</small>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Rank #</th>
                                <th>Student Name</th>
                                <th>Class & Roll</th>
                                <th>Attendance Rate</th>
                                <th>Average Score</th>
                                <th class="text-end pe-4">Performance Standing</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($students) > 0): ?>
                            <?php 
                            $rank = 1;
                            foreach ($students as $stu): 
                                $avg = round((float)($stu['average_marks'] ?? 0));
                                $att = (float)($stu['attendance'] ?? 0);
                                
                                $badge_class = 'badge-info';
                                $standing = 'Good';
                                if ($avg >= 80 && $att >= 80) { $badge_class = 'badge-pass'; $standing = 'Excellent'; }
                                elseif ($avg < 50 || $att < 65) { $badge_class = 'badge-fail'; $standing = 'Needs Attention'; }
                                elseif ($avg >= 60) { $badge_class = 'badge-info'; $standing = 'Average'; }
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $rank++; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2" style="width:34px; height:34px; font-size:12px;">
                                            <?php echo strtoupper(substr($stu['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($stu['name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($stu['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($stu['class']); ?> (Roll: <?php echo htmlspecialchars($stu['roll_number']); ?>)</span></td>
                                <td>
                                    <span class="badge-status <?php echo ($att >= 75) ? 'badge-pass' : 'badge-fail'; ?>">
                                        <?php echo number_format($att, 1); ?>%
                                    </span>
                                </td>
                                <td class="fw-bold text-dark fs-6"><?php echo $avg; ?> / 100</td>
                                <td class="text-end pe-4">
                                    <span class="badge-status <?php echo $badge_class; ?>"><?php echo $standing; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-chart-bar"></i>
                                        <h5>No Performance Data Available</h5>
                                        <p>No student marks or attendance records found in the database.</p>
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