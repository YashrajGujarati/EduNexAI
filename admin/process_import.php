<?php

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

require_once(__DIR__ . '/../config/db.php');

require_once(__DIR__ . '/../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

if(isset($_POST['import']))
{

    if($_FILES['excel_file']['error'] != 0)
    {
        die("Please Select Excel File");
    }

    $file = $_FILES['excel_file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);

    $sheet = $spreadsheet->getActiveSheet();

    $rows = $sheet->toArray();

    // Header Row Skip
    unset($rows[0]);

    // Enrollment Number Start
    $enrollment = 240801001;

    // Last Enrollment Number Check
  $checkEnroll = mysqli_query($conn,

"SELECT MAX(CAST(enrollment_no AS UNSIGNED)) AS last_enrollment
FROM users
WHERE role='student'
AND enrollment_no REGEXP '^[0-9]+$'");

$last = mysqli_fetch_assoc($checkEnroll);

if(!empty($last['last_enrollment']))
{
    $enrollment = $last['last_enrollment'] + 1;
}
else
{
    $enrollment = 240801001;
}
    

    foreach($rows as $row)
    {

        $name = mysqli_real_escape_string($conn, trim($row[0]));
        $email = mysqli_real_escape_string($conn, trim($row[1]));
        $mobile = mysqli_real_escape_string($conn, trim($row[2]));
        $class = mysqli_real_escape_string($conn, trim($row[3]));
        $roll = trim($row[4]);
        $attendance = (float)$row[5];

        // Skip Empty Name
        if(empty($name))
        {
            continue;
        }

        // Duplicate Email
        $emailCheck = mysqli_query($conn,
        "SELECT id FROM users WHERE email='$email'");

        if(mysqli_num_rows($emailCheck) > 0)
        {
            continue;
        }

        // Duplicate Mobile
        $mobileCheck = mysqli_query($conn,
        "SELECT id FROM users WHERE mobile='$mobile'");

        if(mysqli_num_rows($mobileCheck) > 0)
        {
            continue;
        }

        // Password = Mobile Number
        $password = password_hash($mobile, PASSWORD_DEFAULT);

        // Insert into Users Table
        $userInsert = mysqli_query($conn,

        "INSERT INTO users
        (name,email,mobile,enrollment_no,password,role)

        VALUES

        ('$name',
        '$email',
        '$mobile',
        '$enrollment',
        '$password',
        'student')");

        if($userInsert)
        {

            $user_id = mysqli_insert_id($conn);

            mysqli_query($conn,

            "INSERT INTO students
            (user_id,class,roll_number,attendance)

            VALUES

            ('$user_id',
            '$class',
            '$roll',
            '$attendance')");

            $enrollment++;

        }

    }

    echo "<script>

    alert('Students Imported Successfully');

    window.location='manage_students.php';

    </script>";

}

?>