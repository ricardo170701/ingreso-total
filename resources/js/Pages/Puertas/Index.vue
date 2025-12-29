<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Puertas
                    </h1>
                    <p class="text-sm text-slate-600">
                        Gestiona las puertas del edificio y sus permisos.
                    </p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto flex-col sm:flex-row">
                    <button
                        @click="refrescarConexiones"
                        :disabled="refrescandoConexiones"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-sm font-medium"
                        title="Actualizar estados de conexión de las puertas"
                    >
                        <span v-if="refrescandoConexiones">⏳</span>
                        <span v-else>🔄</span>
                        <span>{{ refrescandoConexiones ? 'Actualizando...' : 'Refrescar Conexiones' }}</span>
                    </button>
                    <Link
                        :href="route('puertas.create')"
                        class="w-full sm:w-auto px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium text-sm text-center"
                    >
                        Nueva Puerta
                    </Link>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Filtro por piso (mobile) -->
            <div class="lg:hidden">
                <button
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl bg-white border border-slate-200 text-slate-900"
                    @click="filtroPisosOpen = !filtroPisosOpen"
                >
                    <span class="font-semibold">Filtrar por Piso</span>
                    <span class="text-sm text-slate-600">
                        {{ filtroPisosOpen ? "▲" : "▼" }}
                    </span>
                </button>
                <div
                    v-if="filtroPisosOpen"
                    class="mt-2 bg-white border border-slate-200 rounded-xl p-3"
                >
                    <div class="space-y-2">
                        <Link
                            :href="route('puertas.index')"
                            @click="filtroPisosOpen = false"
                            :class="[
                                'block px-4 py-3 rounded-lg text-sm font-medium transition-colors',
                                !pisoSeleccionado
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-slate-50 text-slate-700 hover:bg-slate-100',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <span>Todas las puertas</span>
                                <span
                                    v-if="!pisoSeleccionado"
                                    class="px-2 py-0.5 bg-white/20 rounded text-xs"
                                >
                                    {{ puertas.total }}
                                </span>
                            </div>
                        </Link>
                        <Link
                            v-for="piso in pisos"
                            :key="piso.id"
                            :href="route('puertas.index', { piso_id: piso.id })"
                            @click="filtroPisosOpen = false"
                            :class="[
                                'block px-4 py-3 rounded-lg text-sm font-medium transition-colors',
                                pisoSeleccionado === piso.id
                                    ? 'bg-slate-900 text-white'
                                    : 'bg-slate-50 text-slate-700 hover:bg-slate-100',
                            ]"
                        >
                            <div class="flex items-center justify-between">
                                <span>{{ piso.nombre }}</span>
                                <span
                                    :class="[
                                        'px-2 py-0.5 rounded text-xs',
                                        pisoSeleccionado === piso.id
                                            ? 'bg-white/20'
                                            : 'bg-slate-200 text-slate-600',
                                    ]"
                                >
                                    {{ piso.puertas_count || 0 }}
                                </span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Layout con sidebar a la derecha (desktop) -->
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Grid de Puertas -->
                <div class="flex-1">
                    <div
                        v-if="puertas.data.length > 0"
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <div
                            v-for="puerta in puertas.data"
                            :key="puerta.id"
                            class="bg-white border border-slate-200 rounded-xl hover:shadow-lg transition-shadow relative group"
                        >
                            <!-- Imagen de la puerta -->
                            <div class="h-48 bg-slate-100 relative overflow-hidden rounded-t-xl">
                                <img
                                    v-if="puerta.imagen"
                                    :src="`/storage/${puerta.imagen}`"
                                    :alt="puerta.nombre"
                                    class="w-full h-full object-cover"
                                />
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center text-slate-400"
                                >
                                    <span class="text-4xl">🚪</span>
                                </div>
                                <!-- Badges de estado -->
                                <div class="absolute top-2 right-2 flex flex-col gap-1 items-end">
                                    <!-- Badge de estado de conexión de entrada -->
                                    <span
                                        v-if="puerta.ip_entrada"
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors',
                                            estadosConexion[puerta.id]?.entrada === true
                                                ? 'bg-green-100 text-green-700'
                                                : estadosConexion[puerta.id]?.entrada === false
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-gray-100 text-gray-500',
                                        ]"
                                        :title="estadosConexion[puerta.id]?.entrada === true ? 'Entrada conectada' : estadosConexion[puerta.id]?.entrada === false ? 'Entrada desconectada' : refrescandoConexiones ? 'Verificando entrada...' : 'Sin verificar entrada'"
                                    >
                                        {{
                                            estadosConexion[puerta.id]?.entrada === true
                                                ? "🟢 Entrada"
                                                : estadosConexion[puerta.id]?.entrada === false
                                                ? "🔴 Entrada"
                                                : refrescandoConexiones
                                                ? "⚪ Entrada..."
                                                : "⚪ Entrada"
                                        }}
                                    </span>
                                    <!-- Badge de estado de conexión de salida -->
                                    <span
                                        v-if="puerta.ip_salida"
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium transition-colors',
                                            estadosConexion[puerta.id]?.salida === true
                                                ? 'bg-green-100 text-green-700'
                                                : estadosConexion[puerta.id]?.salida === false
                                                ? 'bg-red-100 text-red-700'
                                                : 'bg-gray-100 text-gray-500',
                                        ]"
                                        :title="estadosConexion[puerta.id]?.salida === true ? 'Salida conectada' : estadosConexion[puerta.id]?.salida === false ? 'Salida desconectada' : refrescandoConexiones ? 'Verificando salida...' : 'Sin verificar salida'"
                                    >
                                        {{
                                            estadosConexion[puerta.id]?.salida === true
                                                ? "🟢 Salida"
                                                : estadosConexion[puerta.id]?.salida === false
                                                ? "🔴 Salida"
                                                : refrescandoConexiones
                                                ? "⚪ Salida..."
                                                : "⚪ Salida"
                                        }}
                                    </span>
                                    <!-- Badge de estado activo/inactivo -->
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            puerta.activo
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ puerta.activo ? "Activa" : "Inactiva" }}
                                    </span>
                                    <!-- Badge de mantenimiento -->
                                    <span
                                        v-if="puerta.estado_mantenimiento === 'programado'"
                                        class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-700"
                                        title="Puerta en mantenimiento programado"
                                    >
                                        ⚠️ Mantenimiento
                                    </span>
                                    <span
                                        v-else-if="puerta.estado_mantenimiento === 'vencido'"
                                        class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-700"
                                        title="Mantenimiento programado vencido"
                                    >
                                        🔴 Mantenimiento Vencido
                                    </span>
                                </div>
                            </div>

                            <!-- Contenido de la card -->
                            <div class="p-4">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="font-semibold text-slate-900 text-lg">
                                        {{ puerta.nombre }}
                                    </h3>
                                    <span
                                        v-if="puerta.requiere_discapacidad"
                                        class="text-xl"
                                        title="Requiere discapacidad"
                                    >
                                        ♿
                                    </span>
                                </div>

                                <!-- Información simplificada (siempre visible) -->
                                <div class="space-y-2 text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">Piso:</span>
                                        <span>{{ puerta.piso?.nombre || "-" }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">Zona:</span>
                                        <span>{{ puerta.zona?.nombre || "-" }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium">Tipo:</span>
                                        <span
                                            :class="[
                                                'px-2 py-0.5 rounded text-xs font-medium',
                                                puerta.tipo_puerta?.codigo === 'rodillo'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-slate-100 text-slate-700',
                                            ]"
                                        >
                                            {{ puerta.tipo_puerta?.nombre || "-" }}
                                        </span>
                                    </div>
                                    <div
                                        v-if="puerta.tiempo_apertura"
                                        class="flex items-center gap-2"
                                    >
                                        <span class="font-medium">Tiempo Apertura:</span>
                                        <span class="px-2 py-0.5 bg-blue-50 rounded text-xs font-medium text-blue-700">
                                            {{ puerta.tiempo_apertura }}s
                                        </span>
                                        <span
                                            v-if="puerta.tiempo_discapacitados"
                                            class="px-2 py-0.5 bg-purple-50 rounded text-xs font-medium text-purple-700"
                                            title="Tiempo para discapacitados"
                                        >
                                            Disc: {{ puerta.tiempo_discapacitados }}s
                                        </span>
                                    </div>
                                </div>

                                <!-- Acciones (preview): Ver / Abrir / Reiniciar -->
                                <div class="mt-4 pt-4 border-t border-slate-200">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                        <Link
                                            v-if="hasPermission('view_puertas')"
                                            :href="route('puertas.show', { puerta: puerta.id })"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-800 text-sm font-semibold"
                                        >
                                            Ver
                                        </Link>
                                        <button
                                            v-if="hasPermission('toggle_puertas') && puerta.ip_entrada"
                                            type="button"
                                            @click="abrirPuerta(puerta)"
                                            :disabled="abriendo[puerta.id] === true"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-sm font-semibold disabled:opacity-50"
                                            title="Abrir/Cerrar (Entrada)"
                                        >
                                            {{ abriendo[puerta.id] ? "..." : "Abrir" }}
                                        </button>
                                        <button
                                            v-if="hasPermission('reboot_puertas') && (puerta.ip_entrada || puerta.ip_salida)"
                                            type="button"
                                            @click="reiniciarPuerta(puerta)"
                                            :disabled="reiniciando[puerta.id] === true"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-semibold disabled:opacity-50"
                                            title="Reiniciar Raspberry"
                                        >
                                            {{ reiniciando[puerta.id] ? "..." : "Reiniciar" }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tooltip flotante con información completa (visible en hover) -->
                            <div
                                class="hidden lg:block absolute left-full top-4 ml-2 w-80 bg-white border-2 border-slate-300 rounded-xl p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50 shadow-2xl"
                            >
                                    <div class="space-y-2 text-sm">
                                        <h4 class="font-semibold text-slate-900 mb-3 pb-2 border-b border-slate-200">
                                            Información Completa
                                        </h4>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-slate-700 min-w-[100px]">IP Entrada:</span>
                                            <code
                                                v-if="puerta.ip_entrada"
                                                class="px-1.5 py-0.5 bg-slate-100 rounded text-xs"
                                            >
                                                {{ puerta.ip_entrada }}
                                            </code>
                                            <span v-else class="text-slate-400">-</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-slate-700 min-w-[100px]">IP Salida:</span>
                                            <code
                                                v-if="puerta.ip_salida"
                                                class="px-1.5 py-0.5 bg-slate-100 rounded text-xs"
                                            >
                                                {{ puerta.ip_salida }}
                                            </code>
                                            <span v-else class="text-slate-400">-</span>
                                        </div>
                                        <div
                                            v-if="puerta.material"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="font-medium text-slate-700 min-w-[100px]">Material:</span>
                                            <span class="px-2 py-0.5 bg-purple-50 rounded text-xs font-medium text-purple-700">
                                                {{ puerta.material.nombre }}
                                            </span>
                                        </div>
                                        <div
                                            v-if="puerta.alto || puerta.largo || puerta.ancho"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="font-medium text-slate-700 min-w-[100px]">Dimensiones:</span>
                                            <span class="text-xs">
                                                {{ puerta.alto || '-' }} × {{ puerta.largo || '-' }} × {{ puerta.ancho || '-' }} cm
                                            </span>
                                        </div>
                                        <div
                                            v-if="puerta.peso"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="font-medium text-slate-700 min-w-[100px]">Peso:</span>
                                            <span class="px-2 py-0.5 bg-orange-50 rounded text-xs font-medium text-orange-700">
                                                {{ puerta.peso }} kg
                                            </span>
                                        </div>
                                        <div
                                            v-if="puerta.ubicacion"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="font-medium text-slate-700 min-w-[100px]">Ubicación:</span>
                                            <span class="text-xs">{{ puerta.ubicacion }}</span>
                                        </div>
                                        <div
                                            v-if="puerta.codigo_fisico"
                                            class="flex items-center gap-2"
                                        >
                                            <span class="font-medium text-slate-700 min-w-[100px]">Código Físico:</span>
                                            <code class="px-1.5 py-0.5 bg-slate-100 rounded text-xs">
                                                {{ puerta.codigo_fisico }}
                                            </code>
                                        </div>
                                        <div
                                            v-if="puerta.descripcion"
                                            class="pt-2 border-t border-slate-200"
                                        >
                                            <span class="font-medium text-slate-700 block mb-1">Descripción:</span>
                                            <p class="text-xs text-slate-600">
                                                {{ puerta.descripcion }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>

                    <!-- Mensaje cuando no hay puertas -->
                    <div
                        v-else
                        class="bg-white border border-slate-200 rounded-xl p-12 text-center"
                    >
                        <p class="text-slate-500">
                            {{
                                pisoSeleccionado
                                    ? "No hay puertas en este piso."
                                    : "No hay puertas registradas."
                            }}
                        </p>
                    </div>

                    <!-- Paginación -->
                    <div
                        v-if="puertas.links && puertas.links.length > 3"
                        class="mt-6 px-4 py-3 bg-white border border-slate-200 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
                    >
                        <div class="text-sm text-slate-600">
                            Mostrando {{ puertas.from }} a {{ puertas.to }} de
                            {{ puertas.total }} resultados
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-for="link in puertas.links"
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

                <!-- Sidebar de Pisos (pegado a la derecha) -->
                <div class="hidden lg:block shrink-0" style="width: 240px;">
                    <div class="bg-white border border-slate-200 rounded-xl p-4 sticky top-4">
                        <h2 class="font-semibold text-slate-900 mb-4">Filtrar por Piso</h2>
                        <div class="space-y-2">
                            <Link
                                :href="route('puertas.index')"
                                :class="[
                                    'block px-4 py-3 rounded-lg text-sm font-medium transition-colors',
                                    !pisoSeleccionado
                                        ? 'bg-slate-900 text-white'
                                        : 'bg-slate-50 text-slate-700 hover:bg-slate-100',
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <span>Todas las puertas</span>
                                    <span
                                        v-if="!pisoSeleccionado"
                                        class="px-2 py-0.5 bg-white/20 rounded text-xs"
                                    >
                                        {{ puertas.total }}
                                    </span>
                                </div>
                            </Link>
                            <Link
                                v-for="piso in pisos"
                                :key="piso.id"
                                :href="route('puertas.index', { piso_id: piso.id })"
                                :class="[
                                    'block px-4 py-3 rounded-lg text-sm font-medium transition-colors',
                                    pisoSeleccionado === piso.id
                                        ? 'bg-slate-900 text-white'
                                        : 'bg-slate-50 text-slate-700 hover:bg-slate-100',
                                ]"
                            >
                                <div class="flex items-center justify-between">
                                    <span>{{ piso.nombre }}</span>
                                    <span
                                        :class="[
                                            'px-2 py-0.5 rounded text-xs',
                                            pisoSeleccionado === piso.id
                                                ? 'bg-white/20'
                                                : 'bg-slate-200 text-slate-600',
                                        ]"
                                    >
                                        {{ piso.puertas_count || 0 }}
                                    </span>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, usePage } from "@inertiajs/vue3";
