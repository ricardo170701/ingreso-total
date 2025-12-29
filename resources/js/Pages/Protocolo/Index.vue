<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-slate-900">
                    Protocolo de Emergencia
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    Sistema de apertura de emergencia para todas las puertas
                </p>
            </div>

            <!-- Alerta de advertencia -->
            <div
                class="bg-red-50 border-2 border-red-200 rounded-xl p-6"
            >
                <div class="flex items-start gap-4">
                    <span class="text-3xl">⚠️</span>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-red-900 mb-2">
                            Advertencia: Protocolo de Emergencia
                        </h3>
                        <p class="text-sm text-red-800 mb-3">
                            Este protocolo abrirá <strong>todas las puertas activas</strong> del sistema
                            durante <strong>15 minutos</strong> (900 segundos). Solo debe ser utilizado en
                            situaciones de emergencia real.
                        </p>
                        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                            <li>Las puertas permanecerán abiertas por 15 minutos</li>
                            <li>El sistema registrará quién ejecutó el protocolo</li>
                            <li>Se enviará una señal a todas las puertas en paralelo</li>
                            <li>Si se corta la red, las puertas permanecerán abiertas hasta que venza el tiempo</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Botón de Pánico -->
            <div class="bg-white border-2 border-slate-200 rounded-xl p-8">
                <div class="text-center space-y-4">
                    <h2 class="text-xl font-semibold text-slate-900">
                        Activar Protocolo de Emergencia
                    </h2>
                    <p class="text-sm text-slate-600">
                        Se abrirán <strong>{{ puertas.length }}</strong> puerta(s) activa(s) con IPs
                        configuradas
                    </p>

                    <form @submit.prevent="activarEmergencia" class="space-y-4">
                        <div class="flex items-center justify-center gap-4">
                            <label
                                for="duration"
                                class="text-sm font-medium text-slate-700"
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
                                class="w-32 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing || puertas.length === 0"
                            class="inline-flex items-center justify-center gap-3 px-8 py-4 bg-red-600 text-white font-bold text-lg rounded-xl hover:bg-red-700 disabled:bg-slate-400 disabled:cursor-not-allowed transition-colors shadow-lg hover:shadow-xl"
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
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Puertas que se abrirán ({{ puertas.length }})
                </h3>
                <div
                    v-if="puertas.length > 0"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3"
                >
                    <div
                        v-for="puerta in puertas"
                        :key="puerta.id"
                        class="p-3 bg-slate-50 border border-slate-200 rounded-lg"
                    >
                        <p class="font-medium text-slate-900">
                            {{ puerta.nombre }}
                        </p>
                        <div class="mt-2 space-y-1 text-xs text-slate-600">
                            <p v-if="puerta.ip_entrada">
                                <span class="font-medium">IP Entrada:</span>
                                {{ puerta.ip_entrada }}
                            </p>
                            <p v-if="puerta.ip_salida">
                                <span class="font-medium">IP Salida:</span>
                                {{ puerta.ip_salida }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 italic">
                    No hay puertas activas con IPs configuradas
                </p>
            </div>

            <!-- Últimas Corridas -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">
                    Últimas Corridas de Emergencia
                </h3>
                <div v-if="ultimasCorridas.length > 0" class="space-y-3">
                    <div
                        v-for="corrida in ultimasCorridas"
                        :key="corrida.id"
                        class="p-4 bg-slate-50 border border-slate-200 rounded-lg"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <p class="font-medium text-slate-900">
                                    Ejecutado por: {{ corrida.usuario }}
                                </p>
                                <p class="text-sm text-slate-600 mt-1">
                                    {{ corrida.fecha }}
                                </p>
                                <div class="mt-2 flex items-center gap-4 text-sm">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            getEstadoClass(corrida.estado),
                                        ]"
                                    >
                                        {{ corrida.estado }}
                                    </span>
                                    <span class="text-slate-600">
                                        {{ corrida.puertas_exitosas }}/{{ corrida.total_puertas }}
                                        exitosas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 italic">
                    No hay corridas registradas
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
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
            // Recargar la página después de un breve delay para ver los resultados
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        },
    });
};

const getEstadoClass = (estado) => {
    const classes = {
        iniciado: "bg-blue-100 text-blue-800",
        en_proceso: "bg-yellow-100 text-yellow-800",
        completado: "bg-green-100 text-green-800",
        fallido: "bg-red-100 text-red-800",
    };
    return classes[estado] || "bg-slate-100 text-slate-800";
};
</script>

