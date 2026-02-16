<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Nueva Bitácora - {{ ups.codigo }} - {{ ups.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Sube una imagen del panel frontal del UPS para analizar
                        su estado automáticamente.
                    </p>
                </div>
                <Link
                    :href="route('ups.vitacora.index', { ups: ups.id })"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Formulario de carga -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200"
            >
                <div>
                    <label
                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                    >
                        Imágenes del Panel Frontal (hasta 5)
                    </label>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/jpg,image/png"
                        multiple
                        @change="handleFileSelect"
                        class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                    />
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Formatos: JPEG, JPG, PNG. Tamaño máximo: 10MB por imagen. Puedes seleccionar hasta 5 imágenes.
                    </p>
                    <div v-if="cameraAvailable" class="mt-3 flex flex-wrap gap-2">
                        <button
                            v-if="!cameraOpen"
                            type="button"
                            @click="openCamera"
                            :disabled="cameraStarting || selectedFiles.length >= 5"
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 inline-flex items-center gap-2"
                        >
                            <span v-if="cameraStarting">Cargando cámara...</span>
                            <span v-else>Tomar foto con cámara</span>
                        </button>
                        <template v-else>
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="relative rounded-lg overflow-hidden bg-slate-900 max-w-md">
                                    <video
                                        ref="videoRef"
                                        autoplay
                                        playsinline
                                        muted
                                        class="w-full h-auto max-h-64 object-cover"
                                    />
                                    <p
                                        v-if="cameraError"
                                        class="absolute inset-0 flex items-center justify-center bg-black/70 text-red-400 text-sm p-2"
                                    >
                                        {{ cameraError }}
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="takePhoto"
                                        :disabled="selectedFiles.length >= 5"
                                        class="px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 font-medium"
                                    >
                                        Capturar
                                    </button>
                                    <button
                                        type="button"
                                        @click="closeCamera"
                                        class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                    >
                                        Cerrar cámara
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <canvas ref="canvasRef" class="hidden"></canvas>
                </div>

                <!-- Vista previa de imágenes seleccionadas -->
                <div v-if="selectedFiles.length > 0" class="mt-4">
                    <p class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                        Imágenes seleccionadas ({{ selectedFiles.length }}/5):
                    </p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <div
                            v-for="(file, index) in selectedFiles"
                            :key="index"
                            class="relative group"
                        >
                            <div class="aspect-square bg-slate-100 dark:bg-slate-700 rounded-lg border-2 border-slate-200 dark:border-slate-600 overflow-hidden transition-colors duration-200">
                                <img
                                    :src="previewImages[index]"
                                    :alt="file.name"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                            <button
                                @click="removeImage(index)"
                                class="absolute -top-2 -right-2 bg-red-600 dark:bg-red-700 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-700 dark:hover:bg-red-600 transition-colors shadow-lg"
                                title="Eliminar imagen"
                            >
                                ×
                            </button>
                            <p class="mt-1 text-xs text-slate-600 dark:text-slate-400 truncate" :title="file.name">
                                {{ file.name }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-500">
                                {{ formatFileSize(file.size) }}
                            </p>
                        </div>
                    </div>
                </div>

                <button
                    v-if="selectedFiles.length > 0 && !analyzing && !previewData"
                    @click="analyzeImage"
                    :disabled="analyzing"
                    class="w-full px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 font-medium"
                >
                    {{ analyzing ? `Analizando ${analyzingProgress} de ${selectedFiles.length}...` : `Analizar ${selectedFiles.length} Imagen${selectedFiles.length > 1 ? 'es' : ''}` }}
                </button>

                <div
                    v-if="analyzing"
                    class="flex items-center justify-center gap-2 text-blue-600 dark:text-blue-400"
                >
                    <span class="animate-spin">⏳</span>
                    <span>Analizando imagen con IA...</span>
                </div>

                <div
                    v-if="error"
                    class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 transition-colors duration-200"
                >
                    <p class="mb-2">{{ error }}</p>
                    <p v-if="previewData" class="text-sm text-red-700 dark:text-red-300 mt-2">
                        💡 Puedes ingresar los datos manualmente en el formulario de abajo.
                    </p>
                </div>
            </div>

            <!-- Vista previa de datos extraídos -->
            <div
                v-if="previewData"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                    Vista Previa - Datos Extraídos
                </h2>

                <form @submit.prevent="guardarRegistro" class="space-y-4">
                    <!-- Indicadores -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div
                            class="p-4 rounded-lg border transition-colors duration-200"
                            :class="
                                previewData.indicador_normal
                                    ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800'
                                    : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-700'
                            "
                        >
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="previewData.indicador_normal"
                                    class="rounded border-slate-300 dark:border-slate-600 text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400"
                                />
                                <span class="font-medium text-slate-900 dark:text-slate-100">NORMAL</span>
                            </label>
                        </div>
                        <div
                            class="p-4 rounded-lg border transition-colors duration-200"
                            :class="
                                previewData.indicador_battery
                                    ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800'
                                    : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-700'
                            "
                        >
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="previewData.indicador_battery"
                                    class="rounded border-slate-300 dark:border-slate-600 text-yellow-600 dark:text-yellow-400 focus:ring-yellow-500 dark:focus:ring-yellow-400"
                                />
                                <span class="font-medium text-slate-900 dark:text-slate-100">BATTERY</span>
                            </label>
                        </div>
                        <div
                            class="p-4 rounded-lg border transition-colors duration-200"
                            :class="
                                previewData.indicador_bypass
                                    ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800'
                                    : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-700'
                            "
                        >
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="previewData.indicador_bypass"
                                    class="rounded border-slate-300 dark:border-slate-600 text-yellow-600 dark:text-yellow-400 focus:ring-yellow-500 dark:focus:ring-yellow-400"
                                />
                                <span class="font-medium text-slate-900 dark:text-slate-100">BYPASS</span>
                            </label>
                        </div>
                        <div
                            class="p-4 rounded-lg border transition-colors duration-200"
                            :class="
                                previewData.indicador_fault
                                    ? 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800'
                                    : 'bg-slate-50 dark:bg-slate-700/50 border-slate-200 dark:border-slate-700'
                            "
                        >
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    v-model="previewData.indicador_fault"
                                    class="rounded border-slate-300 dark:border-slate-600 text-red-600 dark:text-red-400 focus:ring-red-500 dark:focus:ring-red-400"
                                />
                                <span class="font-medium text-slate-900 dark:text-slate-100">FAULT</span>
                            </label>
                        </div>
                    </div>

                    <!-- Input -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Input - Voltaje (V)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.input_voltage"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Input - Frecuencia (Hz)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.input_frequency"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                    </div>

                    <!-- Output -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Output - Voltaje (V)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.output_voltage"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Output - Frecuencia (Hz)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.output_frequency"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Output - Potencia (W)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.output_power"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                    </div>

                    <!-- Battery -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Battery - Voltaje (V)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.battery_voltage"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Battery - Porcentaje (%)</label
                            >
                            <input
                                type="number"
                                step="1"
                                min="0"
                                max="100"
                                v-model="previewData.battery_percentage"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Battery - Tiempo Respaldo (Min)</label
                            >
                            <input
                                type="number"
                                step="1"
                                min="0"
                                v-model="previewData.battery_tiempo_respaldo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Battery - Tiempo Descarga (Min)</label
                            >
                            <input
                                type="number"
                                step="1"
                                min="0"
                                v-model="previewData.battery_tiempo_descarga"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Battery - Estado</label
                            >
                            <select
                                v-model="previewData.battery_estado"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            >
                                <option value="">Seleccionar...</option>
                                <option value="charging">Charging</option>
                                <option value="discharging">Discharging</option>
                                <option value="standby">Standby</option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                                >Temperatura (°C)</label
                            >
                            <input
                                type="number"
                                step="0.01"
                                v-model="previewData.temperatura"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            />
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1"
                            >Observaciones (opcional)</label
                        >
                        <textarea
                            v-model="previewData.observaciones"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 transition-colors duration-200"
                            placeholder="Agregar observaciones adicionales..."
                        ></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="flex gap-2 pt-4">
                        <button
                            type="submit"
                            :disabled="saving"
                            class="flex-1 px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 font-medium"
                        >
                            {{ saving ? "Guardando..." : "Guardar Registro" }}
                        </button>
                        <button
                            type="button"
                            @click="resetForm"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, nextTick, onMounted, onUnmounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    ups: Object,
});

const fileInput = ref(null);
const selectedFiles = ref([]);
const previewImages = ref([]);

// Cámara
const cameraAvailable = ref(false);
const cameraOpen = ref(false);
const cameraError = ref("");
const cameraStarting = ref(false);
const cameraStream = ref(null);
const videoRef = ref(null);
const canvasRef = ref(null);
const previewData = ref(null);
const analyzing = ref(false);
const analyzingProgress = ref(0);
const saving = ref(false);
const error = ref(null);

const handleFileSelect = (event) => {
    const files = Array.from(event.target.files || []);
    if (files.length === 0) return;

    // Limitar a 5 imágenes
    const filesToAdd = files.slice(0, 5 - selectedFiles.value.length);
    if (filesToAdd.length === 0) {
        error.value = 'Ya has seleccionado el máximo de 5 imágenes';
        return;
    }

    selectedFiles.value = [...selectedFiles.value, ...filesToAdd];
    error.value = null;
    previewData.value = null;

    // Crear preview local para cada imagen
    filesToAdd.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImages.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    selectedFiles.value.splice(index, 1);
    previewImages.value.splice(index, 1);
    // No limpiar el input para permitir agregar más imágenes
};

// --- Cámara ---
const stopCameraStream = () => {
    const stream = cameraStream.value;
    if (stream && typeof stream.getTracks === "function") {
        for (const t of stream.getTracks()) {
            try { t.stop(); } catch { /* ignore */ }
        }
    }
    cameraStream.value = null;
    if (videoRef.value) {
        try { videoRef.value.srcObject = null; } catch { /* ignore */ }
    }
};

const startCamera = async () => {
    cameraError.value = "";
    cameraStarting.value = true;

    try {
        if (typeof window === "undefined") {
            cameraError.value = "La cámara no está disponible en este entorno.";
            return;
        }
        if (!window.isSecureContext) {
            cameraError.value = "La cámara requiere HTTPS (o localhost).";
            return;
        }
        const md = navigator?.mediaDevices;
        if (!md?.getUserMedia) {
            cameraError.value = "Este navegador no soporta acceso a cámara.";
            return;
        }

        stopCameraStream();

        let stream = null;
        try {
            stream = await md.getUserMedia({
                video: { facingMode: { ideal: "environment" } },
                audio: false,
            });
        } catch {
            stream = await md.getUserMedia({ video: true, audio: false });
        }

        cameraStream.value = stream;
        if (videoRef.value) {
            videoRef.value.srcObject = stream;
            try { await videoRef.value.play(); } catch { /* ignore */ }
        }
    } catch (e) {
        cameraError.value =
            "No se pudo acceder a la cámara. Revisa permisos del navegador y vuelve a intentar.";
    } finally {
        cameraStarting.value = false;
    }
};

const openCamera = async () => {
    cameraOpen.value = true;
    cameraError.value = "";
    await nextTick();
    await startCamera();
};

const closeCamera = () => {
    stopCameraStream();
    cameraOpen.value = false;
    cameraError.value = "";
};

const takePhoto = async () => {
    cameraError.value = "";
    const video = videoRef.value;
    const canvas = canvasRef.value;
    if (!video || !canvas) {
        cameraError.value = "La cámara aún no está lista.";
        return;
    }

    if (selectedFiles.value.length >= 5) {
        cameraError.value = "Ya has alcanzado el máximo de 5 imágenes.";
        return;
    }

    const w = video.videoWidth || 1280;
    const h = video.videoHeight || 720;
    canvas.width = w;
    canvas.height = h;
    const ctx = canvas.getContext("2d");
    if (!ctx) {
        cameraError.value = "No se pudo inicializar el canvas.";
        return;
    }
    ctx.drawImage(video, 0, 0, w, h);

    const blob = await new Promise((resolve) => {
        canvas.toBlob(
            (b) => resolve(b),
            "image/jpeg",
            0.9
        );
    });

    if (!blob) {
        cameraError.value = "No se pudo capturar la foto.";
        return;
    }

    const file = new File([blob], `bitacora_ups_${Date.now()}.jpg`, { type: blob.type });

    selectedFiles.value.push(file);
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImages.value.push(e.target.result);
    };
    reader.readAsDataURL(file);

    error.value = null;
    previewData.value = null;

    closeCamera();
};

