<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Gerencias
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gerencias de la secretaría <strong>{{ secretaria.nombre }}</strong>
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('gerencias.create', { secretaria: secretaria.id })"
                        class="px-4 py-2 rounded-lg bg-purple-600 dark:bg-purple-700 text-white hover:bg-purple-700 dark:hover:bg-purple-600 font-medium transition-colors duration-200"
                    >
                        Nueva Gerencia
                    </Link>
                    <Link
                        :href="route('secretarias.show', { secretaria: secretaria.id })"
                        class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <div
                v-if="$page.props.errors?.error"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 transition-colors duration-200"
            >
                {{ $page.props.errors.error }}
            </div>

            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Nombre
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Usuarios
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Descripción
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr
                                v-for="g in gerencias.data"
                                :key="g.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ g.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    {{ g.nombre }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span
                                        class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-xs font-medium transition-colors duration-200"
                                    >
                                        {{ g.users_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span
                                        v-if="g.descripcion"
                                        class="truncate block max-w-xs"
                                        :title="g.descripcion"
                                    >
                                        {{ g.descripcion }}
                                    </span>
                                    <span v-else class="text-slate-400 dark:text-slate-500">-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            g.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                        ]"
                                    >
                                        {{ g.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="
                                                route('gerencias.show', {
                                                    secretaria: secretaria.id,
                                                    gerencia: g.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route('gerencias.edit', {
                                                    secretaria: secretaria.id,
                                                    gerencia: g.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="eliminarGerencia(g)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 text-sm transition-colors duration-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="gerencias.links && gerencias.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ gerencias.from }} a {{ gerencias.to }} de
                        {{ gerencias.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in gerencias.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded border text-sm transition-colors duration-200',
                                link.active
                                    ? 'bg-purple-600 dark:bg-purple-700 text-white border-purple-600 dark:border-purple-700'
                                    : 'bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-600',
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
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    secretaria: Object,
    gerencias: Object,
});

const eliminarGerencia = (gerencia) => {
    if (
        !confirm(
            `¿Estás seguro de eliminar la gerencia "${gerencia.nombre}"?`
        )
    )
        return;
    router.delete(
        route("gerencias.destroy", {
            secretaria: props.secretaria.id,
            gerencia: gerencia.id,
        })
    );
};
</script>