<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                    Protocolo de Emergencia
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                    Sistema de apertura de emergencia para todas las puertas
                </p>
            </div>

            <!-- Alerta de advertencia -->
            <div
                class="bg-red-50 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-800 rounded-xl p-6 transition-colors duration-200"
            >
                <div class="flex items-start gap-4">
                    <span class="text-3xl">⚠️</span>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-red-900 dark:text-red-300 mb-2">
                            Advertencia: Protocolo de Emergencia
                        </h3>
                        <p class="text-sm text-red-800 dark:text-red-300 mb-3">
                            Este protocolo abrirá <strong>todas las puertas activas</strong> del sistema
                            durante <strong>15 minutos</strong> (900 segundos). Solo debe ser utilizado en
                            situaciones de emergencia real.
                        </p>
                        <ul class="text-sm text-red-700 dark:text-red-400 space-y-1 list-disc list-inside">
                            <li>Las puertas permanecerán abiertas por 15 minutos</li>
                            <li>El sistema registrará quién ejecutó el protocolo</li>
                            <li>Se enviará una señal a todas las puertas en paralelo</li>
                            <li>Si se corta la red, las puertas permanecerán abiertas hasta que venza el tiempo</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Botón de Pánico -->
            <div class="bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-xl p-8 transition-colors duration-200">
                <div class="text-center space-y-4">
                    <h2 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Activar Protocolo de Emergencia
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Se abrirán <strong class="text-slate-900 dark:text-slate-100">{{ puertas.length }}</strong> puerta(s) con conexión activa
                    </p>

                    <form @submit.prevent="activarEmergencia" class="space-y-4">
                        <div class="flex items-center justify-center gap-4">
                            <label
                                for="duration"
                                class="text-sm font-medium text-slate-700 dark:text-slate-300"
                            >
                                Duración (segundos):
                            </label>
                            <input
                                id="duration"
                                v-model.number="form.duration_seconds"
                                type="number"
                                min="10"
                                max="3600"
                                step="10"
                                class="w-32 px-3 py-2 border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded-lg focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:border-red-500 dark:focus:border-red-600 transition-colors duration-200"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || puertas.length === 0"
                            class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-red-600 dark:bg-red-700 text-white font-bold text-lg rounded-xl hover:bg-red-700 dark:hover:bg-red-600 disabled:bg-slate-400 dark:disabled:bg-slate-600 disabled:cursor-not-allowed transition-colors duration-200 shadow-lg hover:shadow-xl"
                        >
                            <span class="text-2xl">🚨</span>
                            <span>
                                {{
                                    form.processing
                                        ? "Activando..."
                                        : "ACTIVAR PROTOCOLO DE EMERGENCIA"
                                }}
                            </span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Lista de Puertas -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Puertas que se abrirán ({{ puertas.length }})
                </h3>
                <div
                    v-if="puertas.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                >
                    <div
                        v-for="puerta in puertas"
                        :key="puerta.id"
                        class="p-3 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 rounded-lg transition-colors duration-200"
                    >
                        <p class="font-medium text-slate-900 dark:text-slate-100">
                            {{ puerta.nombre }}
                        </p>
                        <div class="mt-2 space-y-1 text-xs text-slate-600 dark:text-slate-400">
                            <p v-if="puerta.ip_usada && puerta.tipo_ip_usada">
                                <span class="font-medium text-slate-700 dark:text-slate-300">Se usará:</span>
                                {{ puerta.tipo_ip_usada }} ({{ puerta.ip_usada }})
                            </p>
                            <p v-if="puerta.ip_entrada">
                                <span class="font-medium text-slate-700 dark:text-slate-300">IP Entrada:</span>
                                {{ puerta.ip_entrada }}
                            </p>
                            <p v-if="puerta.ip_salida">
                                <span class="font-medium text-slate-700 dark:text-slate-300">IP Salida:</span>
                                {{ puerta.ip_salida }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">
                    No hay puertas con conexión activa disponibles
                </p>
            </div>

            <!-- Últimas Corridas -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                    Últimas Corridas de Emergencia
                </h3>
                <div v-if="ultimasCorridas.length > 0" class="space-y-3">
                    <div
                        v-for="corrida in ultimasCorridas"
                        :key="corrida.id"
                        class="p-4 bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 rounded-lg transition-colors duration-200"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0 space-y-1">
                                <p class="font-medium text-slate-900 dark:text-slate-100">
                                    Ejecutado por: {{ corrida.usuario }}
                                </p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    Fecha y hora: {{ corrida.fecha_inicio }}
                                </p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">
                                    <template v-if="corridaEnProceso(corrida)">
                                        <span class="font-medium text-amber-700 dark:text-amber-400">Cierra en: </span>
                                        {{ countdownParaCorrida(corrida) }}
                                    </template>
                                    <template v-else>
                                        <span class="font-medium text-slate-700 dark:text-slate-300">Culminación: </span>
                                        {{ culminacionCalculada(corrida) }}
                                    </template>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 dark:text-slate-400 italic">
                    No hay corridas registradas
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    puertas: {
        type: Array,
        required: true,
    },
    ultimasCorridas: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    duration_seconds: 900, // 15 minutos por defecto
});

// Ticker para actualizar el countdown cada segundo (solo mientras haya alguna corrida en proceso)
const now = ref(Date.now());
let tickInterval = null;

function fechaFinCorrida(corrida) {
    if (!corrida?.created_at_iso || corrida.duration_seconds == null) return null;
    const inicio = new Date(corrida.created_at_iso).getTime();
    return inicio + corrida.duration_seconds * 1000;
}

function corridaEnProceso(corrida) {
    const fin = fechaFinCorrida(corrida);
    return fin != null && now.value < fin;
}

function countdownParaCorrida(corrida) {
    const fin = fechaFinCorrida(corrida);
    if (fin == null || now.value >= fin) return "—";
    const restante = Math.max(0, Math.floor((fin - now.value) / 1000));
    const m = Math.floor(restante / 60);
    const s = restante % 60;
    return `${m} min ${String(s).padStart(2, "0")} s`;
}

function culminacionCalculada(corrida) {
    const fin = fechaFinCorrida(corrida);
    if (fin == null) return "—";
    const d = new Date(fin);
    const pad = (n) => String(n).padStart(2, "0");
    return `${pad(d.getDate())}/${pad(d.getMonth() + 1)}/${d.getFullYear()} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

onMounted(() => {
    tickInterval = setInterval(() => {
        now.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (tickInterval) clearInterval(tickInterval);
});

const activarEmergencia = () => {
    if (
        !confirm(
            "¿Estás seguro de que deseas activar el protocolo de emergencia? Esto abrirá TODAS las puertas durante 15 minutos."
        )
    ) {
        return;
    }

    form.post(route("protocolo.emergencia.activate"), {
        preserveScroll: true,
        onSuccess: () => {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        },
    });
};
</script>

