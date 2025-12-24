<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Puertas
                    </h1>
                    <p class="text-sm text-slate-600">
                        Gestiona las puertas del edificio y sus permisos.
                    </p>
                </div>
                <Link
                    :href="route('puertas.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nueva Puerta
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
                                    Nombre
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Zona
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Código Físico
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Ubicación
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Estado
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
                                v-for="p in puertas.data"
                                :key="p.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-3 text-slate-600">
                                    {{ p.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ p.nombre }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ p.zona?.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <code
                                        v-if="p.codigo_fisico"
                                        class="px-2 py-1 bg-slate-100 rounded text-xs"
                                    >
                                        {{ p.codigo_fisico }}
                                    </code>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ p.ubicacion || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            p.activo
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ p.activo ? "Activa" : "Inactiva" }}
                                    </span>
                                    <span
                                        v-if="p.requiere_discapacidad"
                                        class="ml-2 px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700"
                                    >
                                        ♿
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <Link
                                        :href="
                                            route('puertas.edit', {
                                                puerta: p.id,
                                            })
                                        "
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700"
                                    >
                                        Editar
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="puertas.links && puertas.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600">
                        Mostrando {{ puertas.from }} a {{ puertas.to }} de
                        {{ puertas.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in puertas.links"
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
    puertas: Object,
});
</script>
