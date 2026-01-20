<template>
    <AppLayout>
        <div class="max-w-7xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">
                        Tarjetas NFC
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Gestiona las tarjetas NFC asignadas a visitantes.
                    </p>
                </div>
                <Link
                    :href="route('tarjetas-nfc.create')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 transition-colors duration-200"
                >
                    <span>➕</span>
                    <span>Nueva Tarjeta</span>
                </Link>
            </div>

            <!-- Buscador -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <input
                        v-model="searchForm.search"
                        type="text"
                        class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 transition-colors duration-200"
                        placeholder="Buscar por código, nombre, usuario o cédula…"
                    />
                    <div class="flex gap-2">
                        <button
                            type="submit"
                            class="px-4 py-2 rounded-lg bg-green-600 dark:bg-green-700 text-white hover:bg-green-700 dark:hover:bg-green-600 font-medium transition-colors duration-200"
                        >
                            Buscar
                        </button>
                        <button
                            type="button"
                            @click="clearSearch"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Limpiar
                        </button>
                    </div>
                </form>
            </div>

            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden transition-colors duration-200"
            >
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700">
                            <tr class="text-left text-slate-600 dark:text-slate-300">
                                <th class="px-4 py-3 font-semibold">ID</th>
                                <th class="px-4 py-3 font-semibold">Código</th>
                                <th class="px-4 py-3 font-semibold">Nombre</th>
                                <th class="px-4 py-3 font-semibold">Usuario Asignado</th>
                                <th class="px-4 py-3 font-semibold">Gerencia</th>
                                <th class="px-4 py-3 font-semibold">Fecha Expiración</th>
                                <th class="px-4 py-3 font-semibold">Estado</th>
                                <th class="px-4 py-3 font-semibold text-right">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr
                                v-for="t in tarjetas.data"
                                :key="t.id"
                                class="text-slate-800 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors duration-200"
                            >
                                <td class="px-4 py-3">{{ t.id }}</td>
                                <td class="px-4 py-3">
                                    <code class="px-2 py-1 bg-slate-100 dark:bg-slate-700 text-slate-900 dark:text-slate-100 rounded text-xs font-mono">
                                        {{ t.codigo }}
                                    </code>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ t.nombre || "-" }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div v-if="t.user">
                                        <div class="font-medium">{{ t.user.name }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ t.user.email }}
                                            <span v-if="t.user.n_identidad"> · CC: {{ t.user.n_identidad }}</span>
                                        </div>
                                    </div>
                                    <span v-else class="text-slate-400 dark:text-slate-500">Sin asignar</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    <div v-if="t.gerencia">
                                        <div class="font-medium">{{ t.gerencia.secretaria?.nombre || "-" }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ t.gerencia.nombre }}
                                        </div>
                                    </div>
                                    <span v-else>-</span>
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ t.fecha_expiracion || "-" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="[
                                            'inline-flex px-2 py-0.5 rounded-full text-xs font-semibold',
                                            t.activo
                                                ? 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400'
                                                : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
                                        ]"
                                    >
                                        {{ t.activo ? "Activa" : "Inactiva" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link
                                            :href="route('tarjetas-nfc.show', { tarjetaNfc: t.id })"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                                        >
                                            Ver
                                        </Link>
                                        <button
                                            v-if="puedeEliminarTarjetaNfc"
                                            type="button"
                                            @click.prevent="eliminarTarjeta(t)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-md border border-red-200 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-700 dark:text-red-300 transition-colors duration-200"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="tarjetas.data.length === 0">
                                <td
                                    class="px-4 py-10 text-center text-slate-500 dark:text-slate-400"
                                    colspan="8"
                                >
                                    No hay tarjetas NFC registradas.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="tarjetas.links?.length"
                    class="flex items-center justify-between px-4 py-3 border-t border-slate-200 dark:border-slate-700 transition-colors duration-200"
                >
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        Mostrando {{ tarjetas.from || 0 }} - {{ tarjetas.to || 0 }} de
                        {{ tarjetas.total || 0 }}
                    </div>
                    <div class="flex gap-1 flex-wrap justify-end">
                        <Link
                            v-for="(l, idx) in tarjetas.links"
                            :key="idx"
                            :href="l.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-md text-sm border transition-colors duration-200',
                                l.active
                                    ? 'bg-slate-900 dark:bg-slate-700 text-white border-slate-900 dark:border-slate-700'
                                    : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700',
                                !l.url ? 'opacity-40 pointer-events-none' : '',
                            ]"
                            v-html="l.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación para Eliminar Tarjeta NFC -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                @click="closeDeleteModal"
                class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-[9999] p-4 transition-colors duration-200"
            >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                @click.stop
            >
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        Confirmar Eliminación
                    </h3>
                    <button
                        type="button"
                        @click="closeDeleteModal"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-2">
                            ¿Estás seguro de que deseas eliminar la tarjeta NFC
                            <strong class="text-slate-900 dark:text-slate-100">{{ tarjetaToDelete?.codigo }}</strong>?
                        </p>
                        <p v-if="tarjetaToDelete?.nombre" class="text-xs text-slate-600 dark:text-slate-400 mb-2">
                            Nombre: {{ tarjetaToDelete.nombre }}
                        </p>
                        <p v-if="tarjetaToDelete?.user" class="text-xs text-slate-600 dark:text-slate-400 mb-2">
                            Usuario asignado: {{ tarjetaToDelete.user.name }}
                        </p>
                        <p class="text-xs text-red-600 dark:text-red-400 font-medium">
                            ⚠️ Esta acción no se puede deshacer.
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="closeDeleteModal"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="confirmDelete"
                            :disabled="deleteForm.processing"
                            class="px-4 py-2 rounded-lg bg-red-600 dark:bg-red-700 text-white hover:bg-red-700 dark:hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed font-medium transition-colors duration-200"
                        >
                            {{ deleteForm.processing ? "Eliminando..." : "Eliminar Tarjeta" }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </Teleport>

        <!-- Sistema de Notificaciones -->
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-x-full"
            enter-to-class="opacity-100 translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-x-0"
            leave-to-class="opacity-0 translate-x-full"
        >
            <div
                v-if="notification.show"
                class="fixed top-4 right-4 z-50 max-w-md"
            >
                <div
                    :class="[
                        'rounded-xl border shadow-lg p-4 flex items-start gap-3 transition-colors duration-200',
                        notification.type === 'success'
                            ? 'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-800'
                            : notification.type === 'error'
                            ? 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-800'
                            : notification.type === 'warning'
                            ? 'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-800'
                            : 'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800',
                    ]"
                >
                    <div class="shrink-0">
                        <span
                            v-if="notification.type === 'success'"
                            class="text-2xl"
                        >
                            ✅
                        </span>
                        <span
                            v-else-if="notification.type === 'error'"
                            class="text-2xl"
                        >
                            ❌
                        </span>
                        <span
                            v-else-if="notification.type === 'warning'"
                            class="text-2xl"
                        >
                            ⚠️
                        </span>
                        <span v-else class="text-2xl">ℹ️</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            :class="[
                                'text-sm font-medium',
                                notification.type === 'success'
                                    ? 'text-green-800 dark:text-green-200'
                                    : notification.type === 'error'
                                    ? 'text-red-800 dark:text-red-200'
                                    : notification.type === 'warning'
                                    ? 'text-yellow-800 dark:text-yellow-200'
                                    : 'text-blue-800 dark:text-blue-200',
                            ]"
                        >
                            {{ notification.message }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="notification.show = false"
                        :class="[
                            'shrink-0 transition-colors',
                            notification.type === 'success'
                                ? 'text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200'
                                : notification.type === 'error'
                                ? 'text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-200'
                                : notification.type === 'warning'
                                ? 'text-yellow-600 dark:text-yellow-400 hover:text-yellow-800 dark:hover:text-yellow-200'
                                : 'text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200',
                        ]"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>
            </div>
        </Transition>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link, usePage, useForm, router } from "@inertiajs/vue3";
