<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

/* User ID મેળવો */
$id = intval($_GET['id']);

/* User Delete Query */
$query = "DELETE FROM users WHERE id='$id'";

if(db_query($conn, $query))
{
    echo "<script>

    alert('User Deleted Successfully');

    window.location='manage_users.php';

    </script>";
}
else
{
    echo "<script>

    alert('Unable To Delete User');

    window.location='manage_users.php';

    </script>";
}

?>