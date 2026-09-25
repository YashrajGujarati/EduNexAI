<?php
$page_title = isset($page_title) ? $page_title : "Faculty Dashboard";
$faculty_name = isset($_SESSION['name']) ? $_SESSION['name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : "Faculty Member");
?>
<script src="../js/theme.js"></script>
<div class="topbar mb-4">
    <div>
        <h4 class="topbar-title"><?php echo htmlspecialchars($page_title); ?></h4>
        <div class="topbar-subtitle">EduNexAI Academic & Grading Management System</div>
    </div>
    <div class="d-flex align-items-center">
        <button id="themeToggleBtn" class="btn btn-light border btn-sm p-2 rounded-circle me-3 theme-toggle-btn" title="Toggle Light/Dark Theme" style="width:38px; height:38px; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-moon text-primary" id="themeToggleIcon" style="font-size:16px;"></i>
        </button>
        <a data-href="profile.php" class="topbar-user text-decoration-none" title="Click to View My Profile">
            <div class="topbar-avatar">
                <i class="fas fa-chalkboard-user"></i>
            </div>
            <div class="d-none d-sm-block">
                <span class="d-block text-dark fw-bold" style="line-height:1.2;"><?php echo htmlspecialchars($faculty_name); ?></span>
                <small class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Faculty Member</small>
            </div>
        </a>
    </div>
</div>