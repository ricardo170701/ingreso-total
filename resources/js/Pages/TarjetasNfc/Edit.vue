<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-xl font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Editar Tarjeta NFC
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        Modifica los datos de la tarjeta NFC.
                    </p>
                </div>
                <Link
                    :href="route('tarjetas-nfc.index')"
                    class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                >
                    Volver
                </Link>
            </div>

            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-6 transition-colors duration-200"
            >
                <form
                    @submit.prevent="showConfirmModal = true"
                    class="grid grid-cols-1 gap-4"
                >
                    <FormField
                        label="Código NFC (UID)"
                        :error="form.errors.codigo"
                    >
                        <input
                            v-model="form.codigo"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            required
                        />
                    </FormField>

                    <FormField
                        label="Nombre (opcional)"
                        :error="form.errors.nombre"
                    >
                        <input
                            v-model="form.nombre"
                            type="text"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                    </FormField>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <FormField
                            label="Usuario Asignado (opcional)"
                            :error="form.errors.user_id"
                        >
                            <div class="relative">
                                <input
                                    v-model="userPickerQuery"
                                    type="text"
                                    @focus="openUserPicker"
                                    @keydown.down.prevent="userPickerMove(1)"
                                    @keydown.up.prevent="userPickerMove(-1)"
                                    @keydown.enter.prevent="
                                        userPickerSelectActive
                                    "
                                    @keydown.esc.prevent="closeUserPicker"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                                    placeholder="Buscar por nombre, email o cédula…"
                                    autocomplete="off"
                                />
                                <input type="hidden" v-model="form.user_id" />

                                <div
                                    v-if="userPickerOpen"
                                    class="absolute z-30 mt-1 w-full rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-lg overflow-hidden max-h-60 overflow-y-auto"
                                >
                                    <div
                                        v-if="
                                            filteredUsuariosForPicker.length ===
                                            0
                                        "
                                        class="px-3 py-3 text-sm text-slate-500 dark:text-slate-400"
                                    >
                                        Sin resultados
                                    </div>
                                    <button
                                        v-for="(
                                            u, idx
                                        ) in filteredUsuariosForPicker"
                                        :key="u.id"
                                        type="button"
                                        @click="selectUsuarioFromPicker(u)"
                                        @mouseenter="
                                            userPickerActiveIndex = idx
                                        "
                                        class="w-full text-left px-3 py-2 flex items-center justify-between gap-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors"
                                        :class="
                                            idx === userPickerActiveIndex
                                                ? 'bg-slate-50 dark:bg-slate-700'
                                                : ''
                                        "
                                    >
                                        <div class="min-w-0">
                                            <div
                                                class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate"
                                            >
                                                {{ u.name || u.email }}
                                            </div>
                                            <div
                                                class="text-xs text-slate-500 dark:text-slate-400 truncate"
                                            >
                                                <span v-if="u.n_identidad"
                                                    >CC:
                                                    {{ u.n_identidad }}</span
                                                >
                                                <span v-else>Sin cédula</span>
                                                <span v-if="u.role">
                                                    · {{ u.role.name }}</span
                                                >
                                            </div>
                                        </div>
                                        <div
                                            class="text-xs text-slate-400 dark:text-slate-500 shrink-0"
                                        >
                                            #{{ u.id }}
                                        </div>
                                    </button>
                                </div>
                            </div>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Puedes asignar la tarjeta a un usuario
                                (visitante, servidor público o contratista).
                                Busca por nombre, email o número de cédula.
                            </p>
                        </FormField>

                        <FormField
                            label="Fecha de Expiración (opcional)"
                            :error="form.errors.fecha_expiracion"
                        >
                            <input
                                v-model="form.fecha_expiracion"
                                type="datetime-local"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                            />
                        </FormField>
                    </div>

                    <FormField
                        label="Observaciones"
                        :error="form.errors.observaciones"
                    >
                        <textarea
                            v-model="form.observaciones"
                            rows="3"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-green-500 dark:focus:ring-green-400 focus:border-transparent transition-colors duration-200"
                        />
                    </FormField>

                    <div class="flex items-center gap-6 pt-2">
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.activo"
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-green-600 dark:text-green-400 focus:ring-green-500 dark:focus:ring-green-400"
                            />
                            <span
                                class="text-sm text-slate-700 dark:text-slate-300"
                                >Activa</span
                            >
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="button"
                            @click="showConfirmModal = true"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 transition-colors duration-200"
                        >
                            {{
                                form.processing
                                    ? "Guardando..."
                                    : "Guardar Cambios"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal de Confirmación -->
        <div
            v-if="showConfirmModal"
            @click="showConfirmModal = false"
            class="fixed inset-0 bg-black/60 dark:bg-black/70 flex items-center justify-center z-50 p-4 transition-colors duration-200"
        >
            <div
                class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl max-w-md w-full border border-slate-200 dark:border-slate-700 transition-colors duration-200"
                @click.stop
            >
                <div
                    class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-700"
                >
                    <h3
                        class="text-lg font-semibold text-slate-900 dark:text-slate-100"
                    >
                        Confirmar Edición
                    </h3>
                    <button
                        type="button"
                        @click="showConfirmModal = false"
                        class="w-9 h-9 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200 flex items-center justify-center"
                        aria-label="Cerrar"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <p class="text-sm text-slate-700 dark:text-slate-300 mb-4">
                        ¿Estás seguro de que deseas editar la tarjeta NFC
                        <strong class="text-slate-900 dark:text-slate-100">{{
                            tarjeta.codigo
                        }}</strong
                        >?
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">
                        Los cambios se guardarán y se aplicarán inmediatamente.
                    </p>

                    <div class="flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="showConfirmModal = false"
                            class="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors duration-200"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="confirmSubmit"
                            :disabled="form.processing"
                            class="px-4 py-2 rounded-lg bg-slate-900 dark:bg-slate-700 text-white hover:bg-slate-800 dark:hover:bg-slate-600 disabled:opacity-50 font-medium transition-colors duration-200"
                        >
                            {{
                                form.processing
                                    ? "Guardando..."
                                    : "Sí, Guardar Cambios"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { ref, computed, watch } from "vue";

const props = defineProps({
    tarjeta: Object,
    usuarios: Array,
});

const showConfirmModal = ref(false);

const form = useForm({
    codigo: props.tarjeta?.codigo || "",
    nombre: props.tarjeta?.nombre || "",
    user_id: props.tarjeta?.user_id || null,
    gerencia_id: props.tarjeta?.gerencia_id || null,
    fecha_expiracion: props.tarjeta?.fecha_expiracion || null,
    activo: props.tarjeta?.activo ?? true,
    observaciones: props.tarjeta?.observaciones || "",
});

// Selector buscable de usuarios
const userPickerOpen = ref(false);
const userPickerQuery = ref("");
const userPickerActiveIndex = ref(0);

const formatUsuarioLabel = (u) => {
    if (!u) return "";
    const base = u.name || u.email || "";
    const cc = u.n_identidad ? ` (CC: ${u.n_identidad})` : "";
    return `${base}${cc}`.trim();
};

const openUserPicker = () => {
    userPickerOpen.value = true;
};

const closeUserPicker = () => {
    userPickerOpen.value = false;
    userPickerActiveIndex.value = 0;
};

const filteredUsuariosForPicker = computed(() => {
    const q = String(userPickerQuery.value || "")
        .trim()
        .toLowerCase();
    let arr = props.usuarios || [];
    if (!q) return arr.slice(0, 50);

    const matches = (u) => {
        const name = String(u?.name || "").toLowerCase();
        const email = String(u?.email || "").toLowerCase();
        const cc = String(u?.n_identidad || "").toLowerCase();
        return name.includes(q) || email.includes(q) || cc.includes(q);
    };

    return arr.filter(matches).slice(0, 50);
});

const selectUsuarioFromPicker = (u) => {
    if (!u) return;
    form.user_id = u.id;
    userPickerQuery.value = formatUsuarioLabel(u);
    closeUserPicker();
};

const userPickerMove = (delta) => {
    if (!userPickerOpen.value) {
        openUserPicker();
    }
    const len = filteredUsuariosForPicker.value.length;
    if (len <= 0) return;
    const next = (userPickerActiveIndex.value + delta + len) % len;
    userPickerActiveIndex.value = next;
};

const userPickerSelectActive = () => {
    const u = filteredUsuariosForPicker.value[userPickerActiveIndex.value];
    if (u) selectUsuarioFromPicker(u);
};

// Mantener input sincronizado cuando cambia el user_id
watch(
    () => form.user_id,
    (id) => {
        const u = props.usuarios?.find((x) => x.id === id);
        if (u) {
            userPickerQuery.value = formatUsuarioLabel(u);
        } else if (!id) {
            userPickerQuery.value = "";
        }
    },
    { immediate: true }
);

const confirmSubmit = () => {
    showConfirmModal.value = false;
    form.put(route("tarjetas-nfc.update", { tarjetaNfc: props.tarjeta.id }));
};
</script>
