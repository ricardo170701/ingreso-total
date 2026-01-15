<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Crear usuario
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Completa los datos básicos para registrar un usuario.
                    </p>
                </div>
                <Link
                    :href="route('usuarios.index')"
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Apellido"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Email" :error="form.errors.email">
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Ej: 001-0000000-0"
                                required
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <FormField
                            label="Observaciones"
                            :error="form.errors.observaciones"
                        >
                            <textarea
                                v-model="form.observaciones"
                                rows="3"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Observaciones adicionales (opcional)"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Notas o comentarios adicionales sobre el usuario (opcional)
                            </p>
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="********"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Tipo de vinculación" :error="form.errors.role_id">
                            <select
                                v-model="form.role_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">-</option>
                                <option
                                    v-for="r in roles"
                                    :key="r.id"
                                    :value="r.id"
                                >
                                    {{ formatTipoVinculacion(r.name) }}
                                </option>
                            </select>
                        </FormField>
                        <FormField v-if="!esVisitante" label="Rol (permisos)" :error="form.errors.cargo_id">
                            <select
                                v-model="form.cargo_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
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

                    <div v-if="!esVisitante" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Cargo (registro)" :error="form.errors.cargo_texto">
                            <input
                                v-model="form.cargo_texto"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="Cargo/denominación (solo registro)"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Este campo es solo informativo y no afecta los permisos.
                            </p>
                        </FormField>
                    </div>

                    <div v-if="!esVisitante" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Secretaría"
                            :error="form.errors.secretaria_id"
                        >
                            <select
                                v-model="form.secretaria_id"
                                @change="onSecretariaChange"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">Sin secretaría</option>
                                <option
                                    v-for="sec in secretarias"
                                    :key="sec.id"
                                    :value="sec.id"
                                >
                                    {{ sec.nombre }}
                                    <span v-if="sec.piso"> - {{ sec.piso.nombre }}</span>
                                </option>
                            </select>
                        </FormField>
                        <FormField
                            label="Gerencia"
                            :error="form.errors.gerencia_id"
                        >
                            <select
                                v-model="form.gerencia_id"
                                :disabled="!form.secretaria_id"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <option :value="null">Despacho</option>
                                <option
                                    v-for="ger in gerenciasFiltradas"
                                    :key="ger.id"
                                    :value="ger.id"
                                >
                                    {{ ger.nombre }}
                                </option>
                            </select>
                            <p v-if="!form.secretaria_id" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Selecciona una secretaría primero
                            </p>
                        </FormField>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField label="Foto" :error="form.errors.foto">
                            <div
                                v-if="fotoPreviewUrl"
                                class="mb-2 flex justify-center"
                            >
                                <img
                                    :src="fotoPreviewUrl"
                                    alt="Previsualización"
                                    class="h-24 w-24 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                                />
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onFotoChange"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 transition-colors duration-200"
                            />
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                JPG/PNG/WebP. Máx 4MB.
                            </p>
                        </FormField>
                    </div>

                    <div v-if="!esVisitante" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Tipo de contrato"
                            :error="form.errors.tipo_contrato"
                        >
                            <select
                                v-model="form.tipo_contrato"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            >
                                <option :value="null">-</option>
                                <option value="prestacion_servicios">Prestación de servicios</option>
                                <option value="contratista_externo">Contratista externo</option>
                                <option value="contrato_indefinido">Contrato indefinido</option>
                            </select>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Tipo de contrato actual (se guarda incluso sin documento)
                            </p>
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            v-if="!esVisitante && form.tipo_contrato !== 'contrato_indefinido'"
                            label="Fecha expiración"
                            :error="form.errors.fecha_expiracion"
                        >
                            <input
                                v-model="form.fecha_expiracion"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>

                        <div class="flex items-center gap-6 pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="form.activo"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-300"
                                    >Activo</span
                                >
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="form.es_discapacitado"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700 dark:text-slate-300"
                                    >Discapacitado</span
                                >
                            </label>
                        </div>
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
import { Link, useForm } from "@inertiajs/vue3";
import FormField from "@/Components/FormField.vue";
import { computed, watch } from "vue";

const props = defineProps({
    roles: Array,
    cargos: Array,
    secretarias: Array,
    gerencias: Array,
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
    cargo_texto: "",
    nombre: "",
    apellido: "",
    n_identidad: "",
    observaciones: "",
    secretaria_id: null,
    gerencia_id: null,
    fecha_expiracion: null,
    foto: null,
    tipo_contrato: null,
    activo: true,
    es_discapacitado: false,
});

// Filtrar gerencias por secretaría seleccionada
const gerenciasFiltradas = computed(() => {
    if (!form.secretaria_id) return [];
    return props.gerencias?.filter(g => g.secretaria_id === form.secretaria_id) || [];
});

// Limpiar gerencia cuando cambia la secretaría
const onSecretariaChange = () => {
    form.gerencia_id = null;
};

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

watch(esVisitante, (isVisitante) => {
    if (isVisitante) {
        form.secretaria_id = null;
        form.gerencia_id = null;
        form.fecha_expiracion = null;
        form.cargo_id = null;
    }
});

watch(() => form.tipo_contrato, (nuevoTipo) => {
    if (nuevoTipo === 'contrato_indefinido') {
        form.fecha_expiracion = null;
    }
});

const formatTipoVinculacion = (name) => {
    const map = {
        visitante: "Visitante",
        servidor_publico: "Servidor público",
        contratista: "Contratista",
        // compatibilidad histórica
        funcionario: "Servidor público",
    };
    return map[name] || name;
};

const submit = () => {
    form.post(route("usuarios.store"), { forceFormData: true });
};
</script>
