<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "student") {
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

if(isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if($new_password != $confirm_password) {
        $message = "New password and confirmation password do not match.";
        $message_type = "danger";
    } else {
        $password = password_hash($new_password, PASSWORD_DEFAULT);

        mysqli_query($conn, "
        UPDATE users
        SET password='$password',
        first_login='0'
        WHERE id='$user_id'
        ");

        $message = "Password updated successfully! Redirecting to dashboard...";
        $message_type = "success";
        echo "<script>setTimeout(function(){ window.location='dashboard.php'; }, 1500);</script>";
    }
}

$page_title = "Change Password";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | EduNexAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content">
        <?php include("header.php"); ?>

        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 text-center">
                        <div class="dashboard-icon icon-purple mx-auto mb-2" style="width:54px; height:54px; font-size:24px;">
                            <i class="fas fa-key"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">Account Security</h4>
                        <small class="text-muted">Update your account password</small>
                    </div>

                    <div class="card-body p-4">
                        <?php if(!empty($message)): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock text-muted"></i></span>
                                    <input type="password" name="new_password" class="form-control ps-2" placeholder="Enter new password" required minlength="6">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-lock-open text-muted"></i></span>
                                    <input type="password" name="confirm_password" class="form-control ps-2" placeholder="Confirm new password" required minlength="6">
                                </div>
                            </div>

                            <button type="submit" name="change_password" class="btn btn-primary w-100 py-2">
                                <i class="fas fa-check-circle me-1"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>