<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Reportes y Exportaciones
                </h1>
                <p class="text-sm text-slate-600">
                    Genera reportes personalizados y exporta los datos en formato CSV.
                </p>
            </div>

            <!-- Reporte de Usuarios -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    📊 Reporte de Usuarios
                </h2>
                <form
                    @submit.prevent="exportarUsuarios"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <FormField label="Rol" :error="formUsuarios.errors.role_id">
                        <select
                            v-model="formUsuarios.role_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                        label="Departamento"
                        :error="formUsuarios.errors.departamento_id"
                    >
                        <select
                            v-model="formUsuarios.departamento_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option :value="null">Todos</option>
                            <option
                                v-for="dept in departamentos"
                                :key="dept.id"
                                :value="dept.id"
                            >
                                {{ dept.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <FormField label="Estado" :error="formUsuarios.errors.activo">
                        <select
                            v-model="formUsuarios.activo"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
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
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </FormField>

                    <FormField
                        label="Fecha Hasta"
                        :error="formAccesos.errors.fecha_hasta"
                    >
                        <input
                            v-model="formAccesos.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </FormField>

                    <FormField label="Usuario" :error="formAccesos.errors.user_id">
                        <select
                            v-model="formAccesos.user_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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

                    <FormField label="Puerta" :error="formAccesos.errors.puerta_id">
                        <select
                            v-model="formAccesos.puerta_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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

                    <FormField label="Permitido" :error="formAccesos.errors.permitido">
                        <select
                            v-model="formAccesos.permitido"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        >
                            <option :value="null">Todos</option>
                            <option :value="true">Permitidos</option>
                            <option :value="false">Denegados</option>
                        </select>
                    </FormField>

                    <div class="md:col-span-2 lg:col-span-4">
                        <button
                            type="submit"
                            :disabled="formAccesos.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
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
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </FormField>

                    <FormField
                        label="Fecha Hasta"
                        :error="formMantenimientos.errors.fecha_hasta"
                    >
                        <input
                            v-model="formMantenimientos.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
                        />
                    </FormField>

                    <FormField
                        label="Puerta"
                        :error="formMantenimientos.errors.puerta_id"
                    >
                        <select
                            v-model="formMantenimientos.puerta_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
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
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    🚪 Reporte de Puertas
                </h2>
                <form
                    @submit.prevent="exportarPuertas"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                >
                    <FormField label="Piso" :error="formPuertas.errors.piso_id">
                        <select
                            v-model="formPuertas.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500"
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
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
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
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    roles: Array,
    cargos: Array,
    departamentos: Array,
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
    departamento_id: null,
    activo: null,
});

// Formulario para accesos
const formAccesos = useForm({
    fecha_desde: null,
    fecha_hasta: null,
    user_id: null,
    puerta_id: null,
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

