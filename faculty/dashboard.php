<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="faculty")
{
    header("Location: ../login.php");
    exit();
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Faculty Dashboard</title>

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

<div class="container mt-4">

<div class="card shadow">

<div class="card-body">

<h2>

Welcome Faculty

</h2>

<p>

Attendance Management System

</p>

</div>

</div>

</div>

</div>

</div>

</body>

</html>