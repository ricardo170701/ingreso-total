<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Reporte de Accesos
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Consulta todos los accesos registrados con filtros avanzados.
                    </p>
                </div>
                <Link
                    :href="route('reportes.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver a Reportes
                </Link>
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="applyFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <FormField label="Fecha Desde" :error="searchForm.errors.fecha_desde">
                        <input
                            v-model="searchForm.fecha_desde"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        />
                    </FormField>

                    <FormField label="Fecha Hasta" :error="searchForm.errors.fecha_hasta">
                        <input
                            v-model="searchForm.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        />
                    </FormField>

                    <FormField label="Dependencia (Secretaría)" :error="searchForm.errors.secretaria_id">
                        <select
                            v-model="searchForm.secretaria_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                            @change="onSecretariaChange"
                        >
                            <option :value="null">Todas</option>
                            <option
                                v-for="secretaria in secretarias"
                                :key="secretaria.id"
                                :value="secretaria.id"
                            >
                                {{ secretaria.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Gerencia" :error="searchForm.errors.gerencia_id">
                        <select
                            v-model="searchForm.gerencia_id"
                            :disabled="!searchForm.secretaria_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option :value="null">Todas</option>
                            <option value="despacho">Despacho</option>
                            <option
                                v-for="gerencia in gerenciasFiltradas"
                                :key="gerencia.id"
                                :value="gerencia.id"
                            >
                                {{ gerencia.nombre }}
                            </option>
                        </select>
                        <p v-if="!searchForm.secretaria_id" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Selecciona una dependencia primero
                        </p>
                    </FormField>

                    <FormField label="Piso" :error="searchForm.errors.piso_id">
                        <select
                            v-model="searchForm.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="piso in pisos"
                                :key="piso.id"
                                :value="piso.id"
                            >
                                {{ piso.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Tipo de Evento" :error="searchForm.errors.tipo_evento">
                        <select
                            v-model="searchForm.tipo_evento"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="denegado">Denegado</option>
                        </select>
                    </FormField>

                    <FormField label="Estado" :error="searchForm.errors.permitido">
                        <select
                            v-model="searchForm.permitido"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option :value="true">Permitidos</option>
                            <option :value="false">Denegados</option>
                        </select>
                    </FormField>

                    <div class="md:col-span-2 lg:col-span-3 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="clearFilters"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Limpiar Filtros
                        </button>
                        <button
                            type="submit"
                            :disabled="searchForm.processing"
                            class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 font-medium transition-colors duration-200"
                        >
                            {{ searchForm.processing ? "Buscando..." : "Buscar" }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tabla de resultados -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-700 transition-colors duration-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Fecha y Hora
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Usuario
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Piso
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Puerta
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Tipo
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Estado
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">
                                    Motivo
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 transition-colors duration-200">
                            <tr
                                v-for="a in accesos.data"
                                :key="a.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ a.fecha_acceso }}
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="a.user">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ a.user.name }}
                                        </div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ a.user.email }}
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 dark:text-slate-500">N/A</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ a.puerta?.piso?.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ a.puerta?.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-xs font-semibold',
                                            a.tipo_evento === 'entrada'
                                                ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400'
                                                : a.tipo_evento === 'salida'
                                                ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400'
                                                : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                        ]"
                                    >
                                        {{ a.tipo_evento === 'entrada' ? 'Entrada' : a.tipo_evento === 'salida' ? 'Salida' : a.tipo_evento || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded-full text-xs font-semibold',
                                            a.permitido
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400',
                                        ]"
                                    >
                                        {{ a.permitido ? "Permitido" : "Denegado" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400 text-xs">
                                    {{ a.motivo_denegacion || "-" }}
                                </td>
                            </tr>
                            <tr v-if="accesos.data.length === 0">
                                <td
                                    class="px-4 py-10 text-center text-slate-500 dark:text-slate-400"
                                    colspan="7"
                                >
                                    No se encontraron accesos con los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="accesos.links?.length"
                    class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ accesos.from || 0 }} - {{ accesos.to || 0 }} de
                        {{ accesos.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(link, idx) in accesos.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md text-sm border transition-colors duration-200',
                                link.active
                                    ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !link.url ? 'opacity-40 pointer-events-none' : '',
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
import FormField from "@/Components/FormField.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    accesos: Object,
    secretarias: Array,
    gerencias: Array,
    pisos: Array,
    filters: Object,
});

const searchForm = useForm({
    fecha_desde: props.filters?.fecha_desde || null,
    fecha_hasta: props.filters?.fecha_hasta || null,
    secretaria_id: props.filters?.secretaria_id || null,
    gerencia_id: props.filters?.gerencia_id || null,
    piso_id: props.filters?.piso_id || null,
    tipo_evento: props.filters?.tipo_evento || null,
    permitido: props.filters?.permitido !== undefined ? props.filters.permitido : null,
});

// Filtrar gerencias por secretaría seleccionada
const gerenciasFiltradas = computed(() => {
    if (!searchForm.secretaria_id) return [];
    return props.gerencias?.filter(g => g.secretaria_id === searchForm.secretaria_id) || [];
});

// Limpiar gerencia cuando cambia la secretaría
const onSecretariaChange = () => {
    searchForm.gerencia_id = null;
};

const applyFilters = () => {
    searchForm.get(route("reportes.accesos"), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchForm.fecha_desde = null;
    searchForm.fecha_hasta = null;
    searchForm.secretaria_id = null;
    searchForm.gerencia_id = null;
    searchForm.piso_id = null;
    searchForm.tipo_evento = null;
    searchForm.permitido = null;
    applyFilters();
};
</script>
