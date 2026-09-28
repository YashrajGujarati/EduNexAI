<?php

// Load .env file if it exists (for local XAMPP development)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    if ($envVars) {
        foreach ($envVars as $key => $value) {
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

$db_host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost');
$db_user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$db_pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : (getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : (getenv('MYSQL_ROOT_PASSWORD') !== false ? getenv('MYSQL_ROOT_PASSWORD') : ''));
$db_name = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'student_ai_system'));
$db_port = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306);

// Support Railway MYSQL_URL / DATABASE_URL if provided
$mysql_url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL');
if ($mysql_url) {
    $db_opts = parse_url($mysql_url);
    if ($db_opts) {
        if (!empty($db_opts['host'])) $db_host = $db_opts['host'];
        if (!empty($db_opts['user'])) $db_user = $db_opts['user'];
        if (isset($db_opts['pass'])) $db_pass = $db_opts['pass'];
        if (!empty($db_opts['path'])) $db_name = ltrim($db_opts['path'], '/');
        if (!empty($db_opts['port'])) $db_port = $db_opts['port'];
    }
}

$conn = mysqli_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port
);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

?>