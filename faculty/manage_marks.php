<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$students = mysqli_query($conn,

"SELECT
students.student_id,
students.roll_number,
users.name

FROM students

INNER JOIN users
ON students.user_id = users.id

ORDER BY students.roll_number ASC");

$subjects = mysqli_query($conn,

"SELECT * FROM subjects");

if(isset($_POST['save']))
{
    $student_id = $_POST['student_id'];

    $subject_id = $_POST['subject_id'];

    $internal_marks = $_POST['internal_marks'];

    $external_marks = $_POST['external_marks'];

    $total_marks = $internal_marks + $external_marks;

    mysqli_query($conn,

    "INSERT INTO marks
    (student_id,subject_id,internal_marks,external_marks,total_marks)

    VALUES

    ('$student_id',
    '$subject_id',
    '$internal_marks',
    '$external_marks',
    '$total_marks')");

    echo "<script>

    alert('Marks Added Successfully');

    window.location='manage_marks.php';

    </script>";
}

?>

<!DOCTYPE html>
<html>

<head>

<head>

    <title>Manage Marks</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>
<div class="wrapper">

    <?php include("sidebar.php"); ?>

    <div class="main-content">

        <?php include("header.php"); ?>

<div class="container mt-5">

<h3>Manage Marks</h3>

<form method="POST">

<div class="mb-3">

<label>Student</label>

<select name="student_id"
class="form-control"
required>

<option value="">Select Student</option>

<?php

while($row = mysqli_fetch_assoc($students))
{

?>

<option value="<?php echo $row['student_id']; ?>">

<?php echo $row['roll_number']; ?>

-

<?php echo $row['name']; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="mb-3">

<label>Subject</label>

<select name="subject_id"
class="form-control"
required>

<option value="">Select Subject</option>

<?php

while($row = mysqli_fetch_assoc($subjects))
{

?>

<option value="<?php echo $row['subject_id']; ?>">

<?php echo $row['subject_name']; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="mb-3">

<label>Internal Marks</label>

<input type="number"
name="internal_marks"
class="form-control"
required>

</div>

<div class="mb-3">

<label>External Marks</label>

<input type="number"
name="external_marks"
class="form-control"
required>

</div>

<button type="submit"
name="save"
class="btn btn-primary">

Save Marks

</button>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>