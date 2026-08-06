<?php
session_start();
include("config/db.php");
if(isset($_POST['register']))
{
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    if($password != $confirm_password)
    {
        echo "<script>
        alert('Password and Confirm Password do not match.');
        </script>";
    }
    else
    {
        $check_email = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn,$check_email);
        if(mysqli_num_rows($result) > 0)
        {
            echo "<script>
            alert('Email already exists.');
            </script>";
        }
        else
        {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = "INSERT INTO users(name,email,password,role)
            VALUES('$name','$email','$hash_password','$role')";
            if(mysqli_query($conn,$insert))
            {
                echo "<script>
                alert('Registration Successful.');
                window.location='login.php';
                </script>";
            }
            else
            {

                 die(mysqli_error($conn));

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduNexAI | Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
<div class="container">
<div class="row justify-content-center align-items-center vh-100">
<div class="col-lg-6">
<div class="register-card">
<div class="text-center mb-4">
<i class="fas fa-user-plus register-icon"></i>
<h2>Create Account</h2>
<p>Register for EduNexAI</p>
</div>
<form action="" method="POST">
<div class="mb-3">
<label class="form-label">Full Name</label>
<input
type="text"
name="name"
class="form-control"
placeholder="Enter Full Name"
required>
</div>
<div class="mb-3">
<label class="form-label">Email</label>
<input
type="email"
name="email"
class="form-control"
placeholder="Enter Email"
required>
</div>
<div class="mb-3">
<label class="form-label">Password</label>
<input
type="password"
name="password"
class="form-control"
placeholder="Create Password"
required>
</div>
<div class="mb-3">
<label class="form-label">Confirm Password</label>
<input
type="password"
name="confirm_password"
class="form-control"
placeholder="Confirm Password"
required>
</div>
<div class="mb-3">
<label class="form-label">Select Role</label>
<select
name="role"
class="form-select"
required>
<option value="">Choose Role</option>
<option value="student">Student</option>
<option value="faculty">Faculty</option>
<option value="admin">Admin</option>
</select>
</div>
<button
type="submit"
name="register"
class="btn btn-primary w-100">
Register
</button>
</form>
<hr>
<p class="text-center">
Already have an account?
<a href="login.php">
Login
</a>
</p>
<p class="text-center">
<a href="index.html">
← Back to Home
</a>
</p>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/register.js"></script>
</body>
</html>