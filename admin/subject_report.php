<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$query = "
SELECT
subjects.subject_id,
subjects.subject_name,
users.name
FROM subjects
LEFT JOIN users
ON subjects.faculty_id = users.id
";

$result = db_query($conn,$query);

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subject Report</title>

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

        <div class="card mt-4 mb-4 shadow-sm">

            <div class="card-body">

                <h3>

                    Subject Report

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-header">

                <h4>

                    All Subjects Report

                </h4>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>ID</th>

                                <th>Subject Name</th>

                                <th>Faculty Name</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php while($row=db_fetch_assoc($result)){ ?>

                            <tr>

                                <td>

                                    <?php echo $row['subject_id']; ?>

                                </td>

                                <td>

                                    <?php echo $row['subject_name']; ?>

                                </td>

                                <td>

                                    <?php echo $row['name']; ?>

                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

                <a
                    href="manage_reports.php"
                    class="btn btn-secondary mt-3">

                    <i class="fas fa-arrow-left"></i>

                    Back

                </a>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>