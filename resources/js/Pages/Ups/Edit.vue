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
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Código" :error="form.errors.codigo">
                            <input
                                v-model="form.codigo"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField label="Nombre" :error="form.errors.nombre">
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                        <FormField label="Estado" :error="form.errors.activo">
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
                            <div v-if="fotoActualUrl" class="space-y-1">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Actual</p>
                                <div class="w-40 aspect-square rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-700 transition-colors duration-200">
                                    <img
                                        :src="fotoActualUrl"
                                        alt="Foto actual"
                                        class="w-full h-full object-cover object-center"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                </div>
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
                        <FormField label="Potencia (W)" :error="form.errors.potencia_w">
                            <input
                                v-model="form.potencia_w"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

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
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar cambios" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { submitUploadForm } from "@/Support/inertiaUploads";
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

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
    nombre: props.ups.nombre,
    piso_id: props.ups.piso_id ?? null,
    ubicacion: props.ups.ubicacion ?? "",
    marca: props.ups.marca ?? "",
    modelo: props.ups.modelo ?? "",
    serial: props.ups.serial ?? "",
    foto: null,
    potencia_va: props.ups.potencia_va ?? null,
    potencia_w: props.ups.potencia_w ?? null,
    activo: !!props.ups.activo,
    observaciones: props.ups.observaciones ?? "",
});

const onFotoChange = (e) => {
    const file = e.target?.files?.[0] || null;
    form.foto = file;
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

const submit = () => {
    submitUploadForm(form, route("ups.update", { ups: props.ups.id }), "put");
};

const eliminar = () => {
    if (!confirm("¿Seguro que deseas eliminar esta UPS? Se eliminarán también sus mantenimientos.")) return;
    router.delete(route("ups.destroy", { ups: props.ups.id }));
};
</script>


