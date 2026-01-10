<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
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

                <!-- Sección de Permisos -->
                <div class="mt-6 pt-4 border-t border-[#006a2d]">
                    <button
                        @click="showPermissions = !showPermissions"
                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-white/80 hover:text-white transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <span class="text-blue-400">🔑</span>
                            <span>Mis Permisos</span>
                        </span>
                        <span class="text-xs">
                            {{ showPermissions ? "▼" : "▶" }}
                        </span>
                    </button>

                    <div
                        v-if="showPermissions"
                        class="mt-2 space-y-2 px-3 pb-2"
                    >
                        <!-- Permisos activos -->
                        <div v-if="userPermissions.length > 0">
                            <p class="text-xs text-white/70 mb-2">
                                Permisos activos ({{ userPermissions.length }}):
                            </p>
                            <div class="space-y-1">
                                <div
                                    v-for="permission in userPermissions"
                                    :key="permission"
                                    class="flex items-center gap-2 text-xs text-white/80"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                    <span>{{ formatPermissionName(permission) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones visibles -->
                        <div class="mt-3 pt-3 border-t border-[#006a2d]">
                            <p class="text-xs text-white/70 mb-2">
                                Botones visibles ({{ filteredMenuItems.length }}):
                            </p>
                            <div class="space-y-1">
                                <div
                                    v-for="item in filteredMenuItems"
                                    :key="item.name"
                                    class="flex items-center gap-2 text-xs text-white/80"
                                >
                                    <span class="text-sm">{{ item.icon }}</span>
                                    <span>{{ item.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sin permisos -->
                        <div
                            v-if="userPermissions.length === 0"
                            class="text-xs text-white/70 italic"
                        >
                            No tienes permisos asignados
                        </div>
                    </div>
                </div>
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

            <!-- Page Content -->
            <main class="p-4 sm:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";
import { useDarkMode } from "@/composables/useDarkMode";

const page = usePage();
const showPermissions = ref(false);
const sidebarOpen = ref(false);
const { isDark, toggleDarkMode } = useDarkMode();

const user = computed(() => page.props.auth?.user || page.props.user);
const esVisitante = computed(() => user.value?.role?.name === "visitante");
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
    return user.value.cargo?.name || "Sin cargo asignado";
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
        label: "Permisos",
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
        permission: "view_users", // Mismo permiso que usuarios
    },
    {
        name: "protocolo",
        label: "Protocolo",
        href: "/protocolo",
        icon: "🚨",
        permission: "view_protocolo",
    },{
        name: "soporte",
        label: "Soporte",
        href: "/soporte",
        icon: "❓",
        permission: null, // Accesible para todos los usuarios autenticados
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
});

onUnmounted(() => {
    if (typeof window === "undefined") return;
    window.removeEventListener("keydown", onKeyDown);
});

const formatPermissionName = (permission) => {
    const names = {
        view_dashboard: "Ver Dashboard",
        view_users: "Ver Usuarios",
        create_users: "Crear Usuarios",
        edit_users: "Editar Usuarios",
        delete_users: "Eliminar Usuarios",
        view_puertas: "Ver Puertas",
        create_puertas: "Crear Puertas",
        edit_puertas: "Editar Puertas",
        delete_puertas: "Eliminar Puertas",
        view_cargos: "Ver Permisos/Cargos",
        create_cargos: "Crear Permisos/Cargos",
        edit_cargos: "Editar Permisos/Cargos",
        delete_cargos: "Eliminar Permisos/Cargos",
        view_ingreso: "Ver Ingreso/QR",
        create_ingreso: "Generar Códigos QR",
        view_mantenimientos: "Ver Mantenimientos",
        create_mantenimientos: "Crear Mantenimientos",
        edit_mantenimientos: "Editar Mantenimientos",
        delete_mantenimientos: "Eliminar Mantenimientos",
        view_protocolo: "Ver Protocolo de Emergencia",
        protocol_emergencia_open_all: "Ejecutar Protocolo de Emergencia",
    };
    return names[permission] || permission;
};
</script>
