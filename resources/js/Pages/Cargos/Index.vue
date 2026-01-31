<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Roles y Permisos
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gestiona los roles (antes “cargos”) y sus permisos de acceso a puertas.
                    </p>
                </div>
                <Link
                    :href="route('cargos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                >
                    Nuevo Rol
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Buscador -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        placeholder="Buscar por nombre o descripción…"
                    />
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 font-medium transition-colors duration-200"
                        >
                            Buscar
                        </button>
                        <button
                            type="button"
                            @click="clearSearch"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 transition-colors duration-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Nombre
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Descripción
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Usuarios
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Permiso superior
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700 transition-colors duration-200">
                            <tr
                                v-for="c in cargos.data"
                                :key="c.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ c.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    {{ c.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ c.description || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span
                                        class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded text-xs transition-colors duration-200"
                                    >
                                        {{ c.users_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            c.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                        ]"
                                    >
                                        {{ c.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            c.requiere_permiso_superior
                                                ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
                                                : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400',
                                        ]"
                                    >
                                        {{ c.requiere_permiso_superior ? "Sí" : "No" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Link
                                            :href="
                                                route('cargos.edit', {
                                                    cargo: c.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                        >
                                            Gestionar Permisos
                                        </Link>
                                        <button
                                            type="button"
                                            @click="abrirHistorial(c)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                        >
                                            Historial
                                        </button>
                                        <button
                                            @click="eliminarCargo(c)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 transition-colors duration-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="cargos.data.length === 0">
                                <td
                                    class="px-4 py-10 text-center text-slate-500 dark:text-slate-400"
                                    colspan="7"
                                >
                                    No se encontraron cargos.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="cargos.links?.length"
                    class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ cargos.from || 0 }} - {{ cargos.to || 0 }} de
                        {{ cargos.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(link, idx) in cargos.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md text-sm border transition-colors duration-200',
                                link.active
                                    ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !link.url ? 'opacity-40 pointer-events-none' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación para Eliminar Rol -->
        <div
            v-if="showDeleteModal"
            @click="closeDeleteModal"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Confirmar Eliminación de Rol
                    </h3>
                    <button
                        type="button"
                        @click="closeDeleteModal"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-2">
                            ¿Estás seguro de que deseas eliminar el rol
                            <strong class="text-slate-900 dark:text-slate-100">{{ cargoToDelete?.name }}</strong>?
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                            ⚠️ Todos los usuarios con este rol se quedarán sin rol. Esta acción no se puede deshacer.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label
                            for="password"
                            class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5"
                        >
                            Confirma tu contraseña
                        </label>
                        <input
                            id="password"
                            v-model="deleteForm.password"
                            type="password"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Ingresa tu contraseña"
                            @keyup.enter="confirmDelete"
                        />
                        <div
                            v-if="deleteForm.errors.password"
                            class="mt-1.5 text-xs text-red-600 dark:text-red-400"
                        >
                            {{ deleteForm.errors.password }}
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="closeDeleteModal"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="confirmDelete"
                            :disabled="deleteForm.processing || !deleteForm.password"
                            class="px-4 py-2 rounded-lg bg-red-600 dark:bg-red-700 text-white hover:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors duration-200"
                        >
                            {{ deleteForm.processing ? "Eliminando..." : "Eliminar Rol" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Historial de cambios -->
        <div
            v-if="showHistorialModal"
            @click="closeHistorialModal"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-2xl w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200 max-h-[90vh] flex flex-col"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 shrink-0">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Historial de cambios{{ cargoHistorial ? `: ${cargoHistorial.name}` : "" }}
                    </h3>
                    <button
                        type="button"
                        @click="closeHistorialModal"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>
                <div class="p-4 overflow-auto flex-1">
                    <p v-if="loadingHistorial" class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center">
                        Cargando historial…
                    </p>
                    <template v-else>
                        <p v-if="!historialList || historialList.length === 0" class="text-sm text-slate-500 dark:text-slate-400 py-4 text-center">
                            No hay registros en el historial.
                        </p>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Editor</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Fecha</th>
                                        <th class="px-3 py-2 text-left font-semibold text-slate-700 dark:text-slate-300">Ver</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                    <tr
                                        v-for="h in historialList"
                                        :key="h.id"
                                        class="hover:bg-slate-50 dark:hover:bg-slate-700/50"
                                    >
                                        <td class="px-3 py-2 text-slate-700 dark:text-slate-300">{{ h.editor }}</td>
                                        <td class="px-3 py-2 text-slate-600 dark:text-slate-400">{{ h.fecha }}</td>
                                        <td class="px-3 py-2">
                                            <button
                                                type="button"
                                                @click="verCambios(h)"
                                                class="inline-flex items-center px-2 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs transition-colors duration-200"
                                            >
                                                Ver
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Modal Ver cambios realizados -->
        <div
            v-if="showVerCambiosModal"
            @click="showVerCambiosModal = false"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-[60] p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-6xl w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200 max-h-[90vh] flex flex-col"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700 shrink-0">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Cambios realizados
                    </h3>
                    <button
                        type="button"
                        @click="showVerCambiosModal = false"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>
                <div class="p-6 overflow-y-auto flex-1 min-h-0">
                    <template v-if="cambioSeleccionado">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                            {{ cambioSeleccionado.fecha }} · {{ cambioSeleccionado.editor }}
                            <span
                                v-if="cambioSeleccionado.action === 'created'"
                                class="ml-2 px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs"
                            >
                                Creación
                            </span>
                            <span
                                v-else-if="tienePermisosSistema"
                                class="ml-2 px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs"
                            >
                                Permisos del sistema
                            </span>
                            <span
                                v-if="tienePuertas"
                                class="ml-2 px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 text-xs"
                            >
                                Puertas
                            </span>
                            <span
                                v-if="cambioSeleccionado.action !== 'created' && !tienePermisosSistema && !tienePuertas"
                                class="ml-2 px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-xs"
                            >
                                Actualización
                            </span>
                        </p>

                        <!-- Vista en 4 columnas (permisos/puertas activados/desactivados) -->
                        <div v-if="tienePermisosSistema || tienePuertas" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                            <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/20 p-4">
                                <p class="text-xs font-semibold text-green-700 dark:text-green-400 mb-2">
                                    Permiso activado
                                </p>
                                <ul
                                    v-if="tienePermisosSistema && permisosActivados.length > 0"
                                    class="text-sm text-slate-700 dark:text-slate-300 list-disc list-inside space-y-1"
                                >
                                    <li v-for="(nombre, i) in permisosActivados" :key="`perm-on-${i}`">
                                        {{ nombre }}
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">
                                    Sin cambios.
                                </p>
                            </div>

                            <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/20 p-4">
                                <p class="text-xs font-semibold text-red-700 dark:text-red-400 mb-2">
                                    Permiso desactivado
                                </p>
                                <ul
                                    v-if="tienePermisosSistema && permisosDesactivados.length > 0"
                                    class="text-sm text-slate-700 dark:text-slate-300 list-disc list-inside space-y-1"
                                >
                                    <li v-for="(nombre, i) in permisosDesactivados" :key="`perm-off-${i}`">
                                        {{ nombre }}
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">
                                    Sin cambios.
                                </p>
                            </div>

                            <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/20 p-4">
                                <p class="text-xs font-semibold text-green-700 dark:text-green-400 mb-2">
                                    Puertas activadas
                                </p>
                                <ul
                                    v-if="tienePuertas && puertasActivadas.length > 0"
                                    class="text-sm text-slate-700 dark:text-slate-300 list-disc list-inside space-y-1"
                                >
                                    <li v-for="(nombre, i) in puertasActivadas" :key="`door-on-${i}`">
                                        {{ nombre }}
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">
                                    Sin cambios.
                                </p>
                            </div>

                            <div class="rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/20 p-4">
                                <p class="text-xs font-semibold text-red-700 dark:text-red-400 mb-2">
                                    Puertas desactivadas
                                </p>
                                <ul
                                    v-if="tienePuertas && puertasDesactivadas.length > 0"
                                    class="text-sm text-slate-700 dark:text-slate-300 list-disc list-inside space-y-1"
                                >
                                    <li v-for="(nombre, i) in puertasDesactivadas" :key="`door-off-${i}`">
                                        {{ nombre }}
                                    </li>
                                </ul>
                                <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">
                                    Sin cambios.
                                </p>
                            </div>
                        </div>

                        <!-- Datos del rol: nombre, descripción, activo, permiso superior -->
                        <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 mb-2">
                                Datos del rol
                            </p>
                            <ul class="space-y-2 text-sm">
                                <li
                                    v-for="linea in lineasCambio"
                                    :key="linea.campo"
                                    class="flex flex-wrap gap-x-2 items-baseline border-b border-slate-100 dark:border-slate-700 pb-2 last:border-0"
                                >
                                    <span class="font-medium text-slate-700 dark:text-slate-300 shrink-0">{{ linea.campo }}:</span>
                                    <span class="text-slate-600 dark:text-slate-400">{{ linea.texto }}</span>
                                </li>
                                <li v-if="lineasCambio.length === 0" class="text-slate-500 dark:text-slate-400 italic text-sm">
                                    Sin cambios en los datos del rol.
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    cargos: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const showDeleteModal = ref(false);
const cargoToDelete = ref(null);

const deleteForm = useForm({
    password: "",
});

// Historial
const showHistorialModal = ref(false);
const cargoHistorial = ref(null);
const historialList = ref([]);
const loadingHistorial = ref(false);
const showVerCambiosModal = ref(false);
const cambioSeleccionado = ref(null);

const LABELS_CAMPO = {
    name: "Nombre",
    description: "Descripción",
    activo: "Activo",
    requiere_permiso_superior: "Permiso superior",
};

function formatValor(campo, valor) {
    if (valor === null || valor === undefined) return "—";
    if (campo === "activo" || campo === "requiere_permiso_superior") return valor ? "Sí" : "No";
    return String(valor);
}

// ¿Este cambio incluye permisos del sistema / puertas?
const tienePermisosSistema = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h) return false;
    return h.old_permission_names !== undefined || h.new_permission_names !== undefined;
});

const tienePuertas = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h) return false;
    return h.old_puerta_names !== undefined || h.new_puerta_names !== undefined;
});

// Solo cambios: permisos activados (agregados) y desactivados (quitados)
const permisosActivados = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h || !tienePermisosSistema.value) return [];
    const oldNames = toArray(h.old_permission_names);
    const newNames = toArray(h.new_permission_names);
    return newNames.filter((n) => !oldNames.includes(n));
});

