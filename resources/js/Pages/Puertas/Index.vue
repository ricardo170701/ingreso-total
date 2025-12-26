<template>
    <AppLayout>
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Puertas
                    </h1>
                    <p class="text-sm text-slate-600">
                        Gestiona las puertas del edificio y sus permisos.
                    </p>
                </div>
                <Link
                    :href="route('puertas.create')"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Nueva Puerta
                </Link>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Layout 70-30 -->
            <div class="flex gap-6">
                <!-- Grid de Puertas (70%) -->
                <div class="flex-1" style="flex: 0 0 70%;">
                    <div v-if="puertas.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="mt-4 pt-4 border-t border-slate-200 flex flex-col gap-2">
                                    <div class="flex gap-2">
                                        <Link
                                            :href="route('puertas.edit', { puerta: puerta.id })"
                                            class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-medium"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            @click="eliminarPuerta(puerta)"
                                            class="flex-1 inline-flex items-center justify-center px-3 py-2 rounded-lg border border-red-200 hover:bg-red-50 text-red-700 text-sm font-medium"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                    <Link
                                        :href="route('mantenimientos.create', { puerta_id: puerta.id })"
                                        class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg border border-blue-200 hover:bg-blue-50 text-blue-700 text-sm font-medium"
                                    >
                                        🔧 Mantenimiento
                                    </Link>
                                </div>
                            </div>

                            <!-- Tooltip flotante con información completa (visible en hover) -->
                            <div
                                class="absolute left-full top-4 ml-2 w-80 bg-white border-2 border-slate-300 rounded-xl p-4 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none z-50 shadow-2xl"
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
                        class="mt-6 px-4 py-3 bg-white border border-slate-200 rounded-xl flex items-center justify-between"
                    >
                        <div class="text-sm text-slate-600">
                            Mostrando {{ puertas.from }} a {{ puertas.to }} de
                            {{ puertas.total }} resultados
                        </div>
                        <div class="flex gap-2">
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

                <!-- Sidebar de Pisos (30%) -->
                <div style="flex: 0 0 30%;">
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
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router } from "@inertiajs/vue3";

defineProps({
    puertas: Object,
    pisos: Array,
    pisoSeleccionado: Number,
});

const eliminarPuerta = (puerta) => {
    if (
        confirm(
            `¿Estás seguro de eliminar la puerta "${puerta.nombre}"?\n\nEsta acción no se puede deshacer.`
        )
    ) {
        router.delete(route("puertas.destroy", { puerta: puerta.id }));
    }
};
</script>
