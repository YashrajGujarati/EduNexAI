<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=student_report.xls");
echo "ID\tName\tClass\tRoll No\tAttendance (%)\n";
$query = "
SELECT
students.student_id,
users.name,
students.class,
students.roll_number,
students.attendance
FROM students
INNER JOIN users
ON students.user_id = users.id
ORDER BY students.student_id ASC
";

$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result))
{
    echo
    $row['student_id']."\t".
    $row['name']."\t".
    $row['class']."\t".
    $row['roll_number']."\t".
    $row['attendance']."%\n";
}

exit();

?>
