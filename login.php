<?php
require_once(__DIR__ . '/config/cors.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/config/db.php');

$error_msg = "";
$success_msg = "";

if (isset($_SESSION['register_success'])) {
    $success_msg = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

// Detect JSON / AJAX request
$is_json_request = (
    (isset($_SERVER['CONTENT_TYPE']) && stripos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
    (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
);

if ($is_json_request && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_input = file_get_contents('php://input');
    $json_data = json_decode($raw_input, true);
    if (is_array($json_data)) {
        $_POST = array_merge($_POST, $json_data);
    }
}

if(isset($_POST['login']) || ($is_json_request && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_id'])))
{
    $login_id = mysqli_real_escape_string($conn, $_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    $query = "SELECT * FROM users
              WHERE email='$login_id'
              OR enrollment_no='$login_id'";

    $result = mysqli_query($conn, $query);

    if($result && mysqli_num_rows($result) == 1)
    {
        $row = mysqli_fetch_assoc($result);

        if(password_verify($password, $row['password']) || $password === $row['password'])
        {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['username'] = $row['name'];
            $_SESSION['role'] = $row['role'];

            $redirect_url = "";
            if($row['role'] == "admin")
            {
                $redirect_url = "admin/dashboard.php";
            }
            elseif($row['role'] == "faculty")
            {
                $redirect_url = "faculty/dashboard.php";
            }
            else
            {
                if(isset($row['first_login']) && $row['first_login'] == 1)
                {
                    $redirect_url = "student/change_password.php";
                }
                else
                {
                    $redirect_url = "student/dashboard.php";
                }
            }

            if ($is_json_request) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'role' => $row['role'],
                    'redirect' => $redirect_url,
                    'user' => [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'role' => $row['role']
                    ]
                ]);
                exit();
            }

            header("Location: " . $redirect_url);
            exit();
        }
        else
        {
            $error_msg = "Incorrect Password. Please check your credentials.";
        }
    }
    else
    {
        $error_msg = "Invalid Login ID or Email. User account not found.";
    }

    if ($is_json_request && !empty($error_msg)) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => $error_msg
        ]);
        exit();
    }
}
$frontend_url = getenv('FRONTEND_URL') ?: 'index.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduNexAI | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/login.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- Glowing Mesh Background Orbs -->
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="bg-orb bg-orb-3"></div>

<div class="login-container">
    <div class="row align-items-center">

        <!-- LEFT COLUMN: HERO SHOWCASE -->
        <div class="col-lg-6 hero-section">
            <div class="brand-badge">
                <i class="fa-solid fa-wand-magic-sparkles"></i> AI ACADEMIC PORTAL
            </div>

            <h1 class="hero-title">
                Welcome Back to <span class="gradient-highlight">EduNexAI</span>
            </h1>

            <p class="hero-desc">
                Sign in to access your personalized academic hub, track student performance, analyze learning metrics, and harness predictive AI insights.
            </p>

            <!-- Feature Showcase -->
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <div class="feature-info">
                        <h4>Smart AI Predictions</h4>
                        <p>Real-time machine learning predictions for student outcomes.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <div class="feature-info">
                        <h4>Academic Analytics</h4>
                        <p>Comprehensive attendance tracking & performance visualization.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa-solid fa-user-lock"></i>
                    </div>
                    <div class="feature-info">
                        <h4>Secure Multi-Role Access</h4>
                        <p>Dedicated access control for Students, Faculty, & Administrators.</p>
                    </div>
                </div>
            </div>

            <!-- Floating Stat Chip -->
            <div class="floating-stat-card">
                <div class="stat-badge">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div>
                    <div class="stat-text">Encrypted & Secure Login</div>
                    <div class="stat-sub">Bcrypt Password Protection</div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: GLASSMORPHISM LOGIN CARD -->
        <div class="col-lg-6">
            <div class="login-card">

                <div class="text-center text-lg-start card-header-box mb-4">
                    <div class="login-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>Account Sign In</h2>
                    <p>Enter your details to access your dashboard</p>
                </div>

                <?php if(!empty($success_msg)): ?>
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-circle-check me-2"></i><?php echo htmlspecialchars($success_msg); ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" id="loginForm">
                    <!-- Login ID / Email / Enrollment No -->
                    <div class="form-group-item">
                        <label class="form-label">Email or Enrollment Number</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input
                                type="text"
                                name="login_id"
                                class="form-control"
                                placeholder="Enter Email or Enrollment No"
                                value="<?php echo isset($_POST['login_id']) ? htmlspecialchars($_POST['login_id']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group-item">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter Password"
                                required>
                            <button class="btn btn-toggle-pw" type="button" id="togglePassword" title="Show/Hide Password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember Me</label>
                        </div>
                        <a href="#" class="text-sm">Forgot Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login" class="btn btn-submit-gradient w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i>Sign In
                    </button>
                </form>

                <hr>

                <div class="card-footer-text text-center">
                    <p class="mb-2">
                        Don't have an account? <a href="register.php">Create Account</a>
                    </p>
                    <p class="mb-0">
                        <a href="<?php echo htmlspecialchars($frontend_url); ?>" class="text-muted">
                            <i class="fa-solid fa-arrow-left me-1"></i>Back to Home
                        </a>
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/login.js?v=<?php echo time(); ?>"></script>
</body>
</html>