import { computed, ref, Teleport, Transition, onMounted, watch } from "vue";

const props = defineProps({
    tarjetas: Object,
    filters: Object,
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const currentUser = computed(() => page.props.auth?.user || page.props.user);
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const puedeEliminarTarjetaNfc = computed(() => {
    return userPermissions.value.includes("delete_tarjetas_nfc");
});

const searchForm = useForm({
    search: props.filters?.search || "",
});

const showDeleteModal = ref(false);
const tarjetaToDelete = ref(null);

const deleteForm = useForm({});

// Sistema de notificaciones
const notification = ref({
    show: false,
    type: "success", // success, error, warning, info
    message: "",
});

const showNotification = (message, type = "success") => {
    notification.value = {
        show: true,
        type,
        message,
    };
    // Auto-cerrar después de 5 segundos para success/info, 7 para error/warning
    setTimeout(() => {
        notification.value.show = false;
    }, type === "error" || type === "warning" ? 7000 : 5000);
};

const applySearch = () => {
    searchForm.get(route("tarjetas-nfc.index"), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchForm.search = "";
    applySearch();
};

const eliminarTarjeta = (tarjeta) => {
    tarjetaToDelete.value = tarjeta;
    deleteForm.reset();
    deleteForm.clearErrors();
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    tarjetaToDelete.value = null;
    deleteForm.reset();
    deleteForm.clearErrors();
};

const confirmDelete = () => {
    if (!tarjetaToDelete.value) return;

    router.delete(route("tarjetas-nfc.destroy", { tarjetaNfc: tarjetaToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            closeDeleteModal();
            showNotification("Tarjeta NFC eliminada exitosamente.", "success");
        },
        onError: (errors) => {
            const errorMessage = errors?.message || "Error al eliminar la tarjeta NFC. Intenta nuevamente.";
            showNotification(errorMessage, "error");
        },
    });
};

// Detectar y mostrar mensajes flash al cargar la página
watch(
    () => page.props.flash,
    (newFlash) => {
        if (newFlash?.message) {
            showNotification(newFlash.message, "success");
        } else if (newFlash?.success) {
            showNotification(newFlash.success, "success");
        }
    },
    { immediate: true }
);

onMounted(() => {
    // También verificar al montar el componente
    const flash = page.props.flash;
    if (flash?.message) {
        showNotification(flash.message, "success");
    } else if (flash?.success) {
        showNotification(flash.success, "success");
    }
});
</script>
