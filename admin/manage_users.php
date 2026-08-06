<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$query = "SELECT * FROM users ORDER BY id ASC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Users</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>
<?php include("sidebar.php"); ?>
<div class="wrapper">

    <div class="main-content">
<div class="card mt-3 mb-3 shadow-sm">
    <div class="card-body py-3">
        <h3 class="mb-0">
            Manage Users
        </h3>
    </div>
</div>

<a href="add_user.php" class="btn btn-success mb-3">
    <i class="fas fa-plus"></i> Add User
</a>

<div class="card">

    <div class="card-header">

        <h4>All Users</h4>

    </div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>

<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Role</th>

<th>Created At</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php

while($row = mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo ucfirst($row['role']); ?></td>

<td><?php echo $row['created_at']; ?></td>
<td>
<?php include("sidebar.php"); ?>
    <a href="edit_user.php?id=<?php echo $row['id']; ?>"
       class="btn btn-warning btn-sm">

        <i class="fas fa-edit"></i>

        Edit

    </a>
     <a href="delete_user.php?id=<?php echo $row['id']; ?>"
                class="btn btn-danger btn-sm ms-1"
                onclick="return confirm('Are you sure you want to delete this user?')">

                <i class="fas fa-trash"></i>

                Delete

        </a>

</td>

</tr>

<?php

}

?>

</tbody>

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