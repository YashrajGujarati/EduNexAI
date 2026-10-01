<?php
require_once(__DIR__ . '/config/cors.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

$redirect_url = getenv('FRONTEND_URL') ?: 'index.html';
header("Location: " . $redirect_url);
exit();
?>