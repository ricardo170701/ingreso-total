<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
        <!-- Bloqueo visual (mejor esfuerzo) ante intento de captura/impresión -->
        <!-- IMPORTANTE: el blur NO debe afectar el modal (solo el fondo) -->
        <div
            v-if="securityOverlayVisible"
            class="fixed inset-0 z-9999 bg-black/85 flex items-center justify-center p-4"
        >
            <div class="max-w-md w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 p-5 shadow-2xl">
                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">
                    Acción bloqueada
                </h3>
                <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">
                    Las capturas de pantalla / impresión están deshabilitadas en este sistema.
                </p>
                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors"
                        @click="hideSecurityOverlay"
                    >
                        Entendido
                    </button>
                </div>
            </div>
        </div>

        <!-- App (se blurea solo el fondo, no el overlay) -->
        <div :class="securityBlur ? 'blur-sm select-none' : ''">
        <!-- Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/40 z-40 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 h-screen w-64 bg-[#008c3a] border-r border-[#006a2d] z-50 flex flex-col transform transition-transform duration-200 ease-out lg:translate-x-0 lg:z-30"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Logo -->
            <div class="h-24 flex items-center justify-center px-4 border-b border-[#006a2d] shrink-0 bg-[#006a2d]/30">
                <div class="flex items-center gap-3 justify-center w-full">
                    <img
                        src="/images/logo-gobernacion-meta.png"
                        alt="Gobernación del Meta"
                        class="h-20 w-auto object-contain drop-shadow-lg"
                        onerror="this.style.display='none'"
                    />
                    <div class="flex flex-col">
                        <h1 class="text-base font-bold text-white leading-tight">Gobernación</h1>
                        <h1 class="text-base font-bold text-white leading-tight">del Meta</h1>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto mt-6 px-3 pb-4">
                <ul class="space-y-1">
                    <li v-for="item in filteredMenuItems" :key="item.name">
                        <Link
                            v-if="item.href !== '#'"
                            :href="item.href"
                            @click="sidebarOpen = false"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200',
                                isActive(item.href)
                                    ? 'bg-[#006a2d] text-white shadow-lg shadow-black/20'
                                    : 'text-white/80 hover:bg-[rgba(0,106,45,0.5)] hover:text-white hover:shadow-md hover:shadow-black/10',
                            ]"
                        >
                            <span v-if="item.icon" class="text-lg">{{
                                item.icon
                            }}</span>
                            <span>{{ item.label }}</span>
                        </Link>
                        <a
                            v-else
                            href="#"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-white/60 cursor-not-allowed"
                        >
                            <span v-if="item.icon" class="text-lg">{{
                                item.icon
                            }}</span>
                            <span>{{ item.label }}</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- User Section -->
            <div
                class="shrink-0 p-4 border-t border-[#006a2d] bg-[#006a2d]/30"
            >

                <form @submit.prevent="logout" class="mt-3">
                    <button
                        type="submit"
                        class="w-full text-left px-3 py-2 text-sm text-white/80 hover:text-white hover:bg-[rgba(0,106,45,0.5)] rounded-lg transition-colors"
                    >
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

            <!-- Main Content -->
            <div class="lg:ml-64">
            <!-- Top Bar -->
            <header
                class="sticky top-0 z-20 bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 h-16 flex items-center justify-between px-4 sm:px-6 transition-colors duration-200"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        type="button"
                        class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        @click="sidebarOpen = !sidebarOpen"
                        aria-label="Abrir menú"
                    >
                        ☰
                    </button>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 truncate">
                        {{ pageTitle }}
                    </h2>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Botón de Modo Oscuro -->
                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        :title="isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                        aria-label="Toggle dark mode"
                    >
                        <span v-if="isDark" class="text-xl">☀️</span>
                        <span v-else class="text-xl">🌙</span>
                    </button>

                    <Link
                        :href="route('profile.show')"
                        class="text-right hover:opacity-80 transition-opacity"
                    >
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                            {{ user?.name || user?.email }}
                        </p>
                        <p class="text-xs text-slate-600 dark:text-slate-400">
                            {{ userSubtitle }}
                        </p>
                    </Link>
                    <Link
                        :href="route('profile.show')"
                        class="w-10 h-10 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-600 shrink-0 hover:ring-2 hover:ring-green-500 dark:hover:ring-green-400 transition-all cursor-pointer"
                    >
                        <img
                            v-if="user?.foto_perfil"
                            :src="storageUrl(user.foto_perfil)"
                            alt="Foto de perfil"
                            class="w-full h-full object-cover"
                        />
                        <div
                            v-else
                            class="w-full h-full flex items-center justify-center text-slate-700 dark:text-slate-200 text-sm font-semibold"
                        >
                            {{ userInitials }}
                        </div>
                    </Link>
                </div>
            </header>

            <!-- Modal de aviso de expiración (<= 14 días) -->
            <Modal
                :show="expiracionAviso.visible && !expiracionAvisoConfirmado"
                title="Aviso: Tu cuenta está próxima a expirar"
                :closable="false"
                :closeOnBackdrop="false"
                :showCancel="false"
                confirmText="Entendido"
                confirmClass="bg-amber-600 dark:bg-amber-700 hover:bg-amber-700 dark:hover:bg-amber-600"
                @confirm="confirmarAvisoExpiracion"
            >
                <div class="text-sm text-slate-700 dark:text-slate-300">
                    <p class="mb-3">
                        <span v-if="expiracionAviso.diasRestantes === 0">
                            Tu cuenta <strong class="font-semibold text-amber-700 dark:text-amber-400">expira hoy</strong>.
                        </span>
                        <span v-else>
                            Tu cuenta expirará en <strong class="font-semibold text-amber-700 dark:text-amber-400">{{ expiracionAviso.diasRestantes }} día(s)</strong>.
                        </span>
                        <span v-if="expiracionAviso.fecha" class="block mt-2 text-slate-600 dark:text-slate-400">
                            Fecha de expiración: {{ formatearFecha(expiracionAviso.fecha) }}
                        </span>
                    </p>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Por favor, contacta al administrador si necesitas extender tu cuenta.
                    </p>
                </div>
            </Modal>

            <!-- Page Content -->
            <main class="p-4 sm:p-6">
                <slot />
            </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";
