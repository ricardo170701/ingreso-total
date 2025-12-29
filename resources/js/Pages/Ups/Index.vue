<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">UPS</h1>
                    <p class="text-sm text-slate-600">
                        Registro y mantenimiento de UPS.
                    </p>
                </div>
                <Link
                    v-if="hasPermission('create_ups')"
                    :href="route('ups.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nueva UPS
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Filtros -->
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <form
                    @submit.prevent="aplicarFiltros"
                    class="grid grid-cols-1 md:grid-cols-3 gap-4"
                >
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Piso
                        </label>
                        <select
                            v-model="filtrosForm.piso_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                            <option :value="null">Todos</option>
                            <option v-for="p in pisos" :key="p.id" :value="p.id">
                                {{ p.nombre }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Buscar
                        </label>
                        <input
                            v-model="filtrosForm.q"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            placeholder="Código, nombre, marca, modelo, serial..."
                        />
                    </div>
                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="flex-1 px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                        >
                            Aplicar
                        </button>
                        <button
                            type="button"
                            @click="limpiarFiltros"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
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
                    class="group bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-lg hover:border-slate-300 transition"
                >
                    <div class="relative">
                        <div class="aspect-video bg-slate-100 relative overflow-hidden">
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
                                class="w-full h-full flex items-center justify-center text-slate-400 text-sm"
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
                                'absolute top-3 right-3 px-2.5 py-1 rounded-full text-xs font-semibold backdrop-blur border shadow-sm',
                                u.activo
                                    ? 'bg-green-50/90 text-green-700 border-green-200'
                                    : 'bg-slate-50/90 text-slate-700 border-slate-200',
                            ]"
                        >
                            {{ u.activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-base font-semibold text-slate-900 leading-snug line-clamp-2">
                                    {{ u.nombre }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    <span class="font-semibold text-slate-700">{{ u.codigo }}</span>
                                    <span class="text-slate-300">·</span>
                                    <span>{{ u.piso?.nombre || "Sin piso" }}</span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                <span class="text-slate-500">Marca:</span> {{ u.marca || "-" }}
                            </span>
                            <span class="px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs text-slate-700">
                                <span class="text-slate-500">Modelo:</span> {{ u.modelo || "-" }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <Link
                                :href="route('ups.show', { ups: u.id })"
                                class="text-center px-3 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-800 text-sm font-semibold transition-colors"
                            >
                                Ver
                            </Link>
                            <Link
                                v-if="hasPermission('edit_ups')"
                                :href="route('ups.edit', { ups: u.id })"
                                class="text-center px-3 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-sm font-semibold transition-colors"
                            >
                                Editar
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="bg-white border border-slate-200 rounded-2xl p-10 text-center text-slate-600"
            >
                <p class="font-semibold text-slate-900">No hay UPS registradas</p>
                <p class="text-sm mt-1">Crea una UPS para comenzar.</p>
            </div>

            <!-- Paginación -->
            <div
                v-if="ups.links?.length"
                class="bg-white border border-slate-200 rounded-xl p-4 flex flex-wrap gap-2"
            >
                <Link
                    v-for="(link, idx) in ups.links"
                    :key="idx"
                    :href="link.url || '#'"
                    :class="[
                        'px-3 py-2 rounded-md border text-sm',
                        link.active ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200',
                        !link.url ? 'opacity-50 pointer-events-none' : 'hover:bg-slate-50',
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


