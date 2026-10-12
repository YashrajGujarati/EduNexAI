/**
<<<<<<< HEAD
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
=======
 * EduNexAI — Frontend Configuration
 * Handles local development and the separately hosted PHP backend.
 * 
 * LOCAL DEVELOPMENT (XAMPP / Localhost):
 * Automatically uses relative paths — no configuration needed.
 *
 * PRODUCTION (Vercel frontend + Railway backend):
 * Set window.__EDUNEX_BACKEND_URL__ before this file loads, or set the
 * value in localStorage with the key EDUNEX_BACKEND_URL.
>>>>>>> 31bcd0e (Commited Successfully)
 */

(function () {
    // 1. Detect if running locally
    const isLocalhost = window.location.hostname === 'localhost' ||
                        window.location.hostname === '127.0.0.1';
    // Replace this with the public Railway URL before deploying to Vercel.
    const deploymentBackendUrl = '';
    const configuredBackendUrl = deploymentBackendUrl ||
        window.__EDUNEX_BACKEND_URL__ ||
        window.localStorage.getItem('EDUNEX_BACKEND_URL') ||
        '';
    const backendUrl = configuredBackendUrl.replace(/\/+$/, '');

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
        BACKEND_URL: isLocalhost ? '' : backendUrl,

        /**
<<<<<<< HEAD
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
=======
         * Returns a backend URL in production and a relative path locally.
         */
        getApiUrl: function (endpoint) {
            const cleanPath = (endpoint || '').replace(/^\/+/, '');
            if (!backendUrl || isLocalhost) return cleanPath;
            return backendUrl + '/' + cleanPath;
>>>>>>> 31bcd0e (Commited Successfully)
        }
    };
})();
