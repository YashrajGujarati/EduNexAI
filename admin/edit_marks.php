<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

$id = $_GET['id'];

/* Student List */

$students = db_query($conn,"
SELECT students.student_id, users.name
FROM students
INNER JOIN users
ON students.user_id = users.id
");

/* Subject List */

$subjects = db_query($conn,"
SELECT *
FROM subjects
");

/* Current Marks */

$query = "
SELECT *
FROM marks
WHERE mark_id='$id'
";

$result = db_query($conn,$query);

$mark = db_fetch_assoc($result);

if(isset($_POST['update_marks']))
{

    $student_id=$_POST['student_id'];
    $subject_id=$_POST['subject_id'];

    $internal=$_POST['internal_marks'];
    $external=$_POST['external_marks'];

    $total=$internal+$external;

    $update="

    UPDATE marks

    SET

    student_id='$student_id',

    subject_id='$subject_id',

    internal_marks='$internal',

    external_marks='$external',

    total_marks='$total'

    WHERE mark_id='$id'

    ";

    if(db_query($conn,$update))
    {
        echo "<script>

        alert('Marks Updated Successfully');

        window.location='manage_marks.php';

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

<title>Edit Marks</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header">

<h3>Edit Student Marks</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label>Student</label>

<select name="student_id" class="form-control">

<?php while($row=db_fetch_assoc($students)){ ?>

<option
value="<?php echo $row['student_id']; ?>"
<?php if($row['student_id']==$mark['student_id']) echo "selected"; ?>>

<?php echo $row['name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Subject</label>

<select name="subject_id" class="form-control">

<?php while($row=db_fetch_assoc($subjects)){ ?>

<option
value="<?php echo $row['subject_id']; ?>"
<?php if($row['subject_id']==$mark['subject_id']) echo "selected"; ?>>

<?php echo $row['subject_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label>Internal Marks</label>

<input
type="number"
name="internal_marks"
class="form-control"
value="<?php echo $mark['internal_marks']; ?>"
required>

</div>

<div class="mb-3">

<label>External Marks</label>

<input
type="number"
name="external_marks"
class="form-control"
value="<?php echo $mark['external_marks']; ?>"
required>

</div>

<button
type="submit"
name="update_marks"
class="btn btn-primary">

Update Marks

</button>

<a href="manage_marks.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</body>

</html>