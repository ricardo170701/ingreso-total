<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Crear Dependencia
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Registra una nueva dependencia en el sistema.
                    </p>
                </div>
                <Link
                    :href="route('dependencias.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <FormField label="Nombre" :error="form.errors.nombre">
                        <input
                            v-model="form.nombre"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Ej: Secretaría de Gobierno, Secretaría de Hacienda"
                            required
                        />
                    </FormField>

                    <FormField label="Piso" :error="form.errors.piso_id">
                        <select
                            v-model="form.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        >
                            <option :value="null">Sin piso específico</option>
                            <option
                                v-for="piso in pisos"
                                :key="piso.id"
                                :value="piso.id"
                            >
                                {{ piso.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField
                        label="Descripción"
                        :error="form.errors.descripcion"
                    >
                        <textarea
                            v-model="form.descripcion"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Descripción de la dependencia..."
                        />
                    </FormField>

                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Activo</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <Link
                            :href="route('dependencias.index')"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-blue-600 text-white hover:bg-slate-800 dark:hover:bg-blue-700 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                form.processing ? "Guardando..." : "Guardar"
                            }}
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
    pisos: Array,
});

const form = useForm({
    nombre: "",
    piso_id: null,
    descripcion: "",
    activo: true,
});

const submit = () => {
    form.post(route("dependencias.store"));
};
</script>