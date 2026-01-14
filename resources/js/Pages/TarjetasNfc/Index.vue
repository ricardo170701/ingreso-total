<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-4">
            <div
                v-if="flash?.success"
                class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 px-4 py-3 transition-colors duration-200"
            >
                {{ flash.success }}
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Tarjetas NFC
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gestiona las tarjetas NFC asignadas a visitantes.
                    </p>
                </div>
                <Link
                    :href="route('tarjetas-nfc.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors duration-200"
                >
                    <span>➕</span>
                    <span>Nueva Tarjeta</span>
                </Link>
            </div>

            <!-- Buscador -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        placeholder="Buscar por código, nombre, usuario o cédula…"
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
                        <thead class="bg-slate-50 dark:bg-slate-700">
                            <tr class="text-left text-slate-600 dark:text-slate-300">
                                <th class="px-4 py-3 font-semibold">ID</th>
                                <th class="px-4 py-3 font-semibold">Código</th>
                                <th class="px-4 py-3 font-semibold">Nombre</th>
                                <th class="px-4 py-3 font-semibold">Usuario Asignado</th>
                                <th class="px-4 py-3 font-semibold">Gerencia</th>
                                <th class="px-4 py-3 font-semibold">Fecha Expiración</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
                                <th class="px-4 py-3 font-semibold text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="t in tarjetas.data"
                                :key="t.id"
                                class="text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                            >
                                <td class="px-4 py-3">{{ t.id }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded text-xs font-mono">
                                        {{ t.codigo }}
                                    </code>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ t.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div v-if="t.user">
                                        <div class="font-medium">{{ t.user.name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ t.user.email }}
                                            <span v-if="t.user.n_identidad"> · CC: {{ t.user.n_identidad }}</span>
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 dark:text-slate-500">Sin asignar</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div v-if="t.gerencia">
                                        <div class="font-medium">{{ t.gerencia.secretaria?.nombre || "-" }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ t.gerencia.nombre }}
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ t.fecha_expiracion || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                            t.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                                                : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                        ]"
                                    >
                                        {{ t.activo ? "Activa" : "Inactiva" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('tarjetas-nfc.show', { tarjetaNfc: t.id })"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                        >
                                            Ver
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="tarjetas.data.length === 0">
                                <td
                                    class="px-4 py-10 text-center text-slate-500 dark:text-slate-400"
                                    colspan="8"
                                >
                                    No hay tarjetas NFC registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="tarjetas.links?.length"
                    class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700 transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ tarjetas.from || 0 }} - {{ tarjetas.to || 0 }} de
                        {{ tarjetas.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(l, idx) in tarjetas.links"
                            :key="idx"
                            :href="l.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md text-sm border transition-colors duration-200',
                                l.active
                                    ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !l.url ? 'opacity-40 pointer-events-none' : '',
                            ]"
                            v-html="l.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, usePage, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    tarjetas: Object,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const applySearch = () => {
    searchForm.get(route("tarjetas-nfc.index"), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = "";
    applySearch();
};
</script>