import { useDarkMode } from "@/composables/useDarkMode";
import Modal from "@/Components/Modal.vue";

const page = usePage();
const sidebarOpen = ref(false);
const { isDark, toggleDarkMode } = useDarkMode();

const user = computed(() => page.props.auth?.user || page.props.user);
const esVisitante = computed(() => user.value?.role?.name === "visitante");
const expiracionAvisoConfirmado = ref(false);

const expiracionAviso = computed(() => {
    // No mostrar si ya fue confirmado
    if (expiracionAvisoConfirmado.value) {
        return { visible: false, diasRestantes: null, fecha: null };
    }
    
    const u = user.value;
    if (!u || u.activo !== true || !u.fecha_expiracion) {
        return { visible: false, diasRestantes: null, fecha: null };
    }

    // fecha_expiracion viene como YYYY-MM-DD
    const expMs = Date.parse(`${u.fecha_expiracion}T00:00:00`);
    if (!Number.isFinite(expMs)) {
        return { visible: false, diasRestantes: null, fecha: null };
    }

    const hoy = new Date();
    const hoyMs = Date.parse(
        `${hoy.getFullYear()}-${String(hoy.getMonth() + 1).padStart(2, "0")}-${String(hoy.getDate()).padStart(2, "0")}T00:00:00`
    );
    const diffDays = Math.floor((expMs - hoyMs) / (1000 * 60 * 60 * 24));

    // Mostrar aviso si faltan 14 días o menos, pero no si ya expiró (porque se inactiva)
    if (diffDays < 0 || diffDays > 14) {
        return { visible: false, diasRestantes: null, fecha: null };
    }

    return { visible: true, diasRestantes: diffDays, fecha: u.fecha_expiracion };
});

