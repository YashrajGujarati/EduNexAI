<?php

// ============================================================
// EduNexAI — Database Configuration
// Supports: XAMPP local development + Vercel + Railway MySQL
// ============================================================

// Load .env file if it exists (for local XAMPP development only).
// On Vercel, environment variables are injected directly — .env is NOT present.
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    if ($envVars !== false) {
        foreach ($envVars as $key => $value) {
            // Only set if not already defined by the actual system environment
            if (getenv($key) === false) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// ============================================================
// Resolve database credentials from environment variables
// Priority: DB_* vars (Vercel) → MYSQL* vars (Railway internal) → defaults (local XAMPP)
// ============================================================

$db_host = getenv('DB_HOST') ?: (getenv('MYSQLHOST') ?: 'localhost');
$db_user = getenv('DB_USER') ?: (getenv('MYSQLUSER') ?: 'root');
$db_pass = getenv('DB_PASSWORD') !== false
    ? getenv('DB_PASSWORD')
    : (getenv('MYSQLPASSWORD') !== false
        ? getenv('MYSQLPASSWORD')
        : (getenv('MYSQL_ROOT_PASSWORD') !== false
            ? getenv('MYSQL_ROOT_PASSWORD')
            : ''));
$db_name = getenv('DB_NAME') ?: (getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'student_ai_system'));
$db_port = getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306);

// Support Railway MYSQL_PUBLIC_URL / MYSQL_URL / DATABASE_URL if provided as full connection string
$mysql_url = getenv('MYSQL_PUBLIC_URL') ?: (getenv('MYSQL_URL') ?: getenv('DATABASE_URL'));
if ($mysql_url) {
    $db_opts = parse_url($mysql_url);
    if ($db_opts !== false) {
        if (!empty($db_opts['host'])) $db_host = $db_opts['host'];
        if (!empty($db_opts['user'])) $db_user = $db_opts['user'];
        if (isset($db_opts['pass']))  $db_pass = $db_opts['pass'];
        if (!empty($db_opts['path'])) $db_name = ltrim($db_opts['path'], '/');
        if (!empty($db_opts['port'])) $db_port = $db_opts['port'];
    }
}

// ============================================================
// Establish MySQLi connection
// ============================================================
$conn = mysqli_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    (int)$db_port
);

if (!$conn) {
    $app_debug = getenv('APP_DEBUG');
    if ($app_debug === 'true' || $app_debug === '1') {
        die("Database Connection Failed: " . mysqli_connect_error());
    } else {
        die("A database connection error occurred. Please contact the administrator.");
    }
}

// Set charset to utf8mb4 for proper character support
mysqli_set_charset($conn, 'utf8mb4');

?>