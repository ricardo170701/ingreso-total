<template>
    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar -->
        <aside
            class="fixed left-0 top-0 h-full w-64 bg-slate-900 border-r border-slate-800 z-30"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-slate-800">
                <h1 class="text-xl font-bold text-white">Escaner Total</h1>
            </div>

            <!-- Navigation -->
            <nav class="mt-6 px-3">
                <ul class="space-y-1">
                    <li v-for="item in menuItems" :key="item.name">
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
            </nav>

            <!-- User Section -->
            <div
                class="absolute bottom-0 left-0 right-0 p-4 border-t border-slate-800"
            >
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
import { computed } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";

const page = usePage();

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
    },
    {
        name: "users",
        label: "Usuarios",
        href: "/usuarios",
        icon: "👥",
    },
    {
        name: "puertas",
        label: "Puertas",
        href: "/puertas",
        icon: "🚪",
    },
    {
        name: "cargos",
        label: "Permisos",
        href: "/cargos",
        icon: "🔐",
    },
    {
        name: "ingreso",
        label: "Ingreso",
        href: "/ingreso",
        icon: "📱",
    },
];

const isActive = (href) => {
    return page.url === href || page.url.startsWith(href + "/");
};

const logout = () => {
    router.post(route("logout"));
};
</script>
