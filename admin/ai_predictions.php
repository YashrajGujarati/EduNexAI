<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");
$excellent = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM prediction_history
         WHERE prediction='Excellent'"
    )
);

$good = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM prediction_history
         WHERE prediction='Good'"
    )
);

$average = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM prediction_history
         WHERE prediction='Average'"
    )
);

$poor = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM prediction_history
         WHERE prediction='Poor'"
    )
);

$query = mysqli_query($conn,

"SELECT
prediction_history.id,
users.name,
prediction_history.attendance,
prediction_history.marks,
prediction_history.prediction,
prediction_history.created_at

FROM prediction_history

INNER JOIN students
ON prediction_history.student_id = students.student_id

INNER JOIN users
ON students.user_id = users.id

ORDER BY prediction_history.created_at DESC");

?>

<!DOCTYPE html>

<html>

<head>

    <title>AI Predictions</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<div class="wrapper">

    <?php include("sidebar.php"); ?>

    <div class="main-content">

        <?php include("header.php"); ?>

        <div class="container-fluid mt-4">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">

                    <h3>AI Prediction Reports</h3>

                </div>
<canvas id="predictionChart"></canvas>

<br>
<hr>
<br>
                <div class="card-body">

                    <table class="table table-bordered">

                        <tr>

                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Attendance</th>
                            <th>Marks</th>
                            <th>Prediction</th>
                            <th>Date</th>

                        </tr>

                        <?php while($row = mysqli_fetch_assoc($query)) { ?>

                        <tr>

                            <td><?php echo $row['id']; ?></td>

                            <td><?php echo $row['name']; ?></td>

                            <td><?php echo $row['attendance']; ?>%</td>

                            <td><?php echo $row['marks']; ?></td>

                            <td><?php echo $row['prediction']; ?></td>

                            <td><?php echo $row['created_at']; ?></td>

                        </tr>

                        <?php } ?>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>
<script>

const ctx = document.getElementById('predictionChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: [
            'Excellent',
            'Good',
            'Average',
            'Poor'
        ],

        datasets: [{
            label: 'Student Performance',

            data: [
                <?php echo $excellent; ?>,
                <?php echo $good; ?>,
                <?php echo $average; ?>,
                <?php echo $poor; ?>
            ],

            borderWidth: 1
        }]
    }
});

</script>
</body>

</html>