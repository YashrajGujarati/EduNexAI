<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$query = "
SELECT
marks.*,
users.name as student_name,
subjects.subject_name
FROM marks
INNER JOIN students ON marks.student_id = students.student_id
INNER JOIN users ON students.user_id = users.id
INNER JOIN subjects ON marks.subject_id = subjects.subject_id
ORDER BY marks.mark_id DESC
";

$result = mysqli_query($conn, $query);
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

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-to-square text-primary me-2"></i>Academic Marks & Grades</h4>
                    <small class="text-muted">Overview of internal, external, and total student marks</small>
                </div>
                <a href="add_marks.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Student Marks
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Record ID</th>
                                <th>Student Name</th>
                                <th>Subject</th>
                                <th>Internal Marks</th>
                                <th>External Marks</th>
                                <th>Total Marks</th>
                                <th>Grade Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $total = (int)$row['total_marks'];
                                $badge_class = $total >= 40 ? 'badge-pass' : 'badge-fail';
                                $status_text = $total >= 40 ? 'Passed' : 'Needs Attention';
                                $m_id = isset($row['mark_id']) ? $row['mark_id'] : (isset($row['id']) ? $row['id'] : 0);
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $m_id; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2" style="width:32px; height:32px; font-size:12px;">
                                            <?php echo strtoupper(substr($row['student_name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['student_name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-primary border"><i class="fas fa-book me-1"></i><?php echo htmlspecialchars($row['subject_name']); ?></span></td>
                                <td><?php echo (int)$row['internal_marks']; ?></td>
                                <td><?php echo (int)$row['external_marks']; ?></td>
                                <td class="fw-bold text-dark fs-6"><?php echo $total; ?> / 100</td>
                                <td><span class="badge-status <?php echo $badge_class; ?>"><?php echo $status_text; ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="edit_marks.php?id=<?php echo $m_id; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_marks.php?id=<?php echo $m_id; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this mark record?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>

                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fas fa-clipboard-check"></i>
                                        <h5>No Marks Recorded Yet</h5>
                                        <p>Click "Add Student Marks" to enter exam and assignment grades.</p>
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