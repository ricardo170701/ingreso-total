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
                        Usuarios
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Lista de usuarios y administración básica.
                    </p>
                </div>
                <Link
                    :href="route('usuarios.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors duration-200"
                >
                    <span>➕</span>
                    <span>Nuevo</span>
                </Link>
            </div>

            <!-- Buscador -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        placeholder="Buscar por nombre, email, identidad o caso…"
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
                                <th class="px-4 py-3 font-semibold">Nombre</th>
                                <th class="px-4 py-3 font-semibold">Email</th>
                                <th class="px-4 py-3 font-semibold">Tipo de vinculación</th>
                                <th class="px-4 py-3 font-semibold">Rol / Cargo</th>
                                <th class="px-4 py-3 font-semibold">Secretaría / Gerencia</th>
                                <th class="px-4 py-3 font-semibold">Activo</th>
                                <th class="px-4 py-3 font-semibold text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="u in users.data"
                                :key="u.id"
                                class="text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                            >
                                <td class="px-4 py-3">{{ u.id }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-600 shrink-0">
                                            <img
                                                v-if="u.foto_perfil"
                                                :src="storageUrl(u.foto_perfil)"
                                                alt="Foto"
                                                class="w-full h-full object-cover"
                                                loading="lazy"
                                                decoding="async"
                                            />
                                            <div v-else class="w-full h-full flex items-center justify-center text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                {{ initials(u.name || u.email) }}
                                            </div>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-medium text-slate-900 dark:text-slate-100 truncate">
                                                {{ u.name || "-" }}
                                            </div>
                                            <div v-if="u.nombre || u.apellido" class="text-xs text-slate-500 dark:text-slate-400 truncate">
                                                {{ [u.nombre, u.apellido].filter(Boolean).join(' ') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ u.email }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ formatTipoVinculacion(u.role?.name) || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div class="min-w-0">
                                        <div class="truncate">
                                            {{ u.cargo?.name || "-" }}
                                        </div>
                                        <div
                                            v-if="u.cargo_texto"
                                            class="text-xs text-slate-500 dark:text-slate-400 truncate"
                                        >
                                            Cargo: {{ u.cargo_texto }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div v-if="u.gerencia">
                                        <div class="font-medium">{{ u.gerencia.secretaria?.nombre || "-" }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ u.gerencia.nombre }}
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-500 dark:text-slate-400 italic">Despacho</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                            u.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                                                : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                        ]"
                                    >
                                        {{ u.activo ? "Sí" : "No" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Link
                                        :href="
                                            route('usuarios.show', {
                                                user: u.id,
                                            })
                                        "
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td
                                    class="px-4 py-10 text-center text-slate-500 dark:text-slate-400"
                                    colspan="8"
                                >
                                    No hay usuarios.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="users.links?.length"
                    class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700 transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ users.from || 0 }} - {{ users.to || 0 }} de
                        {{ users.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(l, idx) in users.links"
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
    users: Object,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const applySearch = () => {
    searchForm.get(route("usuarios.index"), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = "";
    applySearch();
};

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const initials = (text) => {
    const t = String(text || "").trim();
    if (!t) return "U";
    const parts = t.split(/\s+/).filter(Boolean);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return (parts[0][0] || "U").toUpperCase();
};

const formatTipoVinculacion = (name) => {
    const map = {
        visitante: "Visitante",
        servidor_publico: "Servidor público",
        proveedor: "Proveedor",
        contratista: "Proveedor", // Compatibilidad
        // compatibilidad histórica
        funcionario: "Servidor público",
    };
    return map[name] || name || null;
};
</script>
