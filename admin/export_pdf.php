<?php

session_start();

/* =========================================================
   ADMIN SECURITY
========================================================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE & FPDF
========================================================= */

require_once(__DIR__ . '/../config/db.php');

require_once(__DIR__ . '/../fpdf/fpdf.php');


/* =========================================================
   CHECK DATABASE CONNECTION
========================================================= */

if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}


/* =========================================================
   FETCH STUDENT DATA
========================================================= */

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

$result = db_query($conn, $query);

if (!$result) {
    die("Database Query Failed: " . db_error($conn));
}


/* =========================================================
   PREPARE DATA
========================================================= */

$students = [];

$total_students = 0;
$total_attendance = 0;
$highest_attendance = 0;
$best_performer = "N/A";


while ($row = db_fetch_assoc($result)) {

    $students[] = $row;

    $total_students++;

    $attendance = (float)$row['attendance'];

    $total_attendance += $attendance;

    if ($attendance > $highest_attendance) {

        $highest_attendance = $attendance;

        $best_performer = $row['name'];
    }
}


/* =========================================================
   NO DATA CHECK
========================================================= */

if ($total_students == 0) {
    die("No Student Data Found");
}


/* =========================================================
   AVERAGE ATTENDANCE
========================================================= */

$average_attendance = 0;

if ($total_students > 0) {

    $average_attendance =
        $total_attendance / $total_students;
}


/* =========================================================
   CUSTOM PDF CLASS
========================================================= */

class PDF extends FPDF
{

    /* =====================================================
       HEADER
    ===================================================== */

    function Header()
    {

        /* Top Blue Bar */

        $this->SetFillColor(13, 82, 180);

        $this->Rect(
            0,
            0,
            210,
            8,
            'F'
        );


        /* EduNexAI Brand */

        $this->SetTextColor(13, 82, 180);

        $this->SetFont(
            'Arial',
            'B',
            20
        );

        $this->SetXY(
            15,
            15
        );

        $this->Cell(
            70,
            9,
            'EduNexAI',
            0,
            1,
            'L'
        );


        /* Tagline */

        $this->SetTextColor(
            90,
            90,
            90
        );

        $this->SetFont(
            'Arial',
            '',
            8
        );

        $this->SetXY(
            15,
            25
        );

        $this->Cell(
            100,
            5,
            'Smart Student Performance & Learning Analytics',
            0,
            1,
            'L'
        );


        /* Report Title */

        $this->SetTextColor(
            20,
            45,
            90
        );

        $this->SetFont(
            'Arial',
            'B',
            18
        );

        $this->SetXY(
            90,
            16
        );

        $this->Cell(
            105,
            9,
            'STUDENT REPORT',
            0,
            1,
            'R'
        );


        /* Subtitle */

        $this->SetTextColor(
            90,
            90,
            90
        );

        $this->SetFont(
            'Arial',
            '',
            9
        );

        $this->SetXY(
            90,
            26
        );

        $this->Cell(
            105,
            5,
            'Academic Performance Summary',
            0,
            1,
            'R'
        );


        /* Report Date */

        $this->SetTextColor(
            70,
            70,
            70
        );

        $this->SetFont(
            'Arial',
            'B',
            8
        );

        $this->SetXY(
            135,
            36
        );

        $this->Cell(
            60,
            5,
            'Report Date: ' . date('d M Y'),
            0,
            1,
            'R'
        );


        /* Divider */

        $this->SetDrawColor(
            13,
            82,
            180
        );

        $this->SetLineWidth(
            0.5
        );

        $this->Line(
            15,
            48,
            195,
            48
        );
    }


    /* =====================================================
       FOOTER
    ===================================================== */

    function Footer()
    {

        /* Footer Line */

        $this->SetDrawColor(
            13,
            82,
            180
        );

        $this->SetLineWidth(
            0.4
        );

        $this->Line(
            15,
            282,
            195,
            282
        );


        /* Footer Text */

        $this->SetTextColor(
            100,
            100,
            100
        );

        $this->SetFont(
            'Arial',
            '',
            8
        );

        $this->SetXY(
            15,
            285
        );

        $this->Cell(
            60,
            5,
            'EduNexAI',
            0,
            0,
            'L'
        );


        $this->Cell(
            70,
            5,
            'Smart Student Performance & Learning Analytics',
            0,
            0,
            'C'
        );


        $this->Cell(
            50,
            5,
            'Page ' . $this->PageNo(),
            0,
            1,
            'R'
        );
    }
}


/* =========================================================
   CREATE PDF
========================================================= */

$pdf = new PDF(
    'P',
    'mm',
    'A4'
);


$pdf->SetMargins(
    15,
    12,
    15
);


