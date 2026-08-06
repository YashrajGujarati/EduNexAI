<?php
session_start();
include("config/db.php");

if(isset($_POST['login']))
{
    $login_id = mysqli_real_escape_string($conn,$_POST['login_id']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users
              WHERE email='$login_id'
              OR enrollment_no='$login_id'";

    $result = mysqli_query($conn,$query);

    if(mysqli_num_rows($result)==1)
    {
        $row = mysqli_fetch_assoc($result);

        if(password_verify($password, $row['password']))
        {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            if($row['role']=="admin")
            {
                header("Location: admin/dashboard.php");
                exit();
            }
            elseif($row['role']=="faculty")
            {
                header("Location: faculty/dashboard.php");
                exit();
            }
          else
{
    if($row['first_login'] == 1)
    {
        header("Location: student/change_password.php");
        exit();
    }
    else
    {
        header("Location: student/dashboard.php");
        exit();
    }
}
        }
        else
        {
            echo "<script>
            alert('Incorrect Password');
            </script>";
        }
    }
    else
    {
        echo "<script>
        alert('Invalid Login ID');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>EduNexAI | Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="container">

    <div class="row justify-content-center align-items-center vh-100">

        <div class="col-lg-5">

            <div class="login-card">

                <div class="text-center">

                    <i class="fas fa-user-circle login-icon"></i>

                    <h2>Welcome Back</h2>

                    <p>Login to EduNexAI</p>

                </div>

                <form action="" method="POST">

                    <div class="mb-3">

                        <label class="form-label">

                            Login ID

                        </label>

                        <input

                            type="text"

                            name="login_id"

                            class="form-control"

                            placeholder="Enter Email or Enrollment Number"

                            required>

                    </div>

                    <div class="mb-3">

                        <label class="form-label">

                            Password

                        </label>

                        <div class="input-group">

                            <input

                                type="password"

                                id="password"

                                name="password"

                                class="form-control"

                                placeholder="Enter Password"

                                required>

                            <button

                                class="btn btn-outline-secondary"

                                type="button"

                                id="togglePassword">

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                    </div>

                    <div class="d-flex justify-content-between mb-3">

                        <div>

                            <input

                                type="checkbox"

                                class="form-check-input"

                                id="remember">

                            <label

                                class="form-check-label"

                                for="remember">

                                Remember Me

                            </label>

                        </div>

                        <a href="#">

                            Forgot Password?

                        </a>

                    </div>

                    <button

                        type="submit"

                        name="login"

                        class="btn btn-primary w-100">

                        Login

                    </button>

                </form>

                <hr>

                <p class="text-center">

                    Don't have an account?

                    <a href="register.php">

                        Register

                    </a>

                </p>

                <p class="text-center">

                    <a href="index.html">

                        ← Back to Home

                    </a>

                </p>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/login.js"></script>

</body>

</html>