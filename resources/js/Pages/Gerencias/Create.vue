<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Crear Gerencia
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Registra una nueva gerencia en la secretaría <strong>{{ secretaria.nombre }}</strong>.
                    </p>
                </div>
                <Link
                    :href="route('secretarias.show', { secretaria: secretaria.id })"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div
                v-if="$page.props.errors?.nombre"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 transition-colors duration-200"
            >
                {{ $page.props.errors.nombre }}
            </div>

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <FormField label="Nombre" :error="form.errors.nombre">
                        <input
                            v-model="form.nombre"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Ej: Gerencia de Recursos Humanos, Gerencia de Tecnología"
                            required
                        />
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            El nombre debe ser único dentro de esta secretaría.
                        </p>
                    </FormField>

                    <FormField
                        label="Descripción"
                        :error="form.errors.descripcion"
                    >
                        <textarea
                            v-model="form.descripcion"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Descripción de la gerencia..."
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
                            :href="route('secretarias.show', { secretaria: secretaria.id })"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-purple-600 dark:bg-purple-700 text-white hover:bg-purple-700 dark:hover:bg-purple-600 disabled:opacity-50 font-medium transition-colors duration-200"
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
    secretaria: Object,
});

const form = useForm({
    nombre: "",
    descripcion: "",
    activo: true,
});

const submit = () => {
    form.post(route("gerencias.store", { secretaria: props.secretaria.id }));
};
</script>