const permisosDesactivados = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h || !tienePermisosSistema.value) return [];
    const oldNames = toArray(h.old_permission_names);
    const newNames = toArray(h.new_permission_names);
    return oldNames.filter((n) => !newNames.includes(n));
});

// Normalizar a array (por si el backend envía objeto con claves numéricas)
function toArray(val) {
    if (Array.isArray(val)) return val;
    if (val && typeof val === "object") return Object.values(val);
    return [];
}

// Solo cambios: puertas activadas (agregadas) y desactivadas (quitadas)
const puertasActivadas = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h || !tienePuertas.value) return [];
    const oldNames = toArray(h.old_puerta_names);
    const newNames = toArray(h.new_puerta_names);
    return newNames.filter((n) => !oldNames.includes(n));
});

const puertasDesactivadas = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h || !tienePuertas.value) return [];
    const oldNames = toArray(h.old_puerta_names);
    const newNames = toArray(h.new_puerta_names);
    return oldNames.filter((n) => !newNames.includes(n));
});

// Campos del cargo que cambiaron (nombre, descripción, activo, permiso superior)
const lineasCambio = computed(() => {
    const h = cambioSeleccionado.value;
    if (!h) return [];
    const lineas = [];
    const action = h.action || "updated";
    const newData = h.new_data || {};
    const oldData = h.old_data || {};
    for (const [key, label] of Object.entries(LABELS_CAMPO)) {
        const nuevo = newData[key];
        const viejo = oldData[key];
        if (action === "created") {
            lineas.push({ campo: label, texto: formatValor(key, nuevo) });
        } else if (viejo !== nuevo) {
            lineas.push({
                campo: label,
                texto: `${formatValor(key, viejo)} → ${formatValor(key, nuevo)}`,
            });
        }
    }
    return lineas;
});

