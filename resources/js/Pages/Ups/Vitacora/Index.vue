<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Bitácora - {{ ups.codigo }} - {{ ups.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        Historial de estados registrados del UPS.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('ups.show', { ups: ups.id })"
                        class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                    >
                        Volver a UPS
                    </Link>
                    <button
                        type="button"
                        @click.prevent="exportarBitacoras"
                        :disabled="exportando || !filtros.fecha_desde || !filtros.fecha_hasta"
                        class="px-3 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                    >
                        {{ exportando ? 'Exportando...' : 'Exportar ZIP' }}
                    </button>
                    <Link
                        :href="route('ups.vitacora.create', { ups: ups.id })"
                        class="px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                    >
                        Nuevo Registro
                    </Link>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Filtros -->
            <div class="bg-white border border-slate-200 rounded-xl p-4">
                <form @submit.prevent="aplicarFiltros" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Fecha desde
                        </label>
                        <input
                            v-model="filtros.fecha_desde"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Fecha hasta
                        </label>
                        <input
                            v-model="filtros.fecha_hasta"
                            type="date"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-medium"
                        >
                            Filtrar
                        </button>
                        <button
                            type="button"
                            @click="limpiarFiltros"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Lista de registros -->
            <div v-if="vitacora.data.length > 0" class="space-y-4">
                <div
                    v-for="registro in vitacora.data"
                    :key="registro.id"
                    class="bg-white border border-slate-200 rounded-xl p-6 space-y-4"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-sm text-slate-500">
                                    {{ new Date(registro.created_at).toLocaleString('es-ES') }}
                                </span>
                                <span v-if="registro.creado_por" class="text-sm text-slate-500">
                                    · Registrado por: {{ getUsuarioNombre(registro.creado_por) }}
                                </span>
                            </div>

                            <!-- Imágenes (thumbnails) -->
                            <div v-if="registro.imagenes && registro.imagenes.length > 0" class="mb-4">
                                <p class="text-xs text-slate-500 mb-2">
                                    {{ registro.imagenes.length }} imagen{{ registro.imagenes.length > 1 ? 'es' : '' }} (click para ampliar)
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    <div
                                        v-for="(imagen, idx) in registro.imagenes"
                                        :key="idx"
                                        class="relative group"
                                    >
                                        <div class="w-20 h-20 bg-slate-100 rounded-lg border-2 border-slate-200 overflow-hidden cursor-pointer hover:border-blue-500 transition-colors">
                                            <img
                                                :src="`/storage/${imagen.ruta_imagen}`"
                                                alt="Panel frontal UPS"
                                                class="w-full h-full object-cover"
                                                @click="openImageModal(registro.imagenes, idx)"
                                            />
                                        </div>
                                        <span
                                            v-if="registro.imagenes.length > 1"
                                            class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-medium"
                                        >
                                            {{ idx + 1 }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicadores -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-4">
                                <div
                                    class="p-3 rounded-lg text-center"
                                    :class="registro.indicador_normal ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600'"
                                >
                                    <div class="font-medium">NORMAL</div>
                                    <div class="text-xs">{{ registro.indicador_normal ? 'ON' : 'OFF' }}</div>
                                </div>
                                <div
                                    class="p-3 rounded-lg text-center"
                                    :class="registro.indicador_battery ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 text-slate-600'"
                                >
                                    <div class="font-medium">BATTERY</div>
                                    <div class="text-xs">{{ registro.indicador_battery ? 'ON' : 'OFF' }}</div>
                                </div>
                                <div
                                    class="p-3 rounded-lg text-center"
                                    :class="registro.indicador_bypass ? 'bg-yellow-100 text-yellow-800' : 'bg-slate-100 text-slate-600'"
                                >
                                    <div class="font-medium">BYPASS</div>
                                    <div class="text-xs">{{ registro.indicador_bypass ? 'ON' : 'OFF' }}</div>
                                </div>
                                <div
                                    class="p-3 rounded-lg text-center"
                                    :class="registro.indicador_fault ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-600'"
                                >
                                    <div class="font-medium">FAULT</div>
                                    <div class="text-xs">{{ registro.indicador_fault ? 'ON' : 'OFF' }}</div>
                                </div>
                            </div>

                            <!-- Datos -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Input -->
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                                    <h3 class="font-semibold text-slate-900 mb-2">Input</h3>
                                    <div class="space-y-1 text-sm">
                                        <div>
                                            <span class="text-slate-600">Voltaje:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.input_voltage ? `${registro.input_voltage} V` : '-' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-600">Frecuencia:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.input_frequency ? `${registro.input_frequency} Hz` : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Output -->
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                                    <h3 class="font-semibold text-slate-900 mb-2">Output</h3>
                                    <div class="space-y-1 text-sm">
                                        <div>
                                            <span class="text-slate-600">Voltaje:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.output_voltage ? `${registro.output_voltage} V` : '-' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-600">Frecuencia:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.output_frequency ? `${registro.output_frequency} Hz` : '-' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-600">Potencia:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.output_power ? `${registro.output_power} W` : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Battery -->
                                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200">
                                    <h3 class="font-semibold text-slate-900 mb-2">Battery</h3>
                                    <div class="space-y-1 text-sm">
                                        <div>
                                            <span class="text-slate-600">Voltaje:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.battery_voltage ? `${registro.battery_voltage} V` : '-' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-slate-600">Porcentaje:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.battery_percentage !== null ? `${registro.battery_percentage}%` : '-' }}
                                            </span>
                                        </div>
                                        <div v-if="registro.battery_tiempo_respaldo !== null">
                                            <span class="text-slate-600">Tiempo Respaldo:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.battery_tiempo_respaldo }} min
                                            </span>
                                        </div>
                                        <div v-if="registro.battery_tiempo_descarga !== null">
                                            <span class="text-slate-600">Tiempo Descarga:</span>
                                            <span class="font-medium ml-2">
                                                {{ registro.battery_tiempo_descarga }} min
                                            </span>
                                        </div>
                                        <div v-if="registro.battery_estado">
                                            <span class="text-slate-600">Estado:</span>
                                            <span class="font-medium ml-2 capitalize">
                                                {{ registro.battery_estado }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Temperatura -->
                            <div v-if="registro.temperatura !== null" class="mt-4 p-4 rounded-lg bg-blue-50 border border-blue-200">
                                <h3 class="font-semibold text-slate-900 mb-2">Temperatura</h3>
                                <div class="text-lg font-medium text-blue-800">
                                    {{ registro.temperatura }} °C
                                </div>
                            </div>

                            <!-- Observaciones -->
                            <div v-if="registro.observaciones" class="mt-4 pt-4 border-t border-slate-200">
                                <h3 class="font-semibold text-slate-900 mb-2">Observaciones</h3>
                                <p class="text-sm text-slate-700 whitespace-pre-wrap">
                                    {{ registro.observaciones }}
                                </p>
                            </div>
                        </div>

                        <!-- Botón eliminar -->
                        <button
                            @click="eliminarRegistro(registro.id)"
                            class="px-3 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700 text-sm font-medium"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>

                <!-- Paginación -->
                <div v-if="vitacora.links && vitacora.links.length > 3" class="flex justify-center">
                    <div class="flex gap-1">
                        <Link
                            v-for="link in vitacora.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 rounded-lg border text-sm',
                                link.active
                                    ? 'bg-slate-900 text-white border-slate-900'
                                    : 'bg-white text-slate-700 border-slate-200 hover:bg-slate-50',
                                !link.url ? 'opacity-50 cursor-not-allowed' : '',
                            ]"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>

            <!-- Sin registros -->
            <div v-else class="bg-white border border-slate-200 rounded-xl p-12 text-center">
                <p class="text-slate-600">No hay registros de bitácora aún.</p>
                <Link
                    :href="route('ups.vitacora.create', { ups: ups.id })"
                    class="inline-block mt-4 px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                >
                    Crear Primer Registro
                </Link>
            </div>
        </div>

        <!-- Modal de imagen con navegación -->
        <div
            v-if="modalImages && modalImages.length > 0"
            @click="closeImageModal"
            class="fixed inset-0 bg-black/90 flex items-center justify-center z-50 p-4"
        >
            <div class="relative max-w-6xl max-h-[95vh] w-full">
                <!-- Botón cerrar -->
                <button
                    @click="closeImageModal"
                    class="absolute top-4 right-4 bg-white/20 hover:bg-white/30 text-white rounded-full w-10 h-10 flex items-center justify-center text-xl font-bold z-10 transition-colors"
                >
                    ×
                </button>

                <!-- Imagen actual -->
                <div class="flex items-center justify-center h-full">
                    <img
                        :src="`/storage/${modalImages[currentImageIndex].ruta_imagen}`"
                        alt="Panel frontal UPS"
                        class="max-w-full max-h-[90vh] object-contain rounded-lg"
                        @click.stop
                    />
                </div>

                <!-- Navegación (si hay más de una imagen) -->
                <div v-if="modalImages.length > 1" class="absolute inset-0 flex items-center justify-between p-4 pointer-events-none">
                    <button
                        @click.stop="previousImage"
                        class="bg-white/20 hover:bg-white/30 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl font-bold pointer-events-auto transition-colors"
                        :disabled="currentImageIndex === 0"
                        :class="currentImageIndex === 0 ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        ‹
                    </button>
                    <button
                        @click.stop="nextImage"
                        class="bg-white/20 hover:bg-white/30 text-white rounded-full w-12 h-12 flex items-center justify-center text-2xl font-bold pointer-events-auto transition-colors"
                        :disabled="currentImageIndex === modalImages.length - 1"
                        :class="currentImageIndex === modalImages.length - 1 ? 'opacity-50 cursor-not-allowed' : ''"
                    >
                        ›
                    </button>
                </div>

                <!-- Contador -->
                <div
                    v-if="modalImages.length > 1"
                    class="absolute bottom-4 left-1/2 transform -translate-x-1/2 bg-black/50 text-white px-4 py-2 rounded-lg text-sm"
                >
                    {{ currentImageIndex + 1 }} / {{ modalImages.length }}
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ups: Object,
    vitacora: Object,
    filtros: {
        type: Object,
        default: () => ({
            fecha_desde: null,
            fecha_hasta: null,
        }),
    },
});

