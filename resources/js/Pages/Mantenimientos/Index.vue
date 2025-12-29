<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Mantenimientos
                    </h1>
                    <p class="text-sm text-slate-600">
                        Registra y gestiona los mantenimientos de las puertas.
                    </p>
                </div>
                <Link
                    :href="route('mantenimientos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nuevo Mantenimiento
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Filtros -->
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Filtrar por Puerta
                        </label>
                        <select
                            v-model="filtros.puerta_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Filtrar por Estado
                        </label>
                        <select
                            v-model="filtros.tipo"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option :value="null">Todos los estados</option>
                            <option value="realizado">Realizado</option>
                            <option value="programado">Programado</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="flex-1 px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                        >
                            Aplicar Filtros
                        </button>
                        <button
                            type="button"
                            @click="limpiarFiltros"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white border border-slate-200 rounded-xl overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Puerta
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Fecha límite
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Tipo
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Falla
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Documentos
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="m in mantenimientos.data"
                                :key="m.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-3 text-slate-600">
                                    {{ m.id }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">
                                        {{ m.puerta?.nombre }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ m.puerta?.piso?.nombre || "-" }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ new Date(m.fecha_mantenimiento).toLocaleDateString('es-ES') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
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
                                            'px-2 py-1 rounded text-xs font-medium',
                                            m.tipo === 'realizado'
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-yellow-100 text-yellow-700',
                                        ]"
                                    >
                                        {{ m.tipo === 'realizado' ? 'Realizado' : 'Programado' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <div class="max-w-xs truncate" :title="m.falla">
                                        {{ m.falla || '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span class="px-2 py-1 bg-blue-50 rounded text-xs font-medium text-blue-700">
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
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route('mantenimientos.edit', {
                                                    mantenimiento: m.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm"
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
                                                class="inline-flex items-center px-3 py-1.5 rounded-md border border-green-200 bg-green-50 hover:bg-green-100 text-green-700 text-sm font-medium"
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
                    class="px-4 py-3 border-t border-slate-200 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600">
                        Mostrando {{ mantenimientos.from }} a {{ mantenimientos.to }} de
                        {{ mantenimientos.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in mantenimientos.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 rounded-md border text-sm',
                                link.active
                                    ? 'bg-slate-900 text-white border-slate-900'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
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
