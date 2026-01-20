<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Tipos de Vinculación
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Los tipos de vinculación solo indican si un usuario es <b>Visitante</b>, <b>Servidor público</b> o <b>Proveedor</b>. Los permisos del sistema se gestionan por <b>Roles</b> (antes “Cargos”).
                    </p>
                </div>
                <Link
                    :href="route('cargos.index')"
                    class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors duration-200"
                >
                    Ver Roles (Permisos)
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Lista de Roles (bandera) -->
            <div class="space-y-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                                {{ formatTipoVinculacion(role.name) }}
                            </h2>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                {{ role.description || "Sin descripción" }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                                {{ role.users_count || 0 }} usuario(s) con este rol
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    roles: Array,
});

const formatTipoVinculacion = (name) => {
    const map = {
        visitante: "Visitante",
        servidor_publico: "Servidor público",
        proveedor: "Proveedor",
        contratista: "Proveedor", // Compatibilidad
        // compatibilidad histórica
        funcionario: "Servidor público",
    };
    return map[name] || name;
};
</script>

