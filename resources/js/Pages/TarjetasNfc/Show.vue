<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-4">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Tarjeta NFC: {{ tarjeta.codigo }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        {{ tarjeta.nombre || "Sin nombre" }}
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <Link
                        :href="route('tarjetas-nfc.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                    >
                        Volver
                    </Link>
                    <Link
                        :href="route('tarjetas-nfc.edit', { tarjetaNfc: tarjeta.id })"
                        class="px-3 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-medium transition-colors duration-200"
                    >
                        Editar
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
                                Tarjeta NFC actualizada exitosamente
                            </p>
                            <p class="text-xs text-green-700 dark:text-green-300 mt-1">
                                Los cambios se han aplicado a la tarjeta "{{ tarjeta.codigo }}"
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Información de la Tarjeta</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Código NFC</p>
                                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium font-mono">
                                    {{ tarjeta.codigo }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Estado</p>
                                <span
                                    :class="[
                                        'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                        tarjeta.activo
                                            ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                                            : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                    ]"
                                >
                                    {{ tarjeta.activo ? "Activa" : "Inactiva" }}
                                </span>
                            </div>
                            <div v-if="tarjeta.fecha_asignacion">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Fecha Asignación</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ formatDateTime(tarjeta.fecha_asignacion) }}
                                </p>
                            </div>
                            <div v-if="tarjeta.fecha_expiracion">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Fecha Expiración</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ formatDateTime(tarjeta.fecha_expiracion) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="tarjeta.user">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Usuario Asignado</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Nombre</p>
                                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium">
                                    {{ tarjeta.user.name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Email</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ tarjeta.user.email }}
                                </p>
                            </div>
                            <div v-if="tarjeta.user.n_identidad">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Cédula</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ tarjeta.user.n_identidad }}
                                </p>
                            </div>
                            <div v-if="tarjeta.user.role">
                                <p class="text-xs text-slate-500 dark:text-slate-400">Rol</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ tarjeta.user.role.name }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="tarjeta.gerencia">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Gerencia Destino</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Secretaría</p>
                                <p class="text-sm text-slate-900 dark:text-slate-100 font-medium">
                                    {{ tarjeta.gerencia.secretaria?.nombre || "-" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Gerencia</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300">
                                    {{ tarjeta.gerencia.nombre }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div v-if="tarjeta.puertas && tarjeta.puertas.length > 0">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Puertas Autorizadas ({{ tarjeta.puertas.length }})</h2>
                        <div class="space-y-2">
                            <div
                                v-for="p in tarjeta.puertas"
                                :key="p.id"
                                class="p-3 bg-slate-50 dark:bg-slate-700 rounded-lg border border-slate-200 dark:border-slate-600"
                            >
                                <div class="font-medium text-slate-900 dark:text-slate-100">{{ p.nombre }}</div>
                                <div v-if="p.pivot.hora_inicio || p.pivot.hora_fin" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Horario: {{ p.pivot.hora_inicio || "00:00" }} - {{ p.pivot.hora_fin || "23:59" }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="tarjeta.observaciones">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">Observaciones</h2>
                        <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-wrap">
                            {{ tarjeta.observaciones }}
                        </p>
                    </div>

                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                        <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-3">
                            Historial de asignaciones
                        </h2>
                        <div v-if="(asignaciones || []).length === 0" class="text-sm text-slate-600 dark:text-slate-400">
                            Sin historial todavía.
                        </div>
                        <div v-else class="space-y-2">
                            <div
                                v-for="a in asignaciones"
                                :key="a.id"
                                class="p-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">
                                            {{ a.user?.name || "Usuario eliminado" }}
                                            <span v-if="a.user?.n_identidad" class="text-slate-500 dark:text-slate-400 font-normal">
                                                · CC: {{ a.user.n_identidad }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-slate-600 dark:text-slate-400">
                                            {{ formatDateTime(a.fecha_asignacion) }}
                                            <span v-if="a.fecha_desasignacion"> → {{ formatDateTime(a.fecha_desasignacion) }}</span>
                                            <span v-else class="text-green-700 dark:text-green-400"> (asignada)</span>
                                        </div>
                                        <div v-if="a.asignado_por" class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            Asignado por: {{ a.asignado_por.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 space-y-4 transition-colors duration-200">
                    <h2 class="text-sm font-semibold text-slate-900 dark:text-slate-100">Acciones</h2>
                    <div class="space-y-2">
                        <Link
                            :href="route('tarjetas-nfc.edit', { tarjetaNfc: tarjeta.id })"
                            class="w-full block text-center px-4 py-2 rounded-xl bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 font-semibold transition-colors duration-200"
                        >
                            Editar Tarjeta
                        </Link>
                        <button
                            v-if="tarjeta.user"
                            type="button"
                            @click="onDesasignar"
                            class="w-full block text-center px-4 py-2 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-semibold transition-colors duration-200"
                        >
                            Desasignar tarjeta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, watch, Transition } from "vue";

const props = defineProps({
    tarjeta: Object,
    asignaciones: Array,
});

const page = usePage();

// Mensaje de éxito
const showSuccessMessage = ref(false);

// Mostrar mensaje de éxito si hay flash message o success
watch(
    () => page.props.flash?.message || page.props.flash?.success,
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

const onDesasignar = () => {
    if (!confirm("¿Desasignar esta tarjeta NFC? Quedará disponible para otro visitante.")) return;
    router.post(route("tarjetas-nfc.desasignar", { tarjetaNfc: props.tarjeta.id }));
};
</script>
