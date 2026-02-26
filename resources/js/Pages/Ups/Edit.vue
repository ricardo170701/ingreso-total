<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Editar UPS</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ ups.codigo }} - {{ ups.nombre }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('ups.show', { ups: ups.id })"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <form @submit.prevent="showConfirmModal = true" class="space-y-4">
                    <!-- Información básica -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Código" :error="form.errors.codigo">
                            <input
                                v-model="form.codigo"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Comp (Compañía/Compartimiento)" :error="form.errors.comp">
                            <input
                                v-model="form.comp"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Comp (Compañía/Compartimiento)" :error="form.errors.comp">
                            <input
                                v-model="form.comp"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Fecha de adquisición" :error="form.errors.fecha_adquisicion">
                            <input
                                v-model="form.fecha_adquisicion"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Elemt (Elemento)" :error="form.errors.elemt">
                            <input
                                v-model="form.elemt"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="R.I. (Registro Interno)" :error="form.errors.ri">
                            <input
                                v-model="form.ri"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Nombre" :error="form.errors.nombre">
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Piso" :error="form.errors.piso_id">
                            <select
                                v-model="form.piso_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">-</option>
                                <option v-for="p in pisos" :key="p.id" :value="p.id">
                                    {{ p.nombre }}
                                </option>
                            </select>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Estado" :error="form.errors.estado">
                            <input
                                v-model="form.estado"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: En servicio, Mantenimiento, etc."
                            />
                        </FormField>
                        <FormField label="Activo/Inactivo" :error="form.errors.activo">
                            <select
                                v-model="form.activo"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Marca" :error="form.errors.marca">
                            <input
                                v-model="form.marca"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Modelo" :error="form.errors.modelo">
                            <input
                                v-model="form.modelo"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Serial" :error="form.errors.serial">
                            <input
                                v-model="form.serial"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Ubicación" :error="form.errors.ubicacion">
                            <input
                                v-model="form.ubicacion"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <FormField label="Foto" :error="form.errors.foto">
                        <input
                            type="file"
                            accept="image/*"
                            @change="onFotoChange"
                            class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                        />
                        <div class="mt-3 flex flex-wrap gap-3 items-start">
                            <div v-if="fotoActualUrl" class="relative group space-y-1">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Actual</p>
                                <div
                                    :class="[
                                        'w-40 aspect-square rounded-xl border overflow-hidden bg-slate-100 dark:bg-slate-700 transition-colors duration-200',
                                        form.eliminar_foto
                                            ? 'border-red-400 dark:border-red-500 opacity-75'
                                            : 'border-slate-200 dark:border-slate-700'
                                    ]"
                                >
                                    <img
                                        :src="fotoActualUrl"
                                        alt="Foto actual"
                                        class="w-full h-full object-cover object-center"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </div>
                                <button
                                    type="button"
                                    aria-label="Eliminar foto"
                                    @click.prevent="toggleEliminarFoto"
                                    :class="[
                                        'absolute top-6 right-0 w-7 h-7 rounded-full flex items-center justify-center text-white text-sm font-bold transition-colors',
                                        form.eliminar_foto ? 'bg-red-600 dark:bg-red-500' : 'bg-slate-800/80 dark:bg-slate-700/80 hover:bg-red-600 dark:hover:bg-red-500'
                                    ]"
                                >
                                    ×
                                </button>
                                <p v-if="form.eliminar_foto" class="text-xs text-red-600 dark:text-red-400 font-medium">Se eliminará al guardar</p>
                            </div>
                            <div v-if="fotoPreviewUrl" class="space-y-1">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Nueva</p>
                                <div class="w-40 aspect-square rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-700 transition-colors duration-200">
                                    <img
                                        :src="fotoPreviewUrl"
                                        alt="Previsualización"
                                        class="w-full h-full object-cover object-center"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </div>
                            </div>
                        </div>
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Potencia (VA)" :error="form.errors.potencia_va">
                            <input
                                v-model="form.potencia_va"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Potencia (KVA)" :error="form.errors.potencia_kva">
                            <input
                                v-model="form.potencia_kva"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Potencia (W)" :error="form.errors.potencia_w">
                            <input
                                v-model="form.potencia_w"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Potencia (KW)" :error="form.errors.potencia_kw">
                            <input
                                v-model="form.potencia_kw"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Cantidad de baterías" :error="form.errors.cantidad_baterias">
                            <input
                                v-model="form.cantidad_baterias"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Voltaje de baterías (V)" :error="form.errors.voltaje_baterias">
                            <input
                                v-model="form.voltaje_baterias"
                                type="number"
                                step="0.01"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <FormField label="Ficha técnica (PDF)" :error="form.errors.ficha_tecnica">
                        <input
                            type="file"
                            accept=".pdf,application/pdf"
                            @change="onFichaTecnicaChange"
                            class="block w-full text-sm text-slate-700 dark:text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 dark:file:bg-slate-700 file:text-white hover:file:bg-slate-800 dark:hover:file:bg-slate-600 transition-colors duration-200"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Formato PDF, máximo 10MB
                        </p>
                        <div v-if="fichaTecnicaActualUrl" class="mt-2 flex flex-wrap items-center gap-2">
                            <span class="relative inline-flex items-center">
                                <a
                                    :href="fichaTecnicaActualUrl"
                                    target="_blank"
                                    :class="[
                                        'text-sm hover:underline transition-colors pr-8',
                                        form.eliminar_ficha_tecnica ? 'text-red-600 dark:text-red-400 line-through' : 'text-green-600 dark:text-green-400'
                                    ]"
                                >
                                    Ver ficha técnica actual
                                </a>
                                <button
                                    type="button"
                                    aria-label="Eliminar ficha técnica"
                                    @click.prevent="toggleEliminarFichaTecnica"
                                    :class="[
                                        'absolute right-0 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full flex items-center justify-center text-white text-sm font-bold transition-colors',
                                        form.eliminar_ficha_tecnica ? 'bg-red-600 dark:bg-red-500' : 'bg-slate-700/80 hover:bg-red-600 dark:hover:bg-red-500'
                                    ]"
                                >
                                    ×
                                </button>
                            </span>
                            <span v-if="form.eliminar_ficha_tecnica" class="text-xs text-red-600 dark:text-red-400 font-medium">Se eliminará al guardar</span>
                        </div>
                    </FormField>

                    <FormField label="Observaciones" :error="form.errors.observaciones">
                        <textarea
                            v-model="form.observaciones"
                            rows="4"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                    </FormField>

                    <div class="flex items-center justify-between gap-2 pt-2 flex-wrap">
                        <button
                            v-if="hasPermission('delete_ups')"
                            type="button"
                            @click="eliminar"
                            class="px-4 py-2 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-medium transition-colors duration-200"
                        >
                            Eliminar
                        </button>
                        <div class="flex-1"></div>
                        <button
                            type="button"
                            @click="showConfirmModal = true"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 font-medium disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar cambios" }}
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
                        ¿Estás seguro de que deseas editar el UPS
                        <strong class="text-slate-900 dark:text-slate-100">{{ ups.nombre }}</strong>?
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
                            class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 font-medium transition-colors duration-200"
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
import { submitUploadForm } from "@/Support/inertiaUploads";
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    ups: Object,
    pisos: Array,
});

