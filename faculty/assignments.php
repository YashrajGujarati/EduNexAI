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

// Ensure upload directory exists
$upload_dir = "../uploads/assignments/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

/* =========================================================
   HANDLE ASSIGNMENT CREATION
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_assignment'])) {
    $subject_id = (int)$_POST['subject_id'];
    $title = trim(mysqli_real_escape_string($conn, $_POST['title']));
    $description = trim(mysqli_real_escape_string($conn, $_POST['description']));
    $due_date = trim($_POST['due_date']);
    $total_marks = (int)($_POST['total_marks'] ?? 100);

    $file_path = NULL;

    // File upload handling
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['assignment_file']['name'];
        $file_tmp = $_FILES['assignment_file']['tmp_name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'txt', 'zip', 'png', 'jpg', 'jpeg'];

        if (in_array($ext, $allowed)) {
            $new_filename = "assign_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            $target_file = $upload_dir . $new_filename;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $file_path = "uploads/assignments/" . $new_filename;
            }
        } else {
            $message = "Invalid file type. Allowed: PDF, DOC, DOCX, TXT, ZIP, PNG, JPG.";
            $message_type = "danger";
        }
    }

    if (empty($title) || $subject_id <= 0 || empty($due_date)) {
        $message = "Please fill in all required fields (Subject, Title, Due Date).";
        $message_type = "danger";
    } else if (empty($message)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO assignments (title, description, subject_id, faculty_id, due_date, total_marks, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiisis", $title, $description, $subject_id, $faculty_id, $due_date, $total_marks, $file_path);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Assignment created successfully! Students will be notified of the deadline.";
            $message_type = "success";
        } else {
            $message = "Failed to create assignment: " . mysqli_error($conn);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt);
    }
}

/* =========================================================
   HANDLE DELETE ASSIGNMENT
========================================================= */
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = (int)$_GET['id'];

    // Check ownership
    $check = mysqli_query($conn, "SELECT file_path FROM assignments WHERE assignment_id = $del_id AND faculty_id = $faculty_id");
    if ($check && mysqli_num_rows($check) > 0) {
        $row = mysqli_fetch_assoc($check);
        if (!empty($row['file_path']) && file_exists("../" . $row['file_path'])) {
            @unlink("../" . $row['file_path']);
        }
        mysqli_query($conn, "DELETE FROM assignments WHERE assignment_id = $del_id");
        $message = "Assignment deleted successfully.";
        $message_type = "success";
    }
}

/* =========================================================
   HANDLE GRADING SUBMISSION
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['grade_submission'])) {
    $submission_id = (int)$_POST['submission_id'];
    $marks_obtained = (int)$_POST['marks_obtained'];
    $feedback = trim(mysqli_real_escape_string($conn, $_POST['feedback']));

    $update_stmt = mysqli_prepare($conn, "UPDATE assignment_submissions SET marks_obtained = ?, feedback = ?, status = 'graded' WHERE submission_id = ?");
    mysqli_stmt_bind_param($update_stmt, "isi", $marks_obtained, $feedback, $submission_id);
    if (mysqli_stmt_execute($update_stmt)) {
        $message = "Grade and feedback saved successfully!";
        $message_type = "success";
    } else {
        $message = "Error saving grade: " . mysqli_error($conn);
        $message_type = "danger";
    }
    mysqli_stmt_close($update_stmt);
}

/* =========================================================
   FETCH FACULTY SUBJECTS
========================================================= */
$subject_query = mysqli_query($conn, "SELECT subject_id, subject_name, subject_code FROM subjects WHERE faculty_id = $faculty_id ORDER BY subject_name ASC");
$faculty_subjects = [];
while ($sub = mysqli_fetch_assoc($subject_query)) {
    $faculty_subjects[] = $sub;
}

