    <?php

    session_start();

    if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
    {
        header("Location: ../login.php");
        exit();
    }

    include("../config/db.php");

    $total_students = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM students"));

    $total_faculty = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM users WHERE role='faculty'"));

    $total_subjects = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM subjects"));

    $total_marks = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM marks"));

    ?>
    <!DOCTYPE html>

    <html lang="en">

    <head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">

    </head>

    <body>

    <div class="wrapper">

        <?php include("sidebar.php"); ?>

        <div class="main-content">

            <?php include("header.php"); ?>

        <div class="card mt-3 mb-3 shadow-sm">

        <div class="card-body py-3 px-4">

            <h4 class="mb-0">

                Reports Dashboard

            </h4>

        </div>

    </div>

            <div class="row">

                <div class="col-md-3">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-user-graduate dashboard-icon"></i>

                            <h5>Total Students</h5>

                            <h2><?php echo $total_students['total']; ?></h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-chalkboard-teacher dashboard-icon"></i>

                            <h5>Total Faculty</h5>

                            <h2><?php echo $total_faculty['total']; ?></h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-book dashboard-icon"></i>

                            <h5>Total Subjects</h5>

                            <h2><?php echo $total_subjects['total']; ?></h2>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-file-alt dashboard-icon"></i>

                            <h5>Total Marks</h5>

                            <h2><?php echo $total_marks['total']; ?></h2>

                        </div>

                    </div>

                </div>

            </div>

                   </div>

<div class="mt-4">

    <a href="export_excel.php" class="btn btn-success">

        <i class="fas fa-file-excel"></i>
        Download Excel

    </a>

    <a href="export_pdf.php" class="btn btn-danger ms-2">

        <i class="fas fa-file-pdf"></i>
        Download PDF

    </a>

</div>

</div>

</div>

</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    </body>

    </html>