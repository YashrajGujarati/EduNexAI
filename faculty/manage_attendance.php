<?php
session_start();

/* =========================================================
   FACULTY SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "faculty") {
    header("Location: ../login.php");
    exit();
}

require_once("../config/db.php");
$faculty_id = (int)$_SESSION['user_id'];

$message = "";
$message_type = "";
$selected_subject = (int)($_GET['subject_id'] ?? ($_POST['subject_id'] ?? 0));

/* Save Attendance */
if (isset($_POST['save_attendance'])) {
    $sub_id = (int)$_POST['subject_id'];
    $present_arr = $_POST['present_days'] ?? [];
    $total_days = (int)($_POST['total_days'] ?? 100);

    if ($total_days <= 0) $total_days = 100;

    $success_count = 0;

    foreach ($present_arr as $student_id => $p_days) {
        $student_id = (int)$student_id;
        $p_days = (int)$p_days;
        $percentage = round(($p_days / $total_days) * 100, 2);

        /* Update students overall attendance and attendance table */
        mysqli_query($conn, "UPDATE students SET attendance = '$percentage' WHERE student_id = '$student_id'");

        $check = mysqli_query($conn, "SELECT attendance_id FROM attendance WHERE student_id = '$student_id' AND subject_id = '$sub_id'");
        if (mysqli_num_rows($check) > 0) {
            $att_row = mysqli_fetch_assoc($check);
            $att_id = $att_row['attendance_id'];
            mysqli_query($conn, "UPDATE attendance SET present_days = '$p_days', total_days = '$total_days', percentage = '$percentage' WHERE attendance_id = '$att_id'");
        } else {
            mysqli_query($conn, "INSERT INTO attendance (student_id, subject_id, present_days, total_days, percentage) VALUES ('$student_id', '$sub_id', '$p_days', '$total_days', '$percentage')");
        }
        $success_count++;
    }

    $message = "Successfully recorded attendance for $success_count students!";
    $message_type = "success";
}

/* Faculty Subjects */
$subject_query = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects WHERE faculty_id = '$faculty_id' ORDER BY subject_name ASC");

/* Students for selected subject */
$students = [];
if ($selected_subject > 0) {
    $stu_query = mysqli_query($conn, "
        SELECT
            s.student_id,
            u.name,
            s.class,
            s.roll_number,
            s.attendance as overall_att,
            att.present_days,
            att.total_days,
            att.percentage
        FROM students s
        INNER JOIN users u ON s.user_id = u.id
        LEFT JOIN attendance att ON s.student_id = att.student_id AND att.subject_id = '$selected_subject'
        ORDER BY u.name ASC
    ");
    if ($stu_query) {
        while ($row = mysqli_fetch_assoc($stu_query)) {
            $students[] = $row;
        }
    }
}

$page_title = "Manage Attendance";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Subject Selector Container Card -->
        <div class="search-filter-card mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label fw-bold text-dark d-flex align-items-center mb-2">
                        <i class="fas fa-book-bookmark text-primary me-2"></i>Select Teaching Subject
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-book-open text-primary"></i></span>
                        <select name="subject_id" class="form-select" onchange="this.form.submit()" required>
                            <option value="">-- Choose Course Subject --</option>
                            <?php while ($sub = mysqli_fetch_assoc($subject_query)): ?>
                                <option value="<?php echo $sub['subject_id']; ?>" <?php echo ($selected_subject == $sub['subject_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sub['subject_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-filter"></i>
                        <span>Load Attendance Roster</span>
                    </button>
                </div>
            </form>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($selected_subject > 0): ?>
            <form method="POST">
                <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">
                
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-calendar-check text-primary me-2"></i>Record Student Attendance</h5>
                            <small class="text-muted">Enter present days out of total course sessions</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted fw-bold mb-0">Total Sessions:</label>
                            <input type="number" name="total_days" value="100" class="form-control form-control-sm text-center" style="width: 80px;" min="1" required>
                            <button type="submit" name="save_attendance" class="btn btn-success btn-sm ms-2">
                                <i class="fas fa-save me-1"></i> Save Attendance
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Student ID</th>
                                        <th>Student Name</th>
                                        <th>Class / Roll</th>
                                        <th style="width: 180px;">Present Days</th>
                                        <th class="text-end pe-4">Attendance Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $stu): 
                                        $s_id = $stu['student_id'];
                                        $p_days = (int)($stu['present_days'] ?? 85);
                                        $att_rate = (float)($stu['percentage'] ?? ($stu['overall_att'] ?? 85.0));
                                        $badge_class = ($att_rate >= 75) ? 'badge-pass' : 'badge-fail';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">#<?php echo $s_id; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="topbar-avatar me-2" style="width:32px; height:32px; font-size:12px;">
                                                    <?php echo strtoupper(substr($stu['name'], 0, 1)); ?>
                                                </div>
                                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($stu['name']); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($stu['class']); ?> (Roll: <?php echo htmlspecialchars($stu['roll_number']); ?>)</span></td>
                                        <td>
                                            <input type="number" name="present_days[<?php echo $s_id; ?>]" value="<?php echo $p_days; ?>" class="form-control form-control-sm text-center" min="0" max="100" required>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="badge-status <?php echo $badge_class; ?>">
                                                <?php echo number_format($att_rate, 1); ?>%
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5">
                                            <div class="empty-state">
                                                <i class="fas fa-users-slash"></i>
                                                <h5>No Enrolled Students</h5>
                                                <p>No student records found in the roster.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <?php if (count($students) > 0): ?>
                    <div class="text-end mb-4">
                        <button type="submit" name="save_attendance" class="btn btn-primary px-4 py-2">
                            <i class="fas fa-check-circle me-1"></i> Save Attendance Changes
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-calendar-check text-primary"></i>
                        <h5>Select a Teaching Subject</h5>
                        <p>Choose a course from the dropdown menu above to load the student attendance roster.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>