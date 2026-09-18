/* =========================================================
   EduNexAI — Shared Light/Dark Theme Switcher JS
========================================================= */

(function () {
    function applyTheme(theme) {
        const isDark = theme === 'dark';
        if (isDark) {
            document.documentElement.classList.add('dark-theme');
            document.documentElement.setAttribute('data-bs-theme', 'dark');
            if (document.body) document.body.classList.add('dark-theme');
        } else {
            document.documentElement.classList.remove('dark-theme');
            document.documentElement.removeAttribute('data-bs-theme');
            if (document.body) document.body.classList.remove('dark-theme');
        }
    }

    // Apply saved theme immediately
    const savedTheme = localStorage.getItem('edunexai_theme');
    applyTheme(savedTheme);

    function initTheme() {
        const currentTheme = localStorage.getItem('edunexai_theme');
        applyTheme(currentTheme);

        const toggleBtn = document.getElementById('themeToggleBtn');
        const toggleIcon = document.getElementById('themeToggleIcon');

        if (currentTheme === 'dark' && toggleIcon) {
            toggleIcon.className = 'fas fa-sun text-warning';
        }

        if (toggleBtn) {
            toggleBtn.replaceWith(toggleBtn.cloneNode(true));
            const newToggleBtn = document.getElementById('themeToggleBtn');
            const newToggleIcon = document.getElementById('themeToggleIcon');

            if (currentTheme === 'dark' && newToggleIcon) {
                newToggleIcon.className = 'fas fa-sun text-warning';
            }

            newToggleBtn.addEventListener('click', function (e) {
                e.preventDefault();
                const activeIsDark = document.documentElement.classList.contains('dark-theme') || (document.body && document.body.classList.contains('dark-theme'));

                if (!activeIsDark) {
                    applyTheme('dark');
                    localStorage.setItem('edunexai_theme', 'dark');
                    if (newToggleIcon) newToggleIcon.className = 'fas fa-sun text-warning';
                } else {
                    applyTheme('light');
                    localStorage.setItem('edunexai_theme', 'light');
                    if (newToggleIcon) newToggleIcon.className = 'fas fa-moon text-primary';
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