const page = usePage();
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const hasPermission = (permission) => userPermissions.value.includes(permission);

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const form = useForm({
    codigo: props.ups.codigo,
    comp: props.ups.comp ?? "",
    fecha_adquisicion: props.ups.fecha_adquisicion ?? null,
    elemt: props.ups.elemt ?? "",
    ri: props.ups.ri ?? "",
    nombre: props.ups.nombre,
    piso_id: props.ups.piso_id ?? null,
    estado: props.ups.estado ?? "",
    ubicacion: props.ups.ubicacion ?? "",
    marca: props.ups.marca ?? "",
    modelo: props.ups.modelo ?? "",
    serial: props.ups.serial ?? "",
    foto: null,
    ficha_tecnica: null,
    eliminar_foto: false,
    eliminar_ficha_tecnica: false,
    potencia_va: props.ups.potencia_va ?? null,
    potencia_kva: props.ups.potencia_kva ?? null,
    potencia_w: props.ups.potencia_w ?? null,
    potencia_kw: props.ups.potencia_kw ?? null,
    cantidad_baterias: props.ups.cantidad_baterias ?? null,
    voltaje_baterias: props.ups.voltaje_baterias ?? null,
    activo: !!props.ups.activo,
    observaciones: props.ups.observaciones ?? "",
});

function toggleEliminarFoto() {
    form.eliminar_foto = !form.eliminar_foto;
    if (form.eliminar_foto) form.foto = null;
}

function toggleEliminarFichaTecnica() {
    form.eliminar_ficha_tecnica = !form.eliminar_ficha_tecnica;
    if (form.eliminar_ficha_tecnica) form.ficha_tecnica = null;
}

const onFotoChange = (e) => {
    const file = e.target?.files?.[0] || null;
    form.foto = file;
    if (file) form.eliminar_foto = false;
};

const onFichaTecnicaChange = (e) => {
    const file = e.target?.files?.[0] || null;
    form.ficha_tecnica = file;
    if (file) form.eliminar_ficha_tecnica = false;
};

const fotoPreviewUrl = computed(() => {
    if (!form.foto) return null;
    try {
        return URL.createObjectURL(form.foto);
    } catch {
        return null;
    }
});

const fotoActualUrl = computed(() => storageUrl(props.ups?.foto));
const fichaTecnicaActualUrl = computed(() => storageUrl(props.ups?.ficha_tecnica));

const showConfirmModal = ref(false);

const confirmSubmit = () => {
    showConfirmModal.value = false;
    // No enviar foto ni ficha_tecnica si están vacíos para conservar los existentes al editar
    submitUploadForm(form, route("ups.update", { ups: props.ups.id }), "put", {
        transform: (data) => {
            const d = { ...data };
            if (d.foto == null || (typeof d.foto === 'object' && !(d.foto instanceof File))) delete d.foto;
            if (d.ficha_tecnica == null || (typeof d.ficha_tecnica === 'object' && !(d.ficha_tecnica instanceof File))) delete d.ficha_tecnica;
            return d;
        },
    });
};

const eliminar = () => {
    if (!confirm("¿Seguro que deseas eliminar esta UPS? Se eliminarán también sus mantenimientos.")) return;
    router.delete(route("ups.destroy", { ups: props.ups.id }));
};
</script>


