<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Welcome Section -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-8"
            >
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Bienvenido, {{ user?.name || user?.email }}
                </h1>
                <p class="text-slate-600">
                    Sistema de Control de Accesos con QR
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">
                                Usuarios Activos
                            </p>
                            <p class="text-3xl font-bold text-slate-900">
                                {{ stats.total_usuarios }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center"
                        >
                            <span class="text-2xl">👥</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">
                                Accesos Hoy
                            </p>
                            <p class="text-3xl font-bold text-green-600">
                                {{ stats.accesos_permitidos_hoy }}
                            </p>
                            <p
                                v-if="stats.accesos_denegados_hoy > 0"
                                class="text-xs text-red-600 mt-1"
                            >
                                {{ stats.accesos_denegados_hoy }} denegados
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center"
                        >
                            <span class="text-2xl">🚪</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">
                                QR Activos
                            </p>
                            <p class="text-3xl font-bold text-purple-600">
                                {{ stats.qr_activos }}
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ stats.qr_generados_hoy }} generados hoy
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center"
                        >
                            <span class="text-2xl">📱</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 mb-1">
                                Puertas Activas
                            </p>
                            <p class="text-3xl font-bold text-slate-900">
                                {{ stats.total_puertas }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center"
                        >
                            <span class="text-2xl">🔒</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Accesos Recientes -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
                >
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">
                        Accesos Recientes
                    </h2>
                    <div
                        v-if="accesos_recientes.length === 0"
                        class="text-center py-8 text-slate-500"
                    >
                        No hay accesos registrados aún.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="acceso in accesos_recientes"
                            :key="acceso.id"
                            class="flex items-center justify-between p-3 rounded-lg border border-slate-200 hover:bg-slate-50"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-slate-900">
                                    {{ acceso.usuario }}
                                </p>
                                <p class="text-sm text-slate-600">
                                    {{ acceso.puerta }} •
                                    {{ acceso.tipo_evento }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ acceso.fecha_acceso }}
                                </p>
                            </div>
                            <div>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        acceso.permitido
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700',
                                    ]"
                                >
                                    {{
                                        acceso.permitido
                                            ? "Permitido"
                                            : "Denegado"
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Puertas Más Usadas -->
                <div
                    class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
                >
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">
                        Puertas Más Usadas (Hoy)
                    </h2>
                    <div
                        v-if="puertas_mas_usadas.length === 0"
                        class="text-center py-8 text-slate-500"
                    >
                        No hay datos de uso hoy.
                    </div>
                    <div v-else class="space-y-3">
                        <div
                            v-for="(item, index) in puertas_mas_usadas"
                            :key="index"
                            class="flex items-center justify-between p-3 rounded-lg border border-slate-200"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold"
                                >
                                    {{ index + 1 }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">
                                        {{ item.puerta }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-slate-900">
                                    {{ item.total }}
                                </p>
                                <p class="text-xs text-slate-500">accesos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
            >
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Acciones Rápidas
                </h2>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <Link
                        :href="route('ingreso.index')"
                        class="flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors"
                    >
                        <span class="text-2xl">📱</span>
                        <div>
                            <p class="font-medium text-slate-900">Generar QR</p>
                            <p class="text-sm text-slate-600">
                                Crear código QR de acceso
                            </p>
                        </div>
                    </Link>
                    <Link
                        :href="route('usuarios.index')"
                        class="flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors"
                    >
                        <span class="text-2xl">👥</span>
                        <div>
                            <p class="font-medium text-slate-900">
                                Gestionar Usuarios
                            </p>
                            <p class="text-sm text-slate-600">
                                Ver y editar usuarios
                            </p>
                        </div>
                    </Link>
                    <Link
                        :href="route('puertas.index')"
                        class="flex items-center gap-3 p-4 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors"
                    >
                        <span class="text-2xl">🚪</span>
                        <div>
                            <p class="font-medium text-slate-900">
                                Gestionar Puertas
                            </p>
                            <p class="text-sm text-slate-600">
                                Configurar puertas
                            </p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const page = usePage();
const user = computed(() => page.props.auth?.user || page.props.user);

const props = defineProps({
    stats: Object,
    accesos_recientes: Array,
    puertas_mas_usadas: Array,
    accesos_por_hora: Array,
});
</script>
