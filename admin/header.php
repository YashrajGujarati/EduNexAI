<?php
$page_title = isset($page_title) ? $page_title : "Admin Dashboard";
$admin_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : "Administrator");
?>
<script src="../js/theme.js"></script>
<div class="topbar mb-4">
    <div>
        <h4 class="topbar-title"><?php echo htmlspecialchars($page_title); ?></h4>
        <div class="topbar-subtitle">EduNexAI Smart Academic Management System</div>
    </div>
    <div class="d-flex align-items-center">
        <button id="themeToggleBtn" class="btn btn-light border btn-sm p-2 rounded-circle me-3 theme-toggle-btn" title="Toggle Light/Dark Theme" style="width:38px; height:38px; display:flex; align-items:center; justify-content:center;">
            <i class="fas fa-moon text-primary" id="themeToggleIcon" style="font-size:16px;"></i>
        </button>
        <a href="profile.php" class="topbar-user text-decoration-none" title="Click to View My Profile">
            <div class="topbar-avatar">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="d-none d-sm-block">
                <span class="d-block text-dark fw-bold" style="line-height:1.2;"><?php echo htmlspecialchars($admin_name); ?></span>
                <small class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">System Admin</small>
            </div>
        </a>
    </div>
</div>