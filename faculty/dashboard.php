<?php
session_start();

/* =========================================================
   FACULTY SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != "faculty") {
    header("Location: ../login.php");
    exit();
}

/* =========================================================
   DATABASE CONNECTION
========================================================= */
require_once("../config/db.php");

$faculty_id = (int)$_SESSION['user_id'];

/* Faculty Info */
$faculty_query = mysqli_query($conn, "SELECT id, name, email FROM users WHERE id = $faculty_id LIMIT 1");
if (!$faculty_query || mysqli_num_rows($faculty_query) == 0) {
    die("Faculty information not found.");
}
$faculty = mysqli_fetch_assoc($faculty_query);
$faculty_name = $faculty['name'];
$faculty_email = $faculty['email'];

/* Assigned Subjects */
$subject_query = mysqli_query($conn, "SELECT subject_id, subject_name, subject_code FROM subjects WHERE faculty_id = $faculty_id ORDER BY subject_name ASC");
$total_subjects = mysqli_num_rows($subject_query);
$faculty_subjects = [];
while ($subject = mysqli_fetch_assoc($subject_query)) {
    $faculty_subjects[] = $subject;
}

/* Total Students */
$student_count_query = mysqli_query($conn, "SELECT COUNT(DISTINCT s.student_id) as total FROM students s");
$total_students = mysqli_fetch_assoc($student_count_query)['total'] ?? 0;

/* Average Class Performance */
$avg_marks_query = mysqli_query($conn, "SELECT AVG(total_marks) as avg_score FROM marks");
$avg_score = round(mysqli_fetch_assoc($avg_marks_query)['avg_score'] ?? 0);

$page_title = "Faculty Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard | EduNexAI</title>
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
        <div class="hero-banner">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h3>Welcome back, Professor <?php echo htmlspecialchars($faculty_name); ?>! 👋</h3>
                    <p>EduNexAI Faculty Teaching Hub. Oversee assigned subjects, record academic marks, and track student attendance.</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-white text-dark px-3 py-2 fw-bold"><i class="fas fa-book-open text-primary me-1"></i> <?php echo $total_subjects; ?> Courses Assigned</span>
                </div>
            </div>
        </div>

        <!-- Stat Widgets Row -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Assigned Subjects</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_subjects); ?></h2>
                            <small class="text-primary fw-bold"><i class="fas fa-book me-1"></i>Curriculum Courses</small>
                        </div>
                        <div class="dashboard-icon icon-blue">
                            <i class="fas fa-book-bookmark"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Enrolled Scholars</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo number_format($total_students); ?></h2>
                            <small class="text-success fw-bold"><i class="fas fa-user-graduate me-1"></i>Active Students</small>
                        </div>
                        <div class="dashboard-icon icon-green">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm card-hover-effect">
                    <div class="card-body p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Average Class Standing</span>
                            <h2 class="fw-bold text-dark mb-0 mt-1"><?php echo $avg_score; ?> / 100</h2>
                            <small class="text-info fw-bold"><i class="fas fa-chart-line me-1"></i>Overall Subject Marks</small>
                        </div>
                        <div class="dashboard-icon icon-purple">
                            <i class="fas fa-award"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="my_subjects.php" class="quick-action-tile">
                    <i class="fas fa-book-open"></i>
                    <h6>My Teaching Subjects</h6>
                </a>
            </div>
            <div class="col-md-4">
                <a href="manage_marks.php" class="quick-action-tile">
                    <i class="fas fa-pen-to-square"></i>
                    <h6>Manage Student Marks</h6>
                </a>
            </div>
            <div class="col-md-4">
                <a href="manage_attendance.php" class="quick-action-tile">
                    <i class="fas fa-calendar-check"></i>
                    <h6>Record Attendance</h6>
                </a>
            </div>
        </div>

        <!-- My Subjects Table Card -->
        <div class="card border-0 shadow-sm mb-4 card-hover-effect">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-book text-primary me-2"></i>My Teaching Courses</h5>
                    <small class="text-muted">Academic subjects currently assigned to your faculty profile</small>
                </div>
                <a href="my_subjects.php" class="btn btn-outline-primary btn-sm">View All Courses</a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject ID</th>
                                <th>Subject Name</th>
                                <th>Subject Code</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($faculty_subjects) > 0): ?>
                            <?php foreach ($faculty_subjects as $s): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $s['subject_id']; ?></td>
                                <td>
                                    <span class="fw-semibold text-dark"><i class="fas fa-book-bookmark text-primary me-2"></i><?php echo htmlspecialchars($s['subject_name']); ?></span>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($s['subject_code'] ?? ('SUB-'.$s['subject_id'])); ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="manage_marks.php?subject_id=<?php echo $s['subject_id']; ?>" class="btn btn-sm btn-primary me-1"><i class="fas fa-pen me-1"></i> Marks</a>
                                    <a href="manage_attendance.php?subject_id=<?php echo $s['subject_id']; ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-calendar-check me-1"></i> Attendance</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-book-open"></i>
                                        <h5>No Assigned Subjects Found</h5>
                                        <p>You have not been assigned to any teaching subjects yet.</p>
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