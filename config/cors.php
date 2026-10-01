<?php
// ============================================================
// EduNexAI — Centralized CORS & Cross-Origin Session Config
// ============================================================

// 1. Detect HTTPS (including Railway / reverse proxy SSL termination)
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

// 2. Configure Session Cookie BEFORE session_start()
if (session_status() === PHP_SESSION_NONE) {
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '', // Host-only cookie
            'secure'   => $is_https,
            'httponly' => true,
            'samesite' => $is_https ? 'None' : 'Lax',
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=' . ($is_https ? 'None' : 'Lax'), '', $is_https, true);
    }
}

// 3. Centralized CORS Handling
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

// Retrieve allowed origins from environment
$allowed_env = getenv('FRONTEND_URL') ?: (getenv('ALLOWED_ORIGINS') ?: '');
$allowed_origins = [];

if (!empty($allowed_env)) {
    $allowed_origins = array_map('trim', explode(',', $allowed_env));
}

if (!empty($origin)) {
    $origin_allowed = false;

    // Check exact matches from env
    if (in_array($origin, $allowed_origins, true)) {
        $origin_allowed = true;
    }
    // Allow localhost during local development
    elseif (preg_match('/^https?:\/\/(localhost|127\.0\.0\.1)(:\d+)?$/i', $origin)) {
        $origin_allowed = true;
    }
    // If no explicit frontend URL set, safely match Vercel production and preview domains
    elseif (empty($allowed_origins) && preg_match('/^https:\/\/[a-z0-9\-]+\.vercel\.app$/i', $origin)) {
        $origin_allowed = true;
    }

    if ($origin_allowed) {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept");
        header("Access-Control-Max-Age: 86400");
    }

    // Handle Preflight OPTIONS Request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}
