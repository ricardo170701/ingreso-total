<template>
    <AppLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Permisos del Sistema
                    </h1>
                    <p class="text-sm text-slate-600">
                        Gestiona los permisos del sistema para cada rol. Los permisos controlan qué secciones del menú puede ver cada usuario.
                    </p>
                </div>
                <Link
                    :href="route('cargos.index')"
                    class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium"
                >
                    Ver Cargos
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Lista de Roles con sus Permisos -->
            <div class="space-y-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="bg-white border border-slate-200 rounded-xl p-6"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">
                                {{ role.name }}
                            </h2>
                            <p class="text-sm text-slate-600 mt-1">
                                {{ role.description || "Sin descripción" }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ role.users_count || 0 }} usuario(s) con este rol
                            </p>
                        </div>
                        <span
                            v-if="role.name === 'super_usuario'"
                            class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-700"
                        >
                            Todos los permisos
                        </span>
                    </div>

                    <!-- Permisos agrupados por grupo -->
                    <div class="space-y-4">
                        <div
                            v-for="(groupPermissions, groupName) in permissionsGrouped"
                            :key="groupName"
                            class="border border-slate-200 rounded-lg p-4"
                        >
                            <h3 class="text-sm font-semibold text-slate-700 mb-3 pb-2 border-b border-slate-200">
                                {{ formatGroupName(groupName) }}
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <label
                                    v-for="permission in groupPermissions"
                                    :key="permission.id"
                                    class="flex items-center gap-2 p-2 rounded-lg hover:bg-slate-50 cursor-pointer"
                                >
                                    <input
                                        :id="`role-${role.id}-permission-${permission.id}`"
                                        v-model="rolePermissions[role.id]"
                                        type="checkbox"
                                        :value="permission.id"
                                        :disabled="role.name === 'super_usuario'"
                                        :class="[
                                            'h-4 w-4 rounded border-slate-300',
                                            role.name === 'super_usuario'
                                                ? 'opacity-50 cursor-not-allowed'
                                                : 'cursor-pointer',
                                        ]"
                                        @change="updateRolePermissions(role)"
                                    />
                                    <span
                                        :class="[
                                            'text-sm text-slate-700 flex-1',
                                            role.name === 'super_usuario'
                                                ? 'opacity-50'
                                                : '',
                                        ]"
                                    >
                                        {{ permission.description }}
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de guardar (solo si no es super_usuario) -->
                    <div
                        v-if="role.name !== 'super_usuario'"
                        class="mt-4 pt-4 border-t border-slate-200 flex justify-end"
                    >
                        <button
                            @click="saveRolePermissions(role)"
                            :disabled="savingRoles[role.id]"
                            class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 disabled:opacity-50 font-medium"
                        >
                            {{
                                savingRoles[role.id]
                                    ? "Guardando..."
                                    : "Guardar Permisos"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import { ref, reactive, onMounted } from "vue";

const props = defineProps({
    roles: Array,
    permissions: Array,
    permissionsGrouped: Object,
});

// Estado para los permisos de cada rol
const rolePermissions = reactive({});
const savingRoles = reactive({});

// Inicializar los permisos de cada rol
onMounted(() => {
    props.roles.forEach((role) => {
        if (role.name === "super_usuario") {
            // Super usuario tiene todos los permisos
            rolePermissions[role.id] = props.permissions.map((p) => p.id);
        } else {
            // Otros roles tienen solo sus permisos asignados
            rolePermissions[role.id] = role.permissions?.map((p) => p.id) || [];
        }
    });
});

const formatGroupName = (groupName) => {
    const names = {
        dashboard: "Dashboard",
        users: "Usuarios",
        puertas: "Puertas",
        cargos: "Permisos/Cargos",
        ingreso: "Ingreso",
        mantenimientos: "Mantenimientos",
    };
    return names[groupName] || groupName;
};

const updateRolePermissions = (role) => {
    // Esta función se ejecuta cuando se cambia un checkbox
    // No hace nada, solo actualiza el estado local
};

const saveRolePermissions = (role) => {
    savingRoles[role.id] = true;

    router.put(
        route("roles.permissions.update", { role: role.id }),
        {
            permissions: rolePermissions[role.id] || [],
        },
        {
            preserveScroll: true,
            onFinish: () => {
                savingRoles[role.id] = false;
            },
        }
    );
};
</script>

