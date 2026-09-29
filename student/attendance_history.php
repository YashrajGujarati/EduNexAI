    <?php

    session_start();

    if (!isset($_SESSION['user_id']))
    {
        header("Location: ../login.php");
        exit();
    }

    require_once(__DIR__ . '/../config/db.php');

    $user_id = $_SESSION['user_id'];

    $getStudent = mysqli_query($conn,

    "SELECT student_id
    FROM students
    WHERE user_id='$user_id'");

    $row = mysqli_fetch_assoc($getStudent);

    $student_id = $row['student_id'];

    $query = mysqli_query($conn,

    "SELECT *
    FROM attendance
    WHERE student_id='$student_id'
    ORDER BY attendance_id DESC");

    ?>

    <!DOCTYPE html>
    <html>

    <head>

        <title>Attendance History</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet">

    </head>

    <body>
        <?php include("sidebar.php"); ?>

    <div class="main-content">

        <?php include("header.php"); ?>

        



    <div class="container mt-5">

        <h3>Attendance History</h3>

        <table class="table table-bordered">

            <tr>

                <th>ID</th>

                <th>Status</th>

                <th>Date</th>

            </tr>

            <?php

            while($data = mysqli_fetch_assoc($query))
            {

            ?>

            <tr>

                <td><?php echo $data['attendance_id']; ?></td>

                <td><?php echo $data['status']; ?></td>

                <td><?php echo $data['created_at']; ?></td>

            </tr>

            <?php

            }

            ?>

        </table>

    </div>
    </div>

    </body>

    </html>