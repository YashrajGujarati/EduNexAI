<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$query = "SELECT * FROM users WHERE role='faculty' ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$page_title = "Manage Faculty";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty | EduNexAI</title>
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
                    <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-chalkboard-user text-primary me-2"></i>Faculty Members</h4>
                    <small class="text-muted">Manage academic teaching staff accounts</small>
                </div>
                <a href="add_faculty.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Faculty Member
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Faculty Name</th>
                                <th>Email Address</th>
                                <th>Role</th>
                                <th>Date Added</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if(mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $row['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="topbar-avatar me-2 bg-info text-white" style="width:34px; height:34px; font-size:13px;">
                                            <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                        </div>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><span class="badge-status badge-info"><i class="fas fa-chalkboard-user me-1"></i> Faculty</span></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($row['created_at']); ?></td>
                                <td class="text-end pe-4">
                                    <a href="edit_faculty.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_faculty.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this faculty account?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } 
                        } else { ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="fas fa-chalkboard-user"></i>
                                        <h5>No Faculty Members</h5>
                                        <p>Click "Add Faculty Member" to assign teaching accounts.</p>
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