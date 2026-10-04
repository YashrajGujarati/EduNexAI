<?php
session_start();

/* =========================================================
   FACULTY SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "faculty") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$faculty_id = (int)$_SESSION['user_id'];

$message = "";
$message_type = "";
$selected_subject = (int)($_GET['subject_id'] ?? ($_POST['subject_id'] ?? 0));

/* Save Marks */
if (isset($_POST['save_marks'])) {
    $sub_id = (int)$_POST['subject_id'];
    $internal_arr = $_POST['internal_marks'] ?? [];
    $external_arr = $_POST['external_marks'] ?? [];

    $success_count = 0;

    foreach ($internal_arr as $student_id => $internal) {
        $student_id = (int)$student_id;
        $internal = (int)$internal;
        $external = (int)($external_arr[$student_id] ?? 0);
        $total = $internal + $external;

        /* Check existing record */
        $check = db_query($conn, "SELECT mark_id FROM marks WHERE student_id = '$student_id' AND subject_id = '$sub_id'");
        if (db_num_rows($check) > 0) {
            $m_row = db_fetch_assoc($check);
            $m_id = $m_row['mark_id'];
            db_query($conn, "UPDATE marks SET internal_marks = '$internal', external_marks = '$external', total_marks = '$total' WHERE mark_id = '$m_id'");
        } else {
            db_query($conn, "INSERT INTO marks (student_id, subject_id, internal_marks, external_marks, total_marks) VALUES ('$student_id', '$sub_id', '$internal', '$external', '$total')");
        }
        $success_count++;
    }

    $message = "Successfully recorded marks for $success_count students!";
    $message_type = "success";
}

/* Faculty Subjects */
$subject_query = db_query($conn, "SELECT subject_id, subject_name FROM subjects WHERE faculty_id = '$faculty_id' ORDER BY subject_name ASC");

/* Students for selected subject */
$students = [];
if ($selected_subject > 0) {
    $stu_query = db_query($conn, "
        SELECT
            s.student_id,
            u.name,
            s.class,
            s.roll_number,
            m.internal_marks,
            m.external_marks,
            m.total_marks
        FROM students s
        INNER JOIN users u ON s.user_id = u.id
        LEFT JOIN marks m ON s.student_id = m.student_id AND m.subject_id = '$selected_subject'
        ORDER BY u.name ASC
    ");
    if ($stu_query) {
        while ($row = db_fetch_assoc($stu_query)) {
            $students[] = $row;
        }
    }
}

$page_title = "Manage Marks";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Marks | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
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
                            <?php while ($sub = db_fetch_assoc($subject_query)): ?>
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
                        <span>Load Student Roster</span>
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
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square text-primary me-2"></i>Enter & Update Student Marks</h5>
                            <small class="text-muted">Enter internal (Max 40) and external (Max 60) scores for enrolled students</small>
                        </div>
                        <button type="submit" name="save_marks" class="btn btn-success btn-sm">
                            <i class="fas fa-save me-1"></i> Save All Marks
                        </button>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Student ID</th>
                                        <th>Student Name</th>
                                        <th>Class / Roll</th>
                                        <th style="width: 160px;">Internal (Max 40)</th>
                                        <th style="width: 160px;">External (Max 60)</th>
                                        <th class="text-end pe-4">Total Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $stu): 
                                        $s_id = $stu['student_id'];
                                        $internal_val = (int)($stu['internal_marks'] ?? 0);
                                        $external_val = (int)($stu['external_marks'] ?? 0);
                                        $total_val = $internal_val + $external_val;
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
                                            <input type="number" name="internal_marks[<?php echo $s_id; ?>]" value="<?php echo $internal_val; ?>" class="form-control form-control-sm text-center" min="0" max="40" required>
                                        </td>
                                        <td>
                                            <input type="number" name="external_marks[<?php echo $s_id; ?>]" value="<?php echo $external_val; ?>" class="form-control form-control-sm text-center" min="0" max="60" required>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fw-bold fs-6 <?php echo ($total_val >= 40) ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $total_val; ?> / 100
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state">
                                                <i class="fas fa-users-slash"></i>
                                                <h5>No Enrolled Students</h5>
                                                <p>No student records found in the system roster.</p>
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
                        <button type="submit" name="save_marks" class="btn btn-primary px-4 py-2">
                            <i class="fas fa-check-circle me-1"></i> Save Marks Changes
                        </button>
                    </div>
                <?php endif; ?>
            </form>
        <?php else: ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="empty-state">
                        <i class="fas fa-book-open text-primary"></i>
                        <h5>Select a Teaching Subject</h5>
                        <p>Choose a course from the dropdown menu above to load the student marks entry roster.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>