<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$id = intval($_GET['id']);

$query = "SELECT * FROM users WHERE id='$id'";

$result = db_query($conn,$query);

$faculty = db_fetch_assoc($result);

if(isset($_POST['update_faculty']))
{
    $name = db_real_escape_string($conn,$_POST['name']);

    $email = db_real_escape_string($conn,$_POST['email']);

    $update = "UPDATE users
               SET
               name='$name',
               email='$email'
               WHERE id='$id'";

    if(db_query($conn,$update))
    {
        echo "<script>

        alert('Faculty Updated Successfully');

        window.location='manage_faculty.php';

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

    <title>Edit Faculty</title>

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

                    Edit Faculty

                </h3>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <form method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Faculty Name

                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?php echo $faculty['name']; ?>"
                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Email Address

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?php echo $faculty['email']; ?>"
                            required>

                    </div>

                    <button
                        type="submit"
                        name="update_faculty"
                        class="btn btn-primary">

                        <i class="fas fa-save"></i>

                        Update Faculty

                    </button>

                    <a
                        href="manage_faculty.php"
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