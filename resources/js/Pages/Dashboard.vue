<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Welcome Section -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 transition-colors duration-200"
            >
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">
                    Bienvenido, {{ user?.name || user?.email }}
                </h1>
                <p class="text-slate-600 dark:text-slate-400">
                    Sistema de Control de Accesos con QR
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                Usuarios Activos
                            </p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                                {{ stats.total_usuarios }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center transition-colors duration-200"
                        >
                            <span class="text-2xl">👥</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                Accesos Hoy
                            </p>
                            <p class="text-3xl font-bold text-green-600 dark:text-green-400">
                                {{ stats.accesos_permitidos_hoy }}
                            </p>
                            <p
                                v-if="stats.accesos_denegados_hoy > 0"
                                class="text-xs text-red-600 dark:text-red-400 mt-1"
                            >
                                {{ stats.accesos_denegados_hoy }} denegados
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center transition-colors duration-200"
                        >
                            <span class="text-2xl">🚪</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                QR Activos
                            </p>
                            <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">
                                {{ stats.qr_activos }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                {{ stats.qr_generados_hoy }} generados hoy
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center transition-colors duration-200"
                        >
                            <span class="text-2xl">📱</span>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">
                                Puertas Activas
                            </p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-slate-100">
                                {{ stats.total_puertas }}
                            </p>
                        </div>
                        <div
                            class="w-12 h-12 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center transition-colors duration-200"
                        >
                            <span class="text-2xl">🔒</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Gráfico: Accesos por Hora (Últimas 24 horas) -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Accesos por Hora (Últimas 24h)
                    </h2>
                    <LineChart
                        v-if="accesosPorHoraData.labels.length > 0"
                        :data="accesosPorHoraData"
                    />
                    <div
                        v-else
                        class="text-center py-16 text-slate-500 dark:text-slate-400"
                    >
                        No hay datos disponibles
                    </div>
                </div>

                <!-- Gráfico: Accesos Permitidos vs Denegados -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Accesos de Hoy
                    </h2>
                    <DoughnutChart
                        v-if="accesosHoyData.values.some(v => v > 0)"
                        :data="accesosHoyData"
                    />
                    <div
                        v-else
                        class="text-center py-16 text-slate-500 dark:text-slate-400"
                    >
                        No hay accesos hoy
                    </div>
                </div>

                <!-- Gráfico: Accesos por Día (Últimos 7 días) -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Accesos por Día (Últimos 7 días)
                    </h2>
                    <BarChart
                        v-if="accesosPorDiaData.labels.length > 0"
                        :data="accesosPorDiaData"
                    />
                    <div
                        v-else
                        class="text-center py-16 text-slate-500 dark:text-slate-400"
                    >
                        No hay datos disponibles
                    </div>
                </div>

                <!-- Gráfico: Mantenimientos por Estado -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Estado de Mantenimientos
                    </h2>
                    <DoughnutChart
                        v-if="mantenimientosData.values.some(v => v > 0)"
                        :data="mantenimientosData"
                    />
                    <div
                        v-else
                        class="text-center py-16 text-slate-500 dark:text-slate-400"
                    >
                        No hay mantenimientos registrados
                    </div>
                </div>
            </div>

            <!-- Gráfico: Puertas Más Usadas -->
            <div
                v-if="puertasMasUsadasData.labels.length > 0"
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Puertas Más Usadas (Hoy)
                </h2>
                <BarChart
                    :data="puertasMasUsadasData"
                    :horizontal="true"
                    :options="{ maintainAspectRatio: false }"
                />
            </div>

            <!-- Puertas (tarjetas) -->
            <div
                v-if="puertas_dashboard?.length && hasPermission('view_puertas')"
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
            >
                <div class="flex items-center justify-between flex-wrap gap-2 mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Puertas (Acciones rápidas)
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400">
                            Ver, abrir (entrada) y reiniciar según permisos.
                        </p>
                    </div>
                    <Link
                        :href="route('puertas.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium transition-colors duration-200"
                    >
                        Ver todas
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="p in puertas_dashboard"
                        :key="p.id"
                        class="group bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden hover:shadow-lg hover:border-slate-300 dark:hover:border-slate-600 transition-all duration-200"
                    >
                        <div class="relative">
                            <div class="aspect-video bg-slate-100 dark:bg-slate-700 relative overflow-hidden transition-colors duration-200">
                                <img
                                    v-if="p.imagen"
                                    :src="storageUrl(p.imagen)"
                                    :alt="p.nombre"
                                    loading="lazy"
                                    decoding="async"
                                    class="w-full h-full object-cover object-center group-hover:scale-[1.02] transition-transform duration-300"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400 dark:text-slate-500">
                                    <div class="text-center">
                                        <div class="text-4xl leading-none">🚪</div>
                                        <div class="mt-2 text-sm">Sin foto</div>
                                    </div>
                                </div>
                                <div class="absolute inset-0 bg-linear-to-t from-black/35 via-black/0 to-black/0"></div>
                            </div>

                            <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-white/80 dark:bg-slate-800/80 backdrop-blur',
                                        p.activo ? 'text-green-700 dark:text-green-400 border-green-200 dark:border-green-800' : 'text-red-700 dark:text-red-400 border-red-200 dark:border-red-800',
                                    ]"
                                >
                                    {{ p.activo ? "Activa" : "Inactiva" }}
                                </span>
                                <span
                                    v-if="p.estado_mantenimiento === 'programado'"
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-yellow-50/90 dark:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400 border-yellow-200 dark:border-yellow-800 backdrop-blur"
                                >
                                    ⚠️ Mant.
                                </span>
                                <span
                                    v-else-if="p.estado_mantenimiento === 'vencido'"
                                    class="px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-red-50/90 dark:bg-red-900/50 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800 backdrop-blur"
                                >
                                    🔴 Venc.
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <p class="text-base font-semibold text-slate-900 dark:text-slate-100 leading-snug line-clamp-2">
                                {{ p.nombre }}
                            </p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ p.piso?.nombre || "Sin piso" }}
                            </p>

                            <div class="mt-4 grid grid-cols-3 gap-2">
                                <Link
                                    v-if="hasPermission('view_puertas')"
                                    :href="route('puertas.show', { puerta: p.id })"
                                    class="text-center px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-sm font-semibold transition-colors duration-200"
                                >
                                    Ver
                                </Link>
                                <button
                                    v-if="hasPermission('toggle_puertas') && p.ip_entrada"
                                    type="button"
                                    @click="abrirPuerta(p)"
                                    :disabled="abriendo[p.id] === true"
                                    class="text-center px-3 py-2 rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 text-sm font-semibold transition-colors duration-200 disabled:opacity-50"
                                    title="Abrir/Cerrar (Entrada)"
                                >
                                    {{ abriendo[p.id] ? "..." : "Abrir" }}
                                </button>
                                <button
                                    v-if="hasPermission('reboot_puertas') && (p.ip_entrada || p.ip_salida)"
                                    type="button"
                                    @click="reiniciarPuerta(p)"
                                    :disabled="reiniciando[p.id] === true"
                                    class="text-center px-3 py-2 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-sm font-semibold transition-colors duration-200 disabled:opacity-50"
                                    title="Reiniciar Raspberry"
                                >
                                    {{ reiniciando[p.id] ? "..." : "Reiniciar" }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Accesos Recientes -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Accesos Recientes
                    </h2>
                    <div
                        v-if="accesos_recientes.length === 0"
                        class="text-center py-8 text-slate-500 dark:text-slate-400"
                    >
                        No hay accesos registrados aún.
                    </div>
                    <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                        <div
                            v-for="acceso in accesos_recientes"
                            :key="acceso.id"
                            class="flex items-center justify-between p-3 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    {{ acceso.usuario }}
                                </p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    {{ acceso.puerta }} •
                                    {{ acceso.tipo_evento }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ acceso.fecha_acceso }}
                                </p>
                            </div>
                            <div>
                                <span
                                    :class="[
                                        'px-2 py-1 rounded text-xs font-medium',
                                        acceso.permitido
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                                            : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
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

                <!-- Información Adicional -->
                <div
                    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
                >
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                        Resumen de Mantenimientos
                    </h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 transition-colors duration-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">🔧</span>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">Programados</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Mantenimientos pendientes</p>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">
                                {{ mantenimientos?.programados || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 transition-colors duration-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">⚠️</span>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">Vencidos</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Requieren atención</p>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ mantenimientos?.vencidos || 0 }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between p-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 transition-colors duration-200">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">✅</span>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-slate-100">Realizados</p>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">Últimos 30 días</p>
                                </div>
                            </div>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ mantenimientos?.realizados || 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors duration-200"
            >
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Acciones Rápidas
                </h2>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                    <Link
                        :href="route('ingreso.index')"
                        class="flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <span class="text-2xl">📱</span>
                        <div>
                            <p class="font-medium text-slate-900 dark:text-slate-100">Generar QR</p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                Crear código QR de acceso
                            </p>
                        </div>
                    </Link>
                    <Link
                        :href="route('usuarios.index')"
                        class="flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <span class="text-2xl">👥</span>
                        <div>
                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                Gestionar Usuarios
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
                                Ver y editar usuarios
                            </p>
                        </div>
                    </Link>
                    <Link
                        :href="route('puertas.index')"
                        class="flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                    >
                        <span class="text-2xl">🚪</span>
                        <div>
                            <p class="font-medium text-slate-900 dark:text-slate-100">
                                Gestionar Puertas
                            </p>
                            <p class="text-sm text-slate-600 dark:text-slate-400">
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
import { computed, ref } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import LineChart from "@/Components/Charts/LineChart.vue";
import BarChart from "@/Components/Charts/BarChart.vue";
import DoughnutChart from "@/Components/Charts/DoughnutChart.vue";
import axios from "axios";

const page = usePage();
const user = computed(() => page.props.auth?.user || page.props.user);

const props = defineProps({
    stats: Object,
    accesos_recientes: Array,
    puertas_mas_usadas: Array,
    puertas_dashboard: Array,
    accesos_por_hora: Array,
    accesos_por_dia: Array,
    mantenimientos: Object,
});

const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const hasPermission = (permission) => userPermissions.value.includes(permission);

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const abriendo = ref({});
const reiniciando = ref({});

const abrirPuerta = async (puerta) => {
    if (!puerta?.ip_entrada) return;
    if (abriendo.value[puerta.id]) return;

    abriendo.value[puerta.id] = true;
    try {
        await axios.post(
            route("puertas.toggle", { puerta: puerta.id }),
            { tipo: "entrada" },
            {
                withCredentials: true,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );
    } catch (e) {
        console.error("Error al abrir puerta:", e);
        alert("Error al enviar comando de apertura.");
    } finally {
        abriendo.value[puerta.id] = false;
    }
};

const reiniciarPuerta = async (puerta) => {
    if (reiniciando.value[puerta.id]) return;
    if (!confirm(`¿Reiniciar la puerta "${puerta.nombre}"?`)) return;

    reiniciando.value[puerta.id] = true;
    try {
        await axios.post(
            route("puertas.reiniciar", { puerta: puerta.id }),
            {},
            {
                withCredentials: true,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );
        alert("Comando de reinicio enviado.");
    } catch (e) {
        console.error("Error al reiniciar puerta:", e);
        alert("Error al enviar comando de reinicio.");
    } finally {
        reiniciando.value[puerta.id] = false;
    }
};

// Datos para gráfico de accesos por hora
const accesosPorHoraData = computed(() => {
    // Crear array completo de 24 horas (0-23)
    const horasCompletas = Array.from({ length: 24 }, (_, i) => {
        let total = 0;
        if (props.accesos_por_hora && props.accesos_por_hora.length > 0) {
            // Buscar datos para esta hora (formato puede ser "0:00", "1:00", "10:00", etc.)
            const horaData = props.accesos_por_hora.find(item => {
                const horaItem = parseInt(item.hora?.split(':')[0] || -1);
                return horaItem === i;
            });
            total = horaData ? horaData.total : 0;
        }
        return {
            hora: `${i.toString().padStart(2, '0')}:00`,
            total: total
        };
    });

    return {
        label: 'Accesos',
        labels: horasCompletas.map(h => h.hora),
        values: horasCompletas.map(h => h.total)
    };
});

// Datos para gráfico de accesos hoy (permitidos vs denegados)
const accesosHoyData = computed(() => {
    return {
        labels: ['Permitidos', 'Denegados'],
        values: [
            props.stats?.accesos_permitidos_hoy || 0,
            props.stats?.accesos_denegados_hoy || 0
        ],
        colors: [
            'rgba(16, 185, 129, 0.8)',  // Verde para permitidos
            'rgba(239, 68, 68, 0.8)'    // Rojo para denegados
        ],
        borderColors: [
            'rgb(16, 185, 129)',
            'rgb(239, 68, 68)'
        ]
    };
});

// Datos para gráfico de accesos por día
const accesosPorDiaData = computed(() => {
    if (!props.accesos_por_dia || props.accesos_por_dia.length === 0) {
        return { labels: [], datasets: [] };
    }

    return {
        labels: props.accesos_por_dia.map(d => `${d.dia} (${d.dia_nombre})`),
        datasets: [
            {
                label: 'Permitidos',
                data: props.accesos_por_dia.map(d => d.permitidos),
                backgroundColor: 'rgba(16, 185, 129, 0.8)',
                borderColor: 'rgb(16, 185, 129)',
            },
            {
                label: 'Denegados',
                data: props.accesos_por_dia.map(d => d.denegados),
                backgroundColor: 'rgba(239, 68, 68, 0.8)',
                borderColor: 'rgb(239, 68, 68)',
            }
        ]
    };
});

// Datos para gráfico de mantenimientos
const mantenimientosData = computed(() => {
    return {
        labels: ['Programados', 'Vencidos', 'Realizados (30 días)'],
        values: [
            props.mantenimientos?.programados || 0,
            props.mantenimientos?.vencidos || 0,
            props.mantenimientos?.realizados || 0
        ],
        colors: [
            'rgba(251, 146, 60, 0.8)',   // Naranja para programados
            'rgba(239, 68, 68, 0.8)',    // Rojo para vencidos
            'rgba(16, 185, 129, 0.8)'    // Verde para realizados
        ],
        borderColors: [
            'rgb(251, 146, 60)',
            'rgb(239, 68, 68)',
            'rgb(16, 185, 129)'
        ]
    };
});

// Datos para gráfico de puertas más usadas
const puertasMasUsadasData = computed(() => {
    if (!props.puertas_mas_usadas || props.puertas_mas_usadas.length === 0) {
        return { labels: [], values: [] };
    }

    return {
        label: 'Accesos',
        labels: props.puertas_mas_usadas.map(p => p.puerta),
        values: props.puertas_mas_usadas.map(p => p.total),
        colors: [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(251, 146, 60, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(139, 92, 246, 0.8)'
        ]
    };
});
</script>
