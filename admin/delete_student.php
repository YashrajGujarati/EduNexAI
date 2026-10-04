<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$id = intval($_GET['id']);

$query = "DELETE FROM students WHERE student_id='$id'";

if(db_query($conn,$query))
{
    echo "<script>

    alert('Student Deleted Successfully');

    window.location='manage_students.php';

    </script>";
}
else
{
    echo "<script>

    alert('Delete Failed');

    window.location='manage_students.php';

    </script>";
}

?>