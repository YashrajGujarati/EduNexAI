<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

if(isset($_POST['add_faculty']))
{
    $name = mysqli_real_escape_string($conn,$_POST['name']);

    $email = mysqli_real_escape_string($conn,$_POST['email']);

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $role = "faculty";

    $query = "INSERT INTO users(name,email,password,role)
              VALUES('$name','$email','$password','$role')";

    if(mysqli_query($conn,$query))
    {
        echo "<script>

        alert('Faculty Added Successfully');

        window.location='manage_faculty.php';

        </script>";
    }
    else
    {
        echo "<script>

        alert('Something Went Wrong');

        </script>";
    }
}

?><!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Faculty</title>

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

        <div class="card mt-4 mb-4 shadow-sm">

            <div class="card-body">

                <h3>

                    Add Faculty

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Faculty Name

                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter Faculty Name"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Email Address

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter Email"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Password

                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Enter Password"
                            required>

                    </div>

                    <button
                        type="submit"
                        name="add_faculty"
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Add Faculty

                    </button>

                    <a
                        href="manage_faculty.php"
                        class="btn btn-secondary">

                        <i class="fas fa-arrow-left"></i>

                        Back

                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>