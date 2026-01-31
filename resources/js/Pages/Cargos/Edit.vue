<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-xl font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Gestionar Permisos (Rol): {{ cargo.name }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Configura los permisos de acceso a puertas para este
                        rol.
                    </p>
                </div>
                <Link
                    :href="route('cargos.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Formulario básico del cargo -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200"
            >
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4"
                >
                    Información del Rol
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
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                required
                            />
                        </FormField>
                        <div class="flex items-center gap-6 pt-7">
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="formCargo.activo"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400"
                                />
                                <span
                                    class="text-sm text-slate-700 dark:text-slate-300"
                                    >Activo</span
                                >
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input
                                    v-model="formCargo.requiere_permiso_superior"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400"
                                />
                                <span
                                    class="text-sm text-slate-700 dark:text-slate-300"
                                    >Permiso superior</span
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
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                    </FormField>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="formCargo.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors duration-200"
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

            <!-- Permisos a puertas (acordeón por piso, selección por puerta) -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200"
            >
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4"
                >
                    Permisos a puertas
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                    Marca las puertas a las que tendrá acceso este cargo. Puedes
                    abrir cada piso y marcar puertas una a una, o marcar el piso
                    para seleccionar todas sus puertas. Al finalizar, pulsa
                    Guardar.
                </p>

                <div
                    v-if="!pisosConPuertas || pisosConPuertas.length === 0"
                    class="text-center py-8 text-slate-500 dark:text-slate-400"
                >
                    No hay pisos con puertas activas.
                </div>

                <div
                    v-else
                    class="space-y-1 border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden transition-colors duration-200"
                >
                    <div
                        v-for="piso in pisosConPuertas"
                        :key="piso.id"
                        class="border-b border-slate-200 dark:border-slate-700 last:border-b-0 transition-colors duration-200"
                    >
                        <!-- Cabecera acordeón: piso + checkbox "seleccionar todo el piso" -->
                        <div
                            class="flex items-center gap-3 px-4 py-3 bg-slate-50 dark:bg-slate-700/40 hover:bg-slate-100 dark:hover:bg-slate-700/60 cursor-pointer transition-colors duration-200"
                            @click="toggleAccordion(piso.id)"
                        >
                            <button
                                type="button"
                                class="shrink-0 w-8 h-8 flex items-center justify-center rounded text-slate-500 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors duration-200"
                                :aria-label="
                                    openPisoId === piso.id ? 'Cerrar' : 'Abrir'
                                "
                            >
                                <span
                                    class="transition-transform duration-200"
                                    :class="
                                        openPisoId === piso.id
                                            ? 'rotate-90'
                                            : ''
                                    "
                                >
                                    ▶
                                </span>
                            </button>
                            <label
                                class="flex items-center gap-2 flex-1 cursor-pointer min-w-0"
                                @click.stop
                            >
                                <input
                                    type="checkbox"
                                    :checked="isPisoAllSelected(piso)"
                                    :indeterminate.prop="
                                        isPisoIndeterminate(piso)
                                    "
                                    class="h-4 w-4 text-green-600 dark:text-green-500 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-400 shrink-0"
                                    @change="togglePisoAll(piso)"
                                />
                                <span
                                    class="font-medium text-slate-900 dark:text-slate-100 truncate"
                                >
                                    {{ piso.nombre }}
                                </span>
                                <span
                                    v-if="(piso.puertas || []).length"
                                    class="text-xs text-slate-500 dark:text-slate-400 shrink-0"
                                >
                                    ({{ countSelectedInPiso(piso) }}/{{
                                        piso.puertas.length
                                    }})
                                </span>
                            </label>
                        </div>

                        <!-- Contenido: lista de puertas -->
                        <div
                            v-show="openPisoId === piso.id"
                            class="px-4 pb-3 pt-1 pl-12 bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 transition-colors duration-200"
                        >
                            <div
                                v-if="
                                    !piso.puertas || piso.puertas.length === 0
                                "
                                class="text-sm text-slate-500 dark:text-slate-400 py-2"
                            >
                                Sin puertas activas
                            </div>
                            <div v-else class="space-y-1.5 py-2">
                                <label
                                    v-for="puerta in piso.puertas || []"
                                    :key="puerta.id"
                                    class="flex items-center gap-2 py-1.5 px-2 rounded hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors duration-200"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="
                                            selectedPuertaIds.includes(
                                                puerta.id,
                                            )
                                        "
                                        class="h-4 w-4 text-green-600 dark:text-green-500 border-slate-300 dark:border-slate-600 rounded focus:ring-green-500 dark:focus:ring-green-400 shrink-0"
                                        @change="togglePuerta(puerta.id)"
                                    />
                                    <span
                                        class="text-sm text-slate-700 dark:text-slate-300"
                                    >
                                        {{ puerta.nombre }}
                                        <span
                                            v-if="puerta.codigo_fisico"
                                            class="text-slate-500 dark:text-slate-400 text-xs ml-1"
                                        >
                                            ({{ puerta.codigo_fisico }})
                                        </span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-slate-200 dark:border-slate-700"
                >
                    <span
                        class="text-sm text-slate-500 dark:text-slate-400 mr-auto"
                    >
                        {{ selectedPuertaIds.length }} puerta(s) seleccionada(s)
                    </span>
                    <button
                        type="button"
                        :disabled="formPuertasSync.processing"
                        class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 disabled:opacity-50 transition-colors duration-200"
                        @click="submitSyncPuertas"
                    >
                        {{
                            formPuertasSync.processing
                                ? "Guardando..."
                                : "Guardar permisos a puertas"
                        }}
                    </button>
                </div>
            </div>

            <!-- Permisos del Sistema -->
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200"
            >
                <h2
                    class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4"
                >
                    Permisos del Sistema
                </h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">
                    Selecciona los permisos del sistema que tendrán los usuarios
                    con este cargo. Estos permisos controlan qué secciones del
                    menú pueden ver.
                </p>

                <!-- Permisos de la Sidebar (Botones del Menú) -->
                <div
                    class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg transition-colors duration-200"
                >
                    <div class="flex items-center justify-between mb-3">
                        <h3
                            class="text-sm font-semibold text-blue-900 dark:text-blue-300 flex items-center gap-2"
                        >
                            <span>📋</span>
                            <span
                                >Permisos de la Sidebar (Botones del Menú)</span
                            >
                            <span
                                class="text-xs font-normal text-blue-800/80 dark:text-blue-200/80"
                            >
                                ({{ sidebarPermissions.length }})
                            </span>
                        </h3>
                        <button
                            type="button"
                            @click="toggleSelectAllSidebar"
                            class="text-xs px-2 py-1 rounded border border-blue-300 dark:border-blue-700 bg-white dark:bg-slate-700 text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/50 transition-colors duration-200"
                        >
                            {{
                                allSidebarSelected
                                    ? "Deseleccionar todo"
                                    : "Seleccionar todo"
                            }}
                        </button>
                    </div>
                    <p class="text-xs text-blue-700 dark:text-blue-300 mb-3">
                        Estos permisos controlan qué botones aparecen en la
                        barra lateral del menú:
                    </p>
                    <p
                        class="text-xs text-blue-700/80 dark:text-blue-300/90 mb-3"
                    >
                        Los permisos de esta lista se muestran
                        <b>solo aquí</b> (no se repiten en los grupos de abajo)
                        para evitar confusión.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <label
                            v-for="sidebarPermission in sidebarPermissions"
                            :key="sidebarPermission.id"
                            class="flex items-center gap-2 p-2 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 cursor-pointer border border-blue-200 dark:border-blue-800 bg-white dark:bg-slate-700 transition-colors duration-200"
                        >
                            <input
                                :id="`permission-${sidebarPermission.id}`"
                                v-model="cargoPermissions"
                                type="checkbox"
                                :value="sidebarPermission.id"
                                class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400 cursor-pointer"
                            />
                            <div class="flex-1">
                                <span
                                    class="text-sm font-medium text-slate-900 dark:text-slate-100 block"
                                >
                                    {{ sidebarPermission.description }}
                                </span>
                                <span
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{
                                        getSidebarButtonLabel(
                                            sidebarPermission.name,
                                        )
                                    }}
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Otros Permisos agrupados por grupo -->
                <div class="space-y-4">
                    <div
                        v-for="(
                            groupPermissions, groupName
                        ) in otherPermissionsGrouped"
                        :key="groupName"
                        class="border border-slate-200 dark:border-slate-700 rounded-lg p-4 transition-colors duration-200"
                    >
                        <div
                            class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200 dark:border-slate-700"
                        >
                            <h3
                                class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                            >
                                {{ formatGroupName(groupName) }}
                            </h3>
                            <button
                                type="button"
                                @click="
                                    toggleSelectAllGroup(
                                        groupName,
                                        groupPermissions,
                                    )
                                "
                                class="text-xs px-2 py-1 rounded border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-600 transition-colors duration-200"
                            >
                                {{
                                    allGroupSelected(
                                        groupName,
                                        groupPermissions,
                                    )
                                        ? "Deseleccionar todo"
                                        : "Seleccionar todo"
                                }}
                            </button>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                        >
                            <label
                                v-for="permission in groupPermissions"
                                :key="permission.id"
                                class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700/50 cursor-pointer transition-colors duration-200"
                            >
                                <input
                                    :id="`permission-${permission.id}`"
                                    v-model="cargoPermissions"
                                    type="checkbox"
                                    :value="permission.id"
                                    class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400 cursor-pointer"
                                />
                                <span
                                    class="text-sm text-slate-700 dark:text-slate-300 flex-1"
                                >
                                    {{ permission.description }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Botón de guardar -->
                <div
                    class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end transition-colors duration-200"
                >
                    <button
                        @click="showConfirmModal = true"
                        :disabled="savingPermissions"
                        class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
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

        <!-- Mensaje de éxito (notificación temporal) -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-x-full"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-full"
        >
            <div
                v-if="showSuccessMessage"
                class="fixed top-4 right-4 z-50 max-w-md"
            >
                <div
                    class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow-lg flex items-center gap-3"
                >
                    <div class="shrink-0">
                        <span class="text-2xl">✅</span>
                    </div>
                    <div class="flex-1">
                        <p
                            class="text-sm font-medium text-green-800 dark:text-green-200"
                        >
                            Permisos guardados exitosamente
                        </p>
                        <p
                            class="text-xs text-green-700 dark:text-green-300 mt-1"
                        >
                            Los cambios se han aplicado al cargo "{{
                                cargo.name
                            }}"
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="showSuccessMessage = false"
                        class="shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Modal de Confirmación -->
        <div
            v-if="showConfirmModal"
            @click="showConfirmModal = false"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="w-full max-w-md bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-xl"
                @click.stop
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700"
                >
                    <h3
                        class="text-lg font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Confirmar Cambios
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
                        ¿Estás seguro de que deseas guardar los cambios en los
                        permisos del sistema para el cargo
                        <strong class="text-slate-900 dark:text-slate-100">{{
                            cargo.name
                        }}</strong
                        >?
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Se actualizarán los permisos asignados a este cargo. Los
                        usuarios con este cargo verán reflejados los cambios
                        inmediatamente.
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
                            @click="confirmSavePermissions"
                            :disabled="savingPermissions"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                savingPermissions
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
import FormField from "@/Components/FormField.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, onMounted, computed, Transition } from "vue";

