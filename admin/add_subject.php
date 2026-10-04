<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$faculty_query = "SELECT id,name FROM users WHERE role='faculty'";
$faculty_result = db_query($conn,$faculty_query);

if(isset($_POST['add_subject']))
{
    $subject_name = db_real_escape_string($conn,$_POST['subject_name']);
    $faculty_id = $_POST['faculty_id'];

    $query = "INSERT INTO subjects(subject_name,faculty_id)
              VALUES('$subject_name','$faculty_id')";

    if(db_query($conn,$query))
    {
        echo "<script>

        alert('Subject Added Successfully');

        window.location='manage_subjects.php';

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

<title>Add Subject</title>

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

                    Add Subject

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Subject Name

                        </label>

                        <input
                        type="text"
                        name="subject_name"
                        class="form-control"
                        required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Select Faculty

                        </label>

                        <select
                        name="faculty_id"
                        class="form-control"
                        required>

                            <option value="">

                                Select Faculty

                            </option>

                            <?php while($row=db_fetch_assoc($faculty_result)){ ?>

                            <option value="<?php echo $row['id']; ?>">

                                <?php echo $row['name']; ?>

                            </option>

                            <?php } ?>

                        </select>

                    </div>

                    <button
                    type="submit"
                    name="add_subject"
                    class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Add Subject

                    </button>

                    <a
                    href="manage_subjects.php"
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