<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Editar Mantenimiento de UPS
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ ups.codigo }} - {{ ups.nombre }}
                    </p>
                </div>
                <Link
                    :href="route('ups.show', { ups: ups.id })"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200">
                <!-- Auditoría -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 transition-colors duration-200">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-2">Datos de auditoría</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-xs">Creado por</p>
                            <p class="text-slate-900 dark:text-slate-100">
                                {{ mantenimiento.creado_por?.name || mantenimiento.creado_por?.email || "N/A" }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ mantenimiento.created_at ? new Date(mantenimiento.created_at).toLocaleString("es-ES") : "-" }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400 text-xs">Editado por</p>
                            <p class="text-slate-900 dark:text-slate-100">
                                {{ mantenimiento.editado_por?.name || mantenimiento.editado_por?.email || "N/A" }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ mantenimiento.updated_at ? new Date(mantenimiento.updated_at).toLocaleString("es-ES") : "-" }}
                            </p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="showConfirmModal = true" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <FormField label="Fecha" :error="form.errors.fecha_mantenimiento">
                            <input
                                v-model="form.fecha_mantenimiento"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Tipo" :error="form.errors.tipo">
                            <select
                                v-model="form.tipo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option value="realizado">Realizado</option>
                                <option value="programado">Programado</option>
                            </select>
                        </FormField>
                        <FormField
                            v-if="form.tipo === 'programado'"
                            label="Fecha límite"
                            :error="form.errors.fecha_fin_programada"
                        >
                            <input
                                v-model="form.fecha_fin_programada"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <FormField label="Descripción" :error="form.errors.descripcion">
                        <textarea
                            v-model="form.descripcion"
                            rows="4"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                    </FormField>

                    <!-- Adjuntos actuales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">Fotos actuales</label>
                            <div v-if="(mantenimiento.imagenes?.length || 0) > 0" class="flex flex-wrap gap-3">
                                <label
                                    v-for="img in mantenimiento.imagenes"
                                    :key="img.id"
                                    class="flex flex-col items-start gap-1 p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition-colors duration-200"
                                >
                                    <a :href="storageUrl(img.ruta_imagen)" target="_blank" class="block">
                                        <span class="block w-28 aspect-square rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-700 transition-colors duration-200">
                                            <img
                                                :src="storageUrl(img.ruta_imagen)"
                                                class="w-full h-full object-cover object-center"
                                                alt="Foto mantenimiento"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                        </span>
                                    </a>
                                    <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                        <input type="checkbox" v-model="form.imagenes_eliminar" :value="img.id" class="rounded border-slate-300 dark:border-slate-600 text-red-600 dark:text-red-400 focus:ring-red-500 dark:focus:ring-red-400" />
                                        Eliminar
                                    </label>
                                </label>
                            </div>
                            <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">Sin fotos.</p>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-300">PDFs actuales</label>
                            <div v-if="(mantenimiento.documentos?.length || 0) > 0" class="space-y-2">
                                <label
                                    v-for="doc in mantenimiento.documentos"
                                    :key="doc.id"
                                    class="flex items-center justify-between gap-2 p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 transition-colors duration-200"
                                >
                                    <a
                                        :href="storageUrl(doc.ruta_documento)"
                                        target="_blank"
                                        class="text-sm text-blue-700 dark:text-blue-400 hover:underline inline-flex items-center gap-2 transition-colors duration-200"
                                    >
                                        <span>📄</span>
                                        <span class="break-all">{{ doc.nombre_original || "Documento PDF" }}</span>
                                    </a>
                                    <span class="text-xs text-slate-600 dark:text-slate-400 inline-flex items-center gap-2">
                                        <input type="checkbox" v-model="form.documentos_eliminar" :value="doc.id" class="rounded border-slate-300 dark:border-slate-600 text-red-600 dark:text-red-400 focus:ring-red-500 dark:focus:ring-red-400" />
                                        Eliminar
                                    </span>
                                </label>
                            </div>
                            <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">Sin PDFs.</p>
                        </div>
                    </div>

                    <!-- Nuevos adjuntos -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Agregar nuevas fotos" :error="form.errors.fotos">
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                @change="onFotosChange"
                                class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Existentes: {{ fotosActualesCount }} · A eliminar: {{ fotosEliminarCount }} · Nuevas: {{ fotosSeleccionadasCount }}/{{ fotosDisponibles }} · Restantes: {{ fotosRestantesDespues }}
                            </p>
                        </FormField>
                        <FormField label="Agregar nuevos PDFs" :error="form.errors.documentos">
                            <input
                                type="file"
                                accept="application/pdf"
                                multiple
                                @change="onDocumentosChange"
                                class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Existentes: {{ docsActualesCount }} · A eliminar: {{ docsEliminarCount }} · Nuevos: {{ docsSeleccionadosCount }}/{{ docsDisponibles }} · Restantes: {{ docsRestantesDespues }}
                            </p>
                        </FormField>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a
                            v-if="(mantenimiento.imagenes?.length || 0) + (mantenimiento.documentos?.length || 0) > 0"
                            :href="route('ups.mantenimientos.zip', { ups: ups.id, mantenimiento: mantenimiento.id })"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition-colors duration-200"
                        >
                            Descargar ZIP
                        </a>
                        <button
                            type="button"
                            @click="showConfirmModal = true"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div
            v-if="showConfirmModal"
            @click="showConfirmModal = false"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Confirmar Edición
                    </h3>
                    <button
                        type="button"
                        @click="showConfirmModal = false"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        ¿Estás seguro de que deseas editar el mantenimiento #{{ mantenimiento.id }}?
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Los cambios se guardarán y se aplicarán inmediatamente.
                    </p>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="showConfirmModal = false"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="confirmSubmit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                form.processing
                                    ? "Guardando..."
                                    : "Sí, Guardar Cambios"
                            }}
                        </button>
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
import { submitUploadForm } from "@/Support/inertiaUploads";
import { computed } from "vue";

