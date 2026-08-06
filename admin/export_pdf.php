<?php

session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin")
{
    header("Location: ../login.php");
    exit();
}

include("../config/db.php");

require('../fpdf/fpdf.php');

$pdf = new FPDF();

$pdf->AddPage();

$pdf->SetFont('Arial','B',16);

$pdf->Cell(190,10,'EduNexAI Student Report',0,1,'C');

$pdf->Ln(5);

$pdf->SetFont('Arial','B',11);

$pdf->Cell(20,10,'ID',1);

$pdf->Cell(50,10,'Name',1);

$pdf->Cell(30,10,'Class',1);

$pdf->Cell(40,10,'Roll No',1);

$pdf->Cell(50,10,'Attendance',1);

$pdf->Ln();

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
";

$result = mysqli_query($conn, $query);

if(mysqli_num_rows($result) == 0)
{
    die("No Data Found");
}

$pdf->SetFont('Arial','',10);

while($row = mysqli_fetch_assoc($result))
{

    $pdf->Cell(20,10,$row['student_id'],1);

    $pdf->Cell(50,10,$row['name'],1);

    $pdf->Cell(30,10,$row['class'],1);

    $pdf->Cell(40,10,$row['roll_number'],1);

    $pdf->Cell(50,10,$row['attendance'].'%',1);

    $pdf->Ln();

}
while($row = mysqli_fetch_assoc($result))
{
    $pdf->Cell(20,10,$row['student_id'],1);
    $pdf->Cell(50,10,$row['name'],1);
    $pdf->Cell(30,10,$row['class'],1);
    $pdf->Cell(40,10,$row['roll_number'],1);
    $pdf->Cell(50,10,$row['attendance'].'%',1);

    $pdf->Ln();
}

/* PDF Download */

$pdf->Output(
    'D',
    'EduNexAI_Student_Report.pdf'
);

exit();

?>