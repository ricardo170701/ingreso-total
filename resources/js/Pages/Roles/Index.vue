<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Tipos de Usuario
                    </h1>
                    <p class="text-sm text-slate-600">
                        Los roles solo indican si un usuario es <b>funcionario</b> o <b>visitante</b>. Los permisos del sistema se gestionan por <b>Cargos</b>.
                    </p>
                </div>
                <Link
                    :href="route('cargos.index')"
                    class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium"
                >
                    Ver Cargos
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Lista de Roles (bandera) -->
            <div class="space-y-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="bg-white border border-slate-200 rounded-xl p-6"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                {{ role.name }}
                            </h2>
                            <p class="text-sm text-slate-600 mt-1">
                                {{ role.description || "Sin descripción" }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
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
</script>

