<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "faculty")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn,

"SELECT *
FROM users
WHERE id='$user_id'");

$faculty = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Faculty Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet"
href="../css/dashboard.css">

</head>

<body>

<div class="wrapper">

<?php include("sidebar.php"); ?>

<div class="main-content">

<?php include("header.php"); ?>

<div class="container-fluid mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h3>My Profile</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Name</th>
<td><?php echo $faculty['name']; ?></td>
</tr>

<tr>
<th>Email</th>
<td><?php echo $faculty['email']; ?></td>
</tr>

<tr>
<th>Mobile</th>
<td><?php echo $faculty['mobile']; ?></td>
</tr>

<tr>
<th>Role</th>
<td><?php echo $faculty['role']; ?></td>
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