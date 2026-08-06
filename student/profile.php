<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "student")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn,

"SELECT users.*, students.class, students.roll_number, students.attendance

FROM users

LEFT JOIN students

ON users.id = students.user_id

WHERE users.id='$user_id'");

$student = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile</title>

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

<h4>My Profile</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="30%">Name</th>
<td><?php echo $student['name']; ?></td>
</tr>

<tr>
<th>Enrollment Number</th>
<td><?php echo $student['enrollment_no']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $student['email']; ?></td>
</tr>

<tr>
<th>Mobile</th>
<td><?php echo $student['mobile']; ?></td>
</tr>

<tr>
<th>Class</th>
<td><?php echo $student['class']; ?></td>
</tr>

<tr>
<th>Roll Number</th>
<td><?php echo $student['roll_number']; ?></td>
</tr>

<tr>
<th>Attendance</th>
<td><?php echo isset($student['attendance']) ? $student['attendance'] : 0; ?>%</td>
</tr>

</table>

</div>

</div>

</div>

</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>    
</body>

</html>