$pdf->SetAutoPageBreak(
    true,
    18
);


$pdf->AddPage();


/* =========================================================
   REPORT SUMMARY AREA
========================================================= */

$summaryY = 56;


/* Outer Background */

$pdf->SetFillColor(
    248,
    250,
    253
);

$pdf->SetDrawColor(
    225,
    230,
    238
);

$pdf->Rect(
    15,
    $summaryY,
    180,
    42,
    'DF'
);


/* =========================================================
   CARD 1 - TOTAL STUDENTS
========================================================= */

$pdf->SetFillColor(
    239,
    247,
    255
);

$pdf->SetDrawColor(
    210,
    225,
    245
);

$pdf->Rect(
    20,
    62,
    40,
    30,
    'DF'
);


$pdf->SetTextColor(
    13,
    82,
    180
);

$pdf->SetFont(
    'Arial',
    'B',
    8
);

$pdf->SetXY(
    22,
    67
);

$pdf->Cell(
    36,
    5,
    'TOTAL STUDENTS',
    0,
    1,
    'C'
);


$pdf->SetFont(
    'Arial',
    'B',
    17
);

$pdf->SetXY(
    22,
    76
);

$pdf->Cell(
    36,
    9,
    $total_students,
    0,
    1,
    'C'
);


/* =========================================================
   CARD 2 - AVERAGE ATTENDANCE
========================================================= */

$pdf->SetFillColor(
    240,
    250,
    242
);

$pdf->SetDrawColor(
    210,
    235,
    215
);

$pdf->Rect(
    65,
    62,
    40,
    30,
    'DF'
);


$pdf->SetTextColor(
    40,
    140,
    70
);

$pdf->SetFont(
    'Arial',
    'B',
    8
);

$pdf->SetXY(
    67,
    67
);

$pdf->Cell(
    36,
    5,
    'AVG ATTENDANCE',
    0,
    1,
    'C'
);


$pdf->SetFont(
    'Arial',
    'B',
    14
);

$pdf->SetXY(
    67,
    76
);

$pdf->Cell(
    36,
    9,
    number_format(
        $average_attendance,
        2
    ) . '%',
    0,
    1,
    'C'
);


/* =========================================================
   CARD 3 - HIGHEST ATTENDANCE
========================================================= */

$pdf->SetFillColor(
    247,
    243,
    255
);

$pdf->SetDrawColor(
    225,
    215,
    245
);

$pdf->Rect(
    110,
    62,
    40,
    30,
    'DF'
);


$pdf->SetTextColor(
    105,
    55,
    170
);

$pdf->SetFont(
    'Arial',
    'B',
    7.5
);

$pdf->SetXY(
    112,
    67
);

$pdf->Cell(
    36,
    5,
    'HIGHEST ATTENDANCE',
    0,
    1,
    'C'
);


$pdf->SetFont(
    'Arial',
    'B',
    14
);

$pdf->SetXY(
    112,
    76
);

$pdf->Cell(
    36,
    9,
    number_format(
        $highest_attendance,
        2
    ) . '%',
    0,
    1,
    'C'
);


/* =========================================================
   CARD 4 - BEST PERFORMER
========================================================= */

$pdf->SetFillColor(
    255,
    248,
    237
);

$pdf->SetDrawColor(
    245,
    225,
    195
);

$pdf->Rect(
    155,
    62,
    40,
    30,
    'DF'
);


$pdf->SetTextColor(
    230,
    125,
    20
);

$pdf->SetFont(
    'Arial',
    'B',
    7.5
);

$pdf->SetXY(
    157,
    67
);

$pdf->Cell(
    36,
    5,
    'BEST PERFORMER',
    0,
    1,
    'C'
);


$pdf->SetFont(
    'Arial',
    'B',
    9
);

$pdf->SetXY(
    157,
    76
);

$pdf->Cell(
    36,
    9,
    substr(
        $best_performer,
        0,
        18
    ),
    0,
    1,
    'C'
);


/* =========================================================
   STUDENT DETAILS TITLE
========================================================= */

$pdf->SetTextColor(
    13,
    82,
    180
);

$pdf->SetFont(
    'Arial',
    'B',
    13
);

$pdf->SetXY(
    15,
    108
);

$pdf->Cell(
    180,
    8,
    'STUDENT DETAILS',
    0,
    1,
    'C'
);


/* Decorative Lines */

$pdf->SetDrawColor(
    190,
    205,
    225
);

$pdf->SetLineWidth(
    0.3
);

$pdf->Line(
    15,
    112,
    65,
    112
);

$pdf->Line(
    145,
    112,
    195,
    112
);


/* =========================================================
   TABLE HEADER
========================================================= */

