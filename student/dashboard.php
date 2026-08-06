<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "student")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "

SELECT
users.*,
students.student_id,
students.class,
students.roll_number

FROM users

JOIN students
ON users.id = students.user_id

WHERE users.id='$user_id'

");

$student = mysqli_fetch_assoc($query);

$student_id = $student['student_id'];

$total = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM attendance
     WHERE student_id='$student_id'")
);

$present = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) AS present
     FROM attendance
     WHERE student_id='$student_id'
     AND status='Present'")
);

$totalAttendance = $total['total'];

$presentAttendance = $present['present'];

$attendancePercentage = 0;

if($totalAttendance>0)
{
    $attendancePercentage =
    round(($presentAttendance/$totalAttendance)*100,2);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Dashboard</title>

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

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h3>

                        Welcome,

                        <?php echo $student['name']; ?>

                    </h3>

                    <p>

                        Smart Student Performance & Learning Analytics System

                    </p>

                </div>

            </div>

            <div class="row">

              <div class="col-lg-2 col-md-4 col-sm-6 mb-4">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-id-card dashboard-icon"></i>

                            <h5>Enrollment</h5>

                            <h4>

                                <?php echo $student['enrollment_no']; ?>

                            </h4>

                        </div>

                    </div>

                </div>

              <div class="col-lg-2 col-md-4 col-sm-6 mb-4">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-users dashboard-icon"></i>

                            <h5>Class</h5>

                            <h4>

                                <?php echo $student['class']; ?>

                            </h4>

                        </div>

                    </div>

                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-4">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-list-ol dashboard-icon"></i>

                            <h5>Roll No</h5>

                            <h4>

                                <?php echo $student['roll_number']; ?>

                            </h4>

                        </div>

                    </div>

                </div>

           <div class="col-lg-2 col-md-4 col-sm-6 mb-4">

                    <div class="card dashboard-card text-center">

                        <div class="card-body">

                            <i class="fas fa-calendar-check dashboard-icon"></i>

                           <h5>Attendance</h5>

<h4>

<?php echo $attendancePercentage; ?>%

</h4>

<p class="text-muted mb-0" style="font-size:14px;">
Present :
<?php echo $presentAttendance; ?>
/
<?php echo $totalAttendance; ?>
</p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>