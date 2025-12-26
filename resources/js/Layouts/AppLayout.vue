<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 h-screen w-64 bg-slate-900 border-r border-slate-800 z-30 flex flex-col"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800 shrink-0">
                <h1 class="text-xl font-bold text-white">Escaner Total</h1>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto mt-6 px-3 pb-4">
                <ul class="space-y-1">
                    <li v-for="item in filteredMenuItems" :key="item.name">
                        <Link
                            v-if="item.href !== '#'"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors',
                                isActive(item.href)
                                    ? 'bg-slate-800 text-white'
                                    : 'text-slate-400 hover:bg-slate-800/50 hover:text-white',
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
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-400 cursor-not-allowed"
                        >
                            <span v-if="item.icon" class="text-lg">{{
                                item.icon
                            }}</span>
                            <span>{{ item.label }}</span>
                        </a>
                    </li>
                </ul>

                <!-- Sección de Permisos -->
                <div class="mt-6 pt-4 border-t border-slate-800">
                    <button
                        @click="showPermissions = !showPermissions"
                        class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-slate-400 hover:text-white transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <span>🔑</span>
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
                            <p class="text-xs text-slate-500 mb-2">
                                Permisos activos ({{ userPermissions.length }}):
                            </p>
                            <div class="space-y-1">
                                <div
                                    v-for="permission in userPermissions"
                                    :key="permission"
                                    class="flex items-center gap-2 text-xs text-slate-400"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                    <span>{{ formatPermissionName(permission) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Botones visibles -->
                        <div class="mt-3 pt-3 border-t border-slate-800">
                            <p class="text-xs text-slate-500 mb-2">
                                Botones visibles ({{ filteredMenuItems.length }}):
                            </p>
                            <div class="space-y-1">
                                <div
                                    v-for="item in filteredMenuItems"
                                    :key="item.name"
                                    class="flex items-center gap-2 text-xs text-slate-400"
                                >
                                    <span class="text-sm">{{ item.icon }}</span>
                                    <span>{{ item.label }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Sin permisos -->
                        <div
                            v-if="userPermissions.length === 0"
                            class="text-xs text-slate-500 italic"
                        >
                            No tienes permisos asignados
                        </div>
                    </div>
                </div>
            </nav>

            <!-- User Section -->
            <div
                class="shrink-0 p-4 border-t border-slate-800 bg-slate-900"
            >
                <!-- SOS -->
                <Link
                    href="/soporte"
                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-red-600 text-white font-semibold hover:bg-red-500 transition-colors"
                >
                    <span class="text-lg leading-none">🆘</span>
                    <span>SOS</span>
                </Link>

                <div class="flex items-center gap-3">
                    <div
                        class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-white text-sm font-semibold"
                    >
                        {{ userInitials }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">
                            {{ user?.name || user?.email }}
                        </p>
                        <p class="text-xs text-slate-400 truncate">
                            {{ user?.role?.name || "Usuario" }}
                        </p>
                    </div>
                </div>
                <form @submit.prevent="logout" class="mt-3">
                    <button
                        type="submit"
                        class="w-full text-left px-3 py-2 text-sm text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors"
                    >
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="ml-64">
            <!-- Top Bar -->
            <header
                class="sticky top-0 z-20 bg-white border-b border-slate-200 h-16 flex items-center px-6"
            >
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ pageTitle }}
                </h2>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";

const page = usePage();
const showPermissions = ref(false);

const user = computed(() => page.props.auth?.user || page.props.user);
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
        name: "mantenimientos",
        label: "Mantenimientos",
        href: "/mantenimientos",
        icon: "🔧",
        permission: "view_mantenimientos",
    },

    {
        name: "departamentos",
        label: "Departamentos",
        href: "/departamentos",
        icon: "🏢",
        permission: "view_users", // Mismo permiso que usuarios
    },
    {
        name: "reportes",
        label: "Reportes",
        href: "/reportes",
        icon: "📊",
        permission: "view_users", // Mismo permiso que usuarios
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
    };
    return names[permission] || permission;
};
</script>
