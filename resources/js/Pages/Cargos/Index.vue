<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Cargos y Permisos
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gestiona los cargos y sus permisos de acceso a puertas.
                    </p>
                </div>
                <Link
                    :href="route('cargos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                >
                    Nuevo Cargo
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 transition-colors duration-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Nombre
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Descripción
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Usuarios
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 transition-colors duration-200">
                            <tr
                                v-for="c in cargos.data"
                                :key="c.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ c.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    {{ c.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ c.description || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span
                                        class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-xs transition-colors duration-200"
                                    >
                                        {{ c.users_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            c.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                        ]"
                                    >
                                        {{ c.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <Link
                                        :href="
                                            route('cargos.edit', {
                                                cargo: c.id,
                                            })
                                        "
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                    >
                                        Gestionar Permisos
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="cargos.links && cargos.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ cargos.from }} a {{ cargos.to }} de
                        {{ cargos.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in cargos.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded border text-sm transition-colors duration-200',
                                link.active
                                    ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !link.url && 'opacity-50 cursor-not-allowed',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    cargos: Object,
});
</script>
