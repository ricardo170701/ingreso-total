<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Dependencias
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gestiona las secretarías y sus gerencias.
                    </p>
                </div>
                <Link
                    :href="route('secretarias.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-blue-600 text-white hover:bg-slate-800 dark:hover:bg-blue-700 font-medium transition-colors duration-200"
                >
                    Nueva Secretaría
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <div
                v-if="$page.props.errors?.error"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 transition-colors duration-200"
            >
                {{ $page.props.errors.error }}
            </div>

            <!-- Buscador -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        placeholder="Buscar por nombre de secretaría o piso…"
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
                        <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Secretaría
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Piso
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Gerencias
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr
                                v-for="s in secretarias.data"
                                :key="s.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ s.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100"
                                >
                                    {{ s.nombre }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ s.piso?.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span
                                        class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded text-xs font-medium transition-colors duration-200"
                                    >
                                        {{ s.gerencias_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            s.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                        ]"
                                    >
                                        {{ s.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="
                                                route('secretarias.show', {
                                                    secretaria: s.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route('secretarias.edit', {
                                                    secretaria: s.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 hover:bg-slate-50 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="eliminarSecretaria(s)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 text-sm transition-colors duration-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="secretarias.links && secretarias.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ secretarias.from || 0 }} - {{ secretarias.to || 0 }} de
                        {{ secretarias.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(link, idx) in secretarias.links"
                            :key="idx"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md text-sm border transition-colors duration-200',
                                link.active
                                    ? 'bg-slate-900 dark:bg-blue-600 text-white border-slate-900 dark:border-blue-600'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !link.url ? 'opacity-40 pointer-events-none' : '',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";

const props = defineProps({
    secretarias: Object,
    filters: Object,
});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const applySearch = () => {
    searchForm.get(route("dependencias.index"), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = "";
    applySearch();
};

const eliminarSecretaria = (secretaria) => {
    if (
        !confirm(
            `¿Estás seguro de eliminar la secretaría "${secretaria.nombre}"?`
        )
    )
        return;
    router.delete(
        route("secretarias.destroy", { secretaria: secretaria.id })
    );
};
</script>