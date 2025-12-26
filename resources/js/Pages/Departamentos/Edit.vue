<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Editar Departamento
                    </h1>
                    <p class="text-sm text-slate-600">
                        Modifica la información del departamento.
                    </p>
                </div>
                <Link
                    :href="route('departamentos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <FormField label="Nombre" :error="form.errors.nombre">
                        <input
                            v-model="form.nombre"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required
                        />
                    </FormField>

                    <FormField label="Piso" :error="form.errors.piso_id">
                        <select
                            v-model="form.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </FormField>

                    <div class="flex items-center gap-6">
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4"
                            />
                            <span class="text-sm text-slate-700">Activo</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <Link
                            :href="route('departamentos.index')"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
                        >
                            {{
                                form.processing ? "Guardando..." : "Guardar"
                            }}
                        </button>
                        <button
                            type="button"
                            @click="eliminar"
                            class="px-4 py-2 rounded-lg border border-red-200 hover:bg-red-50 text-red-700 font-medium"
                        >
                            Eliminar
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

const props = defineProps({
    departamento: Object,
    pisos: Array,
});

const form = useForm({
    nombre: props.departamento.nombre || "",
    piso_id: props.departamento.piso_id || null,
    descripcion: props.departamento.descripcion || "",
    activo: !!props.departamento.activo,
});

const submit = () => {
    form.put(route("departamentos.update", { departamento: props.departamento.id }));
};

const eliminar = () => {
    if (
        !confirm(
            `¿Estás seguro de eliminar el departamento "${props.departamento.nombre}"?`
        )
    )
        return;
    router.delete(
        route("departamentos.destroy", { departamento: props.departamento.id })
    );
};
</script>

