<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$getStudent = mysqli_query($conn,

"SELECT student_id
FROM students
WHERE user_id='$user_id'");

$student = mysqli_fetch_assoc($getStudent);

$student_id = $student['student_id'];

$query = mysqli_query($conn,

"SELECT
subjects.subject_name,
marks.internal_marks,
marks.external_marks,
marks.total_marks

FROM marks

INNER JOIN subjects

ON marks.subject_id = subjects.subject_id

WHERE marks.student_id='$student_id'");

?>

<!DOCTYPE html>

<html>

<head>

    <title>My Result</title>

   
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

<div class="container-fluid mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>My Results</h3>

</div>

<div class="card-body">
    <table class="table table-bordered">

        <tr>

            <th>Subject</th>

            <th>Internal</th>

            <th>External</th>

            <th>Total</th>

        </tr>

        <?php

        while($row = mysqli_fetch_assoc($query))
        {

        ?>

        <tr>

            <td><?php echo $row['subject_name']; ?></td>

            <td><?php echo $row['internal_marks']; ?></td>

            <td><?php echo $row['external_marks']; ?></td>

            <td><?php echo $row['total_marks']; ?></td>

        </tr>

        <?php

        }

        ?>

   </table>
</div>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>