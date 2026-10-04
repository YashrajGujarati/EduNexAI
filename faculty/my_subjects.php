<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$faculty_id = $_SESSION['user_id'];
$query = db_query($conn, "SELECT * FROM subjects WHERE faculty_id = '$faculty_id' ORDER BY subject_id ASC");
$page_title = "My Subjects";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Subjects | EduNexAI</title>
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
            <div class="card-header bg-white py-3">
                <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-book-bookmark text-primary me-2"></i>My Assigned Subjects</h4>
                <small class="text-muted">Courses assigned to your faculty profile for teaching and grade evaluation</small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject ID</th>
                                <th>Subject Name</th>
                                <th>Subject Code</th>
                                <th class="text-end pe-4">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(db_num_rows($query) > 0) {
                            while($row = db_fetch_assoc($query)) {
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $row['subject_id']; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="fas fa-book text-primary me-2"></i><?php echo htmlspecialchars($row['subject_name']); ?>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($row['subject_code'] ?? 'SUB-'.$row['subject_id']); ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="manage_marks.php?subject_id=<?php echo $row['subject_id']; ?>" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-pen-to-square me-1"></i> Marks
                                    </a>
                                    <a href="manage_attendance.php?subject_id=<?php echo $row['subject_id']; ?>" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-calendar-check me-1"></i> Attendance
                                    </a>
                                </td>
                            </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fas fa-book-open"></i>
                                        <h5>No Subjects Assigned</h5>
                                        <p>Contact your system administrator to get assigned to academic subjects.</p>
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
