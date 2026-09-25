<?php
session_start();

/* =========================================================
   STUDENT SECURITY CHECK
========================================================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit();
}

require_once("../config/db.php");

$user_id = (int)$_SESSION['user_id'];
$message = "";
$message_type = "";

// Fetch user email for display
$user_q = mysqli_query($conn, "SELECT email, password FROM users WHERE id = $user_id LIMIT 1");
$user_data = mysqli_fetch_assoc($user_q);
$current_hashed_password = $user_data['password'] ?? '';
$user_email = $user_data['email'] ?? '';

/* =========================================================
   HANDLE PASSWORD CHANGE
========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // 1. Verify Current Password
    if (!password_verify($current_password, $current_hashed_password)) {
        $message = "Incorrect current password. Please verify and try again.";
        $message_type = "danger";
    } 
    // 2. Verify New Password Match
    else if ($new_password !== $confirm_password) {
        $message = "New password and confirm password do not match.";
        $message_type = "danger";
    } 
    // 3. Verify Password Strength Criteria
    else if (strlen($new_password) < 8) {
        $message = "New password must be at least 8 characters long.";
        $message_type = "danger";
    }
    // 4. Verify password is not same as current
    else if (password_verify($new_password, $current_hashed_password)) {
        $message = "Your new password cannot be the same as your current password.";
        $message_type = "warning";
    }
    else {
        // Hash new password securely
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare($conn, "UPDATE users SET password = ?, first_login = 0 WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $new_hash, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Your password has been successfully updated! Redirecting to dashboard...";
            $message_type = "success";
            $current_hashed_password = $new_hash;
            echo "<script>setTimeout(function(){ window.location='dashboard.php'; }, 2000);</script>";
        } else {
            $message = "Failed to update password. Error: " . mysqli_error($conn);
            $message_type = "danger";
        }
        mysqli_stmt_close($stmt);
    }
}

$page_title = "Change Password";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | EduNexAI Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    
    <!-- SCOPED STYLES STRICTLY FOR CHANGE PASSWORD PAGE -->
    <style>
        .main-content {
            position: relative;
            overflow: hidden;
            background-color: #0f172a !important; /* Dark slate canvas for live background contrast */
            min-height: 100vh;
        }

        /* Live Animated Background Orbs */
        .live-bg-wrapper {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .live-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.45;
            animation-timing-function: ease-in-out;
            animation-iteration-count: infinite;
            animation-direction: alternate;
        }

        .blob-1 {
            width: 540px;
            height: 540px;
            background: radial-gradient(circle, #3b82f6 0%, #6366f1 100%);
            top: -120px;
            right: -100px;
            animation: moveBlob1 14s infinite alternate;
        }

        .blob-2 {
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, #06b6d4 0%, #3b82f6 100%);
            bottom: -120px;
            left: -100px;
            animation: moveBlob2 16s infinite alternate;
        }

        .blob-3 {
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, #8b5cf6 0%, #ec4899 100%);
            top: 35%;
            left: 35%;
            animation: moveBlob3 18s infinite alternate;
        }

        #particleCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.65;
            pointer-events: none;
        }

        @keyframes moveBlob1 {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-110px, 130px) scale(1.15); }
            100% { transform: translate(90px, 190px) scale(0.9); }
        }

        @keyframes moveBlob2 {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(150px, -110px) scale(1.1); }
            100% { transform: translate(-70px, -170px) scale(0.95); }
        }

        @keyframes moveBlob3 {
            0% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-130px, -100px) scale(1.25); }
            100% { transform: translate(130px, 100px) scale(0.85); }
        }

        /* ===== HEADER / TOPBAR — White with Blue-Indigo Matching Accents ===== */
        .main-content .topbar {
            background: #ffffff !important;
            border-radius: 18px !important;
            padding: 16px 26px !important;
            border: 1px solid rgba(99, 102, 241, 0.25) !important;
            border-left: 5px solid #6366f1 !important;
            box-shadow: 0 12px 35px -5px rgba(99, 102, 241, 0.18), 0 4px 12px rgba(0,0,0,0.1) !important;
            margin-bottom: 28px !important;
            position: relative;
            z-index: 10;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .main-content .topbar:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px -5px rgba(99, 102, 241, 0.24), 0 6px 16px rgba(0,0,0,0.12) !important;
        }

        .main-content .topbar .topbar-title {
            color: #1e1b4b !important;
            font-weight: 800 !important;
            letter-spacing: -0.3px;
        }

        .main-content .topbar .topbar-subtitle {
            color: #6366f1 !important;
            font-weight: 500;
            opacity: 0.85;
        }

        .main-content .topbar .topbar-user {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%) !important;
            border: 1px solid rgba(99, 102, 241, 0.3) !important;
            padding: 6px 16px !important;
            border-radius: 50px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-content .topbar .topbar-user:hover {
            background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%) !important;
            border-color: rgba(99, 102, 241, 0.5) !important;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25) !important;
            transform: scale(1.03);
        }

        .main-content .topbar .topbar-user span {
            color: #1e1b4b !important;
        }

        /* ===== SECURITY CARD — White with matching colour accents from background palette ===== */
        .security-card-white {
            position: relative;
            z-index: 2;
            background: #ffffff !important;
            border-radius: 24px;
            border: 1px solid rgba(6, 182, 212, 0.28) !important;
            box-shadow: 0 25px 50px -10px rgba(99, 102, 241, 0.2),
                        0 10px 25px rgba(59, 130, 246, 0.14),
                        0 2px 4px rgba(0,0,0,0.04);
            overflow: hidden;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .security-card-white:hover {
            box-shadow: 0 32px 65px -10px rgba(99, 102, 241, 0.28),
                        0 14px 30px rgba(59, 130, 246, 0.22);
            border-color: rgba(99, 102, 241, 0.45) !important;
        }

        /* Card header — white with multi-colour gradient line */
        .security-header-white {
            background: #ffffff !important;
            position: relative;
            border-bottom: 1px solid #ede9fe;
            padding: 28px 32px;
            color: #1e1b4b;
        }

        /* Top colour accent bar on card header */
        .security-header-white::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 35%, #06b6d4 70%, #8b5cf6 100%);
            border-radius: 24px 24px 0 0;
        }

        /* Shield icon box — matches indigo-to-cyan gradient with pulse glow */
        .icon-shield-box-blue {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 28px;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.45),
                        0 0 0 5px rgba(99, 102, 241, 0.12);
            transition: all 0.35s ease;
            animation: shieldPulse 3s infinite ease-in-out;
        }

        @keyframes shieldPulse {
            0%, 100% { box-shadow: 0 8px 25px rgba(99, 102, 241, 0.45), 0 0 0 5px rgba(99, 102, 241, 0.12); }
            50% { box-shadow: 0 12px 30px rgba(6, 182, 212, 0.6), 0 0 0 8px rgba(6, 182, 212, 0.2); }
        }

        .security-card-white:hover .icon-shield-box-blue {
            transform: scale(1.08) rotate(-4deg);
        }

        /* Input field groups */
        .input-group {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(99, 102, 241, 0.22);
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            border-color: #06b6d4 !important;
            box-shadow: 0 0 0 4px rgba(6, 182, 212, 0.18), 0 4px 12px rgba(6, 182, 212, 0.1) !important;
            transform: translateY(-1px);
        }

        .input-group .form-control {
            border: none !important;
            font-size: 14.5px;
            padding: 12px 14px;
        }

        .input-group-text.bg-white {
            background: #f8fafc !important;
            border: none !important;
            color: #6366f1 !important;
            transition: all 0.25s ease;
        }

        .input-group:focus-within .input-group-text.bg-white {
            background: #eef2ff !important;
            color: #4f46e5 !important;
        }

        .eye-toggle-btn {
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .eye-toggle-btn:hover {
            color: #06b6d4 !important;
            transform: scale(1.15);
        }

        /* Account identifier box — indigo-cyan tinted background */
        .account-id-box {
            background: linear-gradient(135deg, #eef2ff 0%, #e0f2fe 100%) !important;
            border: 1px solid rgba(99, 102, 241, 0.22) !important;
            border-radius: 14px;
            transition: all 0.3s ease;
        }

        .account-id-box:hover {
            border-color: rgba(6, 182, 212, 0.4) !important;
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.12);
        }

        /* Strength bar track — indigo tinted */
        .strength-meter-bar {
            height: 8px;
            border-radius: 10px;
            background-color: #ede9fe;
            overflow: hidden;
            position: relative;
        }

        .strength-progress {
            height: 100%;
            width: 0%;
            border-radius: 10px;
            transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1), background-color 0.35s ease;
        }

        /* Requirements checklist */
        .req-checklist {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .req-checklist li {
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 7px;
            display: flex;
            align-items: center;
            transition: all 0.25s ease;
        }

        .req-checklist li i {
            font-size: 11px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 9px;
            background-color: #ede9fe;
            color: #a5b4fc;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .req-checklist li.valid {
            color: #0f766e;
            font-weight: 600;
            transform: translateX(3px);
        }

        .req-checklist li.valid i {
            background-color: #ccfbf1;
            color: #0d9488;
            transform: scale(1.15) rotate(360deg);
        }

        /* Requirements box background — violet tint matching blob-3 */
        .req-box {
            background: linear-gradient(135deg, #f5f3ff 0%, #e0f2fe 100%) !important;
            border: 1px solid rgba(99, 102, 241, 0.18) !important;
            border-radius: 14px;
        }

        /* Security tip card — matching blue/cyan accents */
        .security-tip-card-white {
            position: relative;
            z-index: 2;
            background: #ffffff !important;
            border-radius: 20px;
            border: 1.5px dashed rgba(6, 182, 212, 0.45) !important;
            box-shadow: 0 12px 32px -5px rgba(6, 182, 212, 0.16),
                        0 4px 12px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .security-tip-card-white:hover {
            border-color: rgba(99, 102, 241, 0.6) !important;
            box-shadow: 0 16px 40px -5px rgba(99, 102, 241, 0.22);
            transform: translateY(-2px);
        }

        /* Update button — blue-to-indigo vibrant gradient */
        .btn-update-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #6366f1 50%, #8b5cf6 100%);
            background-size: 200% auto;
            border: none;
            transition: all 0.35s ease;
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.48);
            letter-spacing: 0.4px;
            position: relative;
            overflow: hidden;
        }

        .btn-update-primary:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 16px 32px -5px rgba(99, 102, 241, 0.62), 0 0 20px rgba(6, 182, 212, 0.4);
        }

        .btn-update-primary:active {
            transform: translateY(1px);
        }
    </style>
