<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Cargos y Permisos
                    </h1>
                    <p class="text-sm text-slate-600">
                        Gestiona los cargos y sus permisos de acceso a puertas.
                    </p>
                </div>
                <Link
                    :href="route('cargos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nuevo Cargo
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
                                    Descripción
                                </th>
                                <th
                                    class="px-4 py-3 text-left font-semibold text-slate-700"
                                >
                                    Usuarios
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
                                v-for="c in cargos.data"
                                :key="c.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-3 text-slate-600">
                                    {{ c.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ c.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ c.description || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span
                                        class="px-2 py-1 bg-slate-100 rounded text-xs"
                                    >
                                        {{ c.users_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            c.activo
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ c.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <Link
                                        :href="
                                            route('cargos.edit', {
                                                cargo: c.id,
                                            })
                                        "
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700"
                                    >
                                        Gestionar Permisos
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div
                    v-if="cargos.links && cargos.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600">
                        Mostrando {{ cargos.from }} a {{ cargos.to }} de
                        {{ cargos.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in cargos.links"
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
    cargos: Object,
});
</script>
