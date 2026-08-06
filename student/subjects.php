<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$query = mysqli_query($conn,
"SELECT * FROM subjects");

?>

<!DOCTYPE html>

<html>

<head>
   

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet" href="../css/dashboard.css">

<title>My Subjects</title>

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

<h3>My Subjects</h3>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th>Subject ID</th>

<th>Subject Name</th>

</tr>

<?php

while($row = mysqli_fetch_assoc($query))
{

?>

<tr>

<td><?php echo $row['subject_id']; ?></td>

<td><?php echo $row['subject_name']; ?></td>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>