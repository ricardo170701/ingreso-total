<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        Mantenimiento #{{ mantenimiento.id }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        Detalles del mantenimiento registrado.
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('mantenimientos.edit', { mantenimiento: mantenimiento.id })"
                        class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                    >
                        Editar
                    </Link>
                    <Link
                        :href="route('mantenimientos.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                    >
                        Volver
                    </Link>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-xl p-6 space-y-6">
                <!-- Información básica -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-slate-500">Puerta</label>
                        <p class="text-base text-slate-900 font-medium">
                            {{ mantenimiento.puerta?.nombre }}
                        </p>
                        <p class="text-sm text-slate-600">
                            {{ mantenimiento.puerta?.piso?.nombre || "-" }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">Fecha</label>
                        <p class="text-base text-slate-900">
                            {{ new Date(mantenimiento.fecha_mantenimiento).toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">Usuario</label>
                        <p class="text-base text-slate-900">
                            {{ mantenimiento.usuario?.name || mantenimiento.usuario?.email }}
                        </p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">Imágenes</label>
                        <p class="text-base text-slate-900">
                            {{ mantenimiento.imagenes?.length || 0 }} imágenes
                        </p>
                    </div>
                </div>

                <!-- Defectos -->
                <div>
                    <label class="text-sm font-medium text-slate-500 mb-3 block">
                        Estado de los Defectos
                    </label>
                    <div class="space-y-2">
                        <div
                            v-for="defecto in mantenimiento.defectos"
                            :key="defecto.id"
                            class="flex items-center gap-4 p-3 bg-slate-50 rounded-lg"
                        >
                            <span class="flex-1 text-sm font-medium text-slate-700">
                                {{ defecto.nombre }}
                            </span>
                            <span
                                :class="[
                                    'px-3 py-1 rounded text-sm font-medium',
                                    defecto.pivot?.nivel_gravedad === 0
                                        ? 'bg-green-100 text-green-700'
                                        : defecto.pivot?.nivel_gravedad === 1
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : defecto.pivot?.nivel_gravedad === 2
                                        ? 'bg-orange-100 text-orange-700'
                                        : 'bg-red-100 text-red-700',
                                ]"
                            >
                                {{
                                    defecto.pivot?.nivel_gravedad === 0
                                        ? "Sin defecto"
                                        : defecto.pivot?.nivel_gravedad === 1
                                        ? "Defecto ligero"
                                        : defecto.pivot?.nivel_gravedad === 2
                                        ? "Defecto grave"
                                        : "Defecto muy grave"
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Otros defectos -->
                <div v-if="mantenimiento.otros_defectos">
                    <label class="text-sm font-medium text-slate-500 mb-2 block">
                        Otros Defectos
                    </label>
                    <p class="text-sm text-slate-700 bg-slate-50 p-3 rounded-lg">
                        {{ mantenimiento.otros_defectos }}
                    </p>
                </div>

                <!-- Observaciones -->
                <div v-if="mantenimiento.observaciones">
                    <label class="text-sm font-medium text-slate-500 mb-2 block">
                        Observaciones
                    </label>
                    <p class="text-sm text-slate-700 bg-slate-50 p-3 rounded-lg">
                        {{ mantenimiento.observaciones }}
                    </p>
                </div>

                <!-- Imágenes -->
                <div v-if="mantenimiento.imagenes?.length > 0">
                    <label class="text-sm font-medium text-slate-500 mb-3 block">
                        Imágenes de Evidencia
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div
                            v-for="imagen in mantenimiento.imagenes"
                            :key="imagen.id"
                            class="relative group"
                        >
                            <img
                                :src="`/storage/${imagen.ruta_imagen}`"
                                :alt="imagen.descripcion || 'Imagen de evidencia'"
                                class="w-full h-48 object-cover rounded-lg border border-slate-200 cursor-pointer hover:shadow-lg transition-shadow"
                                @click="openImageModal(imagen)"
                            />
                            <div
                                v-if="imagen.descripcion"
                                class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-xs p-2 rounded-b-lg"
                            >
                                {{ imagen.descripcion }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para ver imagen en grande -->
            <div
                v-if="imagenModal"
                class="fixed inset-0 bg-black/75 z-50 flex items-center justify-center p-4"
                @click="closeImageModal"
            >
                <div class="relative max-w-4xl max-h-full">
                    <img
                        :src="`/storage/${imagenModal.ruta_imagen}`"
                        :alt="imagenModal.descripcion || 'Imagen'"
                        class="max-w-full max-h-[90vh] rounded-lg"
                    />
                    <button
                        @click="closeImageModal"
                        class="absolute top-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center text-2xl hover:bg-slate-100"
                    >
                        ×
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    mantenimiento: Object,
});

const imagenModal = ref(null);

const openImageModal = (imagen) => {
    imagenModal.value = imagen;
};

const closeImageModal = () => {
    imagenModal.value = null;
};
</script>