onMounted(() => {
    cameraAvailable.value = Boolean(
        typeof window !== "undefined" &&
        window.isSecureContext &&
        navigator?.mediaDevices?.getUserMedia
    );
});

onUnmounted(() => {
    stopCameraStream();
});

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};

const analyzeImage = async () => {
    if (selectedFiles.value.length === 0) return;

    analyzing.value = true;
    analyzingProgress.value = 0;
    error.value = null;

    try {
        const formData = new FormData();
        selectedFiles.value.forEach((file) => {
            formData.append("imagenes[]", file);
        });

        const response = await axios.post(
            route("ups.vitacora.analyze", { ups: props.ups.id }),
            formData,
            {
                headers: {
                    "Content-Type": "multipart/form-data",
                },
            }
        );

        if (response.data.success) {
            previewData.value = response.data.preview;
            // Actualizar preview images con las rutas del servidor si vienen del análisis
            if (response.data.preview.imagenes && Array.isArray(response.data.preview.imagenes)) {
                // Mantener las previews locales si las rutas del servidor están disponibles
                const serverImages = response.data.preview.imagenes.map(img => `/storage/${img}`);
                if (serverImages.length === previewImages.value.length) {
                    previewImages.value = serverImages;
                }
            }
        } else {
            error.value =
                response.data.message || "Error al analizar las imágenes";
            // Si el error permite entrada manual, inicializar datos vacíos
            if (response.data.allow_manual && selectedFiles.value.length > 0) {
                previewData.value = {
                    imagenes: previewImages.value,
                    indicador_normal: false,
                    indicador_battery: false,
                    indicador_bypass: false,
                    indicador_fault: false,
                    input_voltage: null,
                    input_frequency: null,
                    output_voltage: null,
                    output_frequency: null,
                    output_power: null,
                    battery_voltage: null,
                    battery_percentage: null,
                    battery_tiempo_respaldo: null,
                    battery_tiempo_descarga: null,
                    battery_estado: null,
                    temperatura: null,
                    observaciones: '',
                };
            }
        }
    } catch (err) {
        error.value =
            err.response?.data?.message ||
            err.message ||
            "Error al analizar las imágenes";
        // Si el error permite entrada manual, inicializar datos vacíos
        if (err.response?.data?.allow_manual && selectedFiles.value.length > 0) {
            previewData.value = {
                imagenes: previewImages.value,
                indicador_normal: false,
                indicador_battery: false,
                indicador_bypass: false,
                indicador_fault: false,
                input_voltage: null,
                input_frequency: null,
                output_voltage: null,
                output_frequency: null,
                output_power: null,
                battery_voltage: null,
                battery_percentage: null,
                battery_tiempo_respaldo: null,
                battery_tiempo_descarga: null,
                battery_estado: null,
                temperatura: null,
                observaciones: '',
            };
        }
    } finally {
        analyzing.value = false;
        analyzingProgress.value = 0;
    }
};