const props = defineProps({
    ups: Object,
    mantenimiento: Object,
});

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const MAX_FOTOS = 6;
const MAX_PDFS = 5;

const form = useForm({
    fecha_mantenimiento: props.mantenimiento.fecha_mantenimiento,
    tipo: props.mantenimiento.tipo,
    fecha_fin_programada: props.mantenimiento.fecha_fin_programada,
    descripcion: props.mantenimiento.descripcion ?? "",
    fotos: [],
    documentos: [],
    imagenes_eliminar: [],
    documentos_eliminar: [],
});

const fotosActualesCount = computed(() => (props.mantenimiento.imagenes || []).length);
const docsActualesCount = computed(() => (props.mantenimiento.documentos || []).length);
const fotosEliminarCount = computed(() => (form.imagenes_eliminar || []).length);
const docsEliminarCount = computed(() => (form.documentos_eliminar || []).length);

const fotosDisponibles = computed(() =>
    Math.max(0, MAX_FOTOS - Math.max(0, fotosActualesCount.value - fotosEliminarCount.value))
);
const docsDisponibles = computed(() =>
    Math.max(0, MAX_PDFS - Math.max(0, docsActualesCount.value - docsEliminarCount.value))
);

const onFotosChange = (e) => {
    const files = Array.from(e.target?.files || []);
    form.fotos = files.slice(0, fotosDisponibles.value);
};

const onDocumentosChange = (e) => {
    const files = Array.from(e.target?.files || []);
    form.documentos = files.slice(0, docsDisponibles.value);
};

const fotosSeleccionadasCount = computed(() => (form.fotos || []).length);
const docsSeleccionadosCount = computed(() => (form.documentos || []).length);
const fotosRestantes = computed(() => fotosDisponibles.value);
const docsRestantes = computed(() => docsDisponibles.value);
const fotosRestantesDespues = computed(() => Math.max(0, fotosDisponibles.value - fotosSeleccionadasCount.value));
const docsRestantesDespues = computed(() => Math.max(0, docsDisponibles.value - docsSeleccionadosCount.value));

const confirmSubmit = () => {
    showConfirmModal.value = false;
    submitUploadForm(
        form,
        route("ups.mantenimientos.update", {
            ups: props.ups.id,
            mantenimiento: props.mantenimiento.id,
        }),
        "put"
    );
};
</script>


