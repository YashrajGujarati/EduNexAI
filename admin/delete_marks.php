    <?php

    session_start();

    if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
    {
        header("Location: ../login.php");
        exit();
    }

    require_once(__DIR__ . '/../config/db.php');

    if(isset($_GET['id']))
    {

        $id = db_real_escape_string($conn, $_GET['id']);

        $query = "DELETE FROM marks WHERE mark_id = '$id'";

        if(db_query($conn, $query))
        {

            echo "<script>

                alert('Marks Deleted Successfully');

                window.location='manage_marks.php';

            </script>";

        }
        else
        {

            echo "<script>

                alert('Failed to Delete Marks');

                window.location='manage_marks.php';

            </script>";

        }

    }
    else
    {

        header("Location: manage_marks.php");
        exit();

    }

    ?>