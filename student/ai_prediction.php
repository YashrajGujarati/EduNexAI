<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "

SELECT
users.name,
students.attendance,
AVG(marks.total_marks) AS avg_marks

FROM users

INNER JOIN students
ON users.id = students.user_id

LEFT JOIN marks
ON students.student_id = marks.student_id

WHERE users.id = '$user_id'

GROUP BY users.id

");

$data = mysqli_fetch_assoc($query);

$attendance = isset($data['attendance']) ? $data['attendance'] : 0;

$marks = isset($data['avg_marks']) ? round($data['avg_marks']) : 0;

$command = "python ../python/prediction.py $attendance $marks";

$prediction = trim(shell_exec($command));

$student_query = mysqli_query(
    $conn,
    "SELECT student_id
     FROM students
     WHERE user_id = '$user_id'"
);

$student = mysqli_fetch_assoc($student_query);

$student_id = $student['student_id'];

mysqli_query(
    $conn,
    "INSERT INTO prediction_history
    (student_id, attendance, marks, prediction)
    VALUES
    ('$student_id', '$attendance', '$marks', '$prediction')"
);

?>

    <!DOCTYPE html>

    <html>

    <head>

    <title>AI Prediction</title>
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

    <h3>AI Prediction</h3>

    </div>

    <div class="card-body">

    <h4>

    Student :

    <?php echo $data['name']; ?>

    </h4>

    <hr>

    <h5>
Attendance :
<?php echo $attendance; ?>%
</h5>

<hr>

<h5>
Average Marks :
<?php echo $marks; ?>
</h5>

<hr>

<h3>
Prediction :
<?php echo $prediction; ?>
</h3>

    </div>

    </div>

    </div>

    </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    </body>

    </html>