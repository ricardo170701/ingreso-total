/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Obtener token de cookie XSRF-TOKEN (Laravel lo establece automáticamente)
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Interceptor para agregar X-XSRF-TOKEN desde la cookie (método estándar de Laravel)
window.axios.interceptors.request.use(
    (config) => {
        const xsrfToken = getCookie('XSRF-TOKEN');
        if (xsrfToken) {
            // IMPORTANTE:
            // No fijar X-CSRF-TOKEN desde el meta tag en una SPA (Inertia),
            // porque el token de sesión puede cambiar tras logout/login y quedar "viejo".
            // Laravel valida también con X-XSRF-TOKEN (cookie) que sí se actualiza.
            if (config.headers && 'X-CSRF-TOKEN' in config.headers) {
                delete config.headers['X-CSRF-TOKEN'];
            }
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfToken);
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo';

// import Pusher from 'pusher-js';
// window.Pusher = Pusher;

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: import.meta.env.VITE_PUSHER_APP_KEY,
//     cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
//     wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
//     wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
//     wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
//     forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
//     enabledTransports: ['ws', 'wss'],
// });
