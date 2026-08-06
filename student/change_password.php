<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "student")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

if(isset($_POST['change_password']))
{

    $new_password = $_POST['new_password'];

    $confirm_password = $_POST['confirm_password'];

    if($new_password != $confirm_password)
    {
        echo "<script>alert('Password does not match');</script>";
    }
    else
    {

        $password = password_hash($new_password,PASSWORD_DEFAULT);

        mysqli_query($conn,

        "UPDATE users
        SET password='$password',
        first_login='0'
        WHERE id='$user_id'");

        echo "<script>

        alert('Password Changed Successfully');

        window.location='dashboard.php';

        </script>";

    }

}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Change Password</title>

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

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card shadow">

<div class="card-header bg-primary text-white text-center">

<h3>Change Password</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>New Password</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button
type="submit"
name="change_password"
class="btn btn-primary w-100">

Update Password

</button>

</form>

</div> 

</div> 

</div> 

</div> 

</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>