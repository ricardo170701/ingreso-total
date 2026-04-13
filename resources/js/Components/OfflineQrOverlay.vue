<template>
    <Teleport to="body">
        <div
            v-if="showOfflineQrOverlay"
            class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-slate-900/95 text-white p-6 text-center"
            role="alert"
        >
            <p class="text-lg font-semibold mb-1">Sin conexión a Internet</p>
            <p class="text-sm text-slate-300 mb-6 max-w-md">
                No hay red disponible. Si antes viste tu código en <strong>Ingreso</strong> en este dispositivo, puedes usar el último QR guardado.
                El acceso en portería depende de que el código siga vigente en el servidor.
            </p>
            <div
                v-if="offlineQrForDisplay?.svg"
                class="bg-white rounded-xl p-4 max-w-sm w-full"
            >
                <p
                    v-if="offlineQrSessionUnknown"
                    class="text-amber-800 text-xs mb-3 text-left bg-amber-50 border border-amber-200 rounded px-2 py-1.5"
                >
                    No se pudo verificar tu sesión sin conexión. Comprueba que este QR sea el tuyo antes de usarlo.
                </p>
                <div
                    class="[&>svg]:max-w-full [&>svg]:h-auto"
                    v-html="offlineQrForDisplay.svg"
                />
                <p class="text-slate-800 text-sm mt-3 text-left">
                    <span class="font-medium">Usuario:</span> {{ offlineQrForDisplay.userName }}
                </p>
                <p class="text-slate-800 text-xs mt-1 font-mono break-all text-left">
                    {{ offlineQrForDisplay.token }}
                </p>
                <p class="text-slate-600 text-xs mt-2 text-left">
                    Expira: {{ offlineQrForDisplay.expiresAtFormatted || "—" }}
                </p>
                <p class="text-amber-700 text-xs mt-3">
                    Puede estar vencido: al recuperar la conexión, genera un nuevo código en Ingreso si no te dejan pasar.
                </p>
            </div>
            <p v-else class="text-slate-400 text-sm max-w-md">
                No hay un código guardado en este dispositivo. Conéctate a Internet, entra a <strong>Ingreso</strong> y vuelve a cargar tu QR.
            </p>
            <button
                v-if="isOffline"
                type="button"
                class="mt-8 px-4 py-2 rounded-lg border border-slate-500 text-slate-200 hover:bg-slate-800"
                @click="offlineDismissed = true"
            >
                Cerrar aviso (vista normal)
            </button>
        </div>
    </Teleport>
</template>

<script setup>
import { loadOfflineQr } from "@/Support/offlineQrStorage.js";
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const currentUser = computed(() => page.props.auth?.user || page.props.user);

const OFFLINE_LAST_AUTH_ID_KEY = "escaner_total_last_auth_id";

watch(
    () => currentUser.value?.id,
    (id) => {
        if (id == null || typeof sessionStorage === "undefined") return;
        try {
            sessionStorage.setItem(OFFLINE_LAST_AUTH_ID_KEY, String(id));
        } catch {
            /* ignore */
        }
    },
    { immediate: true },
);

const navigatorOnLine = ref(typeof navigator !== "undefined" ? navigator.onLine : true);
const offlineDismissed = ref(false);
const isOffline = computed(() => !navigatorOnLine.value);

const offlineQrForDisplay = computed(() => {
    const data = loadOfflineQr();
    if (!data?.svg || typeof data.userId !== "number") return null;

    let uid = currentUser.value?.id ?? null;
    if (uid == null && isOffline.value && typeof sessionStorage !== "undefined") {
        try {
            const raw = sessionStorage.getItem(OFFLINE_LAST_AUTH_ID_KEY);
            const parsed = raw != null ? parseInt(raw, 10) : NaN;
            uid = Number.isFinite(parsed) ? parsed : null;
        } catch {
            uid = null;
        }
    }

    if (uid != null) {
        return data.userId === uid ? data : null;
    }

    if (isOffline.value) {
        return data;
    }

    return null;
});

const offlineQrSessionUnknown = computed(
    () =>
        isOffline.value &&
        !!offlineQrForDisplay.value?.svg &&
        currentUser.value?.id == null,
);

const showOfflineQrOverlay = computed(() => {
    if (!isOffline.value) return false;
    if (offlineDismissed.value) return false;
    return true;
});

const onBrowserOnline = () => {
    navigatorOnLine.value = true;
    offlineDismissed.value = false;
};
const onBrowserOffline = () => {
    navigatorOnLine.value = false;
};

onMounted(() => {
    if (typeof window !== "undefined") {
        window.addEventListener("online", onBrowserOnline);
        window.addEventListener("offline", onBrowserOffline);
        if (typeof navigator !== "undefined") {
            navigatorOnLine.value = navigator.onLine;
        }
    }
});

onUnmounted(() => {
    if (typeof window !== "undefined") {
        window.removeEventListener("online", onBrowserOnline);
        window.removeEventListener("offline", onBrowserOffline);
    }
});
</script>