async function abrirHistorial(cargo) {
    cargoHistorial.value = cargo;
    showHistorialModal.value = true;
    historialList.value = [];
    loadingHistorial.value = true;
    try {
        const url = route("cargos.historial", { cargo: cargo.id });
        const res = await fetch(url, { headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" } });
        const data = await res.json();
        historialList.value = data.historial || [];
    } catch (e) {
        historialList.value = [];
    } finally {
        loadingHistorial.value = false;
    }
}

function closeHistorialModal() {
    showHistorialModal.value = false;
    cargoHistorial.value = null;
    historialList.value = [];
}

function verCambios(item) {
    cambioSeleccionado.value = item;
    showVerCambiosModal.value = true;
}

const applySearch = () => {
    searchForm.get(route("cargos.index"), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = "";
    applySearch();
};

const eliminarCargo = (cargo) => {
    cargoToDelete.value = cargo;
    deleteForm.reset();
    deleteForm.clearErrors();
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    cargoToDelete.value = null;
    deleteForm.reset();
    deleteForm.clearErrors();
};

const confirmDelete = () => {
    if (!cargoToDelete.value) return;

    deleteForm.delete(route("cargos.destroy", { cargo: cargoToDelete.value.id }), {
        onSuccess: () => {
            closeDeleteModal();
        },
        onError: () => {
            // Los errores ya se muestran en el formulario
        },
    });
};
</script>
