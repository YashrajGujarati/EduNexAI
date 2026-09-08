/* =========================================================
   EduNexAI — Shared Light/Dark Theme Switcher JS
========================================================= */

(function () {
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-theme');
            if (document.body) document.body.classList.add('dark-theme');
        } else {
            document.documentElement.classList.remove('dark-theme');
            if (document.body) document.body.classList.remove('dark-theme');
        }
    }

    // Apply saved theme immediately
    const savedTheme = localStorage.getItem('edunexai_theme');
    applyTheme(savedTheme);

    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('themeToggleBtn');
        const toggleIcon = document.getElementById('themeToggleIcon');

        // Sync initial state
        const currentTheme = localStorage.getItem('edunexai_theme');
        applyTheme(currentTheme);

        if (currentTheme === 'dark' && toggleIcon) {
            toggleIcon.className = 'fas fa-sun text-warning';
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const isDark = document.documentElement.classList.contains('dark-theme') || (document.body && document.body.classList.contains('dark-theme'));

                if (!isDark) {
                    applyTheme('dark');
                    localStorage.setItem('edunexai_theme', 'dark');
                    if (toggleIcon) toggleIcon.className = 'fas fa-sun text-warning';
                } else {
                    applyTheme('light');
                    localStorage.setItem('edunexai_theme', 'light');
                    if (toggleIcon) toggleIcon.className = 'fas fa-moon text-primary';
                }
            });
        }
    });
})();
