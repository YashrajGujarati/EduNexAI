window.addEventListener("load", function () {
    console.log("EduNexAI Loaded Successfully");
});

const nav = document.querySelector(".navbar");
window.addEventListener("scroll", function () {
    if (window.scrollY > 50) {
        nav.style.boxShadow = "0 4px 15px rgba(0,0,0,0.2)";
    } else {
        nav.style.boxShadow = "none";
    }
});

/**
 * Automatically maps PHP links to the Railway backend URL in production,
 * preserving relative paths on localhost.
 */
function applyBackendUrls() {
    if (!window.EDUNEX_CONFIG) return;
    const backendUrl = window.EDUNEX_CONFIG.BACKEND_URL;

    // Find all links to PHP endpoints in the frontend
    const phpLinks = document.querySelectorAll('a[href$=".php"], a[href*=".php"]');
    phpLinks.forEach(function (link) {
        const rawHref = link.getAttribute('href');
        if (!rawHref) return;

        // Skip absolute URLs already configured
        if (rawHref.startsWith('http://') || rawHref.startsWith('https://')) return;

        if (backendUrl) {
            link.href = window.EDUNEX_CONFIG.getApiUrl(rawHref);
        }
    });
}

// Guard click to prevent downloading .php on Vercel if BACKEND_URL is not set
document.addEventListener('click', function (e) {
    const link = e.target.closest('a[href$=".php"], a[href*=".php"]');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const hasBackend = window.EDUNEX_CONFIG && window.EDUNEX_CONFIG.BACKEND_URL;

    // On Vercel without BACKEND_URL, redirect to login.html to show connection portal
    if (!isLocalhost && !hasBackend && href.includes('.php')) {
        e.preventDefault();
        window.location.href = 'login.html';
        return false;
    }
});

// Run once DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyBackendUrls);
} else {
    applyBackendUrls();
}