import axios from "axios";

const props = defineProps({
    puertas: Object,
    pisos: Array,
    pisoSeleccionado: Number,
});

const page = usePage();
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);

// Helper para verificar permisos
const hasPermission = (permission) => {
    return userPermissions.value.includes(permission);
};

// Estados de conexión reactivos (se actualizan sin recargar la página)
const estadosConexion = ref({});
const refrescandoConexiones = ref(false);
const filtroPisosOpen = ref(false);

// Acciones rápidas (por tarjeta)
const abriendo = ref({});
const reiniciando = ref({});

// Refrescar estados de conexión (fuerza nueva verificación)
// Esta función es completamente asíncrona y no bloquea la UI
const refrescarConexiones = async () => {
    // Si ya está refrescando, no hacer nada (evitar múltiples llamadas)
    if (refrescandoConexiones.value) return;

    refrescandoConexiones.value = true;

    try {
        // Obtener IDs de las puertas visibles en la página actual
        const puertaIds = props.puertas.data
            .filter(p => p.ip_entrada || p.ip_salida)
            .map(p => p.id);

        if (puertaIds.length === 0) {
            refrescandoConexiones.value = false;
            return;
        }

        const data = {
            puerta_ids: puertaIds
        };

        if (props.pisoSeleccionado) {
            data.piso_id = props.pisoSeleccionado;
        }

        // Usar AbortController para permitir cancelar la petición si el usuario navega
        const controller = new AbortController();
        
        // Guardar el controller para poder cancelarlo si es necesario
        const timeoutId = setTimeout(() => {
            controller.abort();
        }, 30000); // Timeout de 30 segundos

        try {
            const response = await axios.post(route('puertas.refrescar-conexiones'), data, {
                withCredentials: true,
                signal: controller.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            clearTimeout(timeoutId);

            if (response.data.success && response.data.estados) {
                // Actualizar solo los estados, sin recargar la página
                Object.assign(estadosConexion.value, response.data.estados);
            }
        } catch (requestError) {
            clearTimeout(timeoutId);
            // Si es un error de abort, no mostrar error (el usuario probablemente navegó)
            if (requestError.name !== 'CanceledError' && requestError.name !== 'AbortError') {
                console.error('Error al refrescar estados de conexión:', requestError);
            }
        }
    } catch (error) {
        console.error('Error al refrescar estados de conexión:', error);
    } finally {
        refrescandoConexiones.value = false;
    }
};

const abrirPuerta = async (puerta) => {
    if (!puerta?.ip_entrada) return;
    if (abriendo.value[puerta.id]) return;

    abriendo.value[puerta.id] = true;
    try {
        await axios.post(
            route('puertas.toggle', { puerta: puerta.id }),
            { tipo: 'entrada' },
            {
                withCredentials: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            }
        );
    } catch (error) {
        console.error('Error al abrir/cerrar puerta:', error);
        alert('Error al enviar comando de apertura/cierre. Verifica la consola para más detalles.');
    } finally {
        abriendo.value[puerta.id] = false;
    }
};

const reiniciarPuerta = async (puerta) => {
    if (reiniciando.value[puerta.id]) return;
    if (
        !confirm(
            `¿Estás seguro de reiniciar las Raspberry Pi de la puerta "${puerta.nombre}"?\n\nEsto enviará el comando de reinicio a las IPs de entrada y salida (si están configuradas).`
        )
    ) {
        return;
    }

    reiniciando.value[puerta.id] = true;
    try {
        const response = await axios.post(
            route('puertas.reiniciar', { puerta: puerta.id }),
            {},
            {
                withCredentials: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            }
        );

        if (response.data?.success) {
            alert('Comando de reinicio enviado.');
        }
    } catch (error) {
        console.error('Error al reiniciar puerta:', error);
        alert('Error al enviar comando de reinicio. Verifica la consola para más detalles.');
    } finally {
        reiniciando.value[puerta.id] = false;
    }
};

// Cargar estados al montar el componente (en segundo plano, sin bloquear)
onMounted(() => {
    // Usar setTimeout para diferir la ejecución y permitir que la UI se renderice primero
    // Esto permite que el usuario pueda navegar incluso si entra por error
    setTimeout(() => {
        refrescarConexiones();
    }, 100);
});

// También cargar estados cuando cambien las puertas (paginación, filtros)
watch(
    () => props.puertas.data,
    () => {
        // Limpiar estados anteriores cuando cambian las puertas
        estadosConexion.value = {};
        // Cargar nuevos estados con un pequeño delay para no bloquear la navegación
        setTimeout(() => {
            refrescarConexiones();
        }, 100);
    },
    { immediate: false }
);

watch(
    () => props.pisoSeleccionado,
    () => {
        filtroPisosOpen.value = false;
    }
);

</script>
