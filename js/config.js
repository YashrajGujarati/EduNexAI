/**
 * EduNexAI — Centralized Frontend Environment & Backend URL Configuration
 * 
 * IN PRODUCTION (Vercel):
 * Set your Railway backend URL here, e.g.:
 *   BACKEND_URL: "https://your-edunex-backend.up.railway.app"
 * 
 * OR set it dynamically in browser console / script:
 *   localStorage.setItem('EDUNEX_BACKEND_URL', 'https://your-edunex-backend.up.railway.app');
 * 
 * IN LOCAL DEVELOPMENT (XAMPP / Localhost):
 * Leave BACKEND_URL as "" to automatically use relative paths.
 */

(function () {
    // 1. Detect if running locally
    const isLocalhost = window.location.hostname === 'localhost' ||
                        window.location.hostname === '127.0.0.1';

    // 2. Production placeholder — replace with your actual Railway domain when available
    const PRODUCTION_BACKEND_URL = "";

    // 3. Resolve backend URL with fallbacks:
    // Priority: window.__EDUNEX_BACKEND_URL__ > localStorage > meta tag > PRODUCTION_BACKEND_URL > empty (localhost)
    const metaTag = document.querySelector('meta[name="backend-url"]');
    const metaUrl = metaTag ? metaTag.getAttribute('content') : null;

    let resolvedUrl = (typeof window !== 'undefined' && window.__EDUNEX_BACKEND_URL__)
        || (typeof localStorage !== 'undefined' ? localStorage.getItem('EDUNEX_BACKEND_URL') : null)
        || (metaUrl && metaUrl !== '__BACKEND_URL__' ? metaUrl : null)
        || (isLocalhost ? "" : PRODUCTION_BACKEND_URL);

    if (resolvedUrl) {
        resolvedUrl = resolvedUrl.replace(/\/+$/, ''); // Strip trailing slashes
    } else {
        resolvedUrl = "";
    }

    window.EDUNEX_CONFIG = {
        BACKEND_URL: resolvedUrl,
        isLocalhost: isLocalhost,
        
        /**
         * Resolves a relative path to the backend URL
         * Example: getApiUrl('login.php') -> "https://railway.app/login.php" or "login.php"
         */
        getApiUrl: function (endpoint) {
            const cleanPath = (endpoint || '').replace(/^\/+/, '');
            if (!this.BACKEND_URL) {
                return cleanPath;
            }
            return this.BACKEND_URL + '/' + cleanPath;
        }
    };
})();
