<?php
/* =========================================================
   FACULTY SIDEBAR — Luxury Categorized Navigation
========================================================= */
$current_page = basename($_SERVER['PHP_SELF']);

$nav_groups = [
    [
        "title" => "Overview",
        "items" => [
            ["file" => "dashboard.php", "icon" => "fa-house", "label" => "Dashboard"],
            ["file" => "profile.php", "icon" => "fa-user", "label" => "My Profile"]
        ]
    ],
    [
        "title" => "Classroom & Academics",
        "items" => [
            ["file" => "my_subjects.php", "icon" => "fa-book", "label" => "My Subjects"],
            ["file" => "assignments.php", "icon" => "fa-tasks", "label" => "Assignments"],
            ["file" => "manage_marks.php", "icon" => "fa-pen-to-square", "label" => "Manage Marks"],
            ["file" => "manage_attendance.php", "icon" => "fa-calendar-check", "label" => "Manage Attendance"]
        ]
    ],
    [
        "title" => "Analytics",
        "items" => [
            ["file" => "student_performance.php", "icon" => "fa-chart-line", "label" => "Student Performance"],
            ["file" => "analytics.php", "icon" => "fa-chart-pie", "label" => "Visual Analytics"]
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
                <span class="brand-badge">FACULTY</span>
            </div>
        </a>
    </div>

    <div class="sidebar-role-pill">
        <i class="fas fa-chalkboard-teacher"></i>
        <span>Faculty Portal</span>
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