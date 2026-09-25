<?php
session_start();

/* =========================================================
   STUDENT SECURITY
========================================================= */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit();
}

require_once("../config/db.php");
$user_id = (int)$_SESSION['user_id'];

/* Get Student Info */
$student_q = mysqli_query($conn, "SELECT student_id, class FROM students WHERE user_id = $user_id LIMIT 1");
if (!$student_q || mysqli_num_rows($student_q) == 0) {
    die("Student profile not found.");
}
$student_info = mysqli_fetch_assoc($student_q);
$student_id = (int)$student_info['student_id'];

$message = "";
$message_type = "";

// Ensure upload directory exists
$upload_dir = "../uploads/assignments/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* =========================================================
   HANDLE STUDENT ASSIGNMENT SUBMISSION
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $assignment_id = (int)$_POST['assignment_id'];
    $submission_text = trim(mysqli_real_escape_string($conn, $_POST['submission_text']));

    // Check assignment existence & due date
    $assign_q = mysqli_query($conn, "SELECT due_date FROM assignments WHERE assignment_id = $assignment_id LIMIT 1");
    if ($assign_q && mysqli_num_rows($assign_q) > 0) {
        $assign_data = mysqli_fetch_assoc($assign_q);
        $due_time = strtotime($assign_data['due_date']);
        $now_time = time();

        $status = ($now_time > $due_time) ? 'late' : 'submitted';

        $file_path = NULL;
        $file_upload_ok = true;

        if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['submission_file']['name'];
            $file_tmp = $_FILES['submission_file']['tmp_name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'txt', 'zip', 'png', 'jpg', 'jpeg'];

            if (in_array($ext, $allowed)) {
                $new_filename = "sub_" . $student_id . "_" . $assignment_id . "_" . time() . "." . $ext;
                $target_file = $upload_dir . $new_filename;
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $file_path = "uploads/assignments/" . $new_filename;
                } else {
                    $file_upload_ok = false;
                    $message = "Failed to upload submission file.";
                    $message_type = "danger";
                }
            } else {
                $file_upload_ok = false;
                $message = "Invalid file type. Allowed: PDF, DOC, DOCX, TXT, ZIP, PNG, JPG.";
                $message_type = "danger";
            }
        }

        if ($file_upload_ok) {
            // Check if student already submitted previously
            $chk_sub = mysqli_query($conn, "SELECT submission_id, file_path FROM assignment_submissions WHERE assignment_id = $assignment_id AND student_id = $student_id");
            if (mysqli_num_rows($chk_sub) > 0) {
                $existing_sub = mysqli_fetch_assoc($chk_sub);
                $sub_id = $existing_sub['submission_id'];
                
                // Keep existing file if no new file uploaded
                if (empty($file_path)) {
                    $file_path = $existing_sub['file_path'];
                } else if (!empty($existing_sub['file_path']) && file_exists("../" . $existing_sub['file_path'])) {
                    @unlink("../" . $existing_sub['file_path']);
                }

                $stmt = mysqli_prepare($conn, "UPDATE assignment_submissions SET submission_text = ?, file_path = ?, submitted_at = NOW(), status = ? WHERE submission_id = ?");
                mysqli_stmt_bind_param($stmt, "sssi", $submission_text, $file_path, $status, $sub_id);
                if (mysqli_stmt_execute($stmt)) {
                    $message = ($status === 'late') ? "Assignment submitted (Marked as Late Submission)." : "Assignment updated and submitted successfully!";
                    $message_type = ($status === 'late') ? "warning" : "success";
                } else {
                    $message = "Error updating submission: " . mysqli_error($conn);
                    $message_type = "danger";
                }
                mysqli_stmt_close($stmt);
            } else {
                $stmt = mysqli_prepare($conn, "INSERT INTO assignment_submissions (assignment_id, student_id, submission_text, file_path, status) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iisss", $assignment_id, $student_id, $submission_text, $file_path, $status);
                if (mysqli_stmt_execute($stmt)) {
                    $message = ($status === 'late') ? "Assignment submitted (Marked as Late Submission)." : "Assignment submitted successfully!";
                    $message_type = ($status === 'late') ? "warning" : "success";
                } else {
                    $message = "Error saving submission: " . mysqli_error($conn);
                    $message_type = "danger";
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

/* =========================================================
   FETCH ALL ASSIGNMENTS FOR STUDENT
========================================================= */
$assignments_sql = "
    SELECT 
        a.*,
        sub.subject_name,
        sub.subject_code,
        u_fac.name AS faculty_name,
        subm.submission_id,
        subm.submission_text,
        subm.file_path AS student_file,
        subm.submitted_at,
        subm.status AS submission_status,
        subm.marks_obtained,
        subm.feedback
    FROM assignments a
    JOIN subjects sub ON a.subject_id = sub.subject_id
    JOIN users u_fac ON a.faculty_id = u_fac.id
    LEFT JOIN assignment_submissions subm 
        ON a.assignment_id = subm.assignment_id AND subm.student_id = $student_id
    ORDER BY a.due_date ASC
";

$result_assignments = mysqli_query($conn, $assignments_sql);
$assignments_list = [];
$urgent_count = 0;
$pending_count = 0;
$now_time = time();

while ($row = mysqli_fetch_assoc($result_assignments)) {
    $due_timestamp = strtotime($row['due_date']);
    $diff_seconds = $due_timestamp - $now_time;
    $is_submitted = !empty($row['submission_id']);

    if (!$is_submitted) {
        $pending_count++;
        // Urgent if due within 48 hours
        if ($diff_seconds > 0 && $diff_seconds <= (48 * 3600)) {
            $urgent_count++;
        }
    }

    $row['due_timestamp'] = $due_timestamp;
    $assignments_list[] = $row;
}

$page_title = "My Assignments";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Assignments | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .assignment-card {
            transition: all 0.3s ease;
        }
        .assignment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
        }
        .urgent-pulse {
            animation: pulse-border 2s infinite;
        }
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content p-4">
        <?php include("header.php"); ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- NOTIFICATION BANNER FOR UPCOMING DUE DATES -->
        <?php if ($urgent_count > 0): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between urgent-pulse">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-3" style="width:48px; height:48px; font-size:20px;">
                        <i class="fas fa-bell fa-bounce"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-danger">Upcoming Due Date Warning!</h5>
                        <p class="mb-0 small text-dark">You have <strong><?php echo $urgent_count; ?> pending assignment(s)</strong> due within the next 48 hours. Please complete and submit before the deadline!</p>
                    </div>
                </div>
                <a href="#pending-section" class="btn btn-danger rounded-pill px-4 btn-sm d-none d-md-inline-block">Submit Now</a>
            </div>
        <?php elseif ($pending_count > 0): ?>
            <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center me-3" style="width:44px; height:44px; font-size:18px;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Pending Assignments Reminder</h6>
                        <p class="mb-0 small text-muted">You have <strong><?php echo $pending_count; ?> assignment(s)</strong> awaiting submission.</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" style="width:44px; height:44px; font-size:18px;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1 text-success">All Caught Up!</h6>
                    <p class="mb-0 small text-muted">Great job! You have submitted all your assigned coursework.</p>
                </div>
            </div>
        <?php endif; ?>

        <!-- ASSIGNMENT LIST HEADER -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1"><i class="fas fa-tasks text-primary me-2"></i>Course Assignments</h3>
                <p class="text-muted mb-0">View assigned tasks, download faculty reference files, and submit your completed work.</p>
            </div>
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2">
                    <i class="fas fa-folder-open me-1"></i> Total: <?php echo count($assignments_list); ?>
                </span>
            </div>
        </div>

        <!-- ASSIGNMENTS CARDS GRID -->
        <div class="row g-4" id="pending-section">
            <?php if (empty($assignments_list)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                        <div class="card-body">
                            <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mb-3" style="width:70px; height:70px; font-size:28px;">
                                <i class="fas fa-folder-minus"></i>
                            </div>
                            <h4>No Assignments Posted Yet</h4>
                            <p class="text-muted">Your faculty members have not published any assignments for your subjects yet.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($assignments_list as $assign): ?>
                    <?php 
                        $is_submitted = !empty($assign['submission_id']);
                        $is_graded = ($assign['submission_status'] === 'graded');
                        $due_passed = ($assign['due_timestamp'] < $now_time);
                        $is_urgent = (!$is_submitted && !$due_passed && ($assign['due_timestamp'] - $now_time <= 48 * 3600));

                        // Card Border style
                        $border_class = "border-start border-4 ";
                        if ($is_graded) {
                            $border_class .= "border-success";
                        } else if ($is_submitted) {
                            $border_class .= "border-info";
                        } else if ($due_passed) {
                            $border_class .= "border-danger";
                        } else if ($is_urgent) {
                            $border_class .= "border-warning";
                        } else {
                            $border_class .= "border-primary";
                        }
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 assignment-card <?php echo $border_class; ?> p-3">
                            <div class="card-body d-flex flex-column justify-content-between p-2">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">
                                            <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($assign['subject_name']); ?>
                                        </span>
                                        
                                        <!-- STATUS BADGES -->
                                        <?php if ($is_graded): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                                <i class="fas fa-award me-1"></i>Graded
                                            </span>
                                        <?php elseif ($is_submitted): ?>
                                            <?php if ($assign['submission_status'] === 'late'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 small">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Submitted Late
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small">
                                                    <i class="fas fa-check-circle me-1"></i>Submitted
                                                </span>
                                            <?php endif; ?>
                                        <?php elseif ($due_passed): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 small">
                                                <i class="fas fa-triangle-exclamation me-1"></i>Overdue
                                            </span>
                                        <?php elseif ($is_urgent): ?>
                                            <span class="badge bg-danger text-white rounded-pill px-2 py-1 small">
                                                <i class="fas fa-bell me-1"></i>Due Soon!
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <h5 class="fw-bold text-dark mt-2 mb-1"><?php echo htmlspecialchars($assign['title']); ?></h5>
                                    <small class="text-muted d-block mb-3"><i class="fas fa-chalkboard-user me-1"></i>Faculty: <?php echo htmlspecialchars($assign['faculty_name']); ?></small>
                                    
                                    <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php echo htmlspecialchars($assign['description'] ?? 'No description provided.'); ?>
                                    </p>
                                </div>

                                <div>
                                    <!-- ASSIGNMENT DETAILS BOX -->
                                    <div class="bg-light rounded-3 p-3 mb-3 border">
                                        <div class="d-flex justify-content-between text-muted small mb-1">
                                            <span><i class="fas fa-calendar-alt text-warning me-1"></i>Due Date:</span>
                                            <strong class="<?php echo ($due_passed && !$is_submitted) ? 'text-danger fw-bold' : 'text-dark'; ?>">
                                                <?php echo date('M d, Y - h:i A', strtotime($assign['due_date'])); ?>
                                            </strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-muted small mb-1">
                                            <span><i class="fas fa-trophy text-info me-1"></i>Max Marks:</span>
                                            <strong class="text-dark"><?php echo $assign['total_marks']; ?> Marks</strong>
                                        </div>

                                        <?php if (!empty($assign['file_path'])): ?>
                                            <div class="mt-2 pt-2 border-top">
                                                <a href="../<?php echo htmlspecialchars($assign['file_path']); ?>" target="_blank" class="small text-decoration-none fw-bold text-primary">
                                                    <i class="fas fa-paperclip me-1"></i> Download Faculty Reference File
                                                </a>
                                            </div>
                                        <?php endif; ?>

                                        <!-- IF GRADED -->
                                        <?php if ($is_graded): ?>
                                            <div class="mt-2 pt-2 border-top bg-success-subtle p-2 rounded border border-success-subtle">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="small fw-bold text-success"><i class="fas fa-star me-1"></i>Score Obtained:</span>
                                                    <span class="badge bg-success text-white fs-6"><?php echo $assign['marks_obtained']; ?> / <?php echo $assign['total_marks']; ?></span>
                                                </div>
                                                <?php if (!empty($assign['feedback'])): ?>
                                                    <small class="d-block text-dark mt-1 italic">
                                                        <strong>Faculty Comment:</strong> "<?php echo htmlspecialchars($assign['feedback']); ?>"
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- ACTION BUTTON -->
                                    <div>
                                        <button class="btn btn-<?php echo ($is_submitted ? 'outline-primary' : ($due_passed ? 'outline-danger' : 'primary')); ?> rounded-pill w-100 py-2" data-bs-toggle="modal" data-bs-target="#submitModal<?php echo $assign['assignment_id']; ?>">
                                            <i class="fas fa-<?php echo ($is_submitted ? 'edit' : 'upload'); ?> me-1"></i>
                                            <?php echo ($is_submitted ? 'View / Re-submit Solution' : ($due_passed ? 'Late Submit Solution' : 'Submit Assignment')); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SUBMISSION MODAL -->
                        <div class="modal fade" id="submitModal<?php echo $assign['assignment_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow rounded-4">
                                    <form method="POST" action="assignments.php" enctype="multipart/form-data">
                                        <input type="hidden" name="assignment_id" value="<?php echo $assign['assignment_id']; ?>">
                                        <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                                            <h5 class="modal-title fw-bold text-dark">
                                                <i class="fas fa-upload text-primary me-2"></i>Submit Assignment
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <h6 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($assign['title']); ?></h6>
                                            <p class="text-muted small mb-3">Due: <?php echo date('M d, Y - h:i A', strtotime($assign['due_date'])); ?></p>

                                            <?php if ($due_passed): ?>
                                                <div class="alert alert-danger py-2 small mb-3">
                                                    <i class="fas fa-exclamation-triangle me-1"></i> Notice: The due date has passed. Your submission will be flagged as <strong>Late</strong>.
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($is_submitted && !empty($assign['student_file'])): ?>
                                                <div class="bg-light p-2 rounded mb-3 border small">
                                                    <i class="fas fa-file-circle-check text-success me-1"></i> Previously Uploaded: 
                                                    <a href="../<?php echo htmlspecialchars($assign['student_file']); ?>" target="_blank" class="fw-bold text-primary">Download File</a>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Upload Solution File (PDF, DOC, ZIP, Image)</label>
                                                <input type="file" name="submission_file" class="form-control rounded-3" accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Submission Notes / Written Answer (Optional)</label>
                                                <textarea name="submission_text" class="form-control rounded-3" rows="3" placeholder="Enter any comments, link to github, or written notes for faculty..."><?php echo htmlspecialchars($assign['submission_text'] ?? ''); ?></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                            <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="submit_assignment" class="btn btn-primary rounded-pill px-4">
                                                <i class="fas fa-paper-plane me-1"></i> Confirm & Submit
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> <!-- End col-md-6 col-lg-4 -->
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
