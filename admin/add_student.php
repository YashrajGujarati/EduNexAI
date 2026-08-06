<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$query = "SELECT id,name FROM users WHERE role='student'";
$result = mysqli_query($conn,$query);

if(isset($_POST['add_student']))
{
    $user_id = $_POST['user_id'];
    $class = mysqli_real_escape_string($conn,$_POST['class']);
    $roll_number = mysqli_real_escape_string($conn,$_POST['roll_number']);
    $attendance = mysqli_real_escape_string($conn,$_POST['attendance']);

    $insert = "INSERT INTO students(user_id,class,roll_number,attendance)
    VALUES('$user_id','$class','$roll_number','$attendance')";

    if(mysqli_query($conn,$insert))
    {
        echo "<script>
        alert('Student Added Successfully');
        window.location='manage_students.php';
        </script>";
    }
    else
    {
        echo "<script>
        alert('Something Went Wrong');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Student</title>

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

                    Add Student

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Select Student

                        </label>

                        <select
                        name="user_id"
                        class="form-control"
                        required>

                            <option value="">

                                Select Student

                            </option>

                            <?php while($row=mysqli_fetch_assoc($result)){ ?>

                            <option value="<?php echo $row['id']; ?>">

                                <?php echo $row['name']; ?>

                            </option>

                            <?php } ?>

                        </select>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Class

                        </label>

                        <input
                        type="text"
                        name="class"
                        class="form-control"
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
                        min="0"
                        max="100"
                        required>

                    </div>

                    <button
                    type="submit"
                    name="add_student"
                    class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Add Student

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