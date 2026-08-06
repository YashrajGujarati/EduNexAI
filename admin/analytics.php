<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Analytics Dashboard</title>

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

                    <h3>Analytics Dashboard</h3>

                </div>

                <div class="card-body">

                    <h4>Bar Chart</h4>

                    <img src="../python/output/bar_chart.png"
                         class="img-fluid">

                    <hr>

                    <h4>Pie Chart</h4>

                    <img src="../python/output/pie_chart.png"
                         class="img-fluid">

                    <hr>

                    <h4>Heatmap</h4>

                    <img src="../python/output/heatmap.png"
                         class="img-fluid">

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>