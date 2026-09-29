<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');
$id = intval($_GET['id']);

$query = "SELECT * FROM users WHERE id='$id'";
$result = mysqli_query($conn,$query);

$user = mysqli_fetch_assoc($result);
if(isset($_POST['update_user']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = $_POST['role'];

    $update_query = "UPDATE users
                     SET
                     name='$name',
                     email='$email',
                     role='$role'
                     WHERE id='$id'";

    if(mysqli_query($conn, $update_query))
    {
        echo "<script>
        alert('User Updated Successfully');
        window.location='manage_users.php';
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

<title>Edit User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>
<body>

<div class="wrapper">

    <?php include("sidebar.php"); ?>

    <div class="main-content">

        <?php include("header.php"); ?>

        <div class="container-fluid mt-4">
    <div class="card shadow">

        <div class="card-header">

            <h3>Edit User</h3>

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">

                    <label>Name</label>

                    <input
                    type="text"
                    name="name"
                    class="form-control"
                    value="<?php echo $user['name']; ?>"
                    required>

                </div>

                <div class="mb-3">

                    <label>Email</label>

                    <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="<?php echo $user['email']; ?>"
                    required>

                </div>

                <div class="mb-3">

                    <label>Role</label>

                    <select name="role" class="form-control">

                        <option value="admin"
                        <?php if($user['role']=="admin") echo "selected"; ?>>
                        Admin
                        </option>

                        <option value="faculty"
                        <?php if($user['role']=="faculty") echo "selected"; ?>>
                        Faculty
                        </option>

                        <option value="student"
                        <?php if($user['role']=="student") echo "selected"; ?>>
                        Student
                        </option>

                    </select>

                </div>

                <button
                type="submit"
                name="update_user"
                class="btn btn-primary">

                    Update User

                </button>

                <a href="manage_users.php"
                class="btn btn-secondary">

                    Back

                </a>

            </form>
            </div>

        </div>

    </div>

</div>

</body>

</html>