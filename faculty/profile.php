<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id' LIMIT 1");
$faculty = mysqli_fetch_assoc($query);

/* Count assigned subjects */
$sub_count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM subjects WHERE faculty_id='$user_id'");
$sub_count = (int)(mysqli_fetch_assoc($sub_count_query)['count'] ?? 0);

$page_title = "Faculty Profile";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Profile | EduNexAI</title>
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
                            <i class="fas fa-chalkboard-user"></i>
                        </div>
                    </div>
                    <div class="px-4 pb-4">
                        <h3 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($faculty['name'] ?? 'Faculty Instructor'); ?></h3>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($faculty['email'] ?? 'faculty@edunexai.com'); ?></p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <span class="badge-status badge-info px-3 py-2"><i class="fas fa-graduation-cap me-1"></i> Academic Faculty Staff</span>
                            <span class="badge-status badge-pass px-3 py-2"><i class="fas fa-user-check me-1"></i> Verified Educator</span>
                        </div>
                    </div>
                </div>

                <!-- Metrics Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-hover-effect">
                            <span class="text-muted small fw-bold text-uppercase">Assigned Subjects</span>
                            <h5 class="fw-bold text-primary mt-1 mb-0"><i class="fas fa-book me-1"></i> <?php echo $sub_count; ?> Courses Assigned</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-hover-effect">
                            <span class="text-muted small fw-bold text-uppercase">Department Role</span>
                            <h5 class="fw-bold text-dark mt-1 mb-0"><i class="fas fa-chalkboard me-1"></i> Academic Faculty</h5>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm text-center p-3 card-hover-effect">
                            <span class="text-muted small fw-bold text-uppercase">Account Status</span>
                            <h5 class="fw-bold text-success mt-1 mb-0"><i class="fas fa-check-circle me-1"></i> Active Instructor</h5>
                        </div>
                    </div>
                </div>

                <!-- Account & Credentials Details 2-Column Grid -->
                <div class="card border-0 shadow-sm mb-4 card-hover-effect">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-id-card text-primary me-2"></i>Faculty Account & Credentials Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-user text-primary me-1"></i> Full Name
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($faculty['name'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-envelope text-primary me-1"></i> Email Address
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($faculty['email'] ?? 'N/A'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-phone text-primary me-1"></i> Contact Phone
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($faculty['mobile'] ?? 'Not Specified'); ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-book-bookmark text-primary me-1"></i> Teaching Courses
                                    </small>
                                    <span class="badge bg-info px-3 py-2"><?php echo $sub_count; ?> Subjects Assigned</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-id-badge text-primary me-1"></i> Employee ID
                                    </small>
                                    <span class="fw-bold text-dark fs-6">FAC-<?php echo $user_id; ?></span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block mb-1 font-weight-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                                        <i class="fas fa-calendar-alt text-primary me-1"></i> Faculty Joined Date
                                    </small>
                                    <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($faculty['created_at'] ?? 'N/A'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Action Buttons -->
                <div class="d-flex justify-content-center gap-3">
                    <a href="my_subjects.php" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-book me-1"></i> My Subjects
                    </a>
                    <a href="manage_marks.php" class="btn btn-outline-primary px-4 py-2">
                        <i class="fas fa-pen-to-square me-1"></i> Enter Student Marks
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>