/* =========================================================
   FETCH FACULTY ASSIGNMENTS
========================================================= */
$assignments_query = mysqli_query($conn, "
    SELECT 
        a.*, 
        sub.subject_name, 
        sub.subject_code,
        (SELECT COUNT(*) FROM assignment_submissions sub_m WHERE sub_m.assignment_id = a.assignment_id) AS submitted_count
    FROM assignments a
    JOIN subjects sub ON a.subject_id = sub.subject_id
    WHERE a.faculty_id = $faculty_id
    ORDER BY a.created_at DESC
");

$selected_assignment_id = isset($_GET['view']) ? (int)$_GET['view'] : 0;
$view_assignment = null;
$submissions_list = [];

if ($selected_assignment_id > 0) {
    // Fetch details of selected assignment
    $val_query = mysqli_query($conn, "
        SELECT a.*, sub.subject_name, sub.subject_code 
        FROM assignments a 
        JOIN subjects sub ON a.subject_id = sub.subject_id 
        WHERE a.assignment_id = $selected_assignment_id AND a.faculty_id = $faculty_id
    ");
    if ($val_query && mysqli_num_rows($val_query) > 0) {
        $view_assignment = mysqli_fetch_assoc($val_query);

        // Fetch submissions from all students
        $sub_sql = "
            SELECT 
                st.student_id,
                u.name AS student_name,
                u.email AS student_email,
                st.roll_number,
                st.class,
                subm.submission_id,
                subm.submission_text,
                subm.file_path AS student_file,
                subm.submitted_at,
                subm.status,
                subm.marks_obtained,
                subm.feedback
            FROM students st
            JOIN users u ON st.user_id = u.id
            LEFT JOIN assignment_submissions subm 
                ON st.student_id = subm.student_id AND subm.assignment_id = $selected_assignment_id
            ORDER BY u.name ASC
        ";
        $sub_res = mysqli_query($conn, $sub_sql);
        while ($r = mysqli_fetch_assoc($sub_res)) {
            $submissions_list[] = $r;
        }
    }
}

$page_title = "Manage Assignments";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .assignment-card {
            transition: all 0.3s ease;
            border-left: 4px solid var(--bs-primary);
        }
        .assignment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
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
                <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($view_assignment): ?>
            <!-- VIEW SUBMISSIONS FOR A SPECIFIC ASSIGNMENT -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <a href="assignments.php" class="btn btn-outline-secondary btn-sm rounded-pill mb-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Assignments List
                    </a>
                    <h3 class="fw-bold mb-1">
                        <i class="fas fa-tasks text-primary me-2"></i>
                        Submissions for: <?php echo htmlspecialchars($view_assignment['title']); ?>
                    </h3>
                    <div class="text-muted small">
                        <span class="badge bg-primary-subtle text-primary me-2"><i class="fas fa-book me-1"></i><?php echo htmlspecialchars($view_assignment['subject_name']); ?></span>
                        <span class="me-3"><i class="fas fa-calendar-alt text-warning me-1"></i>Due: <?php echo date('M d, Y - h:i A', strtotime($view_assignment['due_date'])); ?></span>
                        <span><i class="fas fa-trophy text-success me-1"></i>Max Marks: <?php echo $view_assignment['total_marks']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Submissions Table Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-users text-info me-2"></i>Student Submissions Status</h5>
                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                        Total Students: <?php echo count($submissions_list); ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Student</th>
                                    <th>Roll No / Class</th>
                                    <th>Submission Status</th>
                                    <th>Submitted On</th>
                                    <th>Submitted File / Text</th>
                                    <th>Grade & Feedback</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions_list)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle me-1"></i> No students registered for this course yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($submissions_list as $sub): ?>
                                        <tr>
                                            <td class="ps-4 fw-bold">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-2" style="width:36px; height:36px; font-size:14px;">
                                                        <i class="fas fa-user-graduate"></i>
                                                    </div>
                                                    <div>
                                                        <div><?php echo htmlspecialchars($sub['student_name']); ?></div>
                                                        <small class="text-muted fw-normal"><?php echo htmlspecialchars($sub['student_email']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($sub['roll_number'] ?? 'N/A'); ?></span>
                                                <small class="d-block text-muted"><?php echo htmlspecialchars($sub['class'] ?? ''); ?></small>
                                            </td>
                                            <td>
                                                <?php if (!$sub['submission_id']): ?>
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-clock me-1"></i>Not Submitted</span>
                                                <?php elseif ($sub['status'] === 'graded'): ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Graded</span>
                                                <?php elseif (strtotime($sub['submitted_at']) > strtotime($view_assignment['due_date'])): ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Late Submitted</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1"><i class="fas fa-upload me-1"></i>Submitted</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $sub['submitted_at'] ? date('M d, Y h:i A', strtotime($sub['submitted_at'])) : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($sub['student_file'])): ?>
                                                    <a href="../<?php echo htmlspecialchars($sub['student_file']); ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill mb-1">
                                                        <i class="fas fa-paperclip me-1"></i> Attachment
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (!empty($sub['submission_text'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill mb-1" data-bs-toggle="popover" title="Submission Notes" data-bs-content="<?php echo htmlspecialchars($sub['submission_text']); ?>">
                                                        <i class="fas fa-file-lines me-1"></i> Notes
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (empty($sub['student_file']) && empty($sub['submission_text'])): ?>
                                                    <span class="text-muted small">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($sub['status'] === 'graded'): ?>
                                                    <div class="fw-bold text-success">
                                                        <?php echo $sub['marks_obtained']; ?> / <?php echo $view_assignment['total_marks']; ?>
                                                    </div>
                                                    <?php if (!empty($sub['feedback'])): ?>
                                                        <small class="text-muted d-block text-truncate" style="max-width:180px;" title="<?php echo htmlspecialchars($sub['feedback']); ?>">
                                                            "<?php echo htmlspecialchars($sub['feedback']); ?>"
                                                        </small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted small">Not Graded</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-4 text-end">
                                                <?php if ($sub['submission_id']): ?>
                                                    <button class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#gradeModal<?php echo $sub['submission_id']; ?>">
                                                        <i class="fas fa-pen-to-square me-1"></i> <?php echo ($sub['status'] === 'graded') ? 'Edit Grade' : 'Grade'; ?>
                                                    </button>

                                                    <!-- Grade Modal -->
                                                    <div class="modal fade text-start" id="gradeModal<?php echo $sub['submission_id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content rounded-4 border-0 shadow">
                                                                <form method="POST" action="">
                                                                    <input type="hidden" name="submission_id" value="<?php echo $sub['submission_id']; ?>">
                                                                    <div class="modal-header border-0 bg-light rounded-top-4 py-3">
                                                                        <h5 class="modal-title fw-bold text-dark">
                                                                            <i class="fas fa-graduation-cap text-primary me-2"></i>Grade Submission: <?php echo htmlspecialchars($sub['student_name']); ?>
                                                                        </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                    </div>
                                                                    <div class="modal-body p-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Marks Obtained (Out of <?php echo $view_assignment['total_marks']; ?>)</label>
                                                                            <input type="number" name="marks_obtained" class="form-control rounded-3" min="0" max="<?php echo $view_assignment['total_marks']; ?>" value="<?php echo $sub['marks_obtained'] ?? 0; ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label fw-bold">Faculty Feedback / Comments</label>
                                                                            <textarea name="feedback" class="form-control rounded-3" rows="3" placeholder="Enter remarks or feedback for the student..."><?php echo htmlspecialchars($sub['feedback'] ?? ''); ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                                                        <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                                                        <button type="submit" name="grade_submission" class="btn btn-primary rounded-pill px-4">
                                                                            <i class="fas fa-save me-1"></i> Save Grade
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted small">No Submission</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <!-- MAIN ASSIGNMENTS DASHBOARD -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h3 class="fw-bold mb-1"><i class="fas fa-list-check text-primary me-2"></i>Assignments Management</h3>
                    <p class="text-muted mb-0">Create new course assignments, set due dates, and grade student submissions.</p>
                </div>
                <div>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                        <i class="fas fa-plus-circle me-2"></i>Create New Assignment
                    </button>
                </div>
            </div>

            <!-- List of Assignments -->
            <div class="row g-4">
                <?php if (mysqli_num_rows($assignments_query) == 0): ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 text-center py-5">
                            <div class="card-body">
                                <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:70px; height:70px; font-size:28px;">
                                    <i class="fas fa-folder-open"></i>
                                </div>
                                <h4>No Assignments Created Yet</h4>
                                <p class="text-muted mb-4">You haven't posted any assignments for your subjects yet. Click below to create one!</p>
                                <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#createAssignmentModal">
                                    <i class="fas fa-plus me-1"></i> Add Assignment Now
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php while ($assign = mysqli_fetch_assoc($assignments_query)): ?>
                        <?php 
                            $is_expired = (strtotime($assign['due_date']) < time());
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 assignment-card p-3">
                                <div class="card-body d-flex flex-column justify-content-between p-2">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">
                                                <i class="fas fa-book me-1"></i><?php echo htmlspecialchars($assign['subject_name']); ?>
                                            </span>
                                            <?php if ($is_expired): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-1 small">
                                                    <i class="fas fa-clock-rotate-left me-1"></i>Past Due
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small">
                                                    <i class="fas fa-circle-dot me-1"></i>Active
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <h5 class="fw-bold text-dark mt-2 mb-2"><?php echo htmlspecialchars($assign['title']); ?></h5>
                                        <p class="text-muted small mb-3 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <?php echo htmlspecialchars($assign['description'] ?? 'No additional instructions.'); ?>
                                        </p>
                                    </div>

                                    <div>
                                        <div class="bg-light rounded-3 p-3 mb-3 border">
                                            <div class="d-flex justify-content-between text-muted small mb-1">
                                                <span><i class="fas fa-calendar-alt text-warning me-1"></i>Due Date:</span>
                                                <strong class="text-dark"><?php echo date('M d, Y - h:i A', strtotime($assign['due_date'])); ?></strong>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted small mb-1">
                                                <span><i class="fas fa-trophy text-info me-1"></i>Max Marks:</span>
                                                <strong class="text-dark"><?php echo $assign['total_marks']; ?> Marks</strong>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted small">
                                                <span><i class="fas fa-users text-success me-1"></i>Submissions:</span>
                                                <strong class="text-primary"><?php echo $assign['submitted_count']; ?> Submitted</strong>
                                            </div>
                                            <?php if (!empty($assign['file_path'])): ?>
                                                <div class="mt-2 pt-2 border-top">
                                                    <a href="../<?php echo htmlspecialchars($assign['file_path']); ?>" target="_blank" class="small text-decoration-none fw-bold text-primary">
                                                        <i class="fas fa-paperclip me-1"></i> Download Reference Attachment
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <a href="assignments.php?view=<?php echo $assign['assignment_id']; ?>" class="btn btn-outline-primary rounded-pill btn-sm w-100 py-2">
                                                <i class="fas fa-eye me-1"></i> View Submissions
                                            </a>
                                            <a href="assignments.php?action=delete&id=<?php echo $assign['assignment_id']; ?>" class="btn btn-outline-danger rounded-pill btn-sm py-2 px-3" onclick="return confirm('Are you sure you want to delete this assignment?');" title="Delete Assignment">
                                                <i class="fas fa-trash-can"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

            <!-- CREATE ASSIGNMENT MODAL -->
            <div class="modal fade" id="createAssignmentModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow rounded-4">
                        <form method="POST" action="assignments.php" enctype="multipart/form-data">
                            <div class="modal-header border-0 bg-primary text-white rounded-top-4 py-3">
                                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Create New Assignment</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold">Select Subject <span class="text-danger">*</span></label>
                                        <select name="subject_id" class="form-select rounded-3" required>
                                            <option value="">-- Choose Subject --</option>
                                            <?php foreach ($faculty_subjects as $fs): ?>
                                                <option value="<?php echo $fs['subject_id']; ?>">
                                                    <?php echo htmlspecialchars($fs['subject_name']); ?> (<?php echo htmlspecialchars($fs['subject_code']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold">Total Marks</label>
                                        <input type="number" name="total_marks" class="form-control rounded-3" value="100" min="1" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Assignment Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. Chapter 4 Data Structure Implementation" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Description / Instructions</label>
                                        <textarea name="description" class="form-control rounded-3" rows="4" placeholder="Enter assignment instructions, guidelines, and submission requirements..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Due Date & Time <span class="text-danger">*</span></label>
                                        <input type="datetime-local" name="due_date" class="form-control rounded-3" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Attach Reference File (Optional)</label>
                                        <input type="file" name="assignment_file" class="form-control rounded-3" accept=".pdf,.doc,.docx,.txt,.zip,.png,.jpg,.jpeg">
                                        <small class="text-muted">Allowed: PDF, DOC, DOCX, TXT, ZIP, PNG, JPG</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                                <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="create_assignment" class="btn btn-primary rounded-pill px-4">
                                    <i class="fas fa-paper-plane me-1"></i> Publish Assignment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize popovers for submission notes
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
    const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl))
</script>
</body>
</html>
