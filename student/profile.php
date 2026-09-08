<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "student") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "
SELECT users.*, students.student_id, students.class, students.roll_number, students.attendance
FROM users
LEFT JOIN students ON users.id = students.user_id
WHERE users.id='$user_id'
LIMIT 1
");

$student = mysqli_fetch_assoc($query);
$attendance = (float)($student['attendance'] ?? 0);
$page_title = "Student Profile";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <!-- Cover Banner Card -->
                <div class="profile-card-wrapper mb-4 text-center card-hover-effect">
                    <div class="profile-cover"></div>
                    <div class="profile-avatar-container">
                        <div class="profile-avatar-ring">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                    </div>
                    <div class="px-4 pb-4">
                        <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($student['name'] ?? 'Enrolled Student'); ?></h3>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($student['email'] ?? 'student@edunexai.com'); ?></p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge-status badge-pass px-3 py-2"><i class="fas fa-graduation-cap me-1"></i> Class: <?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></span>
                            <span class="badge-status badge-info px-3 py-2"><i class="fas fa-hashtag me-1"></i> Roll No: <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-hover-effect">
                            <span class="text-muted small fw-bold text-uppercase">Academic Attendance</span>
                            <h5 class="fw-bold <?php echo ($attendance >= 75) ? 'text-success' : 'text-danger'; ?> mt-1 mb-0">
                                <i class="fas fa-chart-pie me-1"></i> <?php echo number_format($attendance, 1); ?>%
                            </h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-hover-effect">
                            <span class="text-muted small fw-bold text-uppercase">Enrollment Class</span>
                            <h5 class="fw-bold text-dark mt-1 mb-0"><i class="fas fa-users-rectangle me-1"></i> <?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-hover-effect">
                            <span class="text-muted small fw-bold text-uppercase">Standing Status</span>
                            <h5 class="fw-bold text-info mt-1 mb-0">
                                <i class="fas fa-shield-halved me-1"></i> <?php echo ($attendance >= 75) ? 'Good Standing' : 'Needs Review'; ?>
                            </h5>
                        </div>
                    </div>
                </div>

                <!-- Account & Credentials Details 2-Column Grid -->
                <div class="card border-0 shadow-sm mb-4 card-hover-effect">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-id-card text-primary me-2"></i>Student Academic Credentials & Info</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-user text-primary me-1"></i> Full Name
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($student['name'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-id-badge text-primary me-1"></i> Enrollment / Student ID
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($student['enrollment_no'] ?? ('STU-'.$user_id)); ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-envelope text-primary me-1"></i> Email Address
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-phone text-primary me-1"></i> Contact Phone
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($student['mobile'] ?? 'Not Specified'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-users-rectangle text-primary me-1"></i> Class & Section
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?> (Roll: <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?>)</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-percent text-primary me-1"></i> Attendance Percentage
                                    </small>
                                    <span class="badge-status <?php echo ($attendance >= 75) ? 'badge-pass' : 'badge-fail'; ?>">
                                        <?php echo number_format($attendance, 1); ?>% Rate
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="attendance.php" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-calendar-check me-1"></i> My Attendance
                    </a>
                    <a href="ai_prediction.php" class="btn btn-outline-primary px-4 py-2">
                        <i class="fas fa-brain me-1"></i> AI Risk Standing
                    </a>
                    <a href="change_password.php" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-key me-1"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>