<?php
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Import Students</title>

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

        <div class="card mt-3 mb-3 shadow-sm">

            <div class="card-body py-3 px-4">

                <h4 class="mb-0">

                    Import Students

                </h4>

            </div>

        </div>

        <div class="card shadow">

            <div class="card-body">

                <form
                action="process_import.php"
                method="POST"
                enctype="multipart/form-data">

                    <div class="mb-3">

                        <label class="form-label">

                            Select Excel File

                        </label>

                        <input
                        type="file"
                        name="excel_file"
                        class="form-control"
                        accept=".xlsx,.xls"
                        required>

                    </div>

                    <button
                    type="submit"
                    name="import"
                    class="btn btn-success">

                        <i class="fas fa-file-import"></i>

                        Import Students

                    </button>

                    <a
                    href="dashboard.php"
                    class="btn btn-secondary">

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