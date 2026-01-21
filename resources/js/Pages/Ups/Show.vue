<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ ups.codigo }} - {{ ups.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ ups.piso?.nombre || "Sin piso" }} · {{ ups.estado || (ups.activo ? "Activo" : "Inactivo") }}
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <Link
                        :href="route('ups.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                    <Link
                        v-if="hasPermission('edit_ups')"
                        :href="route('ups.edit', { ups: ups.id })"
                        class="px-3 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                    >
                        Editar
                    </Link>
                </div>
            </div>

            <!-- Mensaje de éxito (notificación temporal) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-x-full"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-full"
            >
                <div
                    v-if="showSuccessMessage"
                    class="fixed top-4 right-4 z-50 max-w-md"
                >
                    <div
                        class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow-lg flex items-center gap-3"
                    >
                        <div class="shrink-0">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                UPS actualizado exitosamente
                            </p>
                            <p class="text-xs text-green-700 dark:text-green-300 mt-1">
                                Los cambios se han aplicado al UPS "{{ ups.nombre }}"
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="showSuccessMessage = false"
                            class="shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors"
                            aria-label="Cerrar"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </Transition>

            <!-- Detalles -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200">
                <div v-if="ups.foto" class="flex justify-center">
                    <div class="w-full max-w-2xl aspect-video bg-slate-100 dark:bg-slate-700 rounded-2xl border border-slate-200 dark:border-slate-600 overflow-hidden transition-colors duration-200">
                        <img
                            :src="storageUrl(ups.foto)"
                            alt="Foto UPS"
                            loading="lazy"
                            decoding="async"
                            class="w-full h-full object-cover object-center"
                        />
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Comp (Compañía/Compartimiento)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.comp || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Fecha de adquisición</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.fecha_adquisicion ? formatDate(ups.fecha_adquisicion) : "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Elemt (Elemento)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.elemt || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">R.I. (Registro Interno)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.ri || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Estado</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.estado || (ups.activo ? "Activo" : "Inactivo") }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Ubicación</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.ubicacion || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Serial</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.serial || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Marca</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.marca || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Modelo</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.modelo || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Potencia (VA)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.potencia_va ? `${ups.potencia_va} VA` : "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Potencia (KVA)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.potencia_kva ? `${ups.potencia_kva} KVA` : "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Potencia (W)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.potencia_w ? `${ups.potencia_w} W` : "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Potencia (KW)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.potencia_kw ? `${ups.potencia_kw} KW` : "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Cantidad de baterías</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.cantidad_baterias || "-" }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Voltaje de baterías (V)</label>
                        <p class="text-sm text-slate-900 dark:text-slate-100">{{ ups.voltaje_baterias ? `${ups.voltaje_baterias} V` : "-" }}</p>
                    </div>
                </div>

                <div v-if="ups.ficha_tecnica" class="pt-4 border-t border-slate-200 dark:border-slate-700">
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Ficha técnica</label>
                    <p class="mt-1">
                        <a
                            :href="storageUrl(ups.ficha_tecnica)"
                            target="_blank"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-2 transition-colors duration-200"
                        >
                            <span>📄</span>
                            <span>Ver ficha técnica (PDF)</span>
                        </a>
                    </p>
                </div>

                <div v-if="ups.observaciones" class="pt-4 border-t border-slate-200 dark:border-slate-700">
                    <label class="text-sm font-medium text-slate-500 dark:text-slate-400">Observaciones</label>
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap mt-1">
                        {{ ups.observaciones }}
                    </p>
                </div>
            </div>

            <!-- Bitácora -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Bitácora</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Registro de estados del UPS mediante análisis de imágenes del panel frontal.
                        </p>
                    </div>
                    <Link
                        :href="route('ups.vitacora.index', { ups: ups.id })"
                        class="px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 font-medium text-sm transition-colors duration-200"
                    >
                        Ver Bitácora
                    </Link>
                </div>
            </div>

            <!-- Mantenimientos -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Mantenimientos</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Historial de mantenimientos de esta UPS.
                        </p>
                    </div>
                </div>

                <form
                    v-if="hasPermission('create_ups_mantenimientos')"
                    @submit.prevent="crearMantenimiento"
                    class="p-4 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 space-y-3 transition-colors duration-200"
                >
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <FormField label="Fecha" :error="mForm.errors.fecha_mantenimiento">
                            <input
                                v-model="mForm.fecha_mantenimiento"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Tipo" :error="mForm.errors.tipo">
                            <select
                                v-model="mForm.tipo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option value="realizado">Realizado</option>
                                <option value="programado">Programado</option>
                            </select>
                        </FormField>
                        <FormField
                            v-if="mForm.tipo === 'programado'"
                            label="Fecha límite"
                            :error="mForm.errors.fecha_fin_programada"
                        >
                            <input
                                v-model="mForm.fecha_fin_programada"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>
                    <FormField label="Descripción" :error="mForm.errors.descripcion">
                        <textarea
                            v-model="mForm.descripcion"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Detalle del mantenimiento..."
                        />
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <FormField label="Fotos (opcional)" :error="mForm.errors.fotos">
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                @change="onFotosChange"
                                class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Existentes: 0 · Seleccionadas: {{ fotosSeleccionadasCount }}/{{ MAX_FOTOS }} · Restantes: {{ fotosRestantes }}
                            </p>
                        </FormField>
                        <FormField label="PDFs (opcional)" :error="mForm.errors.documentos">
                            <input
                                type="file"
                                accept="application/pdf"
                                multiple
                                @change="onDocumentosChange"
                                class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Existentes: 0 · Seleccionados: {{ docsSeleccionadosCount }}/{{ MAX_PDFS }} · Restantes: {{ docsRestantes }}
                            </p>
                        </FormField>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="mForm.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ mForm.processing ? "Guardando..." : "Registrar mantenimiento" }}
                        </button>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 transition-colors duration-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Fecha</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Tipo</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Fecha límite</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Descripción</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="m in ups.mantenimientos"
                                :key="m.id"
                                class="border-b border-slate-100 dark:border-slate-700 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-900 dark:text-slate-100">
                                    {{ formatDate(m.fecha_mantenimiento) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-semibold transition-colors duration-200',
                                            m.tipo === 'realizado'
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
                                        ]"
                                    >
                                        {{ m.tipo === "realizado" ? "Realizado" : "Programado" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ m.tipo === 'programado' ? (m.fecha_fin_programada ? formatDate(m.fecha_fin_programada) : '-') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300 whitespace-pre-wrap">
                                    {{ m.descripcion || "-" }}
                                    <div class="mt-2 text-xs text-slate-500 dark:text-slate-400 space-y-0.5">
                                        <p>
                                            <span class="font-medium">Creado:</span>
                                            {{ m.creado_por?.name || m.creado_por?.email || "N/A" }}
                                            · {{ m.created_at ? new Date(m.created_at).toLocaleString("es-ES") : "-" }}
                                        </p>
                                        <p>
                                            <span class="font-medium">Editado:</span>
                                            {{ m.editado_por?.name || m.editado_por?.email || "N/A" }}
                                            · {{ m.updated_at ? new Date(m.updated_at).toLocaleString("es-ES") : "-" }}
                                        </p>
                                    </div>
                                    <div v-if="(m.imagenes?.length || 0) > 0" class="mt-2 flex flex-wrap gap-2">
                                        <a
                                            v-for="img in m.imagenes"
                                            :key="img.id"
                                            :href="storageUrl(img.ruta_imagen)"
                                            target="_blank"
                                            class="block"
                                            title="Ver imagen"
                                        >
                                            <span class="block w-16 aspect-square rounded-lg border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-700 transition-colors duration-200">
                                                <img
                                                    :src="storageUrl(img.ruta_imagen)"
                                                    class="w-full h-full object-cover object-center"
                                                    alt="Foto mantenimiento"
                                                    loading="lazy"
                                                    decoding="async"
                                                />
                                            </span>
                                        </a>
                                    </div>
                                    <div v-if="(m.documentos?.length || 0) > 0" class="mt-2 space-y-1">
                                        <a
                                            v-for="doc in m.documentos"
                                            :key="doc.id"
                                            :href="storageUrl(doc.ruta_documento)"
                                            target="_blank"
                                            class="text-sm text-blue-700 dark:text-blue-400 hover:underline inline-flex items-center gap-2 transition-colors duration-200"
                                        >
                                            <span>📄</span>
                                            <span>{{ doc.nombre_original || "Documento PDF" }}</span>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        <form
                                            v-if="m.tipo === 'programado' && hasPermission('edit_ups_mantenimientos')"
                                            @submit.prevent="completarMantenimiento(m.id)"
                                            class="inline"
                                        >
                                            <button
                                                type="submit"
                                                class="px-3 py-1.5 rounded-md border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 hover:bg-green-100 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 text-sm font-medium transition-colors duration-200"
                                            >
                                                ✅ Completar
                                            </button>
                                        </form>
                                        <a
                                            v-if="(m.imagenes?.length || 0) + (m.documentos?.length || 0) > 0"
                                            :href="route('ups.mantenimientos.zip', { ups: ups.id, mantenimiento: m.id })"
                                            class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                                        >
                                            ZIP
                                        </a>
                                        <Link
                                            v-if="hasPermission('edit_ups_mantenimientos')"
                                            :href="route('ups.mantenimientos.edit', { ups: ups.id, mantenimiento: m.id })"
                                            class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            v-if="hasPermission('delete_ups_mantenimientos')"
                                            type="button"
                                            @click="eliminarMantenimiento(m.id)"
                                            class="px-3 py-1.5 rounded-md border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 text-sm font-medium transition-colors duration-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!ups.mantenimientos || ups.mantenimientos.length === 0">
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">
                                    No hay mantenimientos registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch, Transition } from "vue";

const props = defineProps({
    ups: Object,
});

const page = usePage();

// Mensaje de éxito
const showSuccessMessage = ref(false);

// Mostrar mensaje de éxito si hay flash message
watch(
    () => page.props.flash?.message,
    (message) => {
        if (message) {
            showSuccessMessage.value = true;
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        }
    },
    { immediate: true }
);

const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const hasPermission = (permission) => userPermissions.value.includes(permission);

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const MAX_FOTOS = 6;
const MAX_PDFS = 5;

const mForm = useForm({
    fecha_mantenimiento: "",
    tipo: "realizado",
    fecha_fin_programada: "",
    descripcion: "",
    fotos: [],
    documentos: [],
});

const onFotosChange = (e) => {
    const files = Array.from(e.target?.files || []);
    mForm.fotos = files.slice(0, MAX_FOTOS);
};

const onDocumentosChange = (e) => {
    const files = Array.from(e.target?.files || []);
    mForm.documentos = files.slice(0, MAX_PDFS);
};

const fotosSeleccionadasCount = computed(() => (mForm.fotos || []).length);
const docsSeleccionadosCount = computed(() => (mForm.documentos || []).length);
const fotosRestantes = computed(() => Math.max(0, MAX_FOTOS - fotosSeleccionadasCount.value));
const docsRestantes = computed(() => Math.max(0, MAX_PDFS - docsSeleccionadosCount.value));

const crearMantenimiento = () => {
    mForm.post(route("ups.mantenimientos.store", { ups: props.ups.id }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            mForm.reset("fecha_mantenimiento", "tipo", "fecha_fin_programada", "descripcion", "fotos", "documentos");
            mForm.tipo = "realizado";
        },
    });
};

const completarMantenimiento = (mantenimientoId) => {
    if (!confirm("¿Marcar este mantenimiento como completado?")) return;
    router.post(route("ups.mantenimientos.completar", { ups: props.ups.id, mantenimiento: mantenimientoId }), {
        preserveScroll: true,
    });
};

const eliminarMantenimiento = (id) => {
    if (!confirm("¿Seguro que deseas eliminar este mantenimiento?")) return;
    router.delete(route("ups.mantenimientos.destroy", { ups: props.ups.id, mantenimiento: id }), {
        preserveScroll: true,
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};
</script>


