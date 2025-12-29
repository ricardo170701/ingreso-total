<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Gestionar Permisos: {{ cargo.name }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        Configura los permisos de acceso a puertas para este
                        cargo.
                    </p>
                </div>
                <Link
                    :href="route('cargos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                >
                    Volver
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Formulario básico del cargo -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Información del Cargo
                </h2>
                <form
                    @submit.prevent="submitCargo"
                    class="grid grid-cols-1 gap-4"
                >
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Nombre"
                            :error="formCargo.errors.name"
                        >
                            <input
                                v-model="formCargo.name"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                required
                            />
                        </FormField>
                        <div class="flex items-center gap-6 pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="formCargo.activo"
                                    type="checkbox"
                                    class="h-4 w-4"
                                />
                                <span class="text-sm text-slate-700"
                                    >Activo</span
                                >
                            </label>
                        </div>
                    </div>
                    <FormField
                        label="Descripción"
                        :error="formCargo.errors.description"
                    >
                        <textarea
                            v-model="formCargo.description"
                            rows="2"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        />
                    </FormField>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="formCargo.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50"
                        >
                            {{
                                formCargo.processing
                                    ? "Guardando..."
                                    : "Guardar Cambios"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Agregar Permiso de Piso -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Agregar Permiso de Piso
                </h2>
                <p class="text-sm text-slate-600 mb-4">
                    Asigna permisos de acceso físico por piso. Los usuarios con este cargo podrán acceder a todas las puertas del piso asignado según las reglas de horario y vigencia.
                </p>
                <form @submit.prevent="submitPiso">
                    <FormField
                        label="Piso"
                        :error="formPiso.errors.piso_id"
                    >
                        <select
                            v-model="formPiso.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required
                        >
                            <option value="">Selecciona un piso</option>
                            <option
                                v-for="piso in todosLosPisos"
                                :key="piso.id"
                                :value="piso.id"
                            >
                                {{ piso.nombre }}
                            </option>
                        </select>
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <FormField
                            label="Hora Inicio (opcional)"
                            :error="formPiso.errors.hora_inicio"
                        >
                            <input
                                v-model="formPiso.hora_inicio"
                                type="time"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                        <FormField
                            label="Hora Fin (opcional)"
                            :error="formPiso.errors.hora_fin"
                        >
                            <input
                                v-model="formPiso.hora_fin"
                                type="time"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Fecha Inicio (opcional)"
                            :error="formPiso.errors.fecha_inicio"
                        >
                            <input
                                v-model="formPiso.fecha_inicio"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                        <FormField
                            label="Fecha Fin (opcional)"
                            :error="formPiso.errors.fecha_fin"
                        >
                            <input
                                v-model="formPiso.fecha_fin"
                                type="date"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                        </FormField>
                    </div>

                    <FormField
                        label="Días de la Semana (opcional)"
                        :error="formPiso.errors.dias_semana"
                    >
                        <input
                            v-model="formPiso.dias_semana"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Ej: 1,2,3,4,5 (1=Lunes, 7=Domingo)"
                        />
                        <p class="mt-1 text-xs text-slate-500">
                            Deja vacío para todos los días. Formato: números separados por comas (1-7)
                        </p>
                    </FormField>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="formPiso.processing"
                            class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
                        >
                            {{
                                formPiso.processing
                                    ? "Agregando..."
                                    : "Agregar Permiso"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de pisos asignados -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Pisos con Permiso ({{ pisosAsignados.length }})
                </h2>

                <div
                    v-if="pisosAsignados.length === 0"
                    class="text-center py-8 text-slate-500"
                >
                    No hay pisos asignados a este cargo.
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="piso in pisosAsignados"
                        :key="piso.id"
                        class="border border-slate-200 rounded-lg p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <h3 class="font-semibold text-slate-900">
                                        {{ piso.nombre }}
                                    </h3>
                                    <span
                                        v-if="piso.pivot.activo"
                                        class="px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-700"
                                    >
                                        Activo
                                    </span>
                                    <span
                                        v-else
                                        class="px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700"
                                    >
                                        Inactivo
                                    </span>
                                </div>
                                <div class="text-sm text-slate-600 space-y-1">
                                    <p
                                        v-if="
                                            piso.pivot.hora_inicio ||
                                            piso.pivot.hora_fin
                                        "
                                    >
                                        <span class="font-medium">Horario:</span>
                                        {{ piso.pivot.hora_inicio || "00:00" }} -
                                        {{ piso.pivot.hora_fin || "23:59" }}
                                    </p>
                                    <p v-if="piso.pivot.dias_semana">
                                        <span class="font-medium">Días:</span>
                                        {{ formatDiasSemana(piso.pivot.dias_semana) }}
                                    </p>
                                    <p
                                        v-if="
                                            piso.pivot.fecha_inicio ||
                                            piso.pivot.fecha_fin
                                        "
                                    >
                                        <span class="font-medium">Período:</span>
                                        {{ piso.pivot.fecha_inicio || "Sin inicio" }} -
                                        {{ piso.pivot.fecha_fin || "Sin fin" }}
                                    </p>
                                </div>
                            </div>
                            <button
                                @click="revokePiso(piso.id)"
                                class="px-3 py-1.5 rounded-md border border-red-200 text-red-700 hover:bg-red-50 text-sm"
                            >
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permisos del Sistema -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Permisos del Sistema
                </h2>
                <p class="text-sm text-slate-600 mb-4">
                    Selecciona los permisos del sistema que tendrán los usuarios con este cargo. Estos permisos controlan qué secciones del menú pueden ver.
                </p>

                <!-- Permisos de la Sidebar (Botones del Menú) -->
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                    <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
                        <span>📋</span>
                        <span>Permisos de la Sidebar (Botones del Menú)</span>
                    </h3>
                    <p class="text-xs text-blue-700 mb-3">
                        Estos permisos controlan qué botones aparecen en la barra lateral del menú:
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <label
                            v-for="sidebarPermission in sidebarPermissions"
                            :key="sidebarPermission.id"
                            class="flex items-center gap-2 p-2 rounded-lg hover:bg-blue-100 cursor-pointer border border-blue-200 bg-white"
                        >
                            <input
                                :id="`permission-${sidebarPermission.id}`"
                                v-model="cargoPermissions"
                                type="checkbox"
                                :value="sidebarPermission.id"
                                class="h-4 w-4 rounded border-slate-300 cursor-pointer"
                            />
                            <div class="flex-1">
                                <span class="text-sm font-medium text-slate-900 block">
                                    {{ sidebarPermission.description }}
                                </span>
                                <span class="text-xs text-slate-500">
                                    {{ getSidebarButtonLabel(sidebarPermission.name) }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Otros Permisos agrupados por grupo -->
                <div class="space-y-4">
                    <div
                        v-for="(groupPermissions, groupName) in otherPermissionsGrouped"
                        :key="groupName"
                        class="border border-slate-200 rounded-lg p-4"
                    >
                        <h3 class="text-sm font-semibold text-slate-700 mb-3 pb-2 border-b border-slate-200">
                            {{ formatGroupName(groupName) }}
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <label
                                v-for="permission in groupPermissions"
                                :key="permission.id"
                                class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 cursor-pointer"
                            >
                                <input
                                    :id="`permission-${permission.id}`"
                                    v-model="cargoPermissions"
                                    type="checkbox"
                                    :value="permission.id"
                                    class="h-4 w-4 rounded border-slate-300 cursor-pointer"
                                />
                                <span class="text-sm text-slate-700 flex-1">
                                    {{ permission.description }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botón de guardar -->
                <div class="mt-6 pt-4 border-t border-slate-200 flex justify-end">
                    <button
                        @click="savePermissions"
                        :disabled="savingPermissions"
                        class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
                    >
                        {{
                            savingPermissions
                                ? "Guardando..."
                                : "Guardar Permisos del Sistema"
                        }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";

const props = defineProps({
    cargo: Object,
    pisosAsignados: Array,
    todosLosPisos: Array,
    permissions: Array,
    permissionsGrouped: Object,
});

const formCargo = useForm({
    name: props.cargo.name || "",
    description: props.cargo.description || "",
    activo: !!props.cargo.activo,
});

const formPiso = useForm({
    piso_id: null,
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
    activo: true,
});

const submitCargo = () => {
    formCargo.put(route("cargos.update", { cargo: props.cargo.id }));
};

const submitPiso = () => {
    formPiso.post(route("cargos.pisos.store", { cargo: props.cargo.id }), {
        onSuccess: () => {
            formPiso.reset();
        },
    });
};

const revokePiso = (pisoId) => {
    if (!confirm("¿Eliminar este permiso de piso?")) return;
    router.delete(
        route("cargos.pisos.destroy", {
            cargo: props.cargo.id,
            piso: pisoId,
        })
    );
};

const formatDiasSemana = (dias) => {
    const nombres = {
        1: "Lun",
        2: "Mar",
        3: "Mié",
        4: "Jue",
        5: "Vie",
        6: "Sáb",
        7: "Dom",
    };
    if (!dias) return "Todos";
    return dias
        .split(",")
        .map((d) => nombres[d.trim()] || d.trim())
        .join(", ");
};

// Permisos del sistema
const cargoPermissions = ref([]);
const savingPermissions = ref(false);

// Inicializar permisos del cargo
onMounted(() => {
    cargoPermissions.value = props.cargo.permissions?.map((p) => p.id) || [];
});

const formatGroupName = (groupName) => {
    const names = {
        dashboard: "Dashboard",
        users: "Usuarios",
        puertas: "Puertas",
        cargos: "Permisos/Cargos",
        ingreso: "Ingreso",
        mantenimientos: "Mantenimientos",
    };
    return names[groupName] || groupName;
};

// Permisos que controlan los botones de la sidebar
const sidebarPermissionNames = [
    'view_dashboard',
    'view_users',
    'view_puertas',
    'view_cargos',
    'view_ingreso',
    'view_mantenimientos',
];

// Filtrar permisos de sidebar
const sidebarPermissions = computed(() => {
    return props.permissions.filter(p => sidebarPermissionNames.includes(p.name));
});

// Filtrar otros permisos (excluyendo los de sidebar)
const otherPermissionsGrouped = computed(() => {
    const other = props.permissions.filter(p => !sidebarPermissionNames.includes(p.name));
    const grouped = {};
    other.forEach(permission => {
        const group = permission.group || 'otros';
        if (!grouped[group]) {
            grouped[group] = [];
        }
        grouped[group].push(permission);
    });
    return grouped;
});

const getSidebarButtonLabel = (permissionName) => {
    const labels = {
        'view_dashboard': '📊 Dashboard',
        'view_users': '👥 Usuarios',
        'view_puertas': '🚪 Puertas',
        'view_cargos': '🔐 Permisos',
        'view_ingreso': '📱 Ingreso',
        'view_mantenimientos': '🔧 Mantenimientos',
    };
    return labels[permissionName] || permissionName;
};

const savePermissions = () => {
    savingPermissions.value = true;

    router.put(
        route("cargos.permissions.update", { cargo: props.cargo.id }),
        {
            permissions: cargoPermissions.value || [],
        },
        {
            preserveScroll: true,
            onFinish: () => {
                savingPermissions.value = false;
            },
        }
    );
};
</script>
