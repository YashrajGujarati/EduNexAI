<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$where = "";
if(!empty($search)) {
    $where = " WHERE users.name LIKE '%$search%' OR students.class LIKE '%$search%' OR students.roll_number LIKE '%$search%'";
}

$query = "
SELECT students.*, users.name, users.email
FROM students
INNER JOIN users ON students.user_id = users.id
$where
ORDER BY students.student_id ASC
";

$result = mysqli_query($conn, $query);
$page_title = "Manage Students";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students | EduNexAI</title>
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
            <div class="card-body p-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-md-8 col-sm-12">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by student name, roll number, or class..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary px-4">Search</button>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-12 text-md-end">
                        <?php if(!empty($search)): ?>
                            <a href="manage_students.php" class="btn btn-outline-secondary me-2"><i class="fas fa-times me-1"></i> Clear Filter</a>
                        <?php endif; ?>
                        <a href="add_student.php" class="btn btn-success me-1"><i class="fas fa-user-plus me-1"></i> Add Student</a>
                        <a href="import_students.php" class="btn btn-outline-primary"><i class="fas fa-file-import me-1"></i> Import</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 card-header-flex">
                <div>
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-user-graduate text-primary me-2"></i>Student Records</h4>
                    <small class="text-muted">Total Enrolled Students: <?php echo mysqli_num_rows($result); ?></small>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Roll Number</th>
                                <th>Attendance Rate</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)){ 
                                $att = (float)$row['attendance'];
                                $att_badge = $att >= 75 ? 'badge-pass' : 'badge-fail';
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $row['student_id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2" style="width:34px; height:34px; font-size:13px;">
                                            <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($row['name']); ?></span>
                                            <small class="text-muted"><?php echo htmlspecialchars($row['email'] ?? ''); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border px-2 py-1"><?php echo htmlspecialchars($row['class']); ?></span></td>
                                <td class="fw-semibold"><?php echo htmlspecialchars($row['roll_number']); ?></td>
                                <td>
                                    <span class="badge-status <?php echo $att_badge; ?>">
                                        <i class="fas fa-chart-pie me-1"></i> <?php echo number_format($att, 1); ?>%
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="edit_student.php?id=<?php echo $row['student_id']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_student.php?id=<?php echo $row['student_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student record?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-user-slash"></i>
                                        <h5>No Students Found</h5>
                                        <p>No matching student records available. Try adjusting your search query.</p>
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