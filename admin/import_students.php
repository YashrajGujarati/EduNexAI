<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}

$page_title = "Import Students Batch";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Students | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?php echo time(); ?>">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h4 class="mb-0 fw-bold text-dark"><i class="fas fa-file-import text-primary me-2"></i>Batch Student Import (Excel)</h4>
                        <small class="text-muted">Upload an `.xlsx` or `.xls` spreadsheet to bulk-register students</small>
                    </div>

                    <div class="card-body p-4">
                        <div class="alert alert-info border-0 shadow-sm mb-4">
                            <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-2"></i>Spreadsheet Format Guidelines:</h6>
                            <ul class="mb-0 small ps-3">
                                <li>The file must be in <strong>.xlsx</strong> or <strong>.xls</strong> format.</li>
                                <li>Required Columns: <code>Name</code>, <code>Email</code>, <code>Password</code>, <code>Class</code>, <code>Roll Number</code>, <code>Attendance</code></li>
                                <li>Student accounts and profiles will be automatically created in MySQL.</li>
                            </ul>
                        </div>

                        <form action="process_import.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Select Excel Spreadsheet File</label>
                                <input type="file" name="excel_file" class="form-control form-control-lg" accept=".xlsx,.xls" required>
                                <div class="form-text mt-2">Maximum file size: 10MB</div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="import" class="btn btn-primary px-4">
                                    <i class="fas fa-upload me-1"></i> Process & Import Students
                                </button>
                                <a href="manage_students.php" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Students
                                </a>
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