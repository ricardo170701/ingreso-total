<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">UPS</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Registro y mantenimiento de UPS.
                    </p>
                </div>
                <Link
                    v-if="hasPermission('create_ups')"
                    :href="route('ups.create')"
                    class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 font-medium transition-colors duration-200"
                >
                    Nueva UPS
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form
                    @submit.prevent="aplicarFiltros"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4"
                >
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Piso
                        </label>
                        <select
                            v-model="filtrosForm.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        >
                            <option :value="null">Todos</option>
                            <option v-for="p in pisos" :key="p.id" :value="p.id">
                                {{ p.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                            Buscar
                        </label>
                        <input
                            v-model="filtrosForm.q"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            placeholder="Código, nombre, marca, modelo, serial..."
                        />
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="flex-1 px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 font-medium transition-colors duration-200"
                        >
                            Aplicar
                        </button>
                        <button
                            type="button"
                            @click="limpiarFiltros"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tarjetas -->
            <div
                v-if="ups.data.length > 0"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
            >
                <div
                    v-for="u in ups.data"
                    :key="u.id"
                    class="group bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-600 transition-colors duration-200"
                >
                    <div class="relative">
                        <div class="aspect-video bg-slate-100 dark:bg-slate-700 relative overflow-hidden transition-colors duration-200">
                            <img
                                v-if="u.foto"
                                :src="storageUrl(u.foto)"
                                alt="Foto UPS"
                                loading="lazy"
                                decoding="async"
                                class="w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-300"
                            />
                            <div
                                v-else
                                class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500 text-sm"
                            >
                                <div class="text-center">
                                    <div class="text-3xl leading-none">🔋</div>
                                    <div class="mt-1">Sin foto</div>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-linear-to-t from-black/35 via-black/0 to-black/0"></div>
                        </div>
                        <span
                            :class="[
                                'absolute top-3 right-3 px-2.5 py-1 rounded-full text-xs font-semibold backdrop-blur border shadow-sm transition-colors duration-200',
                                u.activo
                                    ? 'bg-green-50/90 dark:bg-green-900/50 text-green-700 dark:text-green-400 border-green-200 dark:border-green-800'
                                    : 'bg-slate-50/90 dark:bg-slate-700/90 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-600',
                            ]"
                        >
                            {{ u.activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-base font-semibold text-slate-900 dark:text-slate-100 leading-snug line-clamp-2">
                                    {{ u.nombre }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ u.codigo }}</span>
                                    <span class="text-slate-300 dark:text-slate-600">·</span>
                                    <span>{{ u.piso?.nombre || "Sin piso" }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs text-slate-700 dark:text-slate-300 transition-colors duration-200">
                                <span class="text-slate-500 dark:text-slate-400">Marca:</span> {{ u.marca || "-" }}
                            </span>
                            <span class="px-2 py-1 rounded-lg bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-xs text-slate-700 dark:text-slate-300 transition-colors duration-200">
                                <span class="text-slate-500 dark:text-slate-400">Modelo:</span> {{ u.modelo || "-" }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <Link
                                :href="route('ups.show', { ups: u.id })"
                                class="text-center px-3 py-2 rounded-xl border border-blue-200 dark:border-blue-700 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-sm font-semibold transition-colors duration-200"
                            >
                                Ver
                            </Link>
                            <Link
                                v-if="hasPermission('edit_ups')"
                                :href="route('ups.edit', { ups: u.id })"
                                class="text-center px-3 py-2 rounded-xl bg-amber-600 dark:bg-amber-700 text-white hover:bg-amber-700 dark:hover:bg-amber-600 text-sm font-semibold transition-colors duration-200"
                            >
                                Editar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-10 text-center text-slate-600 dark:text-slate-400 transition-colors duration-200"
            >
                <p class="font-semibold text-slate-900 dark:text-slate-100">No hay UPS registradas</p>
                <p class="text-sm mt-1">Crea una UPS para comenzar.</p>
            </div>

            <!-- Paginación -->
            <div
                v-if="ups.links?.length"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 flex flex-wrap gap-2 transition-colors duration-200"
            >
                <Link
                    v-for="(link, idx) in ups.links"
                    :key="idx"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-2 rounded-md border text-sm transition-colors duration-200',
                        link.active ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-700',
                        !link.url ? 'opacity-50 pointer-events-none' : 'hover:bg-slate-50 dark:hover:bg-slate-700',
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { reactive, computed } from "vue";

const props = defineProps({
    ups: Object,
    pisos: Array,
    filtros: Object,
});

const page = usePage();
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);

const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const filtrosForm = reactive({
    piso_id: props.filtros?.piso_id ?? null,
    q: props.filtros?.q ?? "",
});

const aplicarFiltros = () => {
    router.get(
        route("ups.index"),
        {
            piso_id: filtrosForm.piso_id,
            q: filtrosForm.q || null,
        },
        { preserveScroll: true, preserveState: true }
    );
};

const limpiarFiltros = () => {
    filtrosForm.piso_id = null;
    filtrosForm.q = "";
    router.get(route("ups.index"), {}, { preserveScroll: true, preserveState: true });
};
</script>