const modalImages = ref([]);
const currentImageIndex = ref(0);
const exportando = ref(false);

const filtros = useForm({
    fecha_desde: props.filtros?.fecha_desde || null,
    fecha_hasta: props.filtros?.fecha_hasta || null,
});

const openImageModal = (imagenes, index = 0) => {
    modalImages.value = imagenes;
    currentImageIndex.value = index;
};

const closeImageModal = () => {
    modalImages.value = [];
    currentImageIndex.value = 0;
};

const nextImage = () => {
    if (currentImageIndex.value < modalImages.value.length - 1) {
        currentImageIndex.value++;
    }
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    }
};

const aplicarFiltros = () => {
    filtros.get(route('ups.vitacora.index', { ups: props.ups.id }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const limpiarFiltros = () => {
    filtros.reset();
    filtros.get(route('ups.vitacora.index', { ups: props.ups.id }), {
        preserveState: true,
        preserveScroll: true,
    });
};

const eliminarRegistro = (id) => {
    if (confirm('¿Estás seguro de eliminar este registro?')) {
        router.delete(route('ups.vitacora.destroy', { ups: props.ups.id, vitacora: id }));
    }
};

const getUsuarioNombre = (usuario) => {
    if (!usuario) return 'Desconocido';

    // Intentar nombre + apellido
    if (usuario.nombre && usuario.apellido) {
        return `${usuario.nombre} ${usuario.apellido}`;
    }

    // Intentar name (campo estándar de Laravel)
    if (usuario.name) {
        return usuario.name;
    }

    // Fallback a email
    if (usuario.email) {
        return usuario.email;
    }

    return 'Usuario';
};

const exportarBitacoras = (event) => {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const fechaDesde = filtros.fecha_desde;
    const fechaHasta = filtros.fecha_hasta;

    if (!fechaDesde || !fechaHasta) {
        alert('Por favor, selecciona un rango de fechas para exportar.');
        return;
    }

    exportando.value = true;

    try {
        // Construir URL con filtros
        const baseUrl = route('ups.vitacora.export', { ups: props.ups.id });
        const url = `${baseUrl}?fecha_desde=${encodeURIComponent(fechaDesde)}&fecha_hasta=${encodeURIComponent(fechaHasta)}`;

        // Crear un elemento <a> temporal para forzar la descarga
        const link = document.createElement('a');
        link.href = url;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Esperar un poco antes de deshabilitar el botón
        setTimeout(() => {
            exportando.value = false;
        }, 3000);
    } catch (error) {
        console.error('Error al exportar:', error);
        alert('Error al generar la exportación. Por favor, intenta nuevamente.');
        exportando.value = false;
    }
};

// Navegación con teclado
onMounted(() => {
    const handleKeyPress = (e) => {
        if (modalImages.value.length > 0) {
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                nextImage();
            } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                previousImage();
            } else if (e.key === 'Escape') {
                closeImageModal();
            }
        }
    };

    window.addEventListener('keydown', handleKeyPress);

    return () => {
        window.removeEventListener('keydown', handleKeyPress);
    };
});
</script>