$pdf->SetXY(
    15,
    119
);

$pdf->SetFillColor(
    13,
    82,
    180
);

$pdf->SetTextColor(
    255,
    255,
    255
);

$pdf->SetFont(
    'Arial',
    'B',
    9
);


/* ID */

$pdf->Cell(
    18,
    10,
    'ID',
    1,
    0,
    'C',
    true
);


/* NAME */

$pdf->Cell(
    48,
    10,
    'Name',
    1,
    0,
    'C',
    true
);


/* CLASS */

$pdf->Cell(
    32,
    10,
    'Class',
    1,
    0,
    'C',
    true
);


/* ROLL */

$pdf->Cell(
    35,
    10,
    'Roll No',
    1,
    0,
    'C',
    true
);


/* ATTENDANCE */

$pdf->Cell(
    47,
    10,
    'Attendance',
    1,
    1,
    'C',
    true
);


/* =========================================================
   TABLE DATA
========================================================= */

$pdf->SetFont(
    'Arial',
    '',
    9
);


foreach ($students as $index => $row) {

    /* Alternate Row Background */

    if ($index % 2 == 0) {

        $pdf->SetFillColor(
            248,
            251,
            255
        );

    } else {

        $pdf->SetFillColor(
            255,
            255,
            255
        );
    }


    /* Normal Text */

    $pdf->SetTextColor(
        40,
        40,
        40
    );


    /* Student ID */

    $pdf->Cell(
        18,
        10,
        $row['student_id'],
        1,
        0,
        'C',
        true
    );


    /* Student Name */

    $pdf->Cell(
        48,
        10,
        substr(
            $row['name'],
            0,
            25
        ),
        1,
        0,
        'L',
        true
    );


    /* Class */

    $pdf->Cell(
        32,
        10,
        $row['class'],
        1,
        0,
        'C',
        true
    );


    /* Roll Number */

    $pdf->Cell(
        35,
        10,
        $row['roll_number'],
        1,
        0,
        'C',
        true
    );


    /* Attendance Color */

    $attendance =
        (float)$row['attendance'];


    if ($attendance >= 90) {

        $pdf->SetTextColor(
            35,
            130,
            65
        );

    } elseif ($attendance >= 75) {

        $pdf->SetTextColor(
            220,
            135,
            20
        );

    } else {

        $pdf->SetTextColor(
            210,
            50,
            60
        );
    }


    /* Attendance */

    $pdf->Cell(
        47,
        10,
        number_format(
            $attendance,
            2
        ) . '%',
        1,
        1,
        'C',
        true
    );
}


/* =========================================================
   REPORT SUMMARY
========================================================= */

$currentY =
    $pdf->GetY() + 10;


/*
   Keep summary on same page if enough space.
   Otherwise start a new page.
*/

if ($currentY > 245) {

    $pdf->AddPage();

    $currentY = 25;
}


/* Summary Box */

$pdf->SetFillColor(
    248,
    250,
    253
);

$pdf->SetDrawColor(
    220,
    225,
    235
);

$pdf->Rect(
    15,
    $currentY,
    180,
    38,
    'DF'
);


/* Summary Title */

$pdf->SetTextColor(
    13,
    82,
    180
);

$pdf->SetFont(
    'Arial',
    'B',
    11
);

$pdf->SetXY(
    22,
    $currentY + 6
);

$pdf->Cell(
    166,
    6,
    'REPORT SUMMARY',
    0,
    1,
    'L'
);


/* Summary Details */

$pdf->SetTextColor(
    70,
    70,
    70
);

$pdf->SetFont(
    'Arial',
    '',
    9
);


$pdf->SetXY(
    22,
    $currentY + 16
);

$pdf->Cell(
    80,
    6,
    'Total Students: ' . $total_students,
    0,
    0,
    'L'
);


$pdf->Cell(
    80,
    6,
    'Average Attendance: ' .
    number_format(
        $average_attendance,
        2
    ) . '%',
    0,
    1,
    'L'
);


$pdf->SetX(
    22
);

$pdf->Cell(
    80,
    6,
    'Highest Attendance: ' .
    number_format(
        $highest_attendance,
        2
    ) . '%',
    0,
    0,
    'L'
);


$pdf->Cell(
    80,
    6,
    'Best Performer: ' .
    substr(
        $best_performer,
        0,
        22
    ),
    0,
    1,
    'L'
);


/* =========================================================
   CLEAN OUTPUT BUFFER
========================================================= */

if (ob_get_length()) {
    ob_end_clean();
}


/* =========================================================
   DOWNLOAD PDF
========================================================= */

$pdf->Output(
    'D',
    'EduNexAI_Student_Report.pdf'
);

exit();

?>