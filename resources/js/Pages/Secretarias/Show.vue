<template>
    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ secretaria.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Información de la secretaría y sus gerencias
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('secretarias.edit', { secretaria: secretaria.id })"
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

            <!-- Información de la Secretaría -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Información General
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Piso</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ secretaria.piso?.nombre || "Sin piso asignado" }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Estado</p>
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
                    <div v-if="secretaria.descripcion" class="md:col-span-2">
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Descripción</p>
                        <p class="text-sm text-slate-900 dark:text-slate-100">
                            {{ secretaria.descripcion }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Gerencias -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Gerencias ({{ secretaria.gerencias?.length || 0 }})
                    </h2>
                    <Link
                        :href="route('gerencias.create', { secretaria: secretaria.id })"
                        class="px-4 py-2 rounded-lg bg-purple-600 dark:bg-purple-700 text-white hover:bg-purple-700 dark:hover:bg-purple-600 font-medium transition-colors duration-200"
                    >
                        Nueva Gerencia
                    </Link>
                </div>

                <div v-if="secretaria.gerencias && secretaria.gerencias.length > 0" class="space-y-3">
                    <div
                        v-for="gerencia in secretaria.gerencias"
                        :key="gerencia.id"
                        class="p-4 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 rounded-lg transition-colors duration-200"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ gerencia.nombre }}
                                </h3>
                                <p v-if="gerencia.descripcion" class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                                    {{ gerencia.descripcion }}
                                </p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        Usuarios: 
                                        <span class="font-medium text-slate-700 dark:text-slate-300">
                                            {{ gerencia.users_count || 0 }}
                                        </span>
                                    </span>
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
                            </div>
                            <div class="flex gap-2 ml-4">
                                <Link
                                    :href="route('gerencias.show', { secretaria: secretaria.id, gerencia: gerencia.id })"
                                    class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                >
                                    Ver
                                </Link>
                                <Link
                                    :href="route('gerencias.edit', { secretaria: secretaria.id, gerencia: gerencia.id })"
                                    class="px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm transition-colors duration-200"
                                >
                                    Editar
                                </Link>
                                <button
                                    @click="eliminarGerencia(gerencia)"
                                    class="px-3 py-1.5 rounded-md border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 text-sm transition-colors duration-200"
                                >
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-slate-500 dark:text-slate-400">
                    No hay gerencias registradas. 
                    <Link
                        :href="route('gerencias.create', { secretaria: secretaria.id })"
                        class="text-purple-600 dark:text-purple-400 hover:underline"
                    >
                        Crear la primera gerencia
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    secretaria: Object,
});

const eliminarGerencia = (gerencia) => {
    if (
        !confirm(
            `¿Estás seguro de eliminar la gerencia "${gerencia.nombre}"?`
        )
    )
        return;
    router.delete(
        route("gerencias.destroy", {
            secretaria: props.secretaria.id,
            gerencia: gerencia.id,
        })
    );
};
</script>