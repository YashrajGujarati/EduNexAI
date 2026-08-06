<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$query = "
SELECT
users.name,
students.class,
students.roll_number,
SUM(marks.internal_marks + marks.external_marks) AS total_marks
FROM marks
INNER JOIN students
ON marks.student_id = students.student_id
INNER JOIN users
ON students.user_id = users.id
GROUP BY students.student_id
ORDER BY total_marks DESC
";

$result = mysqli_query($conn,$query);

?>
<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Topper Report</title>

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

                    Topper Students Report

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-header">

                <h4>

                    Students Ranking

                </h4>

            </div>

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>Rank</th>

                                <th>Student Name</th>

                                <th>Class</th>

                                <th>Roll Number</th>

                                <th>Total Marks</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        $rank = 1;

                        while($row = mysqli_fetch_assoc($result))
                        {

                        ?>

                        <tr>

                            <td>

                                <?php echo $rank++; ?>

                            </td>

                            <td>

                                <?php echo $row['name']; ?>

                            </td>

                            <td>

                                <?php echo $row['class']; ?>

                            </td>

                            <td>

                                <?php echo $row['roll_number']; ?>

                            </td>

                            <td>

                                <?php echo $row['total_marks']; ?>

                            </td>

                        </tr>

                        <?php

                        }

                        ?>

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