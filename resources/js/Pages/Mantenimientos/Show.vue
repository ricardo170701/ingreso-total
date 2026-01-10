<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Mantenimiento #{{ mantenimiento.id }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Detalles del mantenimiento registrado.
                    </p>
                </div>
                <div class="flex gap-2">
                    <a
                        :href="route('mantenimientos.pdf', { mantenimiento: mantenimiento.id })"
                        target="_blank"
                        class="px-3 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors duration-200 flex items-center gap-2"
                    >
                        <span>📄</span>
                        <span>Descargar PDF</span>
                    </a>
                    <form
                        v-if="mantenimiento.tipo === 'programado'"
                        @submit.prevent="completarMantenimiento"
                        class="inline"
                    >
                        <button
                            type="submit"
                            class="px-3 py-2 rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 font-medium transition-colors duration-200"
                        >
                            ✅ Marcar como Completado
                        </button>
                    </form>
                    <Link
                        :href="route('mantenimientos.edit', { mantenimiento: mantenimiento.id, from_puerta_id: fromPuertaId })"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Editar
                    </Link>
                    <Link
                        :href="fromPuertaId ? route('puertas.show', { puerta: fromPuertaId }) : route('mantenimientos.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-6 transition-colors duration-200">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Puerta</label>
                        <p class="text-base text-slate-900 dark:text-slate-100 font-medium">
                            {{ mantenimiento.puerta?.nombre }}
                        </p>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            {{ mantenimiento.puerta?.piso?.nombre || "-" }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Fecha de Mantenimiento</label>
                        <p class="text-base text-slate-900 dark:text-slate-100">
                            {{ new Date(mantenimiento.fecha_mantenimiento).toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                    <div v-if="mantenimiento.tipo === 'programado'">
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Fecha límite</label>
                        <p class="text-base text-slate-900 dark:text-slate-100">
                            {{ mantenimiento.fecha_fin_programada ? new Date(mantenimiento.fecha_fin_programada).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Tipo</label>
                        <p class="text-base text-slate-900 dark:text-slate-100">
                            <span
                                :class="[
                                    'px-2 py-1 rounded text-sm font-medium transition-colors duration-200',
                                    mantenimiento.tipo === 'realizado'
                                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                        : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                ]"
                            >
                                {{ mantenimiento.tipo === 'realizado' ? 'Realizado' : 'Programado' }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Datos de auditoría -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Creado por</label>
                        <p class="text-base text-slate-900 dark:text-slate-100">
                            {{ mantenimiento.creado_por?.name || mantenimiento.creado_por?.email || 'N/A' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Fecha de creación: {{ mantenimiento.created_at ? new Date(mantenimiento.created_at).toLocaleString('es-ES') : '-' }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Editado por</label>
                        <p class="text-base text-slate-900 dark:text-slate-100">
                            {{ mantenimiento.editado_por?.name || mantenimiento.editado_por?.email || 'N/A' }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Fecha de modificación: {{ mantenimiento.updated_at ? new Date(mantenimiento.updated_at).toLocaleString('es-ES') : '-' }}
                        </p>
                    </div>
                </div>

                <!-- Falla -->
                <div v-if="mantenimiento.falla">
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2 block">
                        Falla
                    </label>
                    <p class="text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 p-3 rounded-lg whitespace-pre-wrap transition-colors duration-200">
                        {{ mantenimiento.falla }}
                    </p>
                </div>

                <!-- Documentos -->
                <div v-if="mantenimiento.documentos?.length > 0">
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-3 block">
                        Documentos PDF ({{ mantenimiento.documentos.length }})
                    </label>
                    <div class="space-y-2">
                        <div
                            v-for="documento in mantenimiento.documentos"
                            :key="documento.id"
                            class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 transition-colors duration-200"
                        >
                            <div class="flex items-center gap-3">
                                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                        {{ documento.nombre_original || 'Documento PDF' }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        Documento PDF
                                    </p>
                                </div>
                            </div>
                            <a
                                :href="`/storage/${documento.ruta_documento}`"
                                target="_blank"
                                class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                            >
                                Ver PDF
                            </a>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2 block">
                        Documentos PDF
                    </label>
                    <p class="text-sm text-slate-500 dark:text-slate-400 italic">
                        No hay documentos asociados a este mantenimiento.
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    mantenimiento: Object,
    fromPuertaId: Number,
});

const completarMantenimiento = () => {
    if (confirm('¿Estás seguro de marcar este mantenimiento como completado?')) {
        router.post(route('mantenimientos.completar', { mantenimiento: props.mantenimiento.id }), {
            from_puerta_id: props.fromPuertaId || null,
        });
    }
};
</script>
