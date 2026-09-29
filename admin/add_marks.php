<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

/* Student Dropdown */
$students = mysqli_query($conn,"
SELECT students.student_id, users.name
FROM students
INNER JOIN users ON students.user_id = users.id
ORDER BY users.name ASC
");

/* Subject Dropdown */
$subjects = mysqli_query($conn,"
SELECT subject_id, subject_name
FROM subjects
ORDER BY subject_name ASC
");

$message = "";
$message_type = "";

if(isset($_POST['add_marks'])) {
    $student_id = (int)$_POST['student_id'];
    $subject_id = (int)$_POST['subject_id'];
    $internal = (int)$_POST['internal_marks'];
    $external = (int)$_POST['external_marks'];
    $total = $internal + $external;

    $query = "INSERT INTO marks (student_id, subject_id, internal_marks, external_marks, total_marks) VALUES ('$student_id', '$subject_id', '$internal', '$external', '$total')";

    if(mysqli_query($conn, $query)) {
        $message = "Student marks added successfully! Redirecting...";
        $message_type = "success";
        echo "<script>setTimeout(function(){ window.location='manage_marks.php'; }, 1200);</script>";
    } else {
        $message = "Failed to add student marks. Please try again.";
        $message_type = "danger";
    }
}

$page_title = "Add Student Marks";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student Marks | EduNexAI</title>
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
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 card-header-flex">
                        <div>
                            <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square text-primary me-2"></i>Enter Academic Marks</h4>
                            <small class="text-muted">Record internal and external evaluation scores for students</small>
                        </div>
                        <a href="manage_marks.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to Marks List
                        </a>
                    </div>

                    <div class="card-body p-4">
                        <?php if(!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Select Student <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user-graduate"></i></span>
                                        <select name="student_id" class="form-select" required>
                                            <option value="">-- Choose Student --</option>
                                            <?php while($s = mysqli_fetch_assoc($students)): ?>
                                                <option value="<?php echo $s['student_id']; ?>">
                                                    <?php echo htmlspecialchars($s['name']); ?> (ID: #<?php echo $s['student_id']; ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Select Subject <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-book-bookmark"></i></span>
                                        <select name="subject_id" class="form-select" required>
                                            <option value="">-- Choose Subject --</option>
                                            <?php while($sub = mysqli_fetch_assoc($subjects)): ?>
                                                <option value="<?php echo $sub['subject_id']; ?>">
                                                    <?php echo htmlspecialchars($sub['subject_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Internal Marks (Max 40) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-file-pen"></i></span>
                                        <input type="number" name="internal_marks" class="form-control" placeholder="e.g. 35" min="0" max="40" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">External Marks (Max 60) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                        <input type="number" name="external_marks" class="form-control" placeholder="e.g. 50" min="0" max="60" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="add_marks" class="btn btn-primary px-4 py-2">
                                    <i class="fas fa-check-circle me-1"></i> Save Marks Record
                                </button>
                                <a href="manage_marks.php" class="btn btn-light px-4 py-2 border">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>