<?php

session_start();

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn,"
SELECT student_id
FROM students
WHERE user_id='$user_id'
");

$student = mysqli_fetch_assoc($query);

$student_id = $student['student_id'];

$attendance = mysqli_query($conn,"
SELECT attendance_date, unit_no, topic, status
FROM attendance
WHERE student_id='$student_id'
ORDER BY attendance_date DESC
");

?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet" href="../css/dashboard.css">

<title>Attendance</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

</head>

<body>

<div class="wrapper">

<?php include("sidebar.php"); ?>

<div class="main-content">

<?php include("header.php"); ?>

<div class="container-fluid mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>Attendance History</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Date</th>
<th>Unit</th>
<th>Topic</th>
<th>Status</th>
</tr>

<?php while($row=mysqli_fetch_assoc($attendance)){ ?>

<tr>

<td><?php echo $row['attendance_date']; ?></td>
<td><?php echo $row['unit_no']; ?></td>
<td><?php echo $row['topic']; ?></td>
<td><?php echo $row['status']; ?></td>
</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>