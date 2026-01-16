<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Editar usuario #{{ user.id }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Actualiza los datos del usuario.
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
                <form @submit.prevent="showConfirmModal = true" class="grid grid-cols-1 gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            :label="esVisitante ? 'Email (opcional)' : 'Email'"
                            :error="form.errors.email"
                        >
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                :required="!esVisitante"
                            />
                            <p
                                v-if="esVisitante"
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Si no tiene correo, no podrá iniciar sesión ni tener QR. Solo se le puede asignar tarjeta NFC.
                            </p>
                        </FormField>
                        <FormField
                            label="Nueva contraseña (opcional)"
                            :error="form.errors.password"
                        >
                            <input
                                v-model="form.password"
                                type="password"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                placeholder="(dejar vacío para no cambiar)"
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
                        <FormField
                            label="Apellido"
                            :error="form.errors.apellido"
                        >
                            <input
                                v-model="form.apellido"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="N. identidad"
                            :error="form.errors.n_identidad"
                        >
                            <input
                                v-model="form.n_identidad"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
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
                                v-if="fotoPreviewUrl || fotoActualUrl"
                                class="mb-2 flex justify-center"
                            >
                                <img
                                    :src="fotoPreviewUrl || fotoActualUrl"
                                    alt="Foto de perfil"
                                    class="h-24 w-24 rounded-full object-cover border border-slate-200 dark:border-slate-700"
                                />
                            </div>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onFotoChange"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900"
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
                            type="button"
                            @click="destroy"
                            class="px-4 py-2 rounded-lg border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors duration-200"
                        >
                            Eliminar
                        </button>
                        <button
                            type="button"
                            @click="showConfirmModal = true"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors duration-200"
                        >
                            {{ form.processing ? "Guardando..." : "Guardar" }}
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
                        ¿Estás seguro de que deseas editar el usuario
                        <strong class="text-slate-900 dark:text-slate-100">{{ user.name || user.email }}</strong>?
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
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
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
import { Link, router, useForm } from "@inertiajs/vue3";
import FormField from "@/Components/FormField.vue";
import { submitUploadForm } from "@/Support/inertiaUploads";
import { computed, watch, ref } from "vue";

const props = defineProps({
    user: Object,
    roles: Array,
    cargos: Array,
    secretarias: Array,
    gerencias: Array,
});

const showConfirmModal = ref(false);

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

const esVisitante = computed(() => {
    const role = props.roles?.find((r) => r.id === form.role_id);
    return role?.name === "visitante";
});

const form = useForm({
    email: props.user.email || "",
    password: "",
    role_id: props.user.role_id ?? null,
    cargo_id: props.user.cargo_id ?? null,
    cargo_texto: props.user.cargo_texto || "",
    nombre: props.user.nombre || "",
    apellido: props.user.apellido || "",
    n_identidad: props.user.n_identidad || "",
    observaciones: props.user.observaciones || "",
    secretaria_id: props.user.secretaria_id || null,
    gerencia_id: props.user.gerencia_id || null,
    fecha_expiracion: props.user.fecha_expiracion || null,
    foto: null,
    tipo_contrato: props.user.tipo_contrato || null,
    activo: !!props.user.activo,
    es_discapacitado: !!props.user.es_discapacitado,
});

// Filtrar gerencias por secretaría seleccionada
const gerenciasFiltradas = computed(() => {
    if (!form.secretaria_id) {
        // Si no hay secretaría seleccionada pero el usuario tiene gerencia, cargar todas las gerencias de esa secretaría
        if (props.user.gerencia_id && props.gerencias?.length > 0) {
            const gerenciaActual = props.gerencias.find(g => g.id === props.user.gerencia_id);
            if (gerenciaActual) {
                return props.gerencias.filter(g => g.secretaria_id === gerenciaActual.secretaria_id);
            }
        }
        return [];
    }
    return props.gerencias?.filter(g => g.secretaria_id === form.secretaria_id) || [];
});

// Limpiar gerencia cuando cambia la secretaría (a menos que la gerencia pertenezca a la nueva secretaría)
const onSecretariaChange = () => {
    const gerenciaActual = gerenciasFiltradas.value.find(g => g.id === form.gerencia_id);
    if (!gerenciaActual) {
        form.gerencia_id = null;
    }
};

const onFotoChange = (e) => {
    const file = e.target?.files?.[0] || null;
    form.foto = file;
};

const storageUrl = (path) => {
    if (!path) return null;
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const fotoActualUrl = computed(() => storageUrl(props.user.foto_perfil));

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

const confirmSubmit = () => {
    showConfirmModal.value = false;
    submitUploadForm(form, route("usuarios.update", { user: props.user.id }), "put");
};

const destroy = () => {
    if (!confirm("¿Eliminar este usuario?")) return;
    router.delete(route("usuarios.destroy", { user: props.user.id }));
};

const formatTipoContrato = (tipo) => {
    const map = {
        prestacion_servicios: "Prestación de servicios",
        contratista_externo: "Contratista externo",
        contrato_indefinido: "Contrato indefinido",
    };
    return map[tipo] || tipo;
};
</script>
