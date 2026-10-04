<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$id = intval($_GET['id']);

$query = "DELETE FROM subjects WHERE subject_id='$id'";

if(db_query($conn,$query))
{
    echo "<script>

    alert('Subject Deleted Successfully');

    window.location='manage_subjects.php';

    </script>";
}
else
{
    echo "<script>

    alert('Delete Failed');

    window.location='manage_subjects.php';

    </script>";
}

?>