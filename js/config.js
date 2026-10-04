/**
 * EduNexAI — Centralized Frontend Environment & Backend URL Configuration
 *
 * HOW IT WORKS:
 * ─────────────
 * On LOCAL (XAMPP/localhost):
 *   - BACKEND_URL is empty → all PHP links work as relative paths (e.g. "login.php")
 *
 * On VERCEL (production):
 *   - window.EDUNEX_BACKEND_URL is injected by js/env.js (generated at deploy time)
 *     OR you can set it in Vercel Environment Variables as VITE_BACKEND_URL
 *   - All PHP links are automatically prefixed with the Railway backend URL
 *
 * To set the Railway URL, add this Environment Variable in your Vercel project:
 *   NEXT_PUBLIC_BACKEND_URL = https://your-railway-app.up.railway.app
 *   (Vercel will make this available as window.__BACKEND_URL__ via _vercel/insights
 *    or via the env.js approach below)
 */

(function () {
    // 1. Detect if running locally
    const isLocalhost = window.location.hostname === 'localhost' ||
                        window.location.hostname === '127.0.0.1';

    // 2. Resolve backend URL — priority order:
    //    a) window.__EDUNEX_BACKEND_URL__ (set by env.js on Vercel)
    //    b) localStorage override (for manual testing)
    //    c) Empty string (localhost fallback — uses relative paths)
    let resolvedUrl = '';

    try {
        resolvedUrl =
            (typeof window !== 'undefined' && window.__EDUNEX_BACKEND_URL__ && window.__EDUNEX_BACKEND_URL__ !== '__RAILWAY_URL__'
                ? window.__EDUNEX_BACKEND_URL__
                : null) ||
            (typeof localStorage !== 'undefined' ? localStorage.getItem('EDUNEX_BACKEND_URL') : null) ||
            '';
    } catch (e) {
        resolvedUrl = '';
    }

    // Strip trailing slashes
    if (resolvedUrl) {
        resolvedUrl = resolvedUrl.replace(/\/+$/, '');
    }

    window.EDUNEX_CONFIG = {
        BACKEND_URL: resolvedUrl,
        isLocalhost: isLocalhost,

        /**
         * Resolves a relative PHP path to the full backend URL in production.
         * e.g. getApiUrl('login.php') → 'https://railway-app.up.railway.app/login.php'
         * On localhost → 'login.php'
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
