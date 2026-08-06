<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

// Total Students
$student_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM students");
$student_data = mysqli_fetch_assoc($student_query);
$total_students = $student_data['total'];

// Total Faculty
$faculty_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role='faculty'");
$faculty_data = mysqli_fetch_assoc($faculty_query);
$total_faculty = $faculty_data['total'];

// Total Subjects
$subject_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM subjects");
$subject_data = mysqli_fetch_assoc($subject_query);
$total_subjects = $subject_data['total'];

// Total AI Predictions
$prediction_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM predictions");
$prediction_data = mysqli_fetch_assoc($prediction_query);
$total_predictions = $prediction_data['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | EduNexAI</title>

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

    <!-- Sidebar Start -->

<?php include("sidebar.php"); ?>

    <!-- Sidebar End -->

    <!-- Main Content -->

    <div class="main-content">

        <!-- Navbar -->

<?php include("header.php"); ?>


        <!-- Dashboard Container -->

        <div class="container-fluid mt-4">

            <div class="row">

    <!-- Total Students -->

    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted">

                            Total Students

                        </h6>

                         <h2><?php echo $total_students; ?></h2>
                       

                    </div>

                    <div>

                        <i class="fas fa-user-graduate dashboard-icon"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Total Faculty -->

    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted">

                            Total Faculty

                        </h6>

                            <h2><?php echo $total_faculty; ?></h2>
                        
                    </div>

                    <div>

                        <i class="fas fa-chalkboard-teacher dashboard-icon"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- Total Subjects -->

    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted">

                            Total Subjects

                        </h6>
                    
                            <h2><?php echo $total_subjects; ?></h2>

                    </div>

                    <div>

                        <i class="fas fa-book dashboard-icon"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- AI Predictions -->

    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card dashboard-card">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h6 class="text-muted">

                            AI Predictions

                        </h6>
                            <h2><?php echo $total_predictions; ?></h2>
                    </div>

                    <div>

                        <i class="fas fa-brain dashboard-icon"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- Charts અહીં આવશે -->

        </div>

    </div>

</div>

<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Chart JS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Dashboard JS -->

<script src="../js/dashboard.js"></script>

</body>

</html>