<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "student_ai_system"
);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>