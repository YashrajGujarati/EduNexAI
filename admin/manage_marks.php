<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$query = "
SELECT
marks.*,
users.name,
subjects.subject_name
FROM marks
INNER JOIN students
ON marks.student_id = students.student_id
INNER JOIN users
ON students.user_id = users.id
INNER JOIN subjects
ON marks.subject_id = subjects.subject_id
";

$result = mysqli_query($conn,$query);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Marks | EduNexAI</title>

<!-- Bootstrap CSS -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<!-- Dashboard CSS -->

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>

<div class="wrapper">

    <?php include("sidebar.php"); ?>

    <div class="main-content">

        <?php include("header.php"); ?>


<nav class="navbar bg-white shadow-sm rounded p-3 mb-4">

<h4>Manage Marks</h4>

</nav>

<a href="add_marks.php" class="btn btn-success mb-3">

<i class="fas fa-plus"></i>

Add Marks

</a>

<div class="card">

<div class="card-header">

<h4>All Student Marks</h4>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>

<th>Student</th>

<th>Subject</th>

<th>Internal</th>

<th>External</th>

<th>Total</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['mark_id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['subject_name']; ?></td>

<td><?php echo $row['internal_marks']; ?></td>

<td><?php echo $row['external_marks']; ?></td>

<td><?php echo $row['total_marks']; ?></td>

<td>

<a href="edit_marks.php?id=<?php echo $row['mark_id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a href="delete_marks.php?id=<?php echo $row['mark_id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete Marks?')">

Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>