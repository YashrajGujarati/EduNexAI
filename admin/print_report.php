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
students.attendance
FROM students
INNER JOIN users
ON students.user_id = users.id
";

$result = mysqli_query($conn,$query);

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Print Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body onload="window.print()">

<div class="container mt-4">

    <div class="text-center mb-4">

        <h2>

            EduNexAI

        </h2>

        <h4>

            Student Report

        </h4>

    </div>

    <table class="table table-bordered">

        <thead>

            <tr>

                <th>ID</th>

                <th>Name</th>

                <th>Class</th>

                <th>Roll Number</th>

                <th>Attendance</th>

            </tr>

        </thead>

        <tbody>

        <?php while($row=mysqli_fetch_assoc($result)){ ?>

            <tr>

                <td>

                    <?php echo $row['student_id']; ?>

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

                    <?php echo $row['attendance']; ?>%

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>