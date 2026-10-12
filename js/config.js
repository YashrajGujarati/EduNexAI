/**
 * EduNexAI frontend configuration.
 * The backend is hosted separately from the static Vercel frontend.
 */
(function () {
    var isLocalhost = window.location.hostname === 'localhost' ||
        window.location.hostname === '127.0.0.1';
    var configuredUrl = window.__EDUNEX_BACKEND_URL__ ||
        window.localStorage.getItem('EDUNEX_BACKEND_URL') ||
        '';
    var backendUrl = configuredUrl.replace(/\/+$/, '');

    window.EDUNEX_CONFIG = {
        isLocalhost: isLocalhost,
        BACKEND_URL: isLocalhost ? '' : backendUrl,
        getApiUrl: function (endpoint) {
            var cleanPath = (endpoint || '').replace(/^\/+/, '');
            if (!backendUrl || isLocalhost) return cleanPath;
            return backendUrl + '/' + cleanPath;
        }
    };
})();
