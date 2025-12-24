<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto">
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
            >
                <h1 class="text-xl font-semibold mb-3 text-slate-900">
                    Generador de QR (básico)
                </h1>
                <p class="text-slate-600 mb-6">
                    Escribe un texto/código y el sistema genera un QR en SVG
                    para pruebas rápidas con el lector.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Formulario -->
                    <div>
                        <form @submit.prevent="submit">
                            <label
                                class="block font-semibold mb-2 text-slate-700"
                                >Código / Texto</label
                            >
                            <input
                                v-model="form.codigo"
                                type="text"
                                placeholder="Ej: 0123456789ABCDEF..."
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            />
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-colors"
                            >
                                {{
                                    form.processing
                                        ? "Generando..."
                                        : "Generar QR"
                                }}
                            </button>
                        </form>

                        <div
                            v-if="form.errors.codigo"
                            class="mt-3 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700"
                        >
                            {{ form.errors.codigo }}
                        </div>

                        <div class="mt-4 text-xs text-slate-500">
                            Tip: si quieres probar el flujo real del sistema,
                            pega aquí el
                            <code
                                class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700"
                                >data.token</code
                            >
                            que retorna
                            <code
                                class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700"
                                >POST /api/qrs</code
                            >.
                        </div>
                    </div>

                    <!-- Preview QR -->
                    <div>
                        <div
                            v-if="svg"
                            class="bg-white rounded-xl p-4 inline-block border border-slate-200"
                        >
                            <div v-html="svg"></div>
                        </div>
                        <div v-else class="text-slate-500 text-sm">
                            Aún no has generado ningún QR.
                        </div>
                        <div v-if="codigo" class="mt-3 text-xs text-slate-500">
                            Contenido actual:
                            <code
                                class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700"
                                >{{ codigo }}</code
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    codigo: String,
    svg: String,
});

const form = useForm({
    codigo: props.codigo || "",
});

const submit = () => {
    form.post(route("qr.tool.generate"), {
        preserveScroll: true,
    });
};
</script>
