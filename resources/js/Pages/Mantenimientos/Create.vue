<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Registrar Mantenimiento
                    </h1>
                    <p class="text-sm text-slate-600">
                        Registra un nuevo mantenimiento para una puerta.
                    </p>
                </div>
                <Link
                    :href="route('mantenimientos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Puerta"
                            :error="form.errors.puerta_id"
                        >
                            <select
                                v-model="form.puerta_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            />
                        </FormField>
                        <FormField
                            label="Tipo de Mantenimiento"
                            :error="form.errors.tipo"
                        >
                            <select
                                v-model="form.tipo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Fecha máxima para completar el mantenimiento (si se pasa, la puerta se marca como vencida).
                            </p>
                        </FormField>
                    </div>

                    <FormField
                        label="Falla"
                        :error="form.errors.falla"
                    >
                        <textarea
                            v-model="form.falla"
                            rows="4"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Describa la falla encontrada..."
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Descripción de la falla o problema detectado
                        </p>
                    </FormField>

                    <FormField
                        label="Documentos PDF (máx. 5)"
                        :error="form.errors.documentos"
                    >
                        <input
                            @input="handleDocuments"
                            type="file"
                            multiple
                            accept=".pdf,application/pdf"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Puede seleccionar hasta 5 documentos PDF. Tamaño máximo: 10MB cada uno
                        </p>
                        <div
                            v-if="documentosPreview.length > 0"
                            class="mt-4 space-y-2"
                        >
                            <div
                                v-for="(doc, index) in documentosPreview"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-200"
                            >
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-medium text-slate-700">{{ doc.name }}</span>
                                    <span class="text-xs text-slate-500">({{ formatFileSize(doc.size) }})</span>
                                </div>
                                <button
                                    type="button"
                                    @click="removeDocument(index)"
                                    class="text-red-600 hover:text-red-700 text-sm font-medium"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50"
                        >
                            {{ form.processing ? "Guardando..." : "Registrar Mantenimiento" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    puertas: Array,
    puertaSeleccionada: Number,
});

const documentosPreview = ref([]);
const documentosFiles = ref([]);

const form = useForm({
    puerta_id: props.puertaSeleccionada || null,
    fecha_mantenimiento: new Date().toISOString().split("T")[0],
    fecha_fin_programada: null,
    tipo: "realizado",
    falla: "",
    documentos: [],
});

// UX: si cambia a programado y no hay fecha límite, usar la misma fecha de mantenimiento como base
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

    // Validar cantidad máxima (5)
    if (documentosFiles.value.length + files.length > 5) {
        alert("Solo se pueden seleccionar hasta 5 documentos PDF en total");
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

const submit = () => {
    form.post(route("mantenimientos.store"), {
        forceFormData: true,
    });
};
</script>