const guardarRegistro = async () => {
    if (!previewData.value) return;

    saving.value = true;
    error.value = null;

    try {
        const formData = new FormData();

        // Manejar múltiples imágenes
        if (previewData.value.imagenes && Array.isArray(previewData.value.imagenes)) {
            // Si son rutas del servidor (análisis exitoso)
            previewData.value.imagenes.forEach((img) => {
                if (typeof img === 'string' && !img.startsWith('blob:')) {
                    formData.append('imagenes[]', img);
                }
            });
        }

        // Si hay archivos seleccionados y no se analizaron (entrada manual)
        if (selectedFiles.value.length > 0) {
            selectedFiles.value.forEach((file) => {
                formData.append('imagenes_files[]', file);
            });
        }

        // Agregar todos los demás datos
        formData.append('indicador_normal', previewData.value.indicador_normal ? '1' : '0');
        formData.append('indicador_battery', previewData.value.indicador_battery ? '1' : '0');
        formData.append('indicador_bypass', previewData.value.indicador_bypass ? '1' : '0');
        formData.append('indicador_fault', previewData.value.indicador_fault ? '1' : '0');
        const hasVal = (v) => v != null && v !== '';
        if (hasVal(previewData.value.input_voltage)) formData.append('input_voltage', previewData.value.input_voltage);
        if (hasVal(previewData.value.input_frequency)) formData.append('input_frequency', previewData.value.input_frequency);
        if (hasVal(previewData.value.output_voltage)) formData.append('output_voltage', previewData.value.output_voltage);
        if (hasVal(previewData.value.output_frequency)) formData.append('output_frequency', previewData.value.output_frequency);
        if (hasVal(previewData.value.output_power)) formData.append('output_power', previewData.value.output_power);
        if (hasVal(previewData.value.battery_voltage)) formData.append('battery_voltage', previewData.value.battery_voltage);
        if (hasVal(previewData.value.battery_percentage)) formData.append('battery_percentage', previewData.value.battery_percentage);
        if (hasVal(previewData.value.battery_tiempo_respaldo)) formData.append('battery_tiempo_respaldo', previewData.value.battery_tiempo_respaldo);
        if (hasVal(previewData.value.battery_tiempo_descarga)) formData.append('battery_tiempo_descarga', previewData.value.battery_tiempo_descarga);
        if (previewData.value.battery_estado) formData.append('battery_estado', previewData.value.battery_estado);
        if (hasVal(previewData.value.temperatura)) formData.append('temperatura', previewData.value.temperatura);
        if (previewData.value.observaciones) formData.append('observaciones', previewData.value.observaciones);
        if (previewData.value.datos_extraidos) formData.append('datos_extraidos', JSON.stringify(previewData.value.datos_extraidos));

        await axios.post(
            route("ups.vitacora.store", { ups: props.ups.id }),
            formData,
            {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            }
        );

        router.visit(route("ups.vitacora.index", { ups: props.ups.id }), {
            method: "get",
        });
    } catch (err) {
        error.value =
            err.response?.data?.message ||
            err.message ||
            "Error al guardar el registro";
        saving.value = false;
    }
};

const resetForm = () => {
    selectedFiles.value = [];
    previewImages.value = [];
    previewData.value = null;
    error.value = null;
    if (fileInput.value) {
        fileInput.value.value = "";
    }
};
</script>