</head>

<body>
<div class="wrapper">
    <?php include("sidebar.php"); ?>

    <div class="main-content p-4">
        
        <!-- LIVE ANIMATED BACKGROUND & CANVAS OVERLAY (PAGE-SPECIFIC ONLY) -->
        <div class="live-bg-wrapper">
            <div class="live-blob blob-1"></div>
            <div class="live-blob blob-2"></div>
            <div class="live-blob blob-3"></div>
            <canvas id="particleCanvas"></canvas>
        </div>

        <!-- PURE WHITE TOPBAR / HEADER FOR CHANGE PASSWORD PAGE -->
        <?php include("header.php"); ?>

        <div class="row g-4 justify-content-center my-2" style="position: relative; z-index: 2;">
            <div class="col-lg-7 col-xl-6">
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show border-0 rounded-4 mb-4 p-3 d-flex align-items-center shadow-lg" role="alert">
                        <div class="me-3 fs-4">
                            <i class="fas fa-<?php echo ($message_type === 'success') ? 'check-circle text-success' : (($message_type === 'warning') ? 'exclamation-triangle text-warning' : 'circle-xmark text-danger'); ?>"></i>
                        </div>
                        <div>
                            <strong class="d-block text-dark"><?php echo ($message_type === 'success') ? 'Success!' : 'Security Notice'; ?></strong>
                            <span class="small text-muted"><?php echo htmlspecialchars($message); ?></span>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- CRISP WHITE CARD FORM -->
                <div class="card security-card-white">
                    <!-- Crisp White Card Header -->
                    <div class="security-header-white d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="icon-shield-box-blue me-3">
                                <i class="fas fa-shield-halved"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-1" style="color:#1e1b4b;">Account Security</h4>
                                <p class="mb-0 small" style="color:#6366f1;opacity:0.85;">Update & protect your student portal password</p>
                            </div>
                        </div>
                        <span class="badge rounded-pill px-3 py-2 small d-none d-sm-inline-block fw-semibold" style="background:linear-gradient(135deg,#eef2ff,#e0f2fe);color:#4f46e5;border:1px solid rgba(99,102,241,0.3);">
                            <i class="fas fa-user-lock me-1"></i> Protected
                        </span>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        
                        <div class="account-id-box d-flex align-items-center p-3 mb-4">
                            <div class="d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#06b6d4);color:#fff;">
                                <i class="fas fa-envelope" style="font-size:16px;"></i>
                            </div>
                            <div>
                                <small class="d-block fw-bold" style="font-size:10px;color:#6366f1;letter-spacing:0.8px;">ACCOUNT IDENTIFIER</small>
                                <strong class="small" style="color:#1e1b4b;"><?php echo htmlspecialchars($user_email); ?></strong>
                            </div>
                        </div>

                        <form method="POST" id="passwordForm" autocomplete="off">
                            <!-- CURRENT PASSWORD -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">
                                    Current Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" name="current_password" id="current_password" 
                                           class="form-control bg-white border-start-0 border-end-0 ps-0" 
                                           placeholder="Enter your current password" required>
                                    <span class="input-group-text bg-white border-start-0 text-muted eye-toggle-btn" 
                                          style="cursor:pointer;"
                                          onclick="togglePasswordVisibility('current_password', 'toggleIcon1')">
                                        <i class="fas fa-eye" id="toggleIcon1"></i>
                                    </span>
                                </div>
                            </div>

                            <hr class="my-4" style="border-color: rgba(99, 102, 241, 0.2);">

                            <!-- NEW PASSWORD -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark small">
                                    New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="new_password" id="new_password" 
                                           class="form-control bg-white border-start-0 border-end-0 ps-0" 
                                           placeholder="Enter robust new password" required>
                                    <span class="input-group-text bg-white border-start-0 text-muted eye-toggle-btn" 
                                          style="cursor:pointer;"
                                          onclick="togglePasswordVisibility('new_password', 'toggleIcon2')">
                                        <i class="fas fa-eye" id="toggleIcon2"></i>
                                    </span>
                                </div>
                                
                                <!-- Password Strength Bar -->
                                <div class="mt-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted" style="font-size:11px;">Password Strength:</small>
                                        <small id="strengthLabel" class="fw-bold text-muted" style="font-size:11px;">None</small>
                                    </div>
                                    <div class="strength-meter-bar">
                                        <div class="strength-progress" id="strengthProgress"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASSWORD REQUIREMENTS CHECKLIST -->
                            <div class="req-box p-3 mb-4">
                                <small class="fw-bold d-block mb-2" style="color:#4f46e5;">Password Requirements:</small>
                                <ul class="req-checklist">
                                    <li id="reqLength"><i class="fas fa-check"></i> At least 8 characters long</li>
                                    <li id="reqUpper"><i class="fas fa-check"></i> Includes an uppercase letter (A-Z)</li>
                                    <li id="reqLower"><i class="fas fa-check"></i> Includes a lowercase letter (a-z)</li>
                                    <li id="reqNumber"><i class="fas fa-check"></i> Includes at least one number (0-9)</li>
                                    <li id="reqSpecial"><i class="fas fa-check"></i> Includes a special character (!@#$%^&*)</li>
                                </ul>
                            </div>

                            <!-- CONFIRM PASSWORD -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark small">
                                    Confirm New Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="fas fa-lock-open"></i>
                                    </span>
                                    <input type="password" name="confirm_password" id="confirm_password" 
                                           class="form-control bg-white border-start-0 border-end-0 ps-0" 
                                           placeholder="Re-enter new password" required>
                                    <span class="input-group-text bg-white border-start-0 text-muted eye-toggle-btn" 
                                          style="cursor:pointer;"
                                          onclick="togglePasswordVisibility('confirm_password', 'toggleIcon3')">
                                        <i class="fas fa-eye" id="toggleIcon3"></i>
                                    </span>
                                </div>
                                <small id="matchMessage" class="d-block mt-1 small"></small>
                            </div>

                            <button type="submit" name="change_password" id="submitBtn" class="btn btn-primary btn-update-primary rounded-pill w-100 py-3 fw-bold text-white">
                                <i class="fas fa-shield-check me-2"></i> Update & Secure Password
                            </button>
                        </form>
                    </div>
                </div>

                <!-- SECURITY GUIDELINES BOX -->
                <div class="security-tip-card-white p-4 mt-4">
                    <div class="d-flex align-items-start">
                        <div class="text-primary me-3 fs-4">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Student Security Recommendations</h6>
                            <ul class="text-muted small mb-0 ps-3">
                                <li>Never share your student portal credentials with anyone.</li>
                                <li>Avoid using common dictionary words or personal birthdates.</li>
                                <li>If you suspect unauthorized access, update your password immediately.</li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* Particle Canvas Live Background Script with Interactive Mouse Response */
(function initParticleCanvas() {
    const canvas = document.getElementById('particleCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    let width = canvas.width = canvas.parentElement.clientWidth;
    let height = canvas.height = canvas.parentElement.clientHeight;
    let mouse = { x: null, y: null, radius: 140 };

    window.addEventListener('resize', () => {
        width = canvas.width = canvas.parentElement.clientWidth;
        height = canvas.height = canvas.parentElement.clientHeight;
    });

    window.addEventListener('mousemove', (e) => {
        const rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });

    window.addEventListener('mouseleave', () => {
        mouse.x = null;
        mouse.y = null;
    });

    const particles = [];
    const particleCount = 50;

    for (let i = 0; i < particleCount; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height,
            radius: Math.random() * 2 + 1,
            color: Math.random() > 0.5 ? 'rgba(96, 165, 250, ' : 'rgba(129, 140, 248, ',
            alpha: Math.random() * 0.5 + 0.25,
            vx: (Math.random() - 0.5) * 0.6,
            vy: (Math.random() - 0.5) * 0.6
        });
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        for (let i = 0; i < particleCount; i++) {
            let p = particles[i];
            p.x += p.vx;
            p.y += p.vy;

            if (p.x < 0) p.x = width;
            if (p.x > width) p.x = 0;
            if (p.y < 0) p.y = height;
            if (p.y > height) p.y = 0;

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
            ctx.fillStyle = p.color + p.alpha + ')';
            ctx.fill();

            // Connect particles to mouse cursor if near
            if (mouse.x !== null && mouse.y !== null) {
                let dx = mouse.x - p.x;
                let dy = mouse.y - p.y;
                let distMouse = Math.hypot(dx, dy);
                if (distMouse < mouse.radius) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(mouse.x, mouse.y);
                    ctx.strokeStyle = `rgba(6, 182, 212, ${0.25 * (1 - distMouse / mouse.radius)})`;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }

            for (let j = i + 1; j < particleCount; j++) {
                let p2 = particles[j];
                let dist = Math.hypot(p.x - p2.x, p.y - p2.y);
                if (dist < 115) {
                    ctx.beginPath();
                    ctx.moveTo(p.x, p.y);
                    ctx.lineTo(p2.x, p2.y);
                    ctx.strokeStyle = `rgba(96, 165, 250, ${0.16 * (1 - dist / 115)})`;
                    ctx.lineWidth = 0.8;
                    ctx.stroke();
                }
            }
        }
        requestAnimationFrame(animate);
    }
    animate();
})();

