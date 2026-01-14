<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Editar puerta #{{ puerta.id }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Actualiza los datos de la puerta.
                    </p>
                </div>
                <Link
                    :href="route('puertas.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Nombre" :error="form.errors.nombre">
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
                        <FormField label="Zona" :error="form.errors.zona_id">
                            <select
                                v-model="form.zona_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">- Sin zona -</option>
                                <option
                                    v-for="z in zonas"
                                    :key="z.id"
                                    :value="z.id"
                                >
                                    {{ z.nombre }}
                                </option>
                            </select>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <FormField label="Piso" :error="form.errors.piso_id">
                            <select
                                v-model="form.piso_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">- Sin piso -</option>
                                <option
                                    v-for="p in pisos"
                                    :key="p.id"
                                    :value="p.id"
                                >
                                    {{ p.nombre }}
                                </option>
                            </select>
                        </FormField>
                        <FormField label="Tipo" :error="form.errors.tipo_puerta_id">
                            <select
                                v-model="form.tipo_puerta_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">- Sin tipo -</option>
                                <option
                                    v-for="t in tiposPuerta"
                                    :key="t.id"
                                    :value="t.id"
                                >
                                    {{ t.nombre }}
                                </option>
                            </select>
                        </FormField>
                        <FormField label="Material" :error="form.errors.material_id">
                            <select
                                v-model="form.material_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">- Sin material -</option>
                                <option
                                    v-for="m in materiales"
                                    :key="m.id"
                                    :value="m.id"
                                >
                                    {{ m.nombre }}
                                </option>
                            </select>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <FormField
                            label="Tiempo de Apertura (seg)"
                            :error="form.errors.tiempo_apertura"
                        >
                            <input
                                v-model.number="form.tiempo_apertura"
                                type="number"
                                min="1"
                                max="300"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 5"
                            />
                        </FormField>
                        <FormField
                            label="Tiempo Discapacitados (seg)"
                            :error="form.errors.tiempo_discapacitados"
                        >
                            <input
                                v-model.number="form.tiempo_discapacitados"
                                type="number"
                                min="1"
                                max="600"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 10"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Tiempo adicional para personas discapacitadas
                            </p>
                        </FormField>
                        <FormField label="Alto (cm)" :error="form.errors.alto">
                            <input
                                v-model.number="form.alto"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 200"
                            />
                        </FormField>
                        <FormField label="Largo (cm)" :error="form.errors.largo">
                            <input
                                v-model.number="form.largo"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 100"
                            />
                        </FormField>
                        <FormField label="Ancho (cm)" :error="form.errors.ancho">
                            <input
                                v-model.number="form.ancho"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 5"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Peso (kg)" :error="form.errors.peso">
                            <input
                                v-model.number="form.peso"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 50"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="IP Entrada (Raspberry)"
                            :error="form.errors.ip_entrada"
                        >
                            <input
                                v-model="form.ip_entrada"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 192.168.1.100"
                            />
                        </FormField>
                        <FormField
                            label="IP Salida (Raspberry)"
                            :error="form.errors.ip_salida"
                        >
                            <input
                                v-model="form.ip_salida"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 192.168.1.101"
                            />
                        </FormField>
                    </div>

                    <FormField label="Imagen" :error="form.errors.imagen">
                        <div v-if="puerta.imagen" class="mb-2">
                            <img
                                :src="`/storage/${puerta.imagen}`"
                                :alt="puerta.nombre"
                                class="w-32 h-32 object-cover rounded-lg border border-slate-200 dark:border-slate-700"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Imagen actual
                            </p>
                        </div>
                        <input
                            @input="form.imagen = $event.target.files[0]"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/gif"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Dejar vacío para mantener la imagen actual. Formatos: JPEG, JPG, PNG, GIF (máx. 2MB)
                        </p>
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Código Físico (Entrada)"
                            :error="form.errors.codigo_fisico"
                        >
                            <input
                                v-model="form.codigo_fisico"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField
                            label="Código Físico (Salida)"
                            :error="form.errors.codigo_fisico_salida"
                        >
                            <input
                                v-model="form.codigo_fisico_salida"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                        <FormField
                            label="Ubicación"
                            :error="form.errors.ubicacion"
                        >
                            <input
                                v-model="form.ubicacion"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <FormField
                        label="Descripción"
                        :error="form.errors.descripcion"
                    >
                        <textarea
                            v-model="form.descripcion"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                    </FormField>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Activa</span>
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.requiere_discapacidad"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            <span class="text-sm text-slate-700 dark:text-slate-300"
                                >Requiere discapacidad</span
                            >
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.es_oculta"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            <span class="text-sm text-slate-700 dark:text-slate-300"
                                >Puerta oculta (requiere permiso especial para ver)</span
                            >
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="destroy"
                            class="px-4 py-2 rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors duration-200"
                        >
                            Eliminar
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar" }}
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
import { Link, router, useForm } from "@inertiajs/vue3";
import { submitUploadForm } from "@/Support/inertiaUploads";

const props = defineProps({
    puerta: Object,
    zonas: Array,
    pisos: Array,
    tiposPuerta: Array,
    materiales: Array,
});

const form = useForm({
    nombre: props.puerta.nombre || "",
    zona_id: props.puerta.zona_id ?? null,
    piso_id: props.puerta.piso_id ?? null,
    tipo_puerta_id: props.puerta.tipo_puerta_id ?? null,
    material_id: props.puerta.material_id ?? null,
    ip_entrada: props.puerta.ip_entrada || "",
    ip_salida: props.puerta.ip_salida || "",
    imagen: null,
    tiempo_apertura: props.puerta.tiempo_apertura || 5,
    tiempo_discapacitados: props.puerta.tiempo_discapacitados ?? null,
    alto: props.puerta.alto ?? null,
    largo: props.puerta.largo ?? null,
    ancho: props.puerta.ancho ?? null,
    peso: props.puerta.peso ?? null,
    codigo_fisico: props.puerta.codigo_fisico || "",
    codigo_fisico_salida: props.puerta.codigo_fisico_salida || "",
    ubicacion: props.puerta.ubicacion || "",
    descripcion: props.puerta.descripcion || "",
    activo: !!props.puerta.activo,
    requiere_discapacidad: !!props.puerta.requiere_discapacidad,
    es_oculta: !!props.puerta.es_oculta,
});

const submit = () => {
    submitUploadForm(form, route("puertas.update", { puerta: props.puerta.id }), "put");
};

const destroy = () => {
    if (!confirm("¿Eliminar esta puerta?")) return;
    router.delete(route("puertas.destroy", { puerta: props.puerta.id }));
};
</script>
