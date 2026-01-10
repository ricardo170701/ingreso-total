<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Mantenimientos
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Registra y gestiona los mantenimientos de las puertas.
                    </p>
                </div>
                <Link
                    :href="route('mantenimientos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                >
                    Nuevo Mantenimiento
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Filtrar por Puerta
                        </label>
                        <select
                            v-model="filtros.puerta_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        >
                            <option :value="null">Todas las puertas</option>
                            <option
                                v-for="puerta in puertas"
                                :key="puerta.id"
                                :value="puerta.id"
                            >
                                {{ puerta.nombre }} - {{ puerta.piso?.nombre || "-" }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Filtrar por Estado
                        </label>
                        <select
                            v-model="filtros.tipo"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        >
                            <option :value="null">Todos los estados</option>
                            <option value="realizado">Realizado</option>
                            <option value="programado">Programado</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="flex-1 px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                        >
                            Aplicar Filtros
                        </button>
                        <button
                            type="button"
                            @click="limpiarFiltros"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-700 transition-colors duration-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Puerta
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Fecha límite
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Tipo
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Falla
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Documentos
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr
                                v-for="m in mantenimientos.data"
                                :key="m.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ m.id }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900 dark:text-slate-100">
                                        {{ m.puerta?.nombre }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ m.puerta?.piso?.nombre || "-" }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ new Date(m.fecha_mantenimiento).toLocaleDateString('es-ES') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span v-if="m.tipo === 'programado'">
                                        {{
                                            m.fecha_fin_programada
                                                ? new Date(m.fecha_fin_programada).toLocaleDateString('es-ES')
                                                : "-"
                                        }}
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            m.tipo === 'realizado'
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                        ]"
                                    >
                                        {{ m.tipo === 'realizado' ? 'Realizado' : 'Programado' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <div class="max-w-xs truncate" :title="m.falla">
                                        {{ m.falla || '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 rounded text-xs font-medium text-blue-700 dark:text-blue-400 transition-colors duration-200">
                                        {{ m.documentos?.length || 0 }} PDFs
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="
                                                route('mantenimientos.show', {
                                                    mantenimiento: m.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route('mantenimientos.edit', {
                                                    mantenimiento: m.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                        >
                                            Editar
                                        </Link>
                                        <form
                                            v-if="m.tipo === 'programado'"
                                            @submit.prevent="completarMantenimiento(m.id)"
                                            class="inline"
                                        >
                                            <button
                                                type="submit"
                                                class="inline-flex items-center px-3 py-1.5 rounded-md border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 text-sm font-medium transition-colors duration-200"
                                            >
                                                ✅ Completar
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="mantenimientos.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ mantenimientos.from }} a {{ mantenimientos.to }} de
                        {{ mantenimientos.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in mantenimientos.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 rounded-md border text-sm transition-colors duration-200',
                                link.active
                                    ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !link.url ? 'opacity-50 cursor-not-allowed' : '',
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
import { reactive } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    mantenimientos: Object,
    puertas: Array,
    filtros: {
        type: Object,
        default: () => ({
            puerta_id: null,
            tipo: null,
        }),
    },
});

const filtros = reactive({
    puerta_id: props.filtros?.puerta_id || null,
    tipo: props.filtros?.tipo || null,
});

const aplicarFiltros = () => {
    router.get(route('mantenimientos.index'), filtros, {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    filtros.puerta_id = null;
    filtros.tipo = null;
    router.get(route('mantenimientos.index'), {}, {
        preserveState: false,
    });
};

const completarMantenimiento = (mantenimientoId) => {
    if (confirm('¿Estás seguro de marcar este mantenimiento como completado?')) {
        router.post(route('mantenimientos.completar', { mantenimiento: mantenimientoId }));
    }
};
</script>
