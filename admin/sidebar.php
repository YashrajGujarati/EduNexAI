<?php
/* =========================================================
   ADMIN SIDEBAR — Luxury Categorized Navigation
========================================================= */
$current_page = basename($_SERVER['PHP_SELF']);

$nav_groups = [
    [
        "title" => "Overview",
        "items" => [
            ["file" => "dashboard.php", "icon" => "fa-house", "label" => "Dashboard"]
        ]
    ],
    [
        "title" => "User Management",
        "items" => [
            ["file" => "manage_users.php", "icon" => "fa-users-gear", "label" => "Manage Users"],
            ["file" => "manage_students.php", "icon" => "fa-user-graduate", "label" => "Manage Students"],
            ["file" => "manage_fees.php", "icon" => "fa-wallet", "label" => "Fee Management"],
            ["file" => "import_students.php", "icon" => "fa-file-import", "label" => "Import Students"],
            ["file" => "manage_faculty.php", "icon" => "fa-chalkboard-user", "label" => "Manage Faculty"]
        ]
    ],
    [
        "title" => "Academic Center",
        "items" => [
            ["file" => "manage_subjects.php", "icon" => "fa-book-bookmark", "label" => "Manage Subjects"],
            ["file" => "manage_marks.php", "icon" => "fa-pen-to-square", "label" => "Manage Marks"]
        ]
    ],
    [
        "title" => "Analytics & AI",
        "items" => [
            ["file" => "manage_reports.php", "icon" => "fa-chart-column", "label" => "Reports Module"],
            ["file" => "analytics.php", "icon" => "fa-chart-pie", "label" => "Visual Analytics"],
            ["file" => "ai_predictions.php", "icon" => "fa-brain", "label" => "AI Risk Predictions"]
        ]
    ],
    [
        "title" => "Account",
        "items" => [
            ["file" => "profile.php", "icon" => "fa-user-shield", "label" => "Admin Profile"]
        ]
    ]
];
?>

<div class="sidebar">
    <div class="sidebar-brand-wrapper">
        <a data-href="dashboard.php" class="sidebar-brand">
            <div class="brand-icon-box">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="brand-title">
                EduNex <span class="ai-text">AI</span>
                <span class="brand-badge">ADMIN</span>
            </div>
        </a>
    </div>

    <div class="sidebar-role-pill">
        <i class="fas fa-user-shield"></i>
        <span>Admin Control</span>
    </div>

    <ul class="sidebar-menu">
        <?php foreach ($nav_groups as $group): ?>
            <li class="sidebar-section-label"><?php echo $group['title']; ?></li>
            <?php foreach ($group['items'] as $item): ?>
                <li>
                    <a data-href="<?php echo $item['file']; ?>"
                       class="<?php echo ($current_page === $item['file']) ? 'active' : ''; ?>">
                        <i class="fas <?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <li class="mt-3">
            <a data-href="../logout.php" class="text-danger-hover">
                <i class="fas fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<script>
if (!window.dataHrefListenerAttached) {
    window.dataHrefListenerAttached = true;
    document.addEventListener('click', function(e) {
        var navEl = e.target.closest('[data-href]');
        if (navEl) {
            var destination = navEl.getAttribute('data-href');
            if (destination) {
                e.preventDefault();
                if (e.ctrlKey || e.metaKey) {
                    window.open(destination, '_blank');
                } else {
                    window.location.href = destination;
                }
            }
        }
    });
}
</script>