// Función para formatear la fecha
const formatearFecha = (fecha) => {
    if (!fecha) return '';
    try {
        const fechaObj = new Date(fecha + 'T00:00:00');
        return fechaObj.toLocaleDateString('es-ES', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    } catch {
        return fecha;
    }
};

// Función para confirmar el aviso de expiración
const confirmarAvisoExpiracion = () => {
    expiracionAvisoConfirmado.value = true;
};
const pageTitle = computed(() => {
    const component = page.component || "";
    const parts = component.split("/");
    return parts[parts.length - 1]?.replace(".vue", "") || "Dashboard";
});

const userInitials = computed(() => {
    if (!user.value) return "U";
    const name = user.value.name || user.value.email || "";
    const parts = name.split(" ");
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name[0]?.toUpperCase() || "U";
});

const userSubtitle = computed(() => {
    if (!user.value) return "";
    if (esVisitante.value) return "Visitante";
    return user.value.cargo?.name || "Sin rol asignado";
});

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    // Requiere `php artisan storage:link`
    return `/storage/${path}`;
};

const menuItems = [
    {
        name: "dashboard",
        label: "Dashboard",
        href: "/dashboard",
        icon: "📊",
        permission: "view_dashboard",
    },
    {
        name: "users",
        label: "Usuarios",
        href: "/usuarios",
        icon: "👥",
        permission: "view_users",
    },
    {
        name: "puertas",
        label: "Puertas",
        href: "/puertas",
        icon: "🚪",
        permission: "view_puertas",
    },
    {
        name: "cargos",
        label: "Roles / Permisos",
        href: "/cargos",
        icon: "🔐",
        permission: "view_cargos",
    },
    {
        name: "ingreso",
        label: "Ingreso",
        href: "/ingreso",
        icon: "📱",
        permission: "view_ingreso",
    },
    {
        name: "tarjetas-nfc",
        label: "Tarjetas NFC",
        href: "/tarjetas-nfc",
        icon: "💳",
        permission: "view_tarjetas_nfc",
    },
    {
        name: "ups",
        label: "UPS",
        href: "/ups",
        icon: "🔋",
        permission: "view_ups",
    },

    {
        name: "dependencias",
        label: "Dependencias",
        href: "/dependencias",
        icon: "🏢",
        permission: "view_departamentos", // Mantiene el mismo permiso por compatibilidad
    },
    {
        name: "reportes",
        label: "Reportes",
        href: "/reportes",
        icon: "📊",
        permission: "view_reportes",
    },
    {
        name: "protocolo",
        label: "Protocolo",
        href: "/protocolo",
        icon: "🚨",
        permission: "view_protocolo",
    },
    {
        name: "soporte",
        label: "Soporte",
        href: "/soporte",
        icon: "❓",
        permission: "view_soporte",
    },
];

// Filtrar items del menú según permisos del usuario
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const filteredMenuItems = computed(() => {
    // Visitante: acceso web limitado a Ingreso y Soporte (independiente de permisos del cargo)
    if (esVisitante.value) {
        return menuItems.filter((item) =>
            ["ingreso", "soporte"].includes(item.name)
        );
    }

    return menuItems.filter((item) => {
        // Si no tiene permiso definido, siempre se muestra
        if (!item.permission) {
            return true;
        }
        // Verificar si el usuario tiene el permiso
        return userPermissions.value.includes(item.permission);
    });
});

const isActive = (href) => {
    if (!page.url) return false;
    // Normalizar las rutas para comparación
    const currentUrl = page.url.split('?')[0]; // Remover query params
    const normalizedHref = href.split('?')[0];

    if (currentUrl === normalizedHref) return true;
    if (normalizedHref !== '/dashboard' && currentUrl.startsWith(normalizedHref + '/')) return true;
    return false;
};

const logout = () => {
    router.post(route("logout"));
};

