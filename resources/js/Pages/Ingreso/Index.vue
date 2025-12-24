<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">
                    Generar Código QR de Ingreso
                </h1>
                <p class="text-sm text-slate-600">
                    Genera un código QR temporal (24 horas) para acceso al
                    edificio.
                </p>
            </div>

            <div
                v-if="Object.keys($page.props.errors || {}).length > 0"
                class="p-4 rounded-lg bg-red-50 border border-red-200"
            >
                <ul class="list-disc list-inside text-sm text-red-800">
                    <li v-for="(error, key) in $page.props.errors" :key="key">
                        <span v-if="Array.isArray(error)">{{ error[0] }}</span>
                        <span v-else>{{ error }}</span>
                    </li>
                </ul>
            </div>

            <!-- Formulario de generación -->
            <div class="bg-white border border-slate-200 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    Datos del QR
                </h2>
                <form @submit.prevent="submit" class="grid grid-cols-1 gap-4">
                    <FormField label="Usuario" :error="form.errors.user_id">
                        <select
                            v-model="form.user_id"
                            class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                            required
                        >
                            <option :value="null">Selecciona un usuario</option>
                            <option
                                v-for="u in usuarios"
                                :key="u.id"
                                :value="u.id"
                            >
                                {{ u.name || u.email }}
                                <span v-if="u.role"> - {{ u.role.name }}</span>
                                <span v-if="u.cargo">
                                    ({{ u.cargo.name }})</span
                                >
                            </option>
                        </select>
                    </FormField>

                    <!-- Mostrar selector de puertas si el usuario seleccionado es visitante -->
                    <div
                        v-if="usuarioSeleccionado?.role?.name === 'visitante'"
                        class="space-y-4"
                    >
                        <FormField
                            label="Puertas (obligatorio para visitantes)"
                            :error="form.errors.puertas"
                        >
                            <div
                                class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3"
                            >
                                <label
                                    v-for="p in puertas"
                                    :key="p.id"
                                    class="flex items-center gap-2 p-2 hover:bg-slate-50 rounded"
                                >
                                    <input
                                        type="checkbox"
                                        :value="p.id"
                                        v-model="form.puertas"
                                        class="h-4 w-4"
                                    />
                                    <div class="flex-1">
                                        <span class="font-medium">{{
                                            p.nombre
                                        }}</span>
                                        <span
                                            v-if="p.zona"
                                            class="text-sm text-slate-500 ml-2"
                                        >
                                            - {{ p.zona.nombre }}
                                        </span>
                                    </div>
                                </label>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Selecciona al menos una puerta para el
                                visitante.
                            </p>
                        </FormField>
                    </div>

                    <!-- Opciones avanzadas (horarios) -->
                    <div class="border-t border-slate-200 pt-4">
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">
                            Opciones de Horario (Opcional)
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <FormField
                                label="Hora Inicio"
                                :error="form.errors.hora_inicio"
                            >
                                <input
                                    v-model="form.hora_inicio"
                                    type="time"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </FormField>
                            <FormField
                                label="Hora Fin"
                                :error="form.errors.hora_fin"
                            >
                                <input
                                    v-model="form.hora_fin"
                                    type="time"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </FormField>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <FormField
                                label="Fecha Inicio"
                                :error="form.errors.fecha_inicio"
                            >
                                <input
                                    v-model="form.fecha_inicio"
                                    type="date"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </FormField>
                            <FormField
                                label="Fecha Fin"
                                :error="form.errors.fecha_fin"
                            >
                                <input
                                    v-model="form.fecha_fin"
                                    type="date"
                                    class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                />
                            </FormField>
                        </div>
                        <FormField
                            label="Días de la Semana"
                            :error="form.errors.dias_semana"
                        >
                            <input
                                v-model="form.dias_semana"
                                type="text"
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                placeholder="Ej: 1,2,3,4,5 (1=Lunes, 7=Domingo)"
                            />
                            <p class="mt-1 text-xs text-slate-500">
                                Deja vacío para todos los días. Formato: números
                                separados por comas (1-7)
                            </p>
                        </FormField>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 font-medium"
                        >
                            {{
                                form.processing ? "Generando..." : "Generar QR"
                            }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resultado: QR generado -->
            <div
                v-if="qrGenerado"
                class="bg-white border border-slate-200 rounded-xl p-6"
            >
                <h2 class="text-lg font-semibold text-slate-900 mb-4">
                    QR Generado Exitosamente
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div
                            class="bg-white rounded-xl p-4 inline-block border border-slate-200"
                        >
                            <div
                                v-if="typeof qrGenerado.svg === 'string'"
                                v-html="qrGenerado.svg"
                            ></div>
                            <div v-else class="text-red-600 text-sm">
                                Error: El SVG no se generó correctamente. Tipo:
                                {{ typeof qrGenerado.svg }}
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <p>
                                <span class="font-medium">Usuario:</span>
                                {{ qrGenerado.user_name }}
                            </p>
                            <p>
                                <span class="font-medium">Código:</span>
                                <code
                                    class="px-2 py-1 bg-slate-100 rounded text-xs ml-2"
                                >
                                    {{ qrGenerado.token }}
                                </code>
                            </p>
                            <p>
                                <span class="font-medium">Expira:</span>
                                {{ qrGenerado.expires_at_formatted }}
                            </p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="font-semibold text-slate-900 mb-2">
                                Instrucciones
                            </h3>
                            <ul
                                class="text-sm text-slate-600 space-y-1 list-disc list-inside"
                            >
                                <li>
                                    El código QR es válido por 24 horas desde su
                                    generación.
                                </li>
                                <li>
                                    El usuario puede usar este QR para acceder a
                                    las puertas autorizadas.
                                </li>
                                <li>
                                    Puedes descargar o imprimir el código QR
                                    mostrado.
                                </li>
                            </ul>
                        </div>
                        <div class="flex flex-col gap-2">
                            <button
                                @click="enviarCorreo"
                                :disabled="enviandoCorreo"
                                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {{
                                    enviandoCorreo
                                        ? "Enviando..."
                                        : "Enviar por Correo"
                                }}
                            </button>
                            <button
                                @click="descargarQR"
                                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-center"
                            >
                                Descargar QR
                            </button>
                            <button
                                @click="generarNuevo"
                                class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700"
                            >
                                Generar Nuevo QR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import FormField from "@/Components/FormField.vue";
import { computed, ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";

const props = defineProps({
    usuarios: Array,
    puertas: Array,
    qrGenerado: Object,
});

const form = useForm({
    user_id: null,
    puertas: [],
    hora_inicio: null,
    hora_fin: null,
    dias_semana: "1,2,3,4,5,6,7",
    fecha_inicio: null,
    fecha_fin: null,
});

const enviandoCorreo = ref(false);

const usuarioSeleccionado = computed(() => {
    if (!form.user_id) return null;
    return props.usuarios.find((u) => u.id === form.user_id);
});

const submit = () => {
    form.post(route("ingreso.generate"), {
        preserveScroll: true,
    });
};

const enviarCorreo = () => {
    if (!props.qrGenerado) return;

    const email = prompt(
        "Ingresa el correo electrónico donde enviar el QR:",
        props.qrGenerado.user_email || ""
    );

    if (!email) return;

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert("Por favor ingresa un correo electrónico válido.");
        return;
    }

    enviandoCorreo.value = true;

    router.post(
        route("ingreso.send-email", { qr: props.qrGenerado.id }),
        {
            email,
            token: props.qrGenerado.token,
        },
        {
            preserveScroll: true,
            onFinish: () => {
                enviandoCorreo.value = false;
            },
        }
    );
};

const descargarQR = () => {
    if (!props.qrGenerado) return;

    const url = route("ingreso.download", {
        qr: props.qrGenerado.id,
        token: props.qrGenerado.token,
    });

    window.open(url, "_blank");
};

const generarNuevo = () => {
    form.reset();
    form.user_id = null;
    form.puertas = [];
};
</script>