const props = defineProps({
    cargo: Object,
    pisosAsignados: Array,
    puertasAsignadas: { type: Array, default: () => [] },
    todosLosPisos: Array,
    pisosConPuertas: { type: Array, default: () => [] },
    permissions: Array,
    permissionsGrouped: Object,
});

// Acordeón: piso abierto (solo uno a la vez)
const openPisoId = ref(null);

// IDs de puertas seleccionadas (permisos por puerta; inicial desde backend)
const selectedPuertaIds = ref((props.puertasAsignadas || []).map((p) => p.id));

// Sincronizar lista completa de puertas (PUT)
const formPuertasSync = useForm({
    puertas: [],
});

const formCargo = useForm({
    name: props.cargo.name || "",
    description: props.cargo.description || "",
    activo: !!props.cargo.activo,
    requiere_permiso_superior: !!props.cargo.requiere_permiso_superior,
});

const submitCargo = () => {
    formCargo.put(route("cargos.update", { cargo: props.cargo.id }));
};

function toggleAccordion(pisoId) {
    openPisoId.value = openPisoId.value === pisoId ? null : pisoId;
}

function puertasDelPiso(piso) {
    return piso.puertas || [];
}

function isPisoAllSelected(piso) {
    const puertas = puertasDelPiso(piso);
    if (puertas.length === 0) return false;
    return puertas.every((puerta) =>
        selectedPuertaIds.value.includes(puerta.id),
    );
}

