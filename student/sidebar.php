<?php
/* =========================================================
   STUDENT SIDEBAR — Luxury Categorized Navigation
========================================================= */
$current_page = basename($_SERVER['PHP_SELF']);

$nav_groups = [
    [
        "title" => "Overview",
        "items" => [
            ["file" => "dashboard.php", "icon" => "fa-house", "label" => "Dashboard"],
            ["file" => "profile.php", "icon" => "fa-user-graduate", "label" => "My Profile"]
        ]
    ],
    [
        "title" => "Academic Records",
        "items" => [
            ["file" => "subjects.php", "icon" => "fa-book", "label" => "My Subjects"],
            ["file" => "assignments.php", "icon" => "fa-tasks", "label" => "Assignments"],
            ["file" => "pay_fees.php", "icon" => "fa-credit-card", "label" => "Pay Fees"],
            ["file" => "attendance.php", "icon" => "fa-calendar-check", "label" => "Attendance"],
            ["file" => "result.php", "icon" => "fa-chart-line", "label" => "My Result"],
            ["file" => "marks.php", "icon" => "fa-chart-bar", "label" => "My Marks"]
        ]
    ],
    [
        "title" => "AI & Analytics",
        "items" => [
            ["file" => "performance_analysis.php", "icon" => "fa-chart-pie", "label" => "Performance Analysis"],
            ["file" => "ai_prediction.php", "icon" => "fa-brain", "label" => "AI Prediction"],
            ["file" => "prediction_history.php", "icon" => "fa-clock-rotate-left", "label" => "Prediction History"]
        ]
    ],
    [
        "title" => "Settings",
        "items" => [
            ["file" => "change_password.php", "icon" => "fa-key", "label" => "Change Password"]
        ]
    ]
];
?>

<div class="sidebar">
    <div class="sidebar-brand-wrapper">
        <a href="dashboard.php" class="sidebar-brand">
            <div class="brand-icon-box">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="brand-title">
                EduNex <span class="ai-text">AI</span>
                <span class="brand-badge">STUDENT</span>
            </div>
        </a>
    </div>

    <div class="sidebar-role-pill">
        <i class="fas fa-user-graduate"></i>
        <span>Student Portal</span>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($nav_groups as $group): ?>
            <li class="sidebar-section-label"><?php echo $group['title']; ?></li>
            <?php foreach ($group['items'] as $item): ?>
                <li>
                    <a href="<?php echo $item['file']; ?>"
                       class="<?php echo ($current_page === $item['file']) ? 'active' : ''; ?>">
                        <i class="fas <?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <li class="mt-3">
            <a href="../logout.php" class="text-danger-hover">
                <i class="fas fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>