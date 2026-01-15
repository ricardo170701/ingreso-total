<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Editar Mantenimiento #{{ mantenimiento.id }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Actualiza los datos del mantenimiento.
                    </p>
                </div>
                <Link
                    :href="fromPuertaId ? route('puertas.show', { puerta: fromPuertaId }) : route('mantenimientos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <form @submit.prevent="showConfirmModal = true" class="grid grid-cols-1 gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Puerta"
                            :error="form.errors.puerta_id"
                        >
                            <select
                                v-model="form.puerta_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            >
                                <option :value="null">Seleccione una puerta</option>
                                <option
                                    v-for="p in puertas"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.nombre }} - {{ p.piso?.nombre || "-" }}
                                </option>
                            </select>
                        </FormField>
                        <FormField
                            label="Fecha de Mantenimiento"
                            :error="form.errors.fecha_mantenimiento"
                        >
                            <input
                                v-model="form.fecha_mantenimiento"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
                        <FormField
                            label="Tipo de Mantenimiento"
                            :error="form.errors.tipo"
                        >
                            <select
                                v-model="form.tipo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            >
                                <option value="realizado">Realizado</option>
                                <option value="programado">Programado</option>
                            </select>
                        </FormField>
                        <FormField
                            v-if="form.tipo === 'programado'"
                            label="Fecha límite (Programado)"
                            :error="form.errors.fecha_fin_programada"
                        >
                            <input
                                v-model="form.fecha_fin_programada"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Fecha máxima para completar el mantenimiento.
                            </p>
                        </FormField>
                    </div>

                    <FormField
                        label="Descripción de mantenimiento"
                        :error="form.errors.descripcion_mantenimiento"
                    >
                        <textarea
                            v-model="form.descripcion_mantenimiento"
                            rows="4"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Describa el mantenimiento realizado..."
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Descripción del mantenimiento realizado
                        </p>
                    </FormField>

                    <!-- Documentos existentes -->
                    <div v-if="mantenimiento.documentos?.length > 0">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                            Documentos Actuales
                        </label>
                        <div class="space-y-2">
                            <div
                                v-for="documento in mantenimiento.documentos"
                                :key="documento.id"
                                class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                            >
                                <div class="flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        :value="documento.id"
                                        v-model="form.documentos_eliminar"
                                        class="rounded border-slate-300 dark:border-slate-600 text-red-600 dark:text-red-400 focus:ring-red-500 dark:focus:ring-red-400"
                                    />
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    <a
                                        :href="`/storage/${documento.ruta_documento}`"
                                        target="_blank"
                                        class="text-sm font-medium text-slate-700 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200"
                                    >
                                        {{ documento.nombre_original || 'Documento PDF' }}
                                    </a>
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400">PDF</span>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Marque los documentos que desea eliminar
                        </p>
                    </div>

                    <!-- Agregar nuevos documentos -->
                    <FormField
                        label="Agregar Nuevos Documentos PDF"
                        :error="form.errors.documentos"
                    >
                        <input
                            @input="handleDocuments"
                            type="file"
                            multiple
                            accept=".pdf,application/pdf"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Puede agregar hasta {{ 5 - (mantenimiento.documentos?.length || 0) }} documentos más. Tamaño máximo: 10MB cada uno
                        </p>
                        <div
                            v-if="documentosPreview.length > 0"
                            class="mt-4 space-y-2"
                        >
                            <div
                                v-for="(doc, index) in documentosPreview"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ doc.name }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">({{ formatFileSize(doc.size) }})</span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeDocument(index)"
                                    class="text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 text-sm font-medium transition-colors duration-200"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="destroy"
                            class="px-4 py-2 rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors duration-200"
                        >
                            Eliminar
                        </button>
                        <button
                            type="button"
                            @click="showConfirmModal = true"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar Cambios" }}
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
import { ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { submitUploadForm } from "@/Support/inertiaUploads";

const props = defineProps({
    mantenimiento: Object,
    puertas: Array,
    fromPuertaId: Number,
});

const documentosPreview = ref([]);
const documentosFiles = ref([]);

const form = useForm({
    puerta_id: props.mantenimiento.puerta_id,
    fecha_mantenimiento: props.mantenimiento.fecha_mantenimiento
        ? new Date(props.mantenimiento.fecha_mantenimiento).toISOString().split("T")[0]
        : new Date().toISOString().split("T")[0],
    fecha_fin_programada: props.mantenimiento.fecha_fin_programada
        ? new Date(props.mantenimiento.fecha_fin_programada).toISOString().split("T")[0]
        : null,
    tipo: props.mantenimiento.tipo || "realizado",
    descripcion_mantenimiento: props.mantenimiento.descripcion_mantenimiento || "",
    documentos: [],
    documentos_eliminar: [],
    from_puerta_id: props.fromPuertaId || null, // Para redirección después de actualizar
});

watch(
    () => form.tipo,
    (tipo) => {
        if (tipo === "programado") {
            if (!form.fecha_fin_programada) {
                form.fecha_fin_programada = form.fecha_mantenimiento;
            }
        } else {
            form.fecha_fin_programada = null;
        }
    },
    { immediate: true }
);

const handleDocuments = (event) => {
    const files = Array.from(event.target.files);
    const documentosActuales = props.mantenimiento.documentos?.length || 0;
    const maxNuevos = 5 - documentosActuales;

    if (documentosFiles.value.length + files.length > maxNuevos) {
        alert(`Solo se pueden agregar hasta ${maxNuevos} documentos más`);
        return;
    }

    // Validar que sean PDFs
    const invalidFiles = files.filter(file => file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf'));
    if (invalidFiles.length > 0) {
        alert("Todos los archivos deben ser PDFs");
        return;
    }

    // Agregar a la lista
    files.forEach((file) => {
        documentosFiles.value.push(file);
        documentosPreview.value.push({
            name: file.name,
            size: file.size,
        });
    });

    form.documentos = documentosFiles.value;
};

const removeDocument = (index) => {
    documentosPreview.value.splice(index, 1);
    documentosFiles.value.splice(index, 1);
    form.documentos = documentosFiles.value;
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const confirmSubmit = () => {
    showConfirmModal.value = false;
    submitUploadForm(
        form,
        route("mantenimientos.update", { mantenimiento: props.mantenimiento.id }),
        "put"
    );
};

const destroy = () => {
    if (!confirm("¿Eliminar este mantenimiento?")) return;
    router.delete(route("mantenimientos.destroy", { mantenimiento: props.mantenimiento.id }));
};
</script>