function isPisoIndeterminate(piso) {
    const puertas = puertasDelPiso(piso);
    if (puertas.length === 0) return false;
    const selected = puertas.filter((puerta) =>
        selectedPuertaIds.value.includes(puerta.id),
    ).length;
    return selected > 0 && selected < puertas.length;
}

function countSelectedInPiso(piso) {
    return puertasDelPiso(piso).filter((puerta) =>
        selectedPuertaIds.value.includes(puerta.id),
    ).length;
}

function togglePisoAll(piso) {
    const puertas = puertasDelPiso(piso);
    const ids = puertas.map((p) => p.id);
    const allSelected = ids.every((id) => selectedPuertaIds.value.includes(id));
    if (allSelected) {
        selectedPuertaIds.value = selectedPuertaIds.value.filter(
            (id) => !ids.includes(id),
        );
    } else {
        const combined = [...new Set([...selectedPuertaIds.value, ...ids])];
        selectedPuertaIds.value = combined;
    }
}

function togglePuerta(puertaId) {
    const idx = selectedPuertaIds.value.indexOf(puertaId);
    if (idx === -1) {
        selectedPuertaIds.value = [...selectedPuertaIds.value, puertaId];
    } else {
        selectedPuertaIds.value = selectedPuertaIds.value.filter(
            (id) => id !== puertaId,
        );
    }
}

