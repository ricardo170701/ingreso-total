<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">Nueva UPS</h1>
                    <p class="text-sm text-slate-600">Registrar una UPS en el sistema.</p>
                </div>
                <Link
                    :href="route('ups.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Código" :error="form.errors.codigo">
                            <input
                                v-model="form.codigo"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="UPS-001"
                            />
                        </FormField>
                        <FormField label="Nombre" :error="form.errors.nombre">
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="UPS Principal"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Piso" :error="form.errors.piso_id">
                            <select
                                v-model="form.piso_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                        <FormField label="Modelo" :error="form.errors.modelo">
                            <input
                                v-model="form.modelo"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Serial" :error="form.errors.serial">
                            <input
                                v-model="form.serial"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                        <FormField label="Ubicación" :error="form.errors.ubicacion">
                            <input
                                v-model="form.ubicacion"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Ej: Cuarto eléctrico"
                            />
                        </FormField>
                    </div>

                    <FormField label="Foto" :error="form.errors.foto">
                        <input
                            type="file"
                            accept="image/*"
                            @change="onFotoChange"
                            class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-slate-900 file:text-white hover:file:bg-slate-800"
                        />
                        <div v-if="fotoPreviewUrl" class="mt-3">
                            <div class="w-40 aspect-square rounded-xl border border-slate-200 overflow-hidden bg-slate-100">
                                <img
                                    :src="fotoPreviewUrl"
                                    alt="Previsualización"
                                    class="w-full h-full object-cover object-center"
                                    loading="lazy"
                                    decoding="async"
                                />
                            </div>
                        </div>
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Potencia (VA)" :error="form.errors.potencia_va">
                            <input
                                v-model="form.potencia_va"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Ej: 3000"
                            />
                        </FormField>
                        <FormField label="Potencia (W)" :error="form.errors.potencia_w">
                            <input
                                v-model="form.potencia_w"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Ej: 2700"
                            />
                        </FormField>
                    </div>

                    <FormField label="Observaciones" :error="form.errors.observaciones">
                        <textarea
                            v-model="form.observaciones"
                            rows="4"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Notas, estado de baterías, etc."
                        />
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium disabled:opacity-50"
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
import { Link, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    pisos: Array,
});

const form = useForm({
    codigo: "",
    nombre: "",
    piso_id: null,
    ubicacion: "",
    marca: "",
    modelo: "",
    serial: "",
    foto: null,
    potencia_va: null,
    potencia_w: null,
    activo: true,
    observaciones: "",
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

const submit = () => {
    form.post(route("ups.store"), { forceFormData: true });
};
</script>


