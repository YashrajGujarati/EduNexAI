/**
 * EduNexAI — Frontend Configuration
 * Handles local development environment detection.
 * 
 * LOCAL DEVELOPMENT (XAMPP / Localhost):
 * Automatically uses relative paths — no configuration needed.
 */

(function () {
    const isLocalhost = window.location.hostname === 'localhost' ||
                        window.location.hostname === '127.0.0.1';

    window.EDUNEX_CONFIG = {
        isLocalhost: isLocalhost,

        /**
         * Returns a relative path for local PHP backend access.
         * Only works when running on localhost with XAMPP.
         */
        getApiUrl: function (endpoint) {
            const cleanPath = (endpoint || '').replace(/^\/+/, '');
            return cleanPath;
        }
    };
})();
