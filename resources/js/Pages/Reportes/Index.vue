<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                    Reportes y Exportaciones
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Genera reportes personalizados y exporta los datos en formato CSV.
                </p>
            </div>

            <!-- Reporte de Usuarios -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    📊 Reporte de Usuarios
                </h2>
                <form
                    @submit.prevent="exportarUsuarios"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <FormField label="Rol" :error="formUsuarios.errors.role_id">
                        <select
                            v-model="formUsuarios.role_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="rol in roles"
                                :key="rol.id"
                                :value="rol.id"
                            >
                                {{ rol.name }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Cargo" :error="formUsuarios.errors.cargo_id">
                        <select
                            v-model="formUsuarios.cargo_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="cargo in cargos"
                                :key="cargo.id"
                                :value="cargo.id"
                            >
                                {{ cargo.name }}
                            </option>
                        </select>
                    </FormField>

                    <FormField
                        label="Secretaría"
                        :error="formUsuarios.errors.secretaria_id"
                    >
                        <select
                            v-model="formUsuarios.secretaria_id"
                            @change="onSecretariaChange('usuarios')"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todas</option>
                            <option
                                v-for="sec in (secretarias || [])"
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
                        :error="formUsuarios.errors.gerencia_id"
                    >
                        <select
                            v-model="formUsuarios.gerencia_id"
                            :disabled="!formUsuarios.secretaria_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <option :value="null">Todas</option>
                            <option
                                v-for="ger in gerenciasFiltradasUsuarios"
                                :key="ger.id"
                                :value="ger.id"
                            >
                                {{ ger.nombre }}
                            </option>
                        </select>
                        <p v-if="!formUsuarios.secretaria_id" class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            Selecciona una secretaría primero
                        </p>
                    </FormField>

                    <FormField label="Estado" :error="formUsuarios.errors.activo">
                        <select
                            v-model="formUsuarios.activo"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option :value="true">Activos</option>
                            <option :value="false">Inactivos</option>
                        </select>
                    </FormField>

                    <div class="md:col-span-2 lg:col-span-4">
                        <button
                            type="submit"
                            :disabled="formUsuarios.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                formUsuarios.processing
                                    ? "Exportando..."
                                    : "📥 Exportar Usuarios (CSV)"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reporte de Accesos -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    🚪 Reporte de Accesos
                </h2>
                <form
                    @submit.prevent="exportarAccesos"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <FormField
                        label="Fecha Desde"
                        :error="formAccesos.errors.fecha_desde"
                    >
                        <input
                            v-model="formAccesos.fecha_desde"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        />
                    </FormField>

                    <FormField
                        label="Fecha Hasta"
                        :error="formAccesos.errors.fecha_hasta"
                    >
                        <input
                            v-model="formAccesos.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        />
                    </FormField>

                    <FormField label="Usuario" :error="formAccesos.errors.user_id">
                        <select
                            v-model="formAccesos.user_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="user in usuarios"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }} ({{ user.email }})
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Piso" :error="formAccesos.errors.piso_id">
                        <select
                            v-model="formAccesos.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="piso in pisos"
                                :key="piso.id"
                                :value="piso.id"
                            >
                                {{ piso.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Tipo de Evento" :error="formAccesos.errors.tipo_evento">
                        <select
                            v-model="formAccesos.tipo_evento"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option value="entrada">Entrada</option>
                            <option value="salida">Salida</option>
                            <option value="denegado">Denegado</option>
                        </select>
                    </FormField>

                    <FormField label="Permitido" :error="formAccesos.errors.permitido">
                        <select
                            v-model="formAccesos.permitido"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option :value="true">Permitidos</option>
                            <option :value="false">Denegados</option>
                        </select>
                    </FormField>

                    <div class="md:col-span-2 lg:col-span-4 flex items-center gap-2">
                        <Link
                            :href="route('reportes.accesos')"
                            class="px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 font-medium transition-colors duration-200"
                        >
                            📊 Ver Reporte de Accesos
                        </Link>
                        <button
                            type="submit"
                            :disabled="formAccesos.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                formAccesos.processing
                                    ? "Exportando..."
                                    : "📥 Exportar Accesos (CSV)"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reporte de Mantenimientos -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    🔧 Reporte de Mantenimientos
                </h2>
                <form
                    @submit.prevent="exportarMantenimientos"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <FormField
                        label="Fecha Desde"
                        :error="formMantenimientos.errors.fecha_desde"
                    >
                        <input
                            v-model="formMantenimientos.fecha_desde"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        />
                    </FormField>

                    <FormField
                        label="Fecha Hasta"
                        :error="formMantenimientos.errors.fecha_hasta"
                    >
                        <input
                            v-model="formMantenimientos.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        />
                    </FormField>

                    <FormField
                        label="Puerta"
                        :error="formMantenimientos.errors.puerta_id"
                    >
                        <select
                            v-model="formMantenimientos.puerta_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todas</option>
                            <option
                                v-for="puerta in puertas"
                                :key="puerta.id"
                                :value="puerta.id"
                            >
                                {{ puerta.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Tipo" :error="formMantenimientos.errors.tipo">
                        <select
                            v-model="formMantenimientos.tipo"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option value="realizado">Realizado</option>
                            <option value="programado">Programado</option>
                        </select>
                    </FormField>

                    <FormField
                        label="Usuario"
                        :error="formMantenimientos.errors.usuario_id"
                    >
                        <select
                            v-model="formMantenimientos.usuario_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="user in usuarios"
                                :key="user.id"
                                :value="user.id"
                            >
                                {{ user.name }}
                            </option>
                        </select>
                    </FormField>

                    <div class="md:col-span-2 lg:col-span-4">
                        <button
                            type="submit"
                            :disabled="formMantenimientos.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                formMantenimientos.processing
                                    ? "Exportando..."
                                    : "📥 Exportar Mantenimientos (CSV)"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reporte de Puertas -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    🚪 Reporte de Puertas
                </h2>
                <form
                    @submit.prevent="exportarPuertas"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <FormField label="Piso" :error="formPuertas.errors.piso_id">
                        <select
                            v-model="formPuertas.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
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
                        label="Tipo de Puerta"
                        :error="formPuertas.errors.tipo_puerta_id"
                    >
                        <select
                            v-model="formPuertas.tipo_puerta_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="tipo in tiposPuerta"
                                :key="tipo.id"
                                :value="tipo.id"
                            >
                                {{ tipo.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField
                        label="Material"
                        :error="formPuertas.errors.material_id"
                    >
                        <select
                            v-model="formPuertas.material_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="material in materiales"
                                :key="material.id"
                                :value="material.id"
                            >
                                {{ material.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Estado" :error="formPuertas.errors.activo">
                        <select
                            v-model="formPuertas.activo"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        >
                            <option :value="null">Todas</option>
                            <option :value="true">Activas</option>
                            <option :value="false">Inactivas</option>
                        </select>
                    </FormField>

                    <div class="md:col-span-2 lg:col-span-4">
                        <button
                            type="submit"
                            :disabled="formPuertas.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                formPuertas.processing
                                    ? "Exportando..."
                                    : "📥 Exportar Puertas (CSV)"
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
import { computed } from "vue";

const props = defineProps({
    roles: Array,
    cargos: Array,
    secretarias: Array,
    gerencias: Array,
    pisos: Array,
    puertas: Array,
    tiposPuerta: Array,
    materiales: Array,
    usuarios: Array,
});

// Formulario para usuarios
const formUsuarios = useForm({
    role_id: null,
    cargo_id: null,
    secretaria_id: null,
    gerencia_id: null,
    activo: null,
});

// Filtrar gerencias por secretaría seleccionada
const gerenciasFiltradasUsuarios = computed(() => {
    if (!formUsuarios.secretaria_id) return props.gerencias || [];
    return props.gerencias?.filter(g => g.secretaria_id === formUsuarios.secretaria_id) || [];
});

// Limpiar gerencia cuando cambia la secretaría
const onSecretariaChange = (formType) => {
    if (formType === 'usuarios') {
        formUsuarios.gerencia_id = null;
    }
};

// Formulario para accesos
const formAccesos = useForm({
    fecha_desde: null,
    fecha_hasta: null,
    user_id: null,
    piso_id: null,
    tipo_evento: null,
    permitido: null,
});

// Formulario para mantenimientos
const formMantenimientos = useForm({
    fecha_desde: null,
    fecha_hasta: null,
    puerta_id: null,
    tipo: null,
    usuario_id: null,
});

// Formulario para puertas
const formPuertas = useForm({
    piso_id: null,
    tipo_puerta_id: null,
    material_id: null,
    activo: null,
});

const downloadCsv = (url, form) => {
    form.processing = true;

    // Construir query string
    const params = new URLSearchParams();
    Object.keys(form.data()).forEach((key) => {
        const value = form.data()[key];
        if (value !== null && value !== undefined && value !== '') {
            params.append(key, value);
        }
    });

    const fullUrl = url + (params.toString() ? `?${params.toString()}` : '');

    // Usar window.open para descargar el archivo
    // Esto respeta las cookies de sesión automáticamente
    // Similar a como se hace en Ingreso/Index.vue para descargar QR
    const downloadWindow = window.open(fullUrl, '_blank');

    // Cerrar la ventana después de un breve delay (el navegador descargará el archivo)
    setTimeout(() => {
        if (downloadWindow) {
            downloadWindow.close();
        }
        form.processing = false;
    }, 2000);
};

const exportarUsuarios = () => {
    downloadCsv(route("reportes.exportar.usuarios"), formUsuarios);
};

const exportarAccesos = () => {
    downloadCsv(route("reportes.exportar.accesos"), formAccesos);
};

const exportarMantenimientos = () => {
    downloadCsv(route("reportes.exportar.mantenimientos"), formMantenimientos);
};

const exportarPuertas = () => {
    downloadCsv(route("reportes.exportar.puertas"), formPuertas);
};
</script>