/* Toggle Password Visibility */
function togglePasswordVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/* Real-Time Password Strength & Validation */
const newPassInput = document.getElementById('new_password');
const confirmPassInput = document.getElementById('confirm_password');
const strengthProgress = document.getElementById('strengthProgress');
const strengthLabel = document.getElementById('strengthLabel');
const matchMessage = document.getElementById('matchMessage');

const reqLength = document.getElementById('reqLength');
const reqUpper = document.getElementById('reqUpper');
const reqLower = document.getElementById('reqLower');
const reqNumber = document.getElementById('reqNumber');
const reqSpecial = document.getElementById('reqSpecial');

newPassInput.addEventListener('input', function() {
    const val = newPassInput.value;
    
    // Checklist updates
    const isLength = val.length >= 8;
    const isUpper = /[A-Z]/.test(val);
    const isLower = /[a-z]/.test(val);
    const isNumber = /[0-9]/.test(val);
    const isSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(val);

    updateReq(reqLength, isLength);
    updateReq(reqUpper, isUpper);
    updateReq(reqLower, isLower);
    updateReq(reqNumber, isNumber);
    updateReq(reqSpecial, isSpecial);

    // Calculate score
    let score = 0;
    if (isLength) score += 20;
    if (isUpper) score += 20;
    if (isLower) score += 20;
    if (isNumber) score += 20;
    if (isSpecial) score += 20;

    // Strength styling
    strengthProgress.style.width = score + '%';
    if (score === 0) {
        strengthProgress.style.backgroundColor = '#e2e8f0';
        strengthLabel.textContent = 'None';
        strengthLabel.className = 'fw-bold text-muted';
    } else if (score <= 40) {
        strengthProgress.style.backgroundColor = '#ef4444'; // Red
        strengthLabel.textContent = 'Weak';
        strengthLabel.className = 'fw-bold text-danger';
    } else if (score <= 60) {
        strengthProgress.style.backgroundColor = '#f59e0b'; // Amber
        strengthLabel.textContent = 'Moderate';
        strengthLabel.className = 'fw-bold text-warning';
    } else if (score <= 80) {
        strengthProgress.style.backgroundColor = '#3b82f6'; // Blue
        strengthLabel.textContent = 'Strong';
        strengthLabel.className = 'fw-bold text-primary';
    } else {
        strengthProgress.style.backgroundColor = '#10b981'; // Green
        strengthLabel.textContent = 'Very Strong';
        strengthLabel.className = 'fw-bold text-success';
    }

    checkMatch();
});

confirmPassInput.addEventListener('input', checkMatch);

function checkMatch() {
    const p1 = newPassInput.value;
    const p2 = confirmPassInput.value;

    if (p2.length === 0) {
        matchMessage.textContent = '';
        return;
    }

    if (p1 === p2) {
        matchMessage.innerHTML = '<i class="fas fa-check-circle me-1"></i> Passwords match perfectly!';
        matchMessage.className = 'd-block mt-1 small text-success fw-bold';
    } else {
        matchMessage.innerHTML = '<i class="fas fa-times-circle me-1"></i> Passwords do not match.';
        matchMessage.className = 'd-block mt-1 small text-danger fw-bold';
    }
}

function updateReq(element, isValid) {
    if (isValid) {
        element.classList.add('valid');
    } else {
        element.classList.remove('valid');
    }
}
</script>
</body>
</html>