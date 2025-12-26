<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Departamentos
                    </h1>
                    <p class="text-sm text-slate-600">
                        Gestiona los departamentos del edificio.
                    </p>
                </div>
                <Link
                    :href="route('departamentos.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nuevo Departamento
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <div
                v-if="$page.props.errors?.error"
                class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800"
            >
                {{ $page.props.errors.error }}
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
                                    Piso
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
                                v-for="d in departamentos.data"
                                :key="d.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-3 text-slate-600">
                                    {{ d.id }}
                                </td>
                                <td
                                    class="px-4 py-3 font-medium text-slate-900"
                                >
                                    {{ d.nombre }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ d.piso?.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span
                                        v-if="d.descripcion"
                                        class="truncate block max-w-xs"
                                        :title="d.descripcion"
                                    >
                                        {{ d.descripcion }}
                                    </span>
                                    <span v-else class="text-slate-400">-</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <span
                                        class="px-2 py-1 bg-slate-100 rounded text-xs"
                                    >
                                        {{ d.users_count || 0 }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            d.activo
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ d.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="
                                                route('departamentos.edit', {
                                                    departamento: d.id,
                                                })
                                            "
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="eliminarDepartamento(d)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 hover:bg-red-50 text-red-700 text-sm"
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
                    v-if="departamentos.links && departamentos.links.length > 3"
                    class="px-4 py-3 border-t border-slate-200 flex items-center justify-between"
                >
                    <div class="text-sm text-slate-600">
                        Mostrando {{ departamentos.from }} a {{ departamentos.to }} de
                        {{ departamentos.total }} resultados
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in departamentos.links"
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
import { Link, router } from "@inertiajs/vue3";

defineProps({
    departamentos: Object,
});

const eliminarDepartamento = (departamento) => {
    if (
        !confirm(
            `¿Estás seguro de eliminar el departamento "${departamento.nombre}"?`
        )
    )
        return;
    router.delete(
        route("departamentos.destroy", { departamento: departamento.id })
    );
};
</script>

