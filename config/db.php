<?php

// ============================================================
// EduNexAI — MongoDB Database Configuration
// Supports: Localhost MongoDB + Cloud MongoDB / Atlas / Railway
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

// 4. Include MongoDB Driver & SQL Translation Engine
require_once(__DIR__ . '/../includes/mongodb_driver.php');

// ============================================================
// 5. Resolve MongoDB credentials from environment variables
// ============================================================
$mongo_uri = getenv('MONGODB_URI') ?: (getenv('MONGO_URL') ?: 'mongodb://127.0.0.1:27017');
$mongo_db  = getenv('MONGODB_DATABASE') ?: (getenv('DB_NAME') ?: 'student_ai_system');

try {
    $conn = new EduNexMongoDriver($mongo_uri, $mongo_db);
} catch (Exception $e) {
    error_log("EduNexAI MongoDB Connection Error: " . $e->getMessage());
    $app_debug = strtolower(getenv('APP_DEBUG') ?: 'false');
    if ($app_debug === 'true' || $app_debug === '1') {
        die("MongoDB Connection Failed: " . htmlspecialchars($e->getMessage()));
    } else {
        die("A database connection error occurred. Please contact the administrator.");
    }
}

// ============================================================
// 6. Global Database Compatibility Functions
// ============================================================

if (!function_exists('db_query')) {
    function db_query($connection, $query) {
        if (is_object($connection) && method_exists($connection, 'query')) {
            return $connection->query($query);
        }
        return false;
    }
}

if (!function_exists('db_fetch_assoc')) {
    function db_fetch_assoc($result) {
        if (is_object($result) && method_exists($result, 'fetch_assoc')) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

if (!function_exists('db_fetch_array')) {
    function db_fetch_array($result, $mode = 3) {
        if (is_object($result) && method_exists($result, 'fetch_array')) {
            return $result->fetch_array($mode);
        }
        return null;
    }
}

if (!function_exists('db_fetch_row')) {
    function db_fetch_row($result) {
        if (is_object($result) && method_exists($result, 'fetch_row')) {
            return $result->fetch_row();
        }
        return null;
    }
}

if (!function_exists('db_num_rows')) {
    function db_num_rows($result) {
        if (is_object($result)) {
            if (isset($result->num_rows)) return $result->num_rows;
            if (method_exists($result, 'count')) return $result->count();
        }
        return 0;
    }
}

if (!function_exists('db_insert_id')) {
    function db_insert_id($connection) {
        if (is_object($connection)) {
            if (isset($connection->insert_id)) return $connection->insert_id;
            if (method_exists($connection, 'get_insert_id')) return $connection->get_insert_id();
        }
        return 0;
    }
}

if (!function_exists('db_real_escape_string')) {
    function db_real_escape_string($connection, $string) {
        if (is_object($connection) && method_exists($connection, 'real_escape_string')) {
            return $connection->real_escape_string($string);
        }
        return addslashes((string)$string);
    }
}

if (!function_exists('db_error')) {
    function db_error($connection) {
        if (is_object($connection) && isset($connection->error)) {
            return $connection->error;
        }
        return '';
    }
}

if (!function_exists('db_prepare')) {
    function db_prepare($connection, $query) {
        if (is_object($connection) && method_exists($connection, 'prepare')) {
            return $connection->prepare($query);
        }
        return false;
    }
}

if (!function_exists('db_stmt_bind_param')) {
    function db_stmt_bind_param($stmt, $types, &...$vars) {
        if (is_object($stmt) && method_exists($stmt, 'bind_param')) {
            return $stmt->bind_param($types, ...$vars);
        }
        return false;
    }
}

if (!function_exists('db_stmt_execute')) {
    function db_stmt_execute($stmt) {
        if (is_object($stmt) && method_exists($stmt, 'execute')) {
            return $stmt->execute();
        }
        return false;
    }
}

if (!function_exists('db_stmt_get_result')) {
    function db_stmt_get_result($stmt) {
        if (is_object($stmt) && method_exists($stmt, 'get_result')) {
            return $stmt->get_result();
        }
        return false;
    }
}

if (!function_exists('db_stmt_close')) {
    function db_stmt_close($stmt) {
        if (is_object($stmt) && method_exists($stmt, 'close')) {
            return $stmt->close();
        }
        return true;
    }
}

if (!function_exists('db_data_seek')) {
    function db_data_seek($result, $offset) {
        if (is_object($result) && method_exists($result, 'data_seek')) {
            return $result->data_seek($offset);
        }
        return false;
    }
}

if (!function_exists('db_close')) {
    function db_close($connection) {
        return true;
    }
}

// Fallback mysqli aliases if extension_loaded('mysqli') is false
if (!function_exists('mysqli_query')) {
    function mysqli_query($c, $q) { return db_query($c, $q); }
    function mysqli_fetch_assoc($r) { return db_fetch_assoc($r); }
    function mysqli_fetch_array($r, $m = 3) { return db_fetch_array($r, $m); }
    function mysqli_fetch_row($r) { return db_fetch_row($r); }
    function mysqli_num_rows($r) { return db_num_rows($r); }
    function mysqli_insert_id($c) { return db_insert_id($c); }
    function mysqli_real_escape_string($c, $s) { return db_real_escape_string($c, $s); }
    function mysqli_error($c) { return db_error($c); }
    function mysqli_prepare($c, $q) { return db_prepare($c, $q); }
    function mysqli_stmt_bind_param($s, $t, &...$v) { return db_stmt_bind_param($s, $t, ...$v); }
    function mysqli_stmt_execute($s) { return db_stmt_execute($s); }
    function mysqli_stmt_get_result($s) { return db_stmt_get_result($s); }
    function mysqli_stmt_close($s) { return db_stmt_close($s); }
    function mysqli_data_seek($r, $o) { return db_data_seek($r, $o); }
    function mysqli_close($c) { return db_close($c); }
}