// ===== Seguridad UI (bloqueo mejor-esfuerzo de captura/impresión) =====
// Nota: En navegadores NO es posible impedir al 100% las capturas de pantalla a nivel SO.
// Esto solo bloquea atajos comunes y agrega disuasión visual.
const securityOverlayVisible = ref(false);
const securityBlur = ref(false);
let securityTimeout = null;

const hideSecurityOverlay = () => {
    securityOverlayVisible.value = false;
    securityBlur.value = false;
    if (securityTimeout) {
        clearTimeout(securityTimeout);
        securityTimeout = null;
    }
};

const triggerSecurityBlock = () => {
    securityOverlayVisible.value = true;
    securityBlur.value = true;
    if (securityTimeout) clearTimeout(securityTimeout);
    securityTimeout = setTimeout(() => {
        hideSecurityOverlay();
    }, 2500);
};

const onKeyDownSecurity = (e) => {
    const key = String(e.key || "").toLowerCase();

    // Print Screen (algunos navegadores lo reportan, otros no)
    const isPrintScreen = key === "printscreen" || key === "prtsc" || key === "prtscr";

    // Windows Snipping Tool: Win + Shift + S
    const isWinSnip = e.metaKey && e.shiftKey && key === "s";

    // macOS screenshots: Cmd + Shift + 3/4/5
    const isMacShot = e.metaKey && e.shiftKey && (key === "3" || key === "4" || key === "5");

    // Impresión: Ctrl/Cmd + P (evita imprimir QR)
    const isPrint = (e.ctrlKey || e.metaKey) && key === "p";

    if (isPrintScreen || isWinSnip || isMacShot || isPrint) {
        try {
            e.preventDefault();
            e.stopPropagation();
        } catch (_) {}
        triggerSecurityBlock();
        return false;
    }

    return true;
};

const onContextMenuSecurity = (e) => {
    // Deshabilitar clic derecho (evita “Guardar imagen como...” y algunas herramientas)
    try {
        e.preventDefault();
        e.stopPropagation();
    } catch (_) {}
    triggerSecurityBlock();
    return false;
};

const onDragStartSecurity = (e) => {
    // Evitar arrastrar imágenes/textos fuera del navegador
    try {
        e.preventDefault();
        e.stopPropagation();
    } catch (_) {}
    return false;
};

const onVisibilityChangeSecurity = () => {
    // Mobile/desktop: SOLO cuando realmente la pestaña pasa a segundo plano.
    // Evitamos usar `blur` porque dispara con interacciones normales (barra de direcciones, cambio de foco, etc).
    if (typeof document !== "undefined" && document.hidden) {
        triggerSecurityBlock();
    }
};

// Cerrar sidebar al navegar (mobile)
watch(
    () => page.url,
    () => {
        sidebarOpen.value = false;
    }
);

// Bloquear scroll del body cuando sidebar está abierto (mobile)
watch(
    () => sidebarOpen.value,
    (isOpen) => {
        if (typeof document === "undefined") return;
        document.body.classList.toggle("overflow-hidden", isOpen);
    }
);

// Cerrar con ESC
const onKeyDown = (e) => {
    if (e.key === "Escape") {
        sidebarOpen.value = false;
    }
};

onMounted(() => {
    if (typeof window === "undefined") return;
    window.addEventListener("keydown", onKeyDown);

    // Seguridad mejor-esfuerzo (capturas/impresión)
    window.addEventListener("keydown", onKeyDownSecurity, true);
    window.addEventListener("contextmenu", onContextMenuSecurity, true);
    window.addEventListener("dragstart", onDragStartSecurity, true);
    document.addEventListener("visibilitychange", onVisibilityChangeSecurity, true);
});

onUnmounted(() => {
    if (typeof window === "undefined") return;
    window.removeEventListener("keydown", onKeyDown);

    window.removeEventListener("keydown", onKeyDownSecurity, true);
    window.removeEventListener("contextmenu", onContextMenuSecurity, true);
    window.removeEventListener("dragstart", onDragStartSecurity, true);
    document.removeEventListener("visibilitychange", onVisibilityChangeSecurity, true);
    hideSecurityOverlay();
});
</script>
