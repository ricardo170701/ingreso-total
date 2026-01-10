import { ref, onMounted } from 'vue';

// Estado compartido singleton
let globalIsDark = null;

// Cargar preferencia guardada o detectar preferencia del sistema
const loadDarkMode = () => {
    if (typeof window === 'undefined') return false;
    
    try {
        const saved = localStorage.getItem('darkMode');
        if (saved !== null) {
            return saved === 'true';
        }
        
        // Detectar preferencia del sistema solo si no hay preferencia guardada
        if (window.matchMedia) {
            return window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
    } catch (e) {
        console.error('Error loading dark mode:', e);
    }
    
    return false;
};

// Aplicar clase dark al elemento HTML
const applyDarkMode = (dark) => {
    if (typeof document === 'undefined') return;
    
    try {
        const html = document.documentElement;
        
        if (dark) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        
        // Guardar preferencia
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('darkMode', dark.toString());
        }
    } catch (e) {
        console.error('Error applying dark mode:', e);
    }
};

// Inicializar estado global singleton
const getGlobalState = () => {
    if (!globalIsDark) {
        if (typeof window !== 'undefined') {
            const initialValue = loadDarkMode();
            globalIsDark = ref(initialValue);
            // Aplicar inmediatamente
            applyDarkMode(initialValue);
        } else {
            // En SSR, inicializar con false
            globalIsDark = ref(false);
        }
    }
    return globalIsDark;
};

export function useDarkMode() {
    // Obtener el estado global singleton (siempre retorna un ref)
    const isDark = getGlobalState();

    // Inicializar cuando el componente se monte
    onMounted(() => {
        if (typeof window !== 'undefined' && isDark) {
            // Sincronizar estado con localStorage
            const saved = loadDarkMode();
            if (isDark.value !== saved) {
                isDark.value = saved;
                applyDarkMode(saved);
            }
        }
    });

    // Función para alternar modo oscuro
    const toggleDarkMode = () => {
        if (!isDark || typeof window === 'undefined') {
            console.error('Dark mode state not initialized or window not available');
            return;
        }
        
        try {
            // Invertir el valor
            const newValue = !isDark.value;
            isDark.value = newValue;
            // Aplicar inmediatamente
            applyDarkMode(newValue);
            
            console.log('Dark mode toggled to:', newValue);
            console.log('HTML classes:', document.documentElement.classList.toString());
        } catch (error) {
            console.error('Error toggling dark mode:', error);
        }
    };

    // Función para establecer modo oscuro explícitamente
    const setDarkMode = (dark) => {
        if (!isDark) {
            console.error('Dark mode state not initialized');
            return;
        }
        
        isDark.value = dark;
        applyDarkMode(dark);
    };

    return {
        isDark,
        toggleDarkMode,
        setDarkMode,
    };
}

// Inicializar inmediatamente al cargar el módulo (si estamos en el cliente)
if (typeof window !== 'undefined') {
    getGlobalState();
}
