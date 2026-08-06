<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="faculty")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");
if(isset($_POST['save_attendance']))
{

    $faculty_id = $_SESSION['user_id'];

    $subject_id = $_POST['subject_id'];

    $unit_no = $_POST['unit_no'];

    $topic = mysqli_real_escape_string($conn,$_POST['topic']);

    foreach($_POST['attendance'] as $student_id => $status)
    {

        mysqli_query($conn,

        "INSERT INTO attendance
        (student_id,subject_id,faculty_id,unit_no,topic,attendance_date,status)

        VALUES

        ('$student_id',
        '$subject_id',
        '$faculty_id',
        '$unit_no',
        '$topic',
        CURDATE(),
        '$status')");

    }

    echo "<script>

    alert('Attendance Saved Successfully');

    window.location='manage_attendance.php';

    </script>";

}


$students = mysqli_query($conn,"SELECT * FROM students");

$subjects = mysqli_query($conn,"SELECT * FROM subjects");

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Manage Attendance</title>

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

<div class="container mt-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h4>Manage Attendance</h4>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<div class="mb-3">
    <label class="form-label">Select Class</label>

    <select name="class" class="form-control" required>

        <option value="">Select Class</option>

        <option value="BCA-1">BCA-1</option>

        <option value="BCA-2">BCA-2</option>

        <option value="BCA-3">BCA-3</option>

        <option value="BSc IT-1">BSc IT-1</option>

    </select>
</div>


<div class="mb-3">
    <label class="form-label">Select Subject</label>

    <select name="subject_id" class="form-control" required>

        <option value="">Select Subject</option>

<?php

$query = mysqli_query($conn,"SELECT * FROM subjects");

while($row = mysqli_fetch_assoc($query))
{

?>

<option value="<?php echo $row['subject_id']; ?>">

<?php echo $row['subject_name']; ?>

</option>

<?php

}

?>

    </select>
</div>


<div class="mb-3">

<label>Unit Number</label>

<select name="unit_no" class="form-control">

<option value="1">Unit 1</option>

<option value="2">Unit 2</option>

<option value="3">Unit 3</option>

<option value="4">Unit 4</option>

<option value="5">Unit 5</option>

</select>

</div>


<div class="mb-3">

<label>Today's Topic</label>

<input
type="text"
name="topic"
class="form-control"
placeholder="Enter Today's Topic"
required>

</div>


<button
type="submit"
name="load_students"
class="btn btn-primary">

Load Students

</button>

</div>

<?php

if(isset($_POST['load_students']))
{

$class = $_POST['class'];

$students = mysqli_query($conn,

"SELECT u.enrollment_no,
        s.student_id,
        s.roll_number,
        u.name

FROM students s

INNER JOIN users u

ON s.user_id=u.id

WHERE s.class='$class'

ORDER BY s.roll_number ASC");

?>

<hr>

<h4>Student Attendance</h4>

<table class="table table-bordered">

<tr>

<th>Enrollment</th>

<th>Roll</th>

<th>Name</th>

<th>Attendance</th>

</tr>

<?php

while($stu=mysqli_fetch_assoc($students))
{

?>

<tr>

<td><?php echo $stu['enrollment_no']; ?></td>

<td><?php echo $stu['roll_number']; ?></td>

<td><?php echo $stu['name']; ?></td>

<td>

<label>

<input type="radio"

name="attendance[<?php echo $stu['student_id']; ?>]"

value="Present"

checked>

Present

</label>

&nbsp;&nbsp;

<label>

<input type="radio"

name="attendance[<?php echo $stu['student_id']; ?>]"

value="Absent">

Absent

</label>

</td>

</tr>

<?php

}

?>

</table>
<input type="hidden" name="subject_id" value="<?php echo $_POST['subject_id']; ?>">

<input type="hidden" name="unit_no" value="<?php echo $_POST['unit_no']; ?>">

<input type="hidden" name="topic" value="<?php echo $_POST['topic']; ?>">

<button

class="btn btn-success"

type="submit"

name="save_attendance">

Save Attendance

</button>

<?php

}

?>
</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>