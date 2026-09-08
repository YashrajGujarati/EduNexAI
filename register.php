<?php
session_start();
include("config/db.php");

$error_msg = "";
$success_msg = "";

if(isset($_POST['register']))
{
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'student');

    if(empty($role)) {
        $role = 'student';
    }

    if($password != $confirm_password)
    {
        $error_msg = "Password and Confirm Password do not match.";
    }
    elseif(strlen($password) < 6)
    {
        $error_msg = "Password must be at least 6 characters long.";
    }
    else
    {
        $check_email = "SELECT * FROM users WHERE email='$email'";
        $result = mysqli_query($conn, $check_email);
        if(mysqli_num_rows($result) > 0)
        {
            $error_msg = "An account with this email address already exists.";
        }
        else
        {
            $hash_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = "INSERT INTO users(name, email, password, role)
                       VALUES('$name', '$email', '$hash_password', '$role')";
            if(mysqli_query($conn, $insert))
            {
                $_SESSION['register_success'] = "Registration Successful! Please login to your account.";
                header("Location: login.php");
                exit();
            }
            else
            {
                $error_msg = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduNexAI | Join Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="css/register.css?v=<?php echo time(); ?>">
</head>
<body>

<!-- Glowing Mesh Background Orbs -->
<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="bg-orb bg-orb-3"></div>

<div class="register-container">
    <div class="row align-items-center">

        <!-- LEFT COLUMN: HERO SHOWCASE -->
        <div class="col-lg-6 hero-section">
            <div class="brand-badge">
                <i class="fa-solid fa-sparkles"></i> AI-POWERED ACADEMIC PLATFORM
            </div>

            <h1 class="hero-title">
                Transform Learning with <span class="gradient-highlight">Predictive Intelligence</span>
            </h1>

            <p class="hero-desc">
                EduNexAI empowers students, faculty, and administrators with real-time academic analytics, intelligent performance predictions, and personalized learning insights.
            </p>

            <!-- Feature Showcase -->
            <div class="feature-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="feature-info">
                        <h4>Performance Analytics</h4>
                        <p>Track academic marks and attendance trends automatically.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <div class="feature-info">
                        <h4>AI Risk Prediction</h4>
                        <p>Machine learning model predicts exam outcomes & risk factors.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="feature-info">
                        <h4>Role-Based Hubs</h4>
                        <p>Tailored dashboards for Admin control, Faculty marks, & Student goals.</p>
                    </div>
                </div>
            </div>

            <!-- Floating Stat Chip -->
            <div class="floating-stat-card">
                <div class="stat-badge">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <div class="stat-text">99.4% AI Accuracy</div>
                    <div class="stat-sub">Optimized with Scikit-Learn Engine</div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: GLASSMORPHISM FORM CARD -->
        <div class="col-lg-6">
            <div class="register-card">

                <div class="card-header-box">
                    <h3>Create Your Account</h3>
                    <p>Select your role and start your EduNexAI journey</p>
                </div>

                <?php if(!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-2"></i><?php echo htmlspecialchars($error_msg); ?>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" id="registerForm" novalidate>

                    <!-- Interactive Role Selector Chips -->
                    <label class="role-label">Choose Account Type</label>

                    <div class="role-selector-grid">
                        <label class="role-chip <?php echo (!isset($_POST['role']) || $_POST['role'] == 'student') ? 'active' : ''; ?>" data-role="student" onclick="selectRole('student')">
                            <input type="radio" name="role" value="student" class="role-radio" <?php echo (!isset($_POST['role']) || $_POST['role'] == 'student') ? 'checked' : ''; ?>>
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>Student</span>
                        </label>

                        <label class="role-chip <?php echo (isset($_POST['role']) && $_POST['role'] == 'faculty') ? 'active' : ''; ?>" data-role="faculty" onclick="selectRole('faculty')">
                            <input type="radio" name="role" value="faculty" class="role-radio" <?php echo (isset($_POST['role']) && $_POST['role'] == 'faculty') ? 'checked' : ''; ?>>
                            <i class="fa-solid fa-chalkboard-user"></i>
                            <span>Faculty</span>
                        </label>

                        <label class="role-chip <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'active' : ''; ?>" data-role="admin" onclick="selectRole('admin')">
                            <input type="radio" name="role" value="admin" class="role-radio" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'checked' : ''; ?>>
                            <i class="fa-solid fa-user-shield"></i>
                            <span>Admin</span>
                        </label>
                    </div>

                    <!-- Full Name -->
                    <div class="form-group-item">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                placeholder="Full Name"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <!-- Email Address -->
                    <div class="form-group-item">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                placeholder="Email Address (e.g., john@edunexai.com)"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                required>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group-item">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Create Password"
                                required>
                            <button class="btn btn-toggle-pw" type="button" id="togglePassword" title="Show/Hide Password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="pw-strength-wrapper" id="pwStrengthWrapper">
                            <div class="pw-strength-bar">
                                <div class="pw-strength-fill" id="pwStrengthFill"></div>
                            </div>
                            <span class="pw-strength-text" id="pwStrengthText"></span>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group-item">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Confirm Password"
                                required>
                            <button class="btn btn-toggle-pw" type="button" id="toggleConfirmPassword" title="Show/Hide Password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="pw-match-feedback" id="pwMatchFeedback"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="register" class="btn btn-submit-gradient w-100">
                        <i class="fa-solid fa-rocket me-2"></i>Create Account
                    </button>
                </form>

                <hr>

                <div class="card-footer-text text-center">
                    <p class="mb-2">
                        Already registered? <a href="login.php">Sign in to EduNexAI</a>
                    </p>
                    <p class="mb-0">
                        <a href="index.html" class="text-muted">
                            <i class="fa-solid fa-arrow-left me-1"></i>Back to Home
                        </a>
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/register.js?v=<?php echo time(); ?>"></script>
</body>
</html>

