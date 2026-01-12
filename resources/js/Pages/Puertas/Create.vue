<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Crear puerta
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Registra una nueva puerta en el sistema.
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
                                placeholder="Ej: Entrada Principal"
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
                        <input
                            @input="form.imagen = $event.target.files[0]"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/gif"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Formatos: JPEG, JPG, PNG, GIF (máx. 2MB)
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
                                placeholder="Ej: P1-ENT-01"
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
                                placeholder="Ej: P1-SAL-01"
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
                                placeholder="Ej: Piso 1 - Lobby"
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
                            placeholder="Descripción opcional de la puerta"
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
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Crear" }}
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
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    zonas: Array,
    pisos: Array,
    tiposPuerta: Array,
    materiales: Array,
});

const form = useForm({
    nombre: "",
    zona_id: null,
    piso_id: null,
    tipo_puerta_id: null,
    material_id: null,
    ip_entrada: "",
    ip_salida: "",
    imagen: null,
    tiempo_apertura: 5,
    tiempo_discapacitados: null,
    alto: null,
    largo: null,
    ancho: null,
    peso: null,
    codigo_fisico: "",
    codigo_fisico_salida: "",
    ubicacion: "",
    descripcion: "",
    activo: true,
    requiere_discapacidad: false,
});

const submit = () => {
    form.post(route("puertas.store"), {
        forceFormData: true, // Necesario para subir archivos
    });
};
</script>
