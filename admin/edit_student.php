<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$id = intval($_GET['id']);

$query = "
SELECT students.*, users.name
FROM students
INNER JOIN users
ON students.user_id = users.id
WHERE student_id='$id'
";

$result = db_query($conn,$query);

$student = db_fetch_assoc($result);

if(isset($_POST['update_student']))
{
    $class = db_real_escape_string($conn,$_POST['class']);

    $roll_number = db_real_escape_string($conn,$_POST['roll_number']);

    $attendance = db_real_escape_string($conn,$_POST['attendance']);

    $update = "
    UPDATE students
    SET
    class='$class',
    roll_number='$roll_number',
    attendance='$attendance'
    WHERE student_id='$id'
    ";

    if(db_query($conn,$update))
    {
        echo "<script>

        alert('Student Updated Successfully');

        window.location='manage_students.php';

        </script>";
    }
    else
    {
        echo "<script>

        alert('Update Failed');

        </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Student</title>

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

                    Edit Student

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Student Name

                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?php echo $student['name']; ?>"
                            readonly>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Class

                        </label>

                        <input
                            type="text"
                            name="class"
                            class="form-control"
                            value="<?php echo $student['class']; ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Roll Number

                        </label>

                        <input
                            type="text"
                            name="roll_number"
                            class="form-control"
                            value="<?php echo $student['roll_number']; ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Attendance (%)

                        </label>

                        <input
                            type="number"
                            name="attendance"
                            class="form-control"
                            value="<?php echo $student['attendance']; ?>"
                            min="0"
                            max="100"
                            required>

                    </div>

                    <button
                        type="submit"
                        name="update_student"
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Update Student

                    </button>

                    <a
                        href="manage_students.php"
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