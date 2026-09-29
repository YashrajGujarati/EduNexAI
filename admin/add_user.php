<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

if(isset($_POST['add_user']))
{
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $query = "INSERT INTO users(name,email,password,role)
              VALUES('$name','$email','$password','$role')";

    if(mysqli_query($conn,$query))
    {
        $new_user_id = mysqli_insert_id($conn);
        if ($role === 'student') {
            $enroll_no = "EN" . str_pad($new_user_id, 4, "0", STR_PAD_LEFT);
            @mysqli_query($conn, "UPDATE users SET enrollment_no='$enroll_no' WHERE id=$new_user_id");
            @mysqli_query($conn, "INSERT INTO students (user_id, class, roll_number, attendance) VALUES ($new_user_id, 'BTech-CS', '$enroll_no', 80.00)");
            $new_stu_id = mysqli_insert_id($conn);
            if ($new_stu_id > 0) {
                @mysqli_query($conn, "INSERT INTO student_fees (student_id, total_fee, paid_fee, due_date, status) VALUES ($new_stu_id, 50000.00, 0.00, DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY), 'pending')");
            }
        }
        echo "<script>
        alert('User Added Successfully');
        window.location='manage_users.php';
        </script>";
    }
    else
    {
        echo "<script>alert('Something Went Wrong');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>
    <!-- Sidebar Start -->

<?php include("sidebar.php"); ?>

    <!-- Sidebar End -->
     
    <div class="main-content">

        <!-- Navbar -->

<?php include("header.php"); ?>
<div class="container mt-5">
    <div class="card shadow">

<div class="card-header">

<h3>Add New User</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Name</label>

<input type="text"
class="form-control"
name="name"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input type="email"
class="form-control"
name="email"
required>

</div>

<div class="mb-3">

<label>Password</label>

<input type="password"
class="form-control"
name="password"
required>

</div>

<div class="mb-3">

<label>Role</label>

<select class="form-control" name="role">

<option value="admin">Admin</option>

<option value="faculty">Faculty</option>

<option value="student">Student</option>

</select>

</div>

<button
class="btn btn-primary"
name="add_user">

Add User

</button>

<a href="manage_users.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>
</div>
</div>
</body>

</html>