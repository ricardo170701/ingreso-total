<template>
    <AppLayout>
        <!-- Toasts (feedback de acciones) -->
        <div class="fixed top-4 right-4 z-100 space-y-2 w-[92vw] max-w-sm">
            <div
                v-for="t in toasts"
                :key="t.id"
                :class="[
                    'rounded-xl border shadow-lg p-3 bg-white',
                    t.type === 'success' ? 'border-green-200' : t.type === 'error' ? 'border-red-200' : 'border-slate-200',
                ]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            :class="[
                                'text-sm font-semibold',
                                t.type === 'success' ? 'text-green-700' : t.type === 'error' ? 'text-red-700' : 'text-slate-800',
                            ]"
                        >
                            {{ t.title }}
                        </p>
                        <p v-if="t.message" class="text-xs text-slate-600 mt-0.5 wrap-break-word">
                            {{ t.message }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 text-slate-400 hover:text-slate-700"
                        @click="dismissToast(t.id)"
                        aria-label="Cerrar"
                    >
                        ✕
                    </button>
                </div>
                <details v-if="t.details" class="mt-2">
                    <summary class="text-xs text-slate-500 cursor-pointer select-none">Ver detalle</summary>
                    <pre class="mt-2 text-[11px] leading-snug bg-slate-50 border border-slate-200 rounded-lg p-2 overflow-auto max-h-48">{{ t.details }}</pre>
                </details>
            </div>
        </div>

        <div class="max-w-6xl mx-auto space-y-4">
            <div class="flex items-start justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">
                        {{ puerta.nombre }}
                    </h1>
                    <p class="text-sm text-slate-600">
                        {{ puerta.piso?.nombre || "Sin piso" }}
                        <span class="text-slate-300">·</span>
                        {{ puerta.zona?.nombre || "Sin zona" }}
                    </p>
                </div>
                <div class="flex gap-2 flex-wrap">
                    <Link
                        :href="route('puertas.index')"
                        class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                    >
                        Volver
                    </Link>
                    <Link
                        v-if="hasPermission('edit_puertas')"
                        :href="route('puertas.edit', { puerta: puerta.id })"
                        class="px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 font-medium"
                    >
                        Editar
                    </Link>
                    <button
                        v-if="hasPermission('delete_puertas')"
                        type="button"
                        @click="eliminarPuerta"
                        class="px-3 py-2 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 font-medium"
                    >
                        Eliminar
                    </button>
                </div>
            </div>

            <div
                v-if="$page.props.flash?.message"
                class="p-4 rounded-lg bg-green-50 border border-green-200 text-green-800"
            >
                {{ $page.props.flash.message }}
            </div>

            <!-- Hoja de vida -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl overflow-hidden">
                    <div class="aspect-video bg-slate-100 relative overflow-hidden">
                        <img
                            v-if="puerta.imagen"
                            :src="storageUrl(puerta.imagen)"
                            :alt="puerta.nombre"
                            class="w-full h-full object-cover object-center"
                            loading="lazy"
                            decoding="async"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                            <div class="text-center">
                                <div class="text-4xl leading-none">🚪</div>
                                <div class="mt-2 text-sm">Sin foto</div>
                            </div>
                        </div>

                        <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                            <span
                                :class="[
                                    'px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-white/80 backdrop-blur',
                                    puerta.activo ? 'text-green-700 border-green-200' : 'text-red-700 border-red-200',
                                ]"
                            >
                                {{ puerta.activo ? "Activa" : "Inactiva" }}
                            </span>
                            <span
                                v-if="puerta.estado_mantenimiento === 'programado'"
                                class="px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-yellow-50/90 text-yellow-700 border-yellow-200 backdrop-blur"
                            >
                                ⚠️ Mantenimiento
                            </span>
                            <span
                                v-else-if="puerta.estado_mantenimiento === 'vencido'"
                                class="px-2.5 py-1 rounded-full text-xs font-semibold border shadow-sm bg-red-50/90 text-red-700 border-red-200 backdrop-blur"
                            >
                                🔴 Vencido
                            </span>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-500">Tipo</p>
                                <p class="text-sm text-slate-900 font-medium">
                                    {{ puerta.tipo_puerta?.nombre || "-" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Material</p>
                                <p class="text-sm text-slate-900 font-medium">
                                    {{ puerta.material?.nombre || "-" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Ubicación</p>
                                <p class="text-sm text-slate-700">
                                    {{ puerta.ubicacion || "-" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">Código físico</p>
                                <p class="text-sm text-slate-700">
                                    {{ puerta.codigo_fisico || "-" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">IP Entrada</p>
                                <p class="text-sm text-slate-700 break-all">
                                    {{ puerta.ip_entrada || "-" }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">IP Salida</p>
                                <p class="text-sm text-slate-700 break-all">
                                    {{ puerta.ip_salida || "-" }}
                                </p>
                            </div>
                            <div v-if="puerta.tiempo_apertura">
                                <p class="text-xs text-slate-500">Tiempo de Apertura</p>
                                <p class="text-sm text-slate-700">
                                    {{ puerta.tiempo_apertura }} segundos
                                </p>
                            </div>
                            <div v-if="puerta.tiempo_discapacitados">
                                <p class="text-xs text-slate-500">Tiempo Discapacitados</p>
                                <p class="text-sm text-slate-700">
                                    {{ puerta.tiempo_discapacitados }} segundos
                                </p>
                            </div>
                        </div>

                        <div v-if="puerta.descripcion" class="pt-4 border-t border-slate-200">
                            <p class="text-xs text-slate-500">Descripción</p>
                            <p class="text-sm text-slate-700 whitespace-pre-wrap mt-1">
                                {{ puerta.descripcion }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Acciones -->
                <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-slate-900">Acciones</h2>
                        <button
                            type="button"
                            @click="refrescarEstado({ silent: false })"
                            :disabled="cargandoEstado"
                            class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm disabled:opacity-50"
                        >
                            {{ cargandoEstado ? "Verificando..." : "Verificar" }}
                        </button>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Última verificación</span>
                            <span class="text-slate-600 text-xs">
                                {{ lastConexionCheckedAt ? formatDateTime(lastConexionCheckedAt) : "—" }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Conexión entrada</span>
                            <span class="font-medium" :class="badgeConexionClass(estadosConexion.entrada)">
                                {{ badgeConexionText(estadosConexion.entrada) }}
                            </span>
                        </div>
                        <div
                            v-if="puerta.ip_entrada && hasPermission('toggle_puertas')"
                            class="flex items-center justify-between"
                        >
                            <span class="text-slate-600">Estado (Entrada)</span>
                            <span class="font-medium" :class="badgeManualClass()">
                                {{ badgeManualText() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-600">Conexión salida</span>
                            <span class="font-medium" :class="badgeConexionClass(estadosConexion.salida)">
                                {{ badgeConexionText(estadosConexion.salida) }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <label class="text-sm text-slate-700 select-none flex items-center gap-2">
                                <input type="checkbox" v-model="autoRefreshEnabled" class="rounded border-slate-300" />
                                Auto-refrescar
                            </label>
                            <select
                                v-model.number="autoRefreshSeconds"
                                class="text-sm rounded-lg border border-slate-200 px-2 py-1 text-slate-700"
                                :disabled="!autoRefreshEnabled"
                            >
                                <option :value="10">10s</option>
                                <option :value="15">15s</option>
                                <option :value="30">30s</option>
                                <option :value="60">60s</option>
                            </select>
                        </div>
                        <p class="text-xs text-slate-500">
                            Actualiza en segundo plano (conexión + estado de entrada). No bloquea la navegación.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-200 space-y-2">
                        <button
                            v-if="hasPermission('toggle_puertas') && puerta.ip_entrada"
                            type="button"
                            @click="toggleEntrada()"
                            :disabled="toggling === 'entrada'"
                            class="w-full px-4 py-2 rounded-xl bg-slate-900 text-white hover:bg-slate-800 font-semibold disabled:opacity-50"
                        >
                            {{ toggling === 'entrada' ? "Enviando..." : "Abrir/Cerrar (Entrada)" }}
                        </button>
                        <button
                            v-if="hasPermission('reboot_puertas') && (puerta.ip_entrada || puerta.ip_salida)"
                            type="button"
                            @click="reiniciar"
                            :disabled="reiniciando"
                            class="w-full px-4 py-2 rounded-xl border border-blue-200 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold disabled:opacity-50"
                        >
                            {{ reiniciando ? "Reiniciando..." : "Reiniciar Raspberry" }}
                        </button>
                        <Link
                            v-if="hasPermission('create_mantenimientos')"
                            :href="route('mantenimientos.create', { puerta_id: puerta.id })"
                            class="w-full block text-center px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold"
                        >
                            Nuevo mantenimiento
                        </Link>
                        <Link
                            v-if="hasPermission('view_mantenimientos')"
                            :href="route('mantenimientos.index', { puerta_id: puerta.id })"
                            class="w-full block text-center px-4 py-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold"
                        >
                            Ver mantenimientos
                        </Link>
                    </div>

                    <div v-if="lastAction" class="pt-3 border-t border-slate-200">
                        <details>
                            <summary class="text-sm font-semibold text-slate-800 cursor-pointer select-none">
                                Última respuesta ({{ lastAction.name }})
                            </summary>
                            <div class="mt-2 text-xs text-slate-600 flex items-center justify-between">
                                <span>Fecha</span>
                                <span>{{ formatDateTime(lastAction.at) }}</span>
                            </div>
                            <pre class="mt-2 text-[11px] leading-snug bg-slate-50 border border-slate-200 rounded-lg p-2 max-h-64 overflow-auto whitespace-pre-wrap wrap-break-word">{{ lastAction.details }}</pre>
                        </details>
                    </div>
                </div>
            </div>

            <!-- Mantenimientos (resumen) -->
            <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Mantenimientos</h2>
                        <p class="text-sm text-slate-600">
                            Historial de mantenimientos asociados a esta puerta.
                        </p>
                    </div>
                </div>

                <div v-if="puerta.mantenimientos?.length" class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 border border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Tipo</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Fecha límite</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-700">Falla</th>
                                <th class="px-4 py-3 text-right font-semibold text-slate-700">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="m in puerta.mantenimientos"
                                :key="m.id"
                                class="border-b border-slate-100"
                            >
                                <td class="px-4 py-3 text-slate-900">
                                    {{ formatDate(m.fecha_mantenimiento) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-semibold',
                                            m.tipo === 'realizado'
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-yellow-100 text-yellow-700',
                                        ]"
                                    >
                                        {{ m.tipo === "realizado" ? "Realizado" : "Programado" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ m.tipo === 'programado' ? (m.fecha_fin_programada ? formatDate(m.fecha_fin_programada) : '-') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    <span class="line-clamp-2">
                                        {{ m.falla || "-" }}
                                    </span>
                                    <p v-if="(m.documentos?.length || 0) > 0" class="text-xs text-slate-500 mt-1">
                                        📄 {{ m.documentos.length }} documento(s)
                                    </p>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex justify-end gap-2 flex-wrap">
                                        <Link
                                            v-if="hasPermission('view_mantenimientos')"
                                            :href="route('mantenimientos.show', { mantenimiento: m.id })"
                                            class="px-3 py-1.5 rounded-md border border-slate-200 hover:bg-white text-slate-700 text-sm font-medium"
                                        >
                                            Ver
                                        </Link>
                                        <Link
                                            v-if="hasPermission('edit_mantenimientos')"
                                            :href="route('mantenimientos.edit', { mantenimiento: m.id })"
                                            class="px-3 py-1.5 rounded-md border border-slate-200 hover:bg-white text-slate-700 text-sm font-medium"
                                        >
                                            Editar
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-sm text-slate-500 italic">
                    No hay mantenimientos registrados para esta puerta.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import axios from "axios";

const props = defineProps({
    puerta: Object,
});

const page = usePage();
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const hasPermission = (permission) => userPermissions.value.includes(permission);

const storageUrl = (path) => {
    if (!path) return "";
    if (String(path).startsWith("http")) return path;
    return `/storage/${path}`;
};

const estadosConexion = ref({ entrada: null, salida: null });
const estadosManual = ref({ entrada: null }); // { manual_open: boolean } | null (solo entrada)
const cargandoEstado = ref(false);
const toggling = ref(null);
const reiniciando = ref(false);

// Última verificación / auto refresh
const lastConexionCheckedAt = ref(null); // Date | null
const lastManualCheckedAt = ref(null); // Date | null
const autoRefreshEnabled = ref(false);
const autoRefreshSeconds = ref(15);
let autoRefreshTimer = null;

// Última respuesta (debug)
const lastAction = ref(null); // { name: string, at: Date, details: string } | null

// Toasts (sin dependencias)
const toasts = ref([]);
const toastSeq = ref(1);

const dismissToast = (id) => {
    toasts.value = toasts.value.filter((t) => t.id !== id);
};

const pushToast = ({ type = "info", title, message = "", details = "" }) => {
    const id = toastSeq.value++;
    toasts.value.push({ id, type, title, message, details });
    setTimeout(() => dismissToast(id), type === "error" ? 8000 : 4500);
};

const setLastAction = (name, data) => {
    const details = typeof data === "string" ? data : JSON.stringify(data ?? null, null, 2);
    lastAction.value = { name, at: new Date(), details };
};

const formatDateTime = (d) => {
    if (!d) return "—";
    const date = d instanceof Date ? d : new Date(d);
    return date.toLocaleString("es-ES", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
    });
};

const badgeConexionText = (value) => {
    if (value === true) return "🟢 Conectada";
    if (value === false) return "🔴 Desconectada";
    return "⚪ Sin verificar";
};

const badgeConexionClass = (value) => {
    if (value === true) return "text-green-700";
    if (value === false) return "text-red-700";
    return "text-slate-500";
};

const badgeManualText = () => {
    const estado = estadosManual.value?.entrada;
    if (!estado) return "⚪ Sin verificar";
    return estado.manual_open ? "🟢 Abierta" : "🔴 Cerrada";
};

const badgeManualClass = () => {
    const estado = estadosManual.value?.entrada;
    if (!estado) return "text-slate-500";
    return estado.manual_open ? "text-green-700" : "text-red-700";
};

const obtenerEstadoManualEntrada = async () => {
    if (!props.puerta.ip_entrada) {
        estadosManual.value.entrada = null;
        return;
    }

    try {
        const resp = await axios.get(
            route("puertas.estado", { puerta: props.puerta.id }) + `?tipo=entrada`,
            {
                withCredentials: true,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                },
            }
        );
        if (resp.data?.success && resp.data?.estado) {
            estadosManual.value.entrada = resp.data.estado;
            lastManualCheckedAt.value = new Date();
        }
    } catch (e) {
        console.error(`Error al obtener estado manual (entrada):`, e);
        estadosManual.value.entrada = null;
    }
};

const refrescarEstado = async ({ silent = true } = {}) => {
    if (cargandoEstado.value) return;
    cargandoEstado.value = true;

    try {
        // Usar refrescar-conexiones para obtener estados separados (entrada/salida)
        const resp = await axios.post(
            route("puertas.refrescar-conexiones"),
            { puerta_ids: [props.puerta.id] },
            {
                withCredentials: true,
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
            }
        );

        if (resp.data?.success && resp.data?.estados?.[props.puerta.id]) {
            estadosConexion.value = resp.data.estados[props.puerta.id];
            lastConexionCheckedAt.value = new Date();
            setLastAction("verificar", resp.data);
            if (!silent) {
                pushToast({
                    type: "success",
                    title: "Verificación completada",
                    message: resp.data?.message || "Estados de conexión actualizados",
                });
            }
        } else {
            setLastAction("verificar", resp?.data ?? null);
            if (!silent) {
                pushToast({
                    type: "error",
                    title: "No se pudo verificar",
                    message: "Respuesta inesperada del servidor.",
                    details: JSON.stringify(resp?.data ?? null, null, 2),
                });
            }
        }
    } catch (e) {
        console.error("Error al verificar conexión:", e);
        setLastAction("verificar (error)", e?.response?.data ?? e?.message ?? null);
        if (!silent) {
            pushToast({
                type: "error",
                title: "Error al verificar",
                message: e?.response?.data?.error || e?.message || "Error desconocido",
                details: JSON.stringify(e?.response?.data ?? null, null, 2),
            });
        }
    } finally {
        cargandoEstado.value = false;
    }
};

const toggleEntrada = async () => {
    if (toggling.value) return;
    toggling.value = "entrada";
    try {
        const resp = await axios.post(
            route("puertas.toggle", { puerta: props.puerta.id }),
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
        // Si el backend devuelve el nuevo estado, aplicarlo; si no, refrescar.
        if (resp?.data?.success && resp?.data?.resultado) {
            estadosManual.value.entrada = {
                manual_open: !!resp.data.resultado.manual_open,
            };
            lastManualCheckedAt.value = new Date();
            pushToast({
                type: "success",
                title: "Comando enviado",
                message: resp.data?.message || "Apertura/cierre enviado",
                details: JSON.stringify(resp.data, null, 2),
            });
            setLastAction("toggle entrada", resp.data);
        } else {
            await obtenerEstadoManualEntrada();
            pushToast({
                type: "success",
                title: "Comando enviado",
                message: resp?.data?.message || "Apertura/cierre enviado. Estado actualizado.",
                details: JSON.stringify(resp?.data ?? null, null, 2),
            });
            setLastAction("toggle entrada", resp?.data ?? null);
        }
    } catch (e) {
        console.error("Error al togglear puerta:", e);
        pushToast({
            type: "error",
            title: "Error al enviar comando",
            message: e?.response?.data?.error || e?.message || "Error desconocido",
            details: JSON.stringify(e?.response?.data ?? null, null, 2),
        });
        setLastAction("toggle entrada (error)", e?.response?.data ?? e?.message ?? null);
    } finally {
        toggling.value = null;
    }
};

const reiniciar = async () => {
    if (
        !confirm(
            `¿Reiniciar las Raspberry Pi de la puerta "${props.puerta.nombre}"?\n\nSe enviará el comando a entrada y salida (si aplica).`
        )
    ) {
        return;
    }

    reiniciando.value = true;
    try {
        const resp = await axios.post(
            route("puertas.reiniciar", { puerta: props.puerta.id }),
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
        if (resp.data?.success) {
            const resultados = resp.data?.resultados || {};
            const okEntrada = resultados?.entrada?.success;
            const okSalida = resultados?.salida?.success;

            let msg = resp.data?.message || "Comando de reinicio enviado";
            const partes = [];
            if (props.puerta.ip_entrada) partes.push(`Entrada: ${okEntrada === true ? "OK" : okEntrada === false ? "FALLÓ" : "—"}`);
            if (props.puerta.ip_salida) partes.push(`Salida: ${okSalida === true ? "OK" : okSalida === false ? "FALLÓ" : "—"}`);
            if (partes.length) msg += ` (${partes.join(" · ")})`;

            pushToast({
                type: (okEntrada === false || okSalida === false) ? "error" : "success",
                title: "Reinicio",
                message: msg,
                details: JSON.stringify(resp.data, null, 2),
            });
            setLastAction("reiniciar", resp.data);
        } else {
            pushToast({
                type: "error",
                title: "Reinicio",
                message: "Respuesta inesperada del servidor.",
                details: JSON.stringify(resp?.data ?? null, null, 2),
            });
            setLastAction("reiniciar", resp?.data ?? null);
        }
    } catch (e) {
        console.error("Error al reiniciar puerta:", e);
        pushToast({
            type: "error",
            title: "Error al reiniciar",
            message: e?.response?.data?.error || e?.message || "Error desconocido",
            details: JSON.stringify(e?.response?.data ?? null, null, 2),
        });
        setLastAction("reiniciar (error)", e?.response?.data ?? e?.message ?? null);
    } finally {
        reiniciando.value = false;
    }
};

const eliminarPuerta = () => {
    if (!confirm(`¿Eliminar la puerta "${props.puerta.nombre}"? Esta acción no se puede deshacer.`)) return;
    router.delete(route("puertas.destroy", { puerta: props.puerta.id }));
};

const formatDate = (dateStr) => {
    if (!dateStr) return "-";
    return new Date(dateStr).toLocaleDateString("es-ES", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
};

onMounted(() => {
    // Inicial (silencioso, sin toast)
    refrescarEstado({ silent: true });
    obtenerEstadoManualEntrada();

    // Preferencias auto refresh por puerta
    try {
        const key = `puertas_show_auto_refresh_${props.puerta.id}`;
        const raw = localStorage.getItem(key);
        if (raw) {
            const parsed = JSON.parse(raw);
            autoRefreshEnabled.value = !!parsed.enabled;
            autoRefreshSeconds.value = Number(parsed.seconds || 15);
        }
    } catch (_) {}
});

watch(
    () => [autoRefreshEnabled.value, autoRefreshSeconds.value],
    () => {
        // Guardar preferencia
        try {
            const key = `puertas_show_auto_refresh_${props.puerta.id}`;
            localStorage.setItem(
                key,
                JSON.stringify({
                    enabled: autoRefreshEnabled.value,
                    seconds: autoRefreshSeconds.value,
                })
            );
        } catch (_) {}

        // Reiniciar timer
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
        if (!autoRefreshEnabled.value) return;

        const ms = Math.max(5, Number(autoRefreshSeconds.value || 15)) * 1000;
        autoRefreshTimer = setInterval(async () => {
            if (cargandoEstado.value || reiniciando.value || toggling.value) return;
            await refrescarEstado({ silent: true });
            if (props.puerta.ip_entrada) {
                await obtenerEstadoManualEntrada();
            }
        }, ms);
    },
    { immediate: true }
);

onUnmounted(() => {
    if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
        autoRefreshTimer = null;
    }
});
</script>


