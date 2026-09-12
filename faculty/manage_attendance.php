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

/* Ensure attendance_logs table exists for topic tracking */
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS attendance_logs (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        subject_id INT NOT NULL,
        faculty_id INT NOT NULL,
        topic VARCHAR(255) DEFAULT NULL,
        attendance_date DATE DEFAULT CURRENT_DATE,
        total_students INT DEFAULT 0,
        present_count INT DEFAULT 0,
        absent_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

$message = "";
$message_type = "";
$selected_subject = (int)($_GET['subject_id'] ?? ($_POST['subject_id'] ?? 0));
$lecture_topic = trim($_POST['topic'] ?? '');

/* Save Attendance Submission */
if (isset($_POST['save_attendance'])) {
    $sub_id = (int)$_POST['subject_id'];
    $status_arr = $_POST['attendance_status'] ?? [];
    $topic = trim($_POST['topic'] ?? 'General Session');

    // Get subject name
    $sub_name_res = mysqli_query($conn, "SELECT subject_name FROM subjects WHERE subject_id = '$sub_id'");
    $sub_row = mysqli_fetch_assoc($sub_name_res);
    $sub_name = $sub_row['subject_name'] ?? 'Course';

    $present_count = 0;
    $absent_count = 0;
    $total_students_processed = 0;

    foreach ($status_arr as $student_id => $st) {
        $student_id = (int)$student_id;
        // Unmarked or unchecked students become Absent upon submission (Rule #10 & #12)
        $is_present = ($st === 'present');

        if ($is_present) {
            $present_count++;
        } else {
            $absent_count++;
        }
        $total_students_processed++;

        // Fetch existing attendance record
        $check = mysqli_query($conn, "SELECT attendance_id, present_days, total_days FROM attendance WHERE student_id = '$student_id' AND subject_id = '$sub_id'");
        if ($check && mysqli_num_rows($check) > 0) {
            $att_row = mysqli_fetch_assoc($check);
            $att_id = $att_row['attendance_id'];
            $new_total = (int)$att_row['total_days'] + 1;
            $new_present = (int)$att_row['present_days'] + ($is_present ? 1 : 0);
            $pct = round(($new_present / $new_total) * 100, 2);

            mysqli_query($conn, "UPDATE attendance SET present_days = '$new_present', total_days = '$new_total', percentage = '$pct' WHERE attendance_id = '$att_id'");
        } else {
            $new_total = 1;
            $new_present = ($is_present ? 1 : 0);
            $pct = round(($new_present / $new_total) * 100, 2);

            mysqli_query($conn, "INSERT INTO attendance (student_id, subject_id, present_days, total_days, percentage) VALUES ('$student_id', '$sub_id', '$new_present', '$new_total', '$pct')");
        }

        // Recalculate student overall attendance percentage across all subjects
        $avg_query = mysqli_query($conn, "SELECT AVG(percentage) as avg_pct FROM attendance WHERE student_id = '$student_id'");
        $avg_row = mysqli_fetch_assoc($avg_query);
        $overall_pct = round((float)($avg_row['avg_pct'] ?? $pct), 2);
        mysqli_query($conn, "UPDATE students SET attendance = '$overall_pct' WHERE student_id = '$student_id'");
    }

    // Log attendance session details
    mysqli_query($conn, "INSERT INTO attendance_logs (subject_id, faculty_id, topic, attendance_date, total_students, present_count, absent_count) VALUES ('$sub_id', '$faculty_id', '" . mysqli_real_escape_string($conn, $topic) . "', NOW(), '$total_students_processed', '$present_count', '$absent_count')");

    $message = "Attendance saved successfully for <strong>" . htmlspecialchars($sub_name) . "</strong>" . ($topic ? " – <em>" . htmlspecialchars($topic) . "</em>" : "") . "! (" . $present_count . " Present, " . $absent_count . " Absent)";
    $message_type = "success";
}

/* Faculty Subjects */
$subject_query = mysqli_query($conn, "SELECT subject_id, subject_name FROM subjects WHERE faculty_id = '$faculty_id' ORDER BY subject_name ASC");
$subjects_list = [];
if ($subject_query) {
    while ($s = mysqli_fetch_assoc($subject_query)) {
        $subjects_list[] = $s;
    }
}

/* Students for selected subject */
$students = [];
$selected_subject_name = "";
if ($selected_subject > 0) {
    foreach ($subjects_list as $sub) {
        if ((int)$sub['subject_id'] === $selected_subject) {
            $selected_subject_name = $sub['subject_name'];
            break;
        }
    }

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
        ORDER BY CAST(s.roll_number AS UNSIGNED) ASC, u.name ASC
    ");
    if ($stu_query) {
        while ($row = mysqli_fetch_assoc($stu_query)) {
            $students[] = $row;
        }
    }
}

$page_title = "Faculty Attendance Management";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Attendance Portal | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .topic-datalist-wrapper {
            position: relative;
        }
        .roster-search-input {
            border-radius: 30px;
            padding-left: 40px;
        }
        .roster-search-wrapper {
            position: relative;
        }
        .roster-search-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
        }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <!-- Hero Header -->
        <div class="hero-banner mb-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <span class="badge bg-white text-primary px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">Live Classroom Portal</span>
                    <h3><i class="fas fa-clipboard-user me-2"></i>Faculty Attendance Management</h3>
                    <p>Mark live student attendance seamlessly during lectures, track subjects & topics, and perform instant roll-number bulk entries.</p>
                </div>
                <div class="text-end">
                    <span class="d-inline-flex align-items-center gap-2 bg-white bg-opacity-15 px-3 py-2 rounded-3 text-white small fw-bold">
                        <i class="fas fa-calendar-day text-info"></i> Today: <?php echo date("F j, Y"); ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert">
                <i class="fas fa-circle-check me-2 fs-5 align-middle text-<?php echo $message_type; ?>"></i>
                <span><?php echo $message; ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Class Details & Subject Selector Card -->
        <div class="search-filter-card mb-4">
            <form method="GET" id="subjectForm" class="row g-3 align-items-end">
                <div class="col-lg-6 col-md-7">
                    <label class="form-label fw-bold text-dark d-flex align-items-center mb-2">
                        <i class="fas fa-book-bookmark text-primary me-2"></i>Select Teaching Subject <span class="text-danger ms-1">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-book-open text-primary"></i></span>
                        <select name="subject_id" class="form-select fw-semibold" onchange="this.form.submit()" required>
                            <option value="">-- Select Course Subject --</option>
                            <?php foreach ($subjects_list as $sub): ?>
                                <option value="<?php echo $sub['subject_id']; ?>" <?php echo ($selected_subject == $sub['subject_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sub['subject_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-lg-6 col-md-5">
                    <?php if ($selected_subject > 0): ?>
                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6 px-3 py-2 rounded-pill">
                                <i class="fas fa-users me-1"></i> <?php echo count($students); ?> Enrolled Students
                            </span>
                            <a href="manage_attendance.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                <i class="fas fa-rotate me-1"></i> Change Subject
                            </a>
                        </div>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-users-viewfinder"></i>
                            <span>Load Attendance Roster</span>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if ($selected_subject > 0): ?>
            <!-- LIVE ATTENDANCE SUMMARY HEADER CARDS (Requirement #7) -->
            <div class="row g-3 mb-4">
                <div class="col-xl-2 col-md-4 col-6">
                    <div class="att-stat-card border-start border-4 border-primary">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="att-stat-label">Total Students</span>
                            <div class="att-stat-icon icon-blue"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="att-stat-val text-dark" id="statTotal"><?php echo count($students); ?></div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <div class="att-stat-card border-start border-4 border-success">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="att-stat-label">Present</span>
                            <div class="att-stat-icon icon-green"><i class="fas fa-user-check"></i></div>
                        </div>
                        <div class="att-stat-val text-success" id="statPresent">0</div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-6">
                    <div class="att-stat-card border-start border-4 border-danger">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="att-stat-label">Absent</span>
                            <div class="att-stat-icon icon-orange" style="background:#fef2f2; color:#ef4444; border-color:#fca5a5;"><i class="fas fa-user-xmark"></i></div>
                        </div>
                        <div class="att-stat-val text-danger" id="statAbsent">0</div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-6">
                    <div class="att-stat-card border-start border-4 border-warning">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="att-stat-label">Not Marked</span>
                            <div class="att-stat-icon icon-orange"><i class="fas fa-clock"></i></div>
                        </div>
                        <div class="att-stat-val text-muted" id="statUnmarked"><?php echo count($students); ?></div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 col-12">
                    <div class="att-stat-card border-start border-4 border-purple">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="att-stat-label">Session Rate</span>
                            <div class="att-stat-icon icon-purple"><i class="fas fa-chart-pie"></i></div>
                        </div>
                        <div class="att-stat-val text-purple" style="color:#8b5cf6;" id="statRate">0.0%</div>
                    </div>
                </div>
            </div>

            <!-- MAIN ATTENDANCE FORM -->
            <form method="POST" id="attendanceMainForm">
                <input type="hidden" name="subject_id" value="<?php echo $selected_subject; ?>">

                <!-- Class Details & Topic Section (Requirement #6) -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-layer-group text-primary me-2"></i>Class Session Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Current Subject</label>
                                <input type="text" class="form-control bg-light fw-bold text-primary" value="<?php echo htmlspecialchars($selected_subject_name); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Today's Lecture Topic <span class="text-muted font-normal">(Select or Type Custom Topic)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-heading text-primary"></i></span>
                                    <input type="text" name="topic" id="lectureTopic" list="topicSuggestions" class="form-control fw-semibold" placeholder="e.g. Machine Learning – Regression & Classification" required>
                                    <datalist id="topicSuggestions">
                                        <option value="Machine Learning – Linear & Logistic Regression">
                                        <option value="Neural Networks & Deep Learning Architectures">
                                        <option value="Database Management – SQL Normalization & Indexing">
                                        <option value="Data Structures – Binary Search Trees & Graphs">
                                        <option value="Software Engineering – Design Patterns & Agile">
                                        <option value="Cloud Computing – Microservices & Kubernetes">
                                    </datalist>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STUDENT ATTENDANCE ROSTER TABLE CARD (Requirements #1, #2, #3, #10) -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <div>
                            <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-users-rectangle text-primary me-2"></i>Student Attendance Roster</h5>
                            <small class="text-muted">Unchecked students show neutral status initially. Click checkbox or dual buttons to mark Present/Absent.</small>
                        </div>

                        <!-- Bulk Action Toolbar & Search Filter (Requirement #11) -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="roster-search-wrapper" style="width: 200px;">
                                <i class="fas fa-search"></i>
                                <input type="text" id="rosterSearch" class="form-control form-control-sm roster-search-input" placeholder="Search student / roll...">
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill fw-bold" onclick="bulkMark('present')">
                                <i class="fas fa-check-double me-1"></i> All Present
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill fw-bold" onclick="bulkMark('absent')">
                                <i class="fas fa-user-xmark me-1"></i> All Absent
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="bulkMark('unmarked')">
                                <i class="fas fa-rotate-left me-1"></i> Reset
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="rosterTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4" style="width: 120px;">Roll No.</th>
                                        <th>Student Name</th>
                                        <th style="width: 240px;">Attendance Marking</th>
                                        <th class="text-end pe-4" style="width: 160px;">Live Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $stu): 
                                        $s_id = (int)$stu['student_id'];
                                        $roll = htmlspecialchars($stu['roll_number'] ?? $s_id);
                                        $name = htmlspecialchars($stu['name']);
                                        $cls = htmlspecialchars($stu['class'] ?? 'Class');
                                    ?>
                                    <tr id="row_<?php echo $s_id; ?>" data-roll="<?php echo strtolower($roll); ?>" data-name="<?php echo strtolower($name); ?>">
                                        <td class="ps-4">
                                            <span class="badge bg-light text-primary border border-primary-subtle fw-bold fs-6 px-3 py-1 rounded-pill">
                                                #<?php echo $roll; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="topbar-avatar me-3" style="width:36px; height:36px; font-size:13px;">
                                                    <?php echo strtoupper(substr($name, 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark mb-0"><?php echo $name; ?></div>
                                                    <small class="text-muted"><?php echo $cls; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <!-- Checkbox Interaction (Requirement #2) -->
                                                <input type="checkbox" 
                                                       id="chk_<?php echo $s_id; ?>" 
                                                       class="att-checkbox-lg" 
                                                       onchange="handleCheckboxToggle(<?php echo $s_id; ?>)" 
                                                       title="Check to mark Present">

                                                <!-- Dual Manual Buttons (Requirement #3) -->
                                                <div class="att-btn-group">
                                                    <button type="button" 
                                                            id="btn_p_<?php echo $s_id; ?>" 
                                                            class="att-toggle-btn" 
                                                            onclick="setStudentStatus(<?php echo $s_id; ?>, 'present')">
                                                        <i class="fas fa-check"></i> Present
                                                    </button>
                                                    <button type="button" 
                                                            id="btn_a_<?php echo $s_id; ?>" 
                                                            class="att-toggle-btn" 
                                                            onclick="setStudentStatus(<?php echo $s_id; ?>, 'absent')">
                                                        <i class="fas fa-xmark"></i> Absent
                                                    </button>
                                                </div>

                                                <!-- Hidden Status Input submitted to backend -->
                                                <input type="hidden" name="attendance_status[<?php echo $s_id; ?>]" id="status_<?php echo $s_id; ?>" value="unmarked">
                                            </div>
                                        </td>
                                        <td class="text-end pe-4">
                                            <!-- Initial Status Badge: Neutral / Not Marked (Requirement #1) -->
                                            <span id="badge_<?php echo $s_id; ?>" class="att-status-badge badge-unmarked">
                                                <i class="fas fa-minus text-muted"></i> —
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <i class="fas fa-users-slash text-muted"></i>
                                                <h5>No Students Found</h5>
                                                <p>No student records found for this course roster.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- QUICK ATTENDANCE ENTRY SECTION (Requirements #4, #5) -->
                <div class="quick-entry-card mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dashboard-icon icon-blue" style="width:40px; height:40px; font-size:16px;">
                                <i class="fas fa-bolt-lightning text-primary"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Quick Attendance Entry</h6>
                                <small class="text-muted">Enter multiple roll numbers separated by commas or spaces (e.g. <code>1, 2, 3, 7, 10</code> or <code>101 102 103</code>)</small>
                            </div>
                        </div>

                        <!-- Quick Attendance Mode (Requirement #5) -->
                        <div class="d-flex align-items-center gap-2">
                            <span class="small fw-bold text-muted">Apply As:</span>
                            <div class="quick-mode-selector">
                                <div class="quick-mode-option active-present" id="modePresentOpt" onclick="setQuickMode('present')">
                                    <i class="fas fa-circle-check me-1"></i> Present
                                </div>
                                <div class="quick-mode-option" id="modeAbsentOpt" onclick="setQuickMode('absent')">
                                    <i class="fas fa-circle-xmark me-1"></i> Absent
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center">
                        <div class="col-md-9">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-list-ol text-primary"></i></span>
                                <input type="text" id="quickRollInput" class="form-control form-control-lg fs-6" placeholder="Enter Roll Numbers: e.g. 1, 2, 3, 7, 10 or 101, 102, 103..." onkeypress="if(event.key === 'Enter'){ event.preventDefault(); applyQuickAttendance(); }">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-lg w-100 py-2 fs-6 fw-bold d-flex align-items-center justify-content-center gap-2" onclick="applyQuickAttendance()">
                                <i class="fas fa-wand-magic-sparkles"></i>
                                <span id="quickApplyBtnText">Mark Present</span>
                            </button>
                        </div>
                    </div>

                    <!-- Warning Alert area for invalid roll numbers (Requirement #4) -->
                    <div id="quickWarningBox" class="mt-3" style="display:none;"></div>
                </div>

                <!-- SAVE ATTENDANCE BUTTON & ACTIONS (Requirement #8) -->
                <?php if (count($students) > 0): ?>
                    <div class="card shadow-sm border-0 mb-4 bg-white p-3 text-end">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="text-start">
                                <span class="fw-bold text-dark d-block"><i class="fas fa-shield-halved text-success me-1"></i> Ready to Submit Attendance</span>
                                <small class="text-muted">Review summary statistics before saving session logs.</small>
                            </div>
                            <button type="button" class="btn btn-success btn-lg px-5 py-3 fw-bold rounded-pill shadow" onclick="openSaveConfirmationModal()">
                                <i class="fas fa-cloud-arrow-up me-2"></i> Save Attendance
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </form>

            <!-- CONFIRMATION MODAL (Requirement #8) -->
            <div class="modal fade" id="saveConfirmModal" tabindex="-1" aria-labelledby="saveConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
                        <div class="modal-header bg-gradient text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e40af 100%); border-top-left-radius: var(--radius-lg); border-top-right-radius: var(--radius-lg);">
                            <h5 class="modal-header-title fw-bold mb-0 text-white" id="saveConfirmModalLabel">
                                <i class="fas fa-file-signature text-info me-2"></i>Confirm Attendance Submission
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3 p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted fw-bold">Subject:</span>
                                    <span class="fw-bold text-dark" id="modalSubjectName"><?php echo htmlspecialchars($selected_subject_name); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted fw-bold">Lecture Topic:</span>
                                    <span class="fw-bold text-primary" id="modalTopicName">—</span>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-3"><i class="fas fa-chart-column me-2 text-primary"></i>Attendance Summary</h6>
                            <div class="row g-2 text-center mb-3">
                                <div class="col-4">
                                    <div class="p-2 border rounded bg-light">
                                        <small class="text-muted d-block font-uppercase fw-bold" style="font-size:10px;">TOTAL</small>
                                        <span class="fs-5 fw-bold text-dark" id="modalTotal">0</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border rounded bg-success-subtle border-success-subtle">
                                        <small class="text-success d-block font-uppercase fw-bold" style="font-size:10px;">PRESENT</small>
                                        <span class="fs-5 fw-bold text-success" id="modalPresent">0</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 border rounded bg-danger-subtle border-danger-subtle">
                                        <small class="text-danger d-block font-uppercase fw-bold" style="font-size:10px;">ABSENT</small>
                                        <span class="fs-5 fw-bold text-danger" id="modalAbsent">0</span>
                                    </div>
                                </div>
                            </div>

                            <div id="modalUnmarkedNote" class="alert alert-warning py-2 px-3 small border-warning mb-0" style="display:none;">
                                <i class="fas fa-triangle-exclamation me-1"></i> <span id="modalUnmarkedText">12 unmarked students will automatically be recorded as Absent upon saving.</span>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-3 border-top" style="border-bottom-left-radius: var(--radius-lg); border-bottom-right-radius: var(--radius-lg);">
                            <button type="button" class="btn btn-outline-secondary px-4 fw-bold rounded-pill" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-success px-4 fw-bold rounded-pill shadow" id="confirmSubmitBtn" onclick="submitAttendanceFinal()">
                                <i class="fas fa-check-circle me-1"></i> Confirm & Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- EMPTY STATE WHEN NO SUBJECT IS SELECTED (Requirement #13) -->
            <div class="card shadow-sm border-0 py-5">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="topbar-avatar mx-auto mb-3" style="width:70px; height:70px; font-size:30px;">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Please select a subject before marking attendance.</h4>
                        <p class="text-muted max-w-md mx-auto">Choose your course subject from the dropdown menu above to load the live student attendance roster.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* =========================================================
       FACULTY ATTENDANCE INTERACTION SYSTEM
    ========================================================= */
    let currentQuickMode = 'present';

    // Roster search filter
    const rosterSearch = document.getElementById('rosterSearch');
    if (rosterSearch) {
        rosterSearch.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#rosterTable tbody tr');
            rows.forEach(row => {
                const roll = row.getAttribute('data-roll') || '';
                const name = row.getAttribute('data-name') || '';
                if (roll.includes(query) || name.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    /**
     * Core function to update a single student's attendance state
     * Status options: 'present', 'absent', 'unmarked'
     */
    function setStudentStatus(studentId, status) {
        const chk = document.getElementById('chk_' + studentId);
        const btnP = document.getElementById('btn_p_' + studentId);
        const btnA = document.getElementById('btn_a_' + studentId);
        const input = document.getElementById('status_' + studentId);
        const badge = document.getElementById('badge_' + studentId);

        if (!input || !badge) return;

        input.value = status;

        if (status === 'present') {
            if (chk) chk.checked = true;
            if (btnP) btnP.classList.add('btn-present-active');
            if (btnA) btnA.classList.remove('btn-absent-active');
            badge.className = 'att-status-badge badge-present';
            badge.innerHTML = '<i class="fas fa-check-circle"></i> Present';
        } else if (status === 'absent') {
            if (chk) chk.checked = false;
            if (btnP) btnP.classList.remove('btn-present-active');
            if (btnA) btnA.classList.add('btn-absent-active');
            badge.className = 'att-status-badge badge-absent';
            badge.innerHTML = '<i class="fas fa-xmark"></i> Absent';
        } else {
            // Unmarked state (Requirement #1 & #12)
            if (chk) chk.checked = false;
            if (btnP) btnP.classList.remove('btn-present-active');
            if (btnA) btnA.classList.remove('btn-absent-active');
            badge.className = 'att-status-badge badge-unmarked';
            badge.innerHTML = '<i class="fas fa-minus text-muted"></i> —';
        }

        updateLiveSummary();
    }

    /**
     * Handle direct Checkbox toggle (Requirement #2)
     */
    function handleCheckboxToggle(studentId) {
        const chk = document.getElementById('chk_' + studentId);
        if (chk && chk.checked) {
            setStudentStatus(studentId, 'present');
        } else {
            setStudentStatus(studentId, 'unmarked');
        }
    }

    /**
     * Bulk Action helper (Mark All Present, Mark All Absent, Reset)
     */
    function bulkMark(status) {
        const inputs = document.querySelectorAll('input[id^="status_"]');
        inputs.forEach(inp => {
            const studentId = inp.id.replace('status_', '');
            setStudentStatus(studentId, status);
        });
    }

    /**
     * Quick Attendance Mode Toggle (Requirement #5)
     */
    function setQuickMode(mode) {
        currentQuickMode = mode;
        const optP = document.getElementById('modePresentOpt');
        const optA = document.getElementById('modeAbsentOpt');
        const btnText = document.getElementById('quickApplyBtnText');

        if (mode === 'present') {
            optP.className = 'quick-mode-option active-present';
            optA.className = 'quick-mode-option';
            if (btnText) btnText.innerText = 'Mark Present';
        } else {
            optP.className = 'quick-mode-option';
            optA.className = 'quick-mode-option active-absent';
            if (btnText) btnText.innerText = 'Mark Absent';
        }
    }

    /**
     * Parse and apply Quick Attendance Entry (Requirement #4)
     */
    function applyQuickAttendance() {
        const input = document.getElementById('quickRollInput');
        const warningBox = document.getElementById('quickWarningBox');
        if (!input) return;

        const rawVal = input.value.trim();
        if (!rawVal) {
            if (warningBox) {
                warningBox.style.display = 'block';
                warningBox.className = 'alert alert-info py-2 px-3 small mb-0';
                warningBox.innerHTML = '<i class="fas fa-circle-info me-1"></i> Please enter roll numbers (e.g., 1, 2, 3 or 101 102 103).';
            }
            return;
        }

        // Split by commas, spaces, semicolons, tabs
        const tokens = rawVal.split(/[,;\s]+/).map(t => t.trim().toLowerCase()).filter(t => t.length > 0);
        const rows = document.querySelectorAll('#rosterTable tbody tr');

        const foundRolls = new Set();
        const notFoundRolls = [];

        // Build mapping of roll number to student ID
        const rollToIdMap = {};
        rows.forEach(row => {
            const rVal = row.getAttribute('data-roll');
            const rowId = row.id.replace('row_', '');
            if (rVal && rowId) {
                rollToIdMap[rVal] = rowId;
            }
        });

        tokens.forEach(tok => {
            if (rollToIdMap[tok]) {
                foundRolls.add(tok);
                setStudentStatus(rollToIdMap[tok], currentQuickMode);
            } else {
                notFoundRolls.push(tok);
            }
        });

        if (warningBox) {
            warningBox.style.display = 'block';
            if (notFoundRolls.length > 0) {
                const formattedList = notFoundRolls.map(r => `<strong>${r}</strong>`).join(', ');
                warningBox.className = 'alert alert-warning py-2 px-3 small border-warning mb-0';
                warningBox.innerHTML = `<i class="fas fa-triangle-exclamation me-1"></i> Roll number(s) ${formattedList} were not found in this class roster. Applied ${foundRolls.size} student(s) successfully.`;
            } else {
                warningBox.className = 'alert alert-success py-2 px-3 small border-success mb-0';
                warningBox.innerHTML = `<i class="fas fa-circle-check me-1"></i> Successfully marked ${foundRolls.size} student(s) as ${currentQuickMode === 'present' ? 'Present' : 'Absent'}.`;
            }
        }

        input.value = '';
    }

    /**
     * Recalculate Live Attendance Summary Stats (Requirement #7)
     */
    function updateLiveSummary() {
        const inputs = document.querySelectorAll('input[id^="status_"]');
        let total = inputs.length;
        let present = 0;
        let absent = 0;
        let unmarked = 0;

        inputs.forEach(inp => {
            if (inp.value === 'present') {
                present++;
            } else if (inp.value === 'absent') {
                absent++;
            } else {
                unmarked++;
            }
        });

        const rate = total > 0 ? ((present / total) * 100).toFixed(1) : '0.0';

        const elTotal = document.getElementById('statTotal');
        const elPresent = document.getElementById('statPresent');
        const elAbsent = document.getElementById('statAbsent');
        const elUnmarked = document.getElementById('statUnmarked');
        const elRate = document.getElementById('statRate');

        if (elTotal) elTotal.innerText = total;
        if (elPresent) elPresent.innerText = present;
        if (elAbsent) elAbsent.innerText = absent;
        if (elUnmarked) elUnmarked.innerText = unmarked;
        if (elRate) elRate.innerText = rate + '%';
    }

    /**
     * Modal Confirmation before Save (Requirement #8)
     */
    function openSaveConfirmationModal() {
        const topicInput = document.getElementById('lectureTopic');
        if (!topicInput || !topicInput.value.trim()) {
            alert('Please enter or select today\'s lecture topic before saving attendance.');
            if (topicInput) topicInput.focus();
            return;
        }

        const inputs = document.querySelectorAll('input[id^="status_"]');
        let total = inputs.length;
        let present = 0;
        let absent = 0;
        let unmarked = 0;

        inputs.forEach(inp => {
            if (inp.value === 'present') present++;
            else if (inp.value === 'absent') absent++;
            else unmarked++;
        });

        // Unmarked count added to final Absent count for confirmation summary (Rule #10)
        let finalAbsent = absent + unmarked;

        document.getElementById('modalTopicName').innerText = topicInput.value.trim();
        document.getElementById('modalTotal').innerText = total;
        document.getElementById('modalPresent').innerText = present;
        document.getElementById('modalAbsent').innerText = finalAbsent;

        const note = document.getElementById('modalUnmarkedNote');
        const noteText = document.getElementById('modalUnmarkedText');
        if (unmarked > 0 && note && noteText) {
            note.style.display = 'block';
            noteText.innerText = `${unmarked} unmarked student(s) will automatically be recorded as Absent upon saving.`;
        } else if (note) {
            note.style.display = 'none';
        }

        const modalEl = new bootstrap.Modal(document.getElementById('saveConfirmModal'));
        modalEl.show();
    }

    /**
     * Submit Form with duplicate prevention (Requirement #8)
     */
    function submitAttendanceFinal() {
        const btn = document.getElementById('confirmSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving Attendance...';
        }

        // Add hidden submit flag
        const form = document.getElementById('attendanceMainForm');
        const hiddenSubmit = document.createElement('input');
        hiddenSubmit.type = 'hidden';
        hiddenSubmit.name = 'save_attendance';
        hiddenSubmit.value = '1';
        form.appendChild(hiddenSubmit);

        form.submit();
    }

    // Initialize summary on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateLiveSummary();
    });
</script>
</body>
</html>