<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "student")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$studentQuery = mysqli_query(
    $conn,
    "SELECT student_id
     FROM students
     WHERE user_id='$user_id'"
);

$student = mysqli_fetch_assoc($studentQuery);

$student_id = $student['student_id'];

$marksQuery = mysqli_query(
    $conn,

    "SELECT
    subjects.subject_name,
    marks.internal_marks,
    marks.external_marks,
    marks.total_marks

    FROM marks

    INNER JOIN subjects
    ON marks.subject_id = subjects.subject_id

    WHERE marks.student_id='$student_id'"
);

?>

<!DOCTYPE html>
<html>

<head>

<title>View Marks</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<h3>My Marks</h3>

<table class="table table-bordered">

<tr>

<th>Subject</th>

<th>Internal</th>

<th>External</th>

<th>Total</th>

</tr>

<?php

while($row = mysqli_fetch_assoc($marksQuery))
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

</body>

</html>