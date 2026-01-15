<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ gerencia.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gerencia de la secretaría <strong>{{ secretaria.nombre }}</strong>
                    </p>
                    <p v-if="secretaria.piso" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Piso: {{ secretaria.piso.nombre }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('gerencias.edit', { secretaria: secretaria.id, gerencia: gerencia.id })"
                        class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Editar
                    </Link>
                    <Link
                        :href="route('secretarias.show', { secretaria: secretaria.id })"
                        class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <!-- Mensaje de éxito (notificación temporal) -->
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 translate-x-full"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 translate-x-0"
                leave-to-class="opacity-0 translate-x-full"
            >
                <div
                    v-if="showSuccessMessage"
                    class="fixed top-4 right-4 z-50 max-w-md"
                >
                    <div
                        class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4 shadow-lg flex items-center gap-3"
                    >
                        <div class="shrink-0">
                            <span class="text-2xl">✅</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                Gerencia actualizada exitosamente
                            </p>
                            <p class="text-xs text-green-700 dark:text-green-300 mt-1">
                                Los cambios se han aplicado a la gerencia "{{ gerencia.nombre }}"
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="showSuccessMessage = false"
                            class="shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors"
                            aria-label="Cerrar"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </Transition>

            <div
                v-if="$page.props.errors?.error"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 transition-colors duration-200"
            >
                {{ $page.props.errors.error }}
            </div>

            <!-- Información de la Gerencia -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Información General
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Secretaría</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ secretaria.nombre }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Estado</p>
                        <span
                            :class="[
                                'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                gerencia.activo
                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                    : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                            ]"
                        >
                            {{ gerencia.activo ? "Activo" : "Inactivo" }}
                        </span>
                    </div>
                    <div v-if="gerencia.descripcion" class="md:col-span-2">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Descripción</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ gerencia.descripcion }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Usuarios de la Gerencia -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Usuarios Asignados ({{ gerencia.users?.length || 0 }})
                </h2>

                <div v-if="gerencia.users && gerencia.users.length > 0" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700 border-b border-slate-200 dark:border-slate-600">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Nombre
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Email
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Cargo
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Rol
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Estado
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-200">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            <tr
                                v-for="user in gerencia.users"
                                :key="user.id"
                                class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                            >
                                <td class="px-4 py-3 font-medium text-slate-900 dark:text-slate-100">
                                    {{ user.nombre }} {{ user.apellido }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ user.email }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    {{ user.cargo?.name || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 dark:text-slate-400">
                                    <span
                                        class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded text-xs font-medium transition-colors duration-200"
                                    >
                                        {{ user.role?.name || "-" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            user.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                        ]"
                                    >
                                        {{ user.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <Link
                                        :href="route('usuarios.show', { user: user.id })"
                                        class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                    >
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No hay usuarios asignados a esta gerencia.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { ref, watch, Transition } from "vue";

const props = defineProps({
    secretaria: Object,
    gerencia: Object,
});

const page = usePage();

// Mensaje de éxito
const showSuccessMessage = ref(false);

// Mostrar mensaje de éxito si hay flash message
watch(
    () => page.props.flash?.message,
    (message) => {
        if (message) {
            showSuccessMessage.value = true;
            // Ocultar el mensaje después de 5 segundos
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        }
    },
    { immediate: true }
);
</script>