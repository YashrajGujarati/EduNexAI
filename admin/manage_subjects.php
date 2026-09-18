<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$query = "
SELECT subjects.*, users.name as faculty_name
FROM subjects
LEFT JOIN users ON subjects.faculty_id = users.id
ORDER BY subjects.subject_id ASC
";

$result = mysqli_query($conn, $query);
$page_title = "Manage Subjects";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects | EduNexAI</title>
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
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-book-bookmark text-primary me-2"></i>Academic Subjects</h4>
                    <small class="text-muted">Manage active curriculum subjects and faculty assignments</small>
                </div>
                <a href="add_subject.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Subject
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject ID</th>
                                <th>Subject Name</th>
                                <th>Subject Code / Details</th>
                                <th>Assigned Faculty</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $fac_name = !empty($row['faculty_name']) ? $row['faculty_name'] : 'Not Assigned';
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $row['subject_id']; ?></td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <i class="fas fa-book text-primary me-2"></i><?php echo htmlspecialchars($row['subject_name']); ?>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-secondary border"><?php echo htmlspecialchars($row['subject_code'] ?? 'SUB-'.$row['subject_id']); ?></span></td>
                                <td>
                                    <?php if(!empty($row['faculty_name'])): ?>
                                        <span class="badge-status badge-info"><i class="fas fa-user-tie me-1"></i> <?php echo htmlspecialchars($row['faculty_name']); ?></span>
                                    <?php else: ?>
                                        <span class="badge-status badge-warning"><i class="fas fa-exclamation-circle me-1"></i> Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_subject.php?id=<?php echo $row['subject_id']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_subject.php?id=<?php echo $row['subject_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this subject?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <i class="fas fa-book-open"></i>
                                        <h5>No Subjects Available</h5>
                                        <p>Click "Add Subject" to configure your academic subjects.</p>
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