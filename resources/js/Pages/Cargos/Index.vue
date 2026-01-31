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
                                    <div class="flex gap-2">
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
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";

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
