<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-4">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ user.name || user.email }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ user.email }}
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <Link
                        :href="route('usuarios.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                    <Link
                        v-if="hasPermission('edit_users')"
                        :href="route('usuarios.edit', { user: user.id })"
                        class="px-3 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                    >
                        Editar
                    </Link>
                    <button
                        v-if="hasPermission('delete_users')"
                        type="button"
                        @click="eliminarUsuario"
                        class="px-3 py-2 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-medium transition-colors duration-200"
                    >
                        Eliminar
                    </button>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.message || $page.props.flash?.success"
                class="p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 transition-colors duration-200"
            >
                {{ $page.props.flash.message || $page.props.flash.success }}
            </div>

            <!-- Hoja de vida -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden transition-colors duration-200">
                    <!-- Foto de perfil -->
                    <div class="aspect-video bg-slate-100 dark:bg-slate-700 relative overflow-hidden transition-colors duration-200">
                        <img
                            v-if="user.foto_perfil"
                            :src="storageUrl(user.foto_perfil)"
                            :alt="user.name || user.email"
                            class="w-full h-full object-cover object-center"
                            loading="lazy"
                            decoding="async"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500">
                            <div class="text-center">
                                <div class="text-4xl leading-none">👤</div>
                                <div class="mt-2 text-sm">Sin foto</div>
                            </div>
                        </div>

                        <!-- Badges de estado -->
                        <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                            <span
                                :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-white/80 dark:bg-slate-800/80 backdrop-blur',
                                    user.activo ? 'text-green-700 dark:text-green-400 border-green-200 dark:border-green-800' : 'text-red-700 dark:text-red-400 border-red-200 dark:border-red-800',
                                ]"
                            >
                                {{ user.activo ? "Activo" : "Inactivo" }}
                            </span>
                            <span
                                v-if="user.es_discapacitado"
                                class="px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-blue-50/90 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800 backdrop-blur"
                            >
                                ♿ Discapacitado
                            </span>
                            <span
                                v-if="user.role?.name === 'visitante'"
                                class="px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-purple-50/90 dark:bg-purple-900/50 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-800 backdrop-blur"
                            >
                                Visitante
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Datos personales -->
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Datos Personales</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Nombre completo</p>
                                    <p class="text-sm text-slate-900 dark:text-slate-100 font-medium">
                                        {{ user.nombre && user.apellido ? `${user.nombre} ${user.apellido}` : user.name || "-" }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Email</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 break-all">
                                        {{ user.email || "-" }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">N. Identidad</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ user.n_identidad || "-" }}
                                    </p>
                                </div>
                                <div v-if="user.numero_caso">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Número de Caso</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">
                                        {{ user.numero_caso }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Rol y permisos -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Rol y Permisos</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Rol</p>
                                    <p class="text-sm text-slate-900 dark:text-slate-100 font-medium">
                                        {{ user.role?.name || "-" }}
                                    </p>
                                </div>
                                <div v-if="user.cargo">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Cargo</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ user.cargo?.name || "-" }}
                                    </p>
                                </div>
                                <div v-if="user.gerencia">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Secretaría / Gerencia</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        <span class="font-medium">{{ user.gerencia.secretaria?.nombre || "-" }}</span>
                                        <span v-if="user.gerencia.secretaria?.piso" class="text-slate-500 dark:text-slate-400">
                                            · {{ user.gerencia.secretaria.piso.nombre }}
                                        </span>
                                    </p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">
                                        Gerencia: {{ user.gerencia.nombre }}
                                    </p>
                                </div>
                                <div v-if="user.fecha_expiracion">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Fecha expiración</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ formatDate(user.fecha_expiracion) }}
                                    </p>
                                </div>
                                <div v-if="user.tipo_contrato">
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Tipo de contrato</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium">
                                        {{ formatTipoContrato(user.tipo_contrato) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Auditoría -->
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                            <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Auditoría</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Creado por</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ user.created_by_name || user.creado_por?.name || user.creado_por?.email || "Sistema" }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        {{ formatDateTime(user.created_at) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Última edición por</p>
                                    <p class="text-sm text-slate-700 dark:text-slate-300">
                                        {{ user.updated_by_name || "N/A" }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        {{ formatDateTime(user.updated_at) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Acciones y documentos -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 space-y-4 transition-colors duration-200">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Acciones</h2>
                    <div class="space-y-2">
                        <Link
                            v-if="hasPermission('edit_users')"
                            :href="route('usuarios.edit', { user: user.id })"
                            class="w-full block text-center px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-semibold transition-colors duration-200"
                        >
                            Editar Usuario
                        </Link>
                        <button
                            v-if="hasPermission('delete_users')"
                            type="button"
                            @click="eliminarUsuario"
                            class="w-full px-4 py-2 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-semibold transition-colors duration-200"
                        >
                            Eliminar Usuario
                        </button>
                    </div>

                    <!-- Documentos -->
                    <div v-if="(user.documentos?.length || 0) > 0" class="pt-4 border-t border-slate-200 dark:border-slate-700">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Documentos</h2>
                        <div class="space-y-2">
                            <div
                                v-for="doc in user.documentos"
                                :key="doc.id"
                                class="flex items-center justify-between gap-2 p-2 bg-slate-50 dark:bg-slate-700 rounded-lg transition-colors duration-200"
                            >
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-medium text-slate-900 dark:text-slate-100 truncate">
                                        {{ doc.nombre_original || `Documento #${doc.id}` }}
                                    </p>
                                    <p v-if="doc.tipo_contrato" class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ formatTipoContrato(doc.tipo_contrato) }}
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ formatDateTime(doc.created_at) }}
                                    </p>
                                </div>
                                <a
                                    :href="route('usuarios.documentos.download', { user: user.id, documento: doc.id })"
                                    class="px-2 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs shrink-0 transition-colors duration-200"
                                    target="_blank"
                                >
                                    📥
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-else class="pt-4 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">Sin documentos</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    user: Object,
});

const page = usePage();
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const hasPermission = (permission) => userPermissions.value.includes(permission);

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleString("es-ES", {
        year: "numeric",
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const formatTipoContrato = (tipo) => {
    const map = {
        prestacion_servicios: "Prestación de servicios",
        contratista_externo: "Contratista externo",
        contrato_indefinido: "Contrato indefinido",
    };
    return map[tipo] || tipo;
};

const eliminarUsuario = () => {
    if (!confirm(`¿Eliminar el usuario "${props.user.name || props.user.email}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route("usuarios.destroy", { user: props.user.id }));
};
</script>