function submitSyncPuertas() {
    formPuertasSync.puertas = [...selectedPuertaIds.value];
    // URL explícita para evitar error de Ziggy si la ruta no está en la lista (p. ej. tras deploy o caché)
    const url = `/cargos/${props.cargo.id}/puertas`;
    formPuertasSync.put(url, {
        preserveScroll: true,
        onSuccess: () => {
            formPuertasSync.reset();
        },
    });
}

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
const showConfirmModal = ref(false);
const showSuccessMessage = ref(false);

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
        tarjetas_nfc: "Tarjetas NFC",
        ups: "UPS",
        departamentos: "Dependencias",
        reportes: "Reportes",
        soporte: "Soporte",
        protocolo: "Protocolo",
    };
    return names[groupName] || groupName;
};

// Permisos que controlan los botones de la sidebar
const sidebarPermissionNames = [
    "view_dashboard",
    "view_users",
    "view_puertas",
    "view_cargos",
    "view_ingreso",
    "view_tarjetas_nfc",
    "view_ups",
    "view_departamentos",
    "view_reportes",
    "view_protocolo",
    "view_soporte",
];

// Filtrar permisos de sidebar
const sidebarPermissions = computed(() => {
    const mapByName = new Map(
        (props.permissions || []).map((p) => [p.name, p]),
    );
    // Mantener orden según el menú
    return sidebarPermissionNames
        .map((name) => mapByName.get(name))
        .filter(Boolean);
});

