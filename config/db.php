<?php

$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$db_name = getenv('DB_NAME') ?: 'student_ai_system';

$conn = mysqli_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name
);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>