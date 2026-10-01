<?php

// ============================================================
// EduNexAI — Database Configuration
// Supports: XAMPP local development + Railway PHP + Railway MySQL
// ============================================================

// 1. Include CORS & Cross-Origin Session configuration
require_once(__DIR__ . '/cors.php');

// 2. Include Gemini AI safe helper
require_once(__DIR__ . '/gemini.php');

// 3. Load .env file if present (for local development)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = @file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines !== false) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                $len = strlen($value);
                if ($len >= 2 && (
                    ($value[0] === '"' && $value[$len - 1] === '"') ||
                    ($value[0] === "'" && $value[$len - 1] === "'")
                )) {
                    $value = substr($value, 1, -1);
                }

                if (getenv($key) === false) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }
}

// ============================================================
// 4. Resolve database credentials from environment variables
// Priority: DB_* vars -> MYSQL* vars (Railway) -> defaults
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
$db_port = (int)(getenv('DB_PORT') ?: (getenv('MYSQLPORT') ?: 3306));

// Support Railway MYSQL_PUBLIC_URL / MYSQL_URL / DATABASE_URL if provided
$mysql_url = getenv('MYSQL_PUBLIC_URL') ?: (getenv('MYSQL_URL') ?: getenv('DATABASE_URL'));
if ($mysql_url) {
    $db_opts = parse_url($mysql_url);
    if ($db_opts !== false) {
        if (!empty($db_opts['host'])) $db_host = $db_opts['host'];
        if (!empty($db_opts['user'])) $db_user = $db_opts['user'];
        if (isset($db_opts['pass']))  $db_pass = $db_opts['pass'];
        if (!empty($db_opts['path'])) $db_name = ltrim($db_opts['path'], '/');
        if (!empty($db_opts['port'])) $db_port = (int)$db_opts['port'];
    }
}

// ============================================================
// 5. Establish MySQLi connection
// ============================================================
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect(
    $db_host,
    $db_user,
    $db_pass,
    $db_name,
    $db_port
);

// Fallback to local MySQL only in local/development environment
$app_env = strtolower(getenv('APP_ENV') ?: 'production');
if (!$conn && ($app_env === 'local' || $app_env === 'development') && $db_host !== 'localhost' && $db_host !== '127.0.0.1') {
    $conn = @mysqli_connect('localhost', 'root', '', 'student_ai_system', 3306);
}

if (!$conn) {
    $connect_err = mysqli_connect_error();
    error_log("EduNexAI Database Connection Error: " . $connect_err);

    $app_debug = strtolower(getenv('APP_DEBUG') ?: 'false');
    if ($app_debug === 'true' || $app_debug === '1') {
        die("Database Connection Failed: " . htmlspecialchars($connect_err));
    } else {
        die("A database connection error occurred. Please contact the administrator.");
    }
}

mysqli_set_charset($conn, 'utf8mb4');
?>