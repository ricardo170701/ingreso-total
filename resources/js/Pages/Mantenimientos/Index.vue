<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Mantenimientos
                    </h1>
                    <p class="text-sm text-slate-600">
                        Registra y gestiona los mantenimientos de las puertas.
                    </p>
                </div>
                <Link
                    :href="route('mantenimientos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nuevo Mantenimiento
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <div
                class="bg-white border border-slate-200 rounded-xl overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    ID
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Puerta
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Fecha
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Usuario
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Defectos
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Imágenes
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr
                                v-for="m in mantenimientos.data"
                                :key="m.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-3 text-slate-600">
                                    {{ m.id }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">
                                        {{ m.puerta?.nombre }}
                                    </div>
                                    <div class="text-xs text-slate-500">
                                        {{ m.puerta?.piso?.nombre || "-" }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ new Date(m.fecha_mantenimiento).toLocaleDateString('es-ES') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ m.usuario?.name || m.usuario?.email }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="defecto in m.defectos"
                                            :key="defecto.id"
                                            :class="[
                                                'px-2 py-0.5 rounded text-xs font-medium',
                                                defecto.pivot?.nivel_gravedad === 0
                                                    ? 'bg-green-100 text-green-700'
                                                    : defecto.pivot?.nivel_gravedad === 1
                                                    ? 'bg-yellow-100 text-yellow-700'
                                                    : defecto.pivot?.nivel_gravedad === 2
                                                    ? 'bg-orange-100 text-orange-700'
                                                    : 'bg-red-100 text-red-700',
                                            ]"
                                            :title="`${defecto.nombre}: ${
                                                defecto.pivot?.nivel_gravedad === 0
                                                    ? 'Sin defecto'
                                                    : defecto.pivot?.nivel_gravedad === 1
                                                    ? 'Defecto ligero'
                                                    : defecto.pivot?.nivel_gravedad === 2
                                                    ? 'Defecto grave'
                                                    : 'Defecto muy grave'
                                            }`"
                                        >
                                            {{ defecto.nombre }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span class="px-2 py-1 bg-blue-50 rounded text-xs font-medium text-blue-700">
                                        {{ m.imagenes?.length || 0 }} imágenes
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="
                                                route('mantenimientos.show', {
                                                    mantenimiento: m.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            :href="
                                                route('mantenimientos.edit', {
                                                    mantenimiento: m.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm"
                                        >
                                            Editar
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="mantenimientos.links && mantenimientos.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600">
                        Mostrando {{ mantenimientos.from }} a {{ mantenimientos.to }} de
                        {{ mantenimientos.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in mantenimientos.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 rounded border text-sm',
                                link.active
                                    ? 'bg-slate-900 text-white border-slate-900'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
                                !link.url && 'opacity-50 cursor-not-allowed',
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
import { Link } from "@inertiajs/vue3";

defineProps({
    mantenimientos: Object,
    puertaFiltrada: Number,
});
</script>

