<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="admin")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

/* Student Dropdown */
$students = mysqli_query($conn,"
SELECT students.student_id, users.name
FROM students
INNER JOIN users
ON students.user_id = users.id
");

/* Subject Dropdown */
$subjects = mysqli_query($conn,"
SELECT subject_id, subject_name
FROM subjects
");

if(isset($_POST['add_marks']))
{
    $student_id = $_POST['student_id'];
    $subject_id = $_POST['subject_id'];
    $internal = $_POST['internal_marks'];
    $external = $_POST['external_marks'];

    $total = $internal + $external;

    $query = "INSERT INTO marks
    (student_id,subject_id,internal_marks,external_marks,total_marks)
    VALUES
    ('$student_id','$subject_id','$internal','$external','$total')";

    if(mysqli_query($conn,$query))
    {
        echo "<script>

        alert('Marks Added Successfully');

        window.location='manage_marks.php';

        </script>";
    }
    else
    {
        echo "<script>

        alert('Failed To Add Marks');

        </script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Marks | EduNexAI</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<link rel="stylesheet" href="../css/dashboard.css">

</head>

<body>

<div class="container mt-5">

<div class="card shadow">

<div class="card-header">

<h3>Add Student Marks</h3>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label class="form-label">

Select Student

</label>

<select
name="student_id"
class="form-control"
required>

<option value="">Select Student</option>

<?php while($row=mysqli_fetch_assoc($students)){ ?>

<option value="<?php echo $row['student_id']; ?>">

<?php echo $row['name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Select Subject

</label>

<select
name="subject_id"
class="form-control"
required>

<option value="">Select Subject</option>

<?php while($row=mysqli_fetch_assoc($subjects)){ ?>

<option value="<?php echo $row['subject_id']; ?>">

<?php echo $row['subject_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Internal Marks

</label>

<input
type="number"
name="internal_marks"
class="form-control"
min="0"
max="30"
required>

</div>

<div class="mb-3">

<label class="form-label">

External Marks

</label>

<input
type="number"
name="external_marks"
class="form-control"
min="0"
max="70"
required>

</div>

<button
type="submit"
name="add_marks"
class="btn btn-primary">

<i class="fas fa-save"></i>

Save Marks

</button>

<a href="manage_marks.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>