// Filtrar otros permisos (excluyendo los de sidebar y grupos deprecados)
const otherPermissionsGrouped = computed(() => {
    const other = (props.permissions || []).filter(
        (p) => !sidebarPermissionNames.includes(p.name),
    );

    const grouped = {};
    other.forEach((permission) => {
        const group = permission.group || "otros";
        // Filtrar grupos deprecados (zonas y roles)
        if (group === "zonas" || group === "roles") {
            return; // No mostrar permisos de grupos deprecados
        }
        if (!grouped[group]) grouped[group] = [];
        grouped[group].push(permission);
    });

    // Ordenar permisos dentro de cada grupo por descripción
    Object.keys(grouped).forEach((k) => {
        grouped[k] = grouped[k].slice().sort((a, b) => {
            const ad = String(a?.description || a?.name || "");
            const bd = String(b?.description || b?.name || "");
            return ad.localeCompare(bd, "es");
        });
    });

    // Ordenar grupos por un orden lógico (y luego alfabético)
    const groupOrder = [
        "dashboard",
        "users",
        "puertas",
        "cargos",
        "ingreso",
        "tarjetas_nfc",
        "ups",
        "departamentos",
        "reportes",
        "mantenimientos",
        "protocolo",
        "soporte",
        "otros",
    ];

    const ordered = {};
    const keys = Object.keys(grouped);
    keys.sort((a, b) => {
        const ia = groupOrder.indexOf(a);
        const ib = groupOrder.indexOf(b);
        if (ia === -1 && ib === -1) return a.localeCompare(b, "es");
        if (ia === -1) return 1;
        if (ib === -1) return -1;
        return ia - ib;
    });
    keys.forEach((k) => (ordered[k] = grouped[k]));
    return ordered;
});

const getSidebarButtonLabel = (permissionName) => {
    const labels = {
        view_dashboard: "📊 Dashboard",
        view_users: "👥 Usuarios",
        view_puertas: "🚪 Puertas",
        view_cargos: "🔐 Permisos",
        view_ingreso: "📱 Ingreso",
        view_tarjetas_nfc: "💳 Tarjetas NFC",
        view_ups: "🔋 UPS",
        view_departamentos: "🏢 Dependencias",
        view_reportes: "📊 Reportes",
        view_protocolo: "🚨 Protocolo",
        view_soporte: "❓ Soporte",
    };
    return labels[permissionName] || permissionName;
};

// Verificar si todos los permisos de sidebar están seleccionados
const allSidebarSelected = computed(() => {
    if (sidebarPermissions.value.length === 0) return false;
    const sidebarIds = sidebarPermissions.value.map((p) => p.id);
    return sidebarIds.every((id) => cargoPermissions.value.includes(id));
});

// Seleccionar/deseleccionar todos los permisos de sidebar
const toggleSelectAllSidebar = () => {
    const sidebarIds = sidebarPermissions.value.map((p) => p.id);
    if (allSidebarSelected.value) {
        // Deseleccionar todos
        cargoPermissions.value = cargoPermissions.value.filter(
            (id) => !sidebarIds.includes(id),
        );
    } else {
        // Seleccionar todos (agregar los que faltan)
        const missing = sidebarIds.filter(
            (id) => !cargoPermissions.value.includes(id),
        );
        cargoPermissions.value = [...cargoPermissions.value, ...missing];
    }
};

// Verificar si todos los permisos de un grupo están seleccionados
const allGroupSelected = (groupName, groupPermissions) => {
    if (!groupPermissions || groupPermissions.length === 0) return false;
    const groupIds = groupPermissions.map((p) => p.id);
    return groupIds.every((id) => cargoPermissions.value.includes(id));
};

// Seleccionar/deseleccionar todos los permisos de un grupo
const toggleSelectAllGroup = (groupName, groupPermissions) => {
    if (!groupPermissions || groupPermissions.length === 0) return;
    const groupIds = groupPermissions.map((p) => p.id);
    const allSelected = allGroupSelected(groupName, groupPermissions);

    if (allSelected) {
        // Deseleccionar todos del grupo
        cargoPermissions.value = cargoPermissions.value.filter(
            (id) => !groupIds.includes(id),
        );
    } else {
        // Seleccionar todos del grupo (agregar los que faltan)
        const missing = groupIds.filter(
            (id) => !cargoPermissions.value.includes(id),
        );
        cargoPermissions.value = [...cargoPermissions.value, ...missing];
    }
};

const confirmSavePermissions = () => {
    showConfirmModal.value = false;
    savePermissions();
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
            onSuccess: () => {
                savingPermissions.value = false;
                showSuccessMessage.value = true;
                // Ocultar el mensaje después de 5 segundos
                setTimeout(() => {
                    showSuccessMessage.value = false;
                }, 5000);
            },
            onFinish: () => {
                savingPermissions.value = false;
            },
        },
    );
};
</script>
