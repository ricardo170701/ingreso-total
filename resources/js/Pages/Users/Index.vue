<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-4">
            <div
                v-if="flash?.success"
                class="rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-3"
            >
                {{ flash.success }}
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Usuarios
                    </h1>
                    <p class="text-sm text-slate-600">
                        Lista de usuarios y administración básica.
                    </p>
                </div>
                <Link
                    :href="route('usuarios.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800"
                >
                    <span>➕</span>
                    <span>Nuevo</span>
                </Link>
            </div>

            <div
                class="bg-white border border-slate-200 rounded-xl overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-slate-600">
                                <th class="px-4 py-3 font-semibold">ID</th>
                                <th class="px-4 py-3 font-semibold">Nombre</th>
                                <th class="px-4 py-3 font-semibold">Email</th>
                                <th class="px-4 py-3 font-semibold">Rol</th>
                                <th class="px-4 py-3 font-semibold">Cargo</th>
                                <th class="px-4 py-3 font-semibold">Departamento</th>
                                <th class="px-4 py-3 font-semibold">Activo</th>
                                <th class="px-4 py-3 font-semibold text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="u in users.data"
                                :key="u.id"
                                class="text-slate-800"
                            >
                                <td class="px-4 py-3">{{ u.id }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-900">
                                    {{ u.name || "-" }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ u.email }}</td>
                                <td class="px-4 py-3">
                                    {{ u.role?.name || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ u.cargo?.name || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ u.departamento?.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                            u.activo
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-slate-200 text-slate-700',
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
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 hover:bg-slate-50"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td
                                    class="px-4 py-10 text-center text-slate-500"
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
                    class="flex items-center justify-between px-4 py-3 border-t border-slate-200"
                >
                    <div class="text-sm text-slate-600">
                        Mostrando {{ users.from || 0 }} - {{ users.to || 0 }} de
                        {{ users.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(l, idx) in users.links"
                            :key="idx"
                            :href="l.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md text-sm border',
                                l.active
                                    ? 'bg-slate-900 text-white border-slate-900'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
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
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    users: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});
</script>
