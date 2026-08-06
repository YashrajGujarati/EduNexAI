<?php

session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "

SELECT
prediction_history.attendance,
prediction_history.marks,
prediction_history.prediction,
prediction_history.created_at

FROM prediction_history

INNER JOIN students
ON prediction_history.student_id = students.student_id

WHERE students.user_id = '$user_id'

ORDER BY prediction_history.created_at DESC

");

?>

<!DOCTYPE html>

<html>

<head>

    <title>Prediction History</title>

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

                    <h3>Prediction History</h3>

                </div>

                <div class="card-body">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>Date</th>
                                <th>Attendance</th>
                                <th>Marks</th>
                                <th>Prediction</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php

                        while ($row = mysqli_fetch_assoc($query))
                        {

                        ?>

                            <tr>

                                <td><?php echo $row['created_at']; ?></td>

                                <td><?php echo $row['attendance']; ?>%</td>

                                <td><?php echo $row['marks']; ?></td>

                                <td><?php echo $row['prediction']; ?></td>

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

</body>

</html>