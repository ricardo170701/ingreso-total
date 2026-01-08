import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Escaner Total';

// Configurar timeout de sesión (5 minutos = 300000 ms)
const SESSION_TIMEOUT = 5 * 60 * 1000; // 5 minutos en milisegundos
const WARNING_TIME = 1 * 60 * 1000; // Mostrar aviso 1 minuto antes

let warningShown = false;
let warningTimeoutId = null;
let sessionStartTime = null; // Tiempo absoluto de inicio de sesión
let absoluteTimeoutId = null; // Timer absoluto que NUNCA se reinicia

function resetActivityWarning() {
    // Solo reiniciar el timer de advertencia, NUNCA el timer absoluto
    if (warningTimeoutId) {
        clearTimeout(warningTimeoutId);
    }
    warningShown = false;

    // Calcular cuánto tiempo queda desde el inicio de la sesión
    if (!sessionStartTime) {
        sessionStartTime = Date.now();
    }

    const elapsed = Date.now() - sessionStartTime;
    const remaining = SESSION_TIMEOUT - elapsed;
    const warningRemaining = remaining - WARNING_TIME;

    // Solo programar advertencia si aún hay tiempo suficiente (más de 1 minuto)
    if (warningRemaining > 1000) {
        warningTimeoutId = setTimeout(() => {
            if (!warningShown && sessionStartTime) {
                warningShown = true;
                showSessionWarning();
            }
        }, warningRemaining);
    }
}

function initializeSessionTimer() {
    // Inicializar tiempo absoluto de inicio SOLO UNA VEZ
    if (sessionStartTime === null) {
        sessionStartTime = Date.now();

        // Timer absoluto que se ejecuta SIEMPRE a los 5 minutos, NUNCA se reinicia
        absoluteTimeoutId = setTimeout(() => {
            // Cerrar sesión automáticamente después de 5 minutos
            // Esto se ejecuta sin importar qué haya pasado
            console.log('Sesión cerrada automáticamente por inactividad (5 minutos)');
            window.location.href = '/login';
        }, SESSION_TIMEOUT);
    }

    // Configurar timer de advertencia
    resetActivityWarning();
}

function showSessionWarning() {
    if (!sessionStartTime) return;

    const elapsed = Date.now() - sessionStartTime;
    const remaining = SESSION_TIMEOUT - elapsed;

    // Si ya pasaron 5 minutos o más, cerrar inmediatamente
    if (remaining <= 0) {
        console.log('Sesión expirada, cerrando...');
        window.location.href = '/login';
        return;
    }

    const remainingSeconds = Math.ceil(remaining / 1000);
    const remainingMinutes = Math.floor(remainingSeconds / 60);
    const remainingSecs = remainingSeconds % 60;

    const message = `Tu sesión expirará en aproximadamente ${remainingMinutes} minuto${remainingMinutes !== 1 ? 's' : ''} y ${remainingSecs} segundo${remainingSecs !== 1 ? 's' : ''} debido a inactividad.\n\nNota: La sesión se cerrará automáticamente después de 5 minutos totales de inactividad.\n\n¿Deseas hacer una petición al servidor?`;

    const shouldExtend = confirm(message);

    if (shouldExtend) {
        // Hacer una petición al servidor para mantener la sesión activa
        // PERO el timer absoluto sigue corriendo y cerrará la sesión a los 5 minutos
        fetch('/', {
            method: 'HEAD',
            credentials: 'same-origin',
        }).then(() => {
            // Solo reiniciar el aviso si aún hay tiempo
            // El timer absoluto NO se reinicia
            resetActivityWarning();
        }).catch(() => {
            resetActivityWarning();
        });
    }
    // Si el usuario cancela, no hacemos nada y la sesión se cerrará automáticamente
}

// Detectar actividad del usuario
function setupActivityListeners() {
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];

    events.forEach(event => {
        // Usar resetActivityWarning en lugar de resetSessionTimer
        // Esto solo reinicia el aviso, NO el timer absoluto
        document.addEventListener(event, resetActivityWarning, true);
    });
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Inicializar timer de sesión solo si el usuario está autenticado
        if (props.initialPage.props.auth?.user) {
            initializeSessionTimer();
            setupActivityListeners();
        }

        return app.mount(el);
    },
    progress: {
        color: '#22c55e',
    },
});
