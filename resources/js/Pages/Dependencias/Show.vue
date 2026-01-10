<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ dependencia.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Información de la dependencia y su estructura organizacional
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('dependencias.edit', { dependencia: dependencia.id })"
                        class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Editar
                    </Link>
                    <Link
                        :href="route('dependencias.index')"
                        class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <!-- Flash Messages -->
            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 transition-colors duration-200"
            >
                {{ $page.props.flash.message }}
            </div>

            <div
                v-if="$page.props.errors?.error"
                class="p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 transition-colors duration-200"
            >
                {{ $page.props.errors.error }}
            </div>

            <!-- Información de la Dependencia -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Información General
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Piso</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ dependencia.piso?.nombre || "Sin piso asignado" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Estado</p>
                        <span
                            :class="[
                                'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                dependencia.activo
                                    ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                    : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                            ]"
                        >
                            {{ dependencia.activo ? "Activo" : "Inactivo" }}
                        </span>
                    </div>
                    <div v-if="dependencia.descripcion" class="md:col-span-2">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Descripción</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ dependencia.descripcion }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Secretarías -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Secretarías ({{ dependencia.secretarias?.length || 0 }})
                    </h2>
                    <Link
                        :href="route('secretarias.create', { dependencia: dependencia.id })"
                        class="px-4 py-2 rounded-lg bg-blue-600 dark:bg-blue-700 text-white hover:bg-blue-700 dark:hover:bg-blue-600 font-medium transition-colors duration-200"
                    >
                        Nueva Secretaría
                    </Link>
                </div>

                <div v-if="dependencia.secretarias && dependencia.secretarias.length > 0" class="space-y-3">
                    <div
                        v-for="secretaria in dependencia.secretarias"
                        :key="secretaria.id"
                        class="p-4 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg transition-colors duration-200"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ secretaria.nombre }}
                                </h3>
                                <p v-if="secretaria.descripcion" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                    {{ secretaria.descripcion }}
                                </p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        Gerencias: 
                                        <span class="font-medium text-slate-700 dark:text-slate-300">
                                            {{ secretaria.gerencias_count || 0 }}
                                        </span>
                                    </span>
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors duration-200',
                                            secretaria.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'
                                                : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                        ]"
                                    >
                                        {{ secretaria.activo ? "Activo" : "Inactivo" }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2 ml-4">
                                <Link
                                    :href="route('secretarias.show', { dependencia: dependencia.id, secretaria: secretaria.id })"
                                    class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="route('secretarias.edit', { dependencia: dependencia.id, secretaria: secretaria.id })"
                                    class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                >
                                    Editar
                                </Link>
                            </div>
                        </div>

                        <!-- Gerencias de esta Secretaría -->
                        <div v-if="secretaria.gerencias && secretaria.gerencias.length > 0" class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-600">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2 font-medium">
                                Gerencias:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="gerencia in secretaria.gerencias"
                                    :key="gerencia.id"
                                    class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded text-xs font-medium transition-colors duration-200"
                                >
                                    {{ gerencia.nombre }}
                                    <span v-if="gerencia.users_count > 0" class="ml-1 text-purple-500 dark:text-purple-400">
                                        ({{ gerencia.users_count }})
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No hay secretarías registradas. 
                    <Link
                        :href="route('secretarias.create', { dependencia: dependencia.id })"
                        class="text-blue-600 dark:text-blue-400 hover:underline"
                    >
                        Crear la primera secretaría
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    dependencia: Object,
});
</script>