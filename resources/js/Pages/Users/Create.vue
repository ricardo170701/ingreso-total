<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Crear usuario
                    </h1>
                    <p class="text-sm text-slate-600">
                        Completa los datos básicos para registrar un usuario.
                    </p>
                </div>
                <Link
                    :href="route('usuarios.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Nombre" :error="form.errors.nombre">
                            <input
                                v-model="form.nombre"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Nombre"
                            />
                        </FormField>
                        <FormField
                            label="Apellido"
                            :error="form.errors.apellido"
                        >
                            <input
                                v-model="form.apellido"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Apellido"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Email" :error="form.errors.email">
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="correo@dominio.com"
                            />
                        </FormField>
                        <FormField
                            label="Cédula"
                            :error="form.errors.n_identidad"
                        >
                            <input
                                v-model="form.n_identidad"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Ej: 001-0000000-0"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Contraseña"
                            :error="form.errors.password"
                        >
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="********"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Rol" :error="form.errors.role_id">
                            <select
                                v-model="form.role_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option :value="null">-</option>
                                <option
                                    v-for="r in roles"
                                    :key="r.id"
                                    :value="r.id"
                                >
                                    {{ r.name }}
                                </option>
                            </select>
                        </FormField>
                        <FormField v-if="!esVisitante" label="Cargo" :error="form.errors.cargo_id">
                            <select
                                v-model="form.cargo_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option :value="null">-</option>
                                <option
                                    v-for="c in cargos"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            v-if="!esVisitante"
                            label="Departamento"
                            :error="form.errors.departamento_id"
                        >
                            <select
                                v-model="form.departamento_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            >
                                <option :value="null">Sin departamento</option>
                                <option
                                    v-for="dept in departamentos"
                                    :key="dept.id"
                                    :value="dept.id"
                                >
                                    {{ dept.nombre }}
                                    <span v-if="dept.piso"> - {{ dept.piso.nombre }}</span>
                                </option>
                            </select>
                        </FormField>
                        <FormField label="Foto" :error="form.errors.foto">
                            <div
                                v-if="fotoPreviewUrl"
                                class="mb-2 flex justify-center"
                            >
                                <img
                                    :src="fotoPreviewUrl"
                                    alt="Previsualización"
                                    class="h-24 w-24 rounded-full object-cover border border-slate-200"
                                />
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onFotoChange"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                JPG/PNG/WebP. Máx 4MB.
                            </p>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <FormField
                            label="Documentos de contrato (PDF) (opcional)"
                            :error="form.errors.contratos"
                        >
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    Tipo de contrato
                                </label>
                                <select
                                    v-model="form.tipo_contrato"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                >
                                    <option :value="null">-</option>
                                    <option value="prestacion_servicios">Prestación de servicios</option>
                                    <option value="contratista_externo">Contratista externo</option>
                                </select>
                            </div>
                            <input
                                type="file"
                                accept="application/pdf"
                                multiple
                                @change="onContratosChange"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Puedes subir hasta 5 PDFs (máx 10MB cada uno).
                            </p>
                            <ul
                                v-if="contratosSeleccionados.length > 0"
                                class="mt-2 text-sm text-slate-700 list-disc list-inside"
                            >
                                <li v-for="(f, idx) in contratosSeleccionados" :key="idx">
                                    {{ f.name }}
                                </li>
                            </ul>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            v-if="!esVisitante"
                            label="Fecha expiración"
                            :error="form.errors.fecha_expiracion"
                        >
                            <input
                                v-model="form.fecha_expiracion"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>

                        <div class="flex items-center gap-6 pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700"
                                    >Activo</span
                                >
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="form.es_discapacitado"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700"
                                    >Discapacitado</span
                                >
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50"
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
import { Link, useForm } from "@inertiajs/vue3";
import FormField from "@/Components/FormField.vue";
import { computed, watch } from "vue";

const props = defineProps({
    roles: Array,
    cargos: Array,
    departamentos: Array,
});

const esVisitante = computed(() => {
    const role = props.roles?.find((r) => r.id === form.role_id);
    return role?.name === "visitante";
});

const form = useForm({
    email: "",
    password: "",
    role_id: null,
    cargo_id: null,
    nombre: "",
    apellido: "",
    n_identidad: "",
    departamento_id: null,
    fecha_expiracion: null,
    foto: null,
    contratos: [],
    tipo_contrato: null,
    activo: true,
    es_discapacitado: false,
});

const onFotoChange = (e) => {
    const file = e.target?.files?.[0] || null;
    form.foto = file;
};

const onContratosChange = (e) => {
    const files = Array.from(e.target?.files || []);
    form.contratos = files;
};

const contratosSeleccionados = computed(() => form.contratos || []);

const fotoPreviewUrl = computed(() => {
    if (!form.foto) return null;
    try {
        return URL.createObjectURL(form.foto);
    } catch {
        return null;
    }
});

watch(esVisitante, (isVisitante) => {
    if (isVisitante) {
        form.departamento_id = null;
        form.fecha_expiracion = null;
        form.cargo_id = null;
    }
});

const submit = () => {
    form.post(route("usuarios.store"), { forceFormData: true